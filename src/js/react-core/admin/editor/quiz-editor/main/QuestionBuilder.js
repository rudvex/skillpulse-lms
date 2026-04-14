import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Component, Fragment } from '@wordpress/element';
import { 
    Button,
    Icon,
    Spinner,
    Card,
    CardBody,
    CardHeader,
    SelectControl,
    Modal
} from '@wordpress/components';
import { SplmsIcon } from '../../../../components/SplmsIcon';
import Field from '../../../../components/Field';
import MatchingEditor from '../../../../components/MatchingEditor';
import OrderingField from '../../../../components/OrderingField';
import { getQuizQuestions, updateQuizQuestions } from '../../../../utility/apis/quiz-api';
import { getConfig, getConfigSync, translateLabel } from '../../../../utility/helper';

class QuestionBuilder extends Component {
    constructor(props) {
        super(props);

        this.state = {
            config: null,
            configLoading: true,
            configError: null,
            isLoading: false,
            isSaving: false,
            questions: [],
            currentQuestion: null,
            showAddQuestionModal: false,
            editingQuestionIndex: null,
            newQuestion: null
        };
    }

    /**
     * Load configuration from the new Config Loader API
     */
    async loadConfiguration() {
        try {
            this.setState({ configLoading: true, configError: null });

            // Try to get config synchronously first (from cache)
            const syncConfig = getConfigSync('question_builder_config');
            if (syncConfig && syncConfig.types) {
                this.setState({
                    config: syncConfig,
                    configLoading: false
                });
                return;
            }

            // Fall back to async loading
            const config = await getConfig('question_builder_config', 'admin');

            if (!config || !config.types) {
                this.setState({
                    config: null,
                    configLoading: false,
                    configError: 'Question builder configuration not found'
                });
                return;
            }

            this.setState({
                config,
                configLoading: false
            });

        } catch (error) {
            console.error('Failed to load question builder configuration:', error);
            this.setState({
                config: null,
                configLoading: false,
                configError: error.message || 'Failed to load configuration'
            });
        }
    }

    // Get default question structure from config
    getDefaultQuestion = (type, config) => {
        const questionType = config.types?.find(t => t.type === type) || config.types?.[0];
        const defaultQuestion = {
            question_id: null, // Use question_id instead of id
            type: type || 'multiple_choice',
            question: '',
            description: '',
            options: [],
            correct_answer: '',
            points: 1,
            required: true,
            explanation: '',
            media: {
                type: 'none',
                url: ''
            },
            settings: {
                randomize_options: false,
                partial_credit: false,
                case_sensitive: false
            }
        };
        
        // Apply defaults from config
        if (questionType && questionType.fields) {
            questionType.fields.forEach(field => {
                if (field.default !== undefined) {
                    if (field.type === 'repeatable-group') {
                        // Initialize repeatable groups (options, pairs, items)
                        const minItems = field.min || 2;
                        const fieldId = field.id;
                        defaultQuestion[fieldId] = Array(minItems).fill(null).map(() => {
                            const item = {};
                            if (field.item_fields) {
                                field.item_fields.forEach(itemField => {
                                    item[itemField.id] = itemField.default !== undefined 
                                        ? itemField.default 
                                        : (itemField.type === 'checkbox' ? false : '');
                                });
                            }
                            return item;
                        });
                    } else {
                        defaultQuestion[field.id] = field.default;
                    }
                }
            });
        }
        
        // Ensure proper structure for each type
        if (type === 'multiple_choice' && (!defaultQuestion.options || defaultQuestion.options.length === 0)) {
            defaultQuestion.options = [
                { id: null, text: '', is_correct: false },
                { id: null, text: '', is_correct: false }
            ];
        } else if (type === 'multiple_select' && (!defaultQuestion.options || defaultQuestion.options.length === 0)) {
            defaultQuestion.options = [
                { id: null, text: '', is_correct: false },
                { id: null, text: '', is_correct: false }
            ];
        } else if (type === 'true_false') {
            // Ensure correct_answer is lowercase 'true' or 'false' (no prefixes)
            if (!defaultQuestion.correct_answer || (defaultQuestion.correct_answer !== 'true' && defaultQuestion.correct_answer !== 'false')) {
                // Remove any prefixes like 'skillpulse-lms:'
                const cleanAnswer = String(defaultQuestion.correct_answer || 'true').replace(/^skillpulse-lms:/, '').toLowerCase();
                defaultQuestion.correct_answer = (cleanAnswer === 'true') ? 'true' : 'false';
            }
        } else if (type === 'matching' && (!defaultQuestion.pairs || defaultQuestion.pairs.length === 0)) {
            defaultQuestion.pairs = [
                { left: '', right: '' },
                { left: '', right: '' }
            ];
        } else if (type === 'ordering') {
            if (!defaultQuestion.items || defaultQuestion.items.length === 0) {
                defaultQuestion.items = [
                    { text: '' },
                    { text: '' }
                ];
            }
            // Ensure correct_answer is array for ordering questions
            if (defaultQuestion.type === 'ordering' && !Array.isArray(defaultQuestion.correct_answer)) {
                defaultQuestion.correct_answer = [];
            }
        } else if (type === 'file_upload') {
            defaultQuestion.allowed_types = defaultQuestion.allowed_types || 'pdf,docx,jpg,png';
            defaultQuestion.max_file_size = defaultQuestion.max_file_size || 10;
            defaultQuestion.correct_answer = 'pending_review';
        } else if (type === 'essay') {
            defaultQuestion.correct_answer = '';
        }
        
        return defaultQuestion;
    }

    async componentDidMount() {
        // Load configuration first
        await this.loadConfiguration();

        // Initialize new question with config
        if (this.state.config) {
            this.setState({
                newQuestion: this.getDefaultQuestion('multiple_choice', this.state.config)
            });
        }

        // Load questions
        this.loadQuestions();
    }

    componentDidUpdate(prevProps) {
        // Reload questions if post changes
        if (prevProps.post?.id !== this.props.post?.id) {
            this.loadQuestions();
        }
    }

    loadQuestions = async () => {
        const { post } = this.props;
        if (!post?.id) return;

        this.setState({ isLoading: true });
        
        try {
            const questions = await getQuizQuestions(post.id);
            
            // Normalize questions from API to match QuestionBuilder format
            const normalizedQuestions = (questions || []).map(question => {
                const normalized = { ...question };
                
                // Preserve the database question_id from API
                if (question.question_id) {
                    normalized.question_id = question.question_id;
                }
                // Remove 'id' field if it exists (we only use question_id)
                if (normalized.id) {
                    delete normalized.id;
                }
                
                // Ensure settings is an object
                if (!normalized.settings || typeof normalized.settings !== 'object') {
                    normalized.settings = {
                        randomize_options: false,
                        partial_credit: false,
                        case_sensitive: false
                    };
                }
                
                // Ensure media is an object
                if (!normalized.media || typeof normalized.media !== 'object') {
                    normalized.media = {
                        type: 'none',
                        url: ''
                    };
                }
                
                // Fix true/false correct_answer
                if (normalized.type === 'true_false') {
                    if (normalized.correct_answer !== 'true' && normalized.correct_answer !== 'false') {
                        normalized.correct_answer = 'true';
                    }
                }
                
                // Ensure ordering questions have correct_answer - only rebuild if missing
                if (normalized.type === 'ordering') {
                    if (!normalized.correct_answer || !Array.isArray(normalized.correct_answer) || normalized.correct_answer.length === 0) {
                        if (normalized.items && Array.isArray(normalized.items) && normalized.items.length > 0) {
                            normalized.correct_answer = normalized.items.map(item => {
                                if (!item) return '';
                                const text = (typeof item === 'object' && item !== null && item.text)
                                    ? String(item.text).trim()
                                    : (typeof item === 'string' ? String(item).trim() : '');
                                return text;
                            }).filter(text => text !== '');
                        } else {
                            normalized.correct_answer = [];
                        }
                    }
                }
                
                // Ensure proper structure for each type
                if ((normalized.type === 'multiple_choice' || normalized.type === 'multiple_select')) {
                    // Get correct answer texts ONLY from correct_answer (source of truth).
                    const correctAnswerTexts = [];
                    if (normalized.correct_answer) {
                        if (Array.isArray(normalized.correct_answer)) {
                            if (normalized.type === 'multiple_select') {
                                correctAnswerTexts.push(...normalized.correct_answer);
                            } else {
                                correctAnswerTexts.push(normalized.correct_answer[0] || normalized.correct_answer);
                            }
                        } else {
                            correctAnswerTexts.push(normalized.correct_answer);
                        }
                    }
                    
                    // Ensure options is an array
                    if (!normalized.options || !Array.isArray(normalized.options)) {
                        normalized.options = [
                            { text: '', is_correct: false },
                            { text: '', is_correct: false }
                        ];
                    } else {
                        // Sync is_correct ONLY from correct_answer (source of truth).
                        normalized.options = normalized.options.map(opt => {
                            const optionText = (opt.text || opt.option_text || '').trim();
                            const isCorrect = correctAnswerTexts.includes(optionText);
                            
                            return {
                                id: opt.id || null, // Preserve option ID
                                text: optionText,
                                is_correct: isCorrect // Derived ONLY from correct_answer
                            };
                        });
                        
                        // Ensure at least 2 options
                        if (normalized.options.length < 2) {
                            while (normalized.options.length < 2) {
                                normalized.options.push({ id: null, text: '', is_correct: false });
                            }
                        }
                    }
                }
                
                if (normalized.type === 'matching' && (!normalized.pairs || !Array.isArray(normalized.pairs))) {
                    normalized.pairs = [];
                }
                
                if (normalized.type === 'ordering' && (!normalized.items || !Array.isArray(normalized.items))) {
                    normalized.items = [];
                }
                
                if (normalized.type === 'file_upload') {
                    normalized.allowed_types = normalized.allowed_types || 'pdf,docx,jpg,png';
                    normalized.max_file_size = normalized.max_file_size || 10;
                }
                
                return normalized;
            });
            
            this.setState({
                questions: normalizedQuestions,
                isLoading: false
            });
        } catch (error) {
            console.error('Error loading quiz questions:', error);
            const errorMessage = __('Failed to load quiz questions. Please try again.', 'skillpulse-lms');
            if (window.skillpulseToast) {
                window.skillpulseToast.error(errorMessage);
            }
            this.setState({ 
                isLoading: false
            });
        }
    }

    saveQuestions = async () => {
        const { post } = this.props;
        if (!post?.id) return;

        this.setState({ isSaving: true });

        try {
            // Prepare questions for API: ensure question_id is present, remove any 'id' field
            const questionsToSave = this.state.questions.map(question => {
                const questionData = { ...question };
                // Remove 'id' field if it exists (we only use question_id)
                delete questionData.id;
                // Ensure question_id is set (null for new questions, number for existing)
                if (!questionData.hasOwnProperty('question_id')) {
                    questionData.question_id = null;
                }
                
                // Ensure ordering questions have correct_answer - only rebuild if missing
                if (questionData.type === 'ordering') {
                    if (!questionData.correct_answer || !Array.isArray(questionData.correct_answer) || questionData.correct_answer.length === 0) {
                        // Only rebuild if correct_answer is missing
                        if (questionData.items && Array.isArray(questionData.items) && questionData.items.length > 0) {
                            questionData.correct_answer = questionData.items.map(item => {
                                if (!item) return '';
                                const text = (typeof item === 'object' && item !== null && item.text)
                                    ? String(item.text).trim()
                                    : (typeof item === 'string' ? String(item).trim() : '');
                                return text;
                            }).filter(text => text !== '');
                        } else {
                            questionData.correct_answer = [];
                        }
                    }
                }
                
                return questionData;
            });

            const response = await updateQuizQuestions(post.id, questionsToSave);
            
            // Update questions with real database question_ids and option IDs from response
            if (response && response.questions && Array.isArray(response.questions)) {
                // Match questions by order (API returns questions in the same order they were sent)
                const updatedQuestions = this.state.questions.map((question, index) => {
                    // If response has a question at this index, use its question_id and option IDs
                    if (index < response.questions.length) {
                        const responseQuestion = response.questions[index];
                        if (responseQuestion && responseQuestion.question_id) {
                            // Update with real database question_id (for new questions) or keep existing
                            const updatedQuestion = {
                                ...question,
                                question_id: responseQuestion.question_id
                            };
                            
                            // Update option IDs if available (for multiple_choice/multiple_select)
                            if (responseQuestion.options && Array.isArray(responseQuestion.options) && 
                                (question.type === 'multiple_choice' || question.type === 'multiple_select')) {
                                updatedQuestion.options = question.options.map((opt, optIndex) => {
                                    if (optIndex < responseQuestion.options.length && responseQuestion.options[optIndex].id) {
                                        return {
                                            ...opt,
                                            id: responseQuestion.options[optIndex].id
                                        };
                                    }
                                    return opt;
                                });
                            }
                            
                            return updatedQuestion;
                        }
                    }
                    // If no response question at this index, keep original question
                    return question;
                });
                
                this.setState({ questions: updatedQuestions });
            }
            
            this.setState({
                isSaving: false
            });
            
            if (window.skillpulseToast) {
                window.skillpulseToast.success(__('Questions saved successfully!', 'skillpulse-lms'));
            }
        } catch (error) {
            console.error('Error saving quiz questions:', error);
            const errorMessage = __('Failed to save quiz questions. Please try again.', 'skillpulse-lms');
            if (window.skillpulseToast) {
                window.skillpulseToast.error(errorMessage);
            }
            this.setState({ 
                isSaving: false
            });
        }
    }

    addQuestion = () => {
        this.setState({
            showAddQuestionModal: true,
            editingQuestionIndex: null,
            newQuestion: {
                ...this.getDefaultQuestion('multiple_choice', this.state.config),
                question_id: null // New questions have no question_id until saved
            }
        });
    }

    editQuestion = (index) => {
        const question = this.state.questions[index];
        // Deep copy to avoid mutating original
        let editableQuestion = JSON.parse(JSON.stringify(question));
        
        // Ensure true/false questions have a valid correct_answer
        if (editableQuestion.type === 'true_false' && 
            editableQuestion.correct_answer !== 'true' && 
            editableQuestion.correct_answer !== 'false') {
            editableQuestion.correct_answer = 'true';
        }
        
        // Ensure proper structure for each question type
        if ((editableQuestion.type === 'multiple_choice' || editableQuestion.type === 'multiple_select')) {
            // Get correct answer texts ONLY from correct_answer (source of truth).
            const correctAnswerTexts = [];
            if (editableQuestion.correct_answer) {
                if (Array.isArray(editableQuestion.correct_answer)) {
                    if (editableQuestion.type === 'multiple_select') {
                        correctAnswerTexts.push(...editableQuestion.correct_answer);
                    } else {
                        correctAnswerTexts.push(editableQuestion.correct_answer[0] || editableQuestion.correct_answer);
                    }
                } else {
                    correctAnswerTexts.push(editableQuestion.correct_answer);
                }
            }
            
            // Ensure options is an array
            if (!editableQuestion.options || !Array.isArray(editableQuestion.options)) {
                editableQuestion.options = [
                    { text: '', is_correct: false },
                    { text: '', is_correct: false }
                ];
            } else {
                // Sync is_correct ONLY from correct_answer (source of truth).
                editableQuestion.options = editableQuestion.options.map(opt => {
                    const optionText = (opt.text || opt.option_text || '').trim();
                    const isCorrect = correctAnswerTexts.includes(optionText);
                    
                    return {
                        text: optionText,
                        is_correct: isCorrect // Derived ONLY from correct_answer
                    };
                });
                        // Ensure at least 2 options
                        if (editableQuestion.options.length < 2) {
                            while (editableQuestion.options.length < 2) {
                                editableQuestion.options.push({ text: '', is_correct: false });
                            }
                        }
            }
        } else if (editableQuestion.type === 'matching' && (!editableQuestion.pairs || !Array.isArray(editableQuestion.pairs))) {
            editableQuestion.pairs = [
                { left: '', right: '' },
                { left: '', right: '' }
            ];
        } else if (editableQuestion.type === 'ordering' && (!editableQuestion.items || !Array.isArray(editableQuestion.items))) {
            editableQuestion.items = [
                { text: '' },
                { text: '' }
            ];
        } else if (editableQuestion.type === 'file_upload') {
            editableQuestion.allowed_types = editableQuestion.allowed_types || 'pdf,docx,jpg,png';
            editableQuestion.max_file_size = editableQuestion.max_file_size || 10;
        }
        
        // Ensure settings object exists
        if (!editableQuestion.settings) {
            editableQuestion.settings = {
                randomize_options: false,
                partial_credit: false,
                case_sensitive: false
            };
        }
        
        this.setState({
            showAddQuestionModal: true,
            editingQuestionIndex: index,
            newQuestion: editableQuestion
        });
    }

    deleteQuestion = (index) => {
        if (confirm(__('Are you sure you want to delete this question?', 'skillpulse-lms'))) {
            const questions = [...this.state.questions];
            questions.splice(index, 1);
            this.setState({ questions }, () => {
                this.saveQuestions();
            });
        }
    }

    duplicateQuestion = (index) => {
        const questions = [...this.state.questions];
        const questionToDuplicate = { ...questions[index] };
        questionToDuplicate.question_id = null; // Duplicated questions are new, so no question_id
        questionToDuplicate.question = questionToDuplicate.question + ' (Copy)';
        questions.splice(index + 1, 0, questionToDuplicate);
        this.setState({ questions }, () => {
            this.saveQuestions();
        });
    }

    saveQuestion = () => {
        const { questions, editingQuestionIndex } = this.state;
        let { newQuestion } = this.state;
        
        // Enhanced validation with better user feedback
        if (!newQuestion.question.trim()) {
            if (window.skillpulseToast) {
                window.skillpulseToast.error(__('Please enter a question text.', 'skillpulse-lms'));
            }
            return;
        }

        if (newQuestion.type === 'multiple_choice' || newQuestion.type === 'multiple_select') {
            const hasCorrectOption = newQuestion.options.some(option => option.is_correct);
            const hasValidOptions = newQuestion.options.every(option => option.text && option.text.trim());
            
            if (!hasValidOptions) {
                if (window.skillpulseToast) {
                    window.skillpulseToast.error(__('Please fill in all option texts.', 'skillpulse-lms'));
                }
                return;
            }
            
            if (!hasCorrectOption) {
                if (window.skillpulseToast) {
                    window.skillpulseToast.error(__('Please mark at least one option as correct.', 'skillpulse-lms'));
                }
                return;
            }
        }

        if (newQuestion.type === 'true_false' && !newQuestion.correct_answer) {
            if (window.skillpulseToast) {
                window.skillpulseToast.error(__('Please select the correct answer.', 'skillpulse-lms'));
            }
            return;
        }
        
        if (newQuestion.type === 'matching') {
            // Ensure pairs exist and have at least one pair
            if (!newQuestion.pairs || !Array.isArray(newQuestion.pairs) || newQuestion.pairs.length === 0) {
                if (window.skillpulseToast) {
                    window.skillpulseToast.error(__('Please add at least one matching pair.', 'skillpulse-lms'));
                }
                return;
            }
            
            // Validate that all pairs have non-empty left and right values
            const hasValidPairs = newQuestion.pairs.every(pair => 
                pair && pair.left && pair.left.trim() && pair.right && pair.right.trim()
            );
            
            if (!hasValidPairs) {
                if (window.skillpulseToast) {
                    window.skillpulseToast.error(__('Please fill in all matching pairs. Both left and right items are required.', 'skillpulse-lms'));
                }
                return;
            }
        }
        
        if (newQuestion.type === 'ordering') {
            const hasValidItems = newQuestion.items && newQuestion.items.every(item => 
                item.text && item.text.trim()
            );
            
            if (!hasValidItems) {
                this.setState({ 
                    error: __('Please fill in all ordering items.', 'skillpulse-lms')
                });
                return;
            }
            
            // Ensure order property matches array index before saving
            const questionToSave = { ...newQuestion };
            questionToSave.items = questionToSave.items.map((item, idx) => ({
                ...item,
                order: idx  // Ensure order property matches array position
            }));
            // Ensure correct_answer is set - only rebuild if missing
            if (!questionToSave.correct_answer || !Array.isArray(questionToSave.correct_answer) || questionToSave.correct_answer.length === 0) {
                // Only rebuild if correct_answer is missing
                if (questionToSave.items && Array.isArray(questionToSave.items) && questionToSave.items.length > 0) {
                    questionToSave.correct_answer = questionToSave.items.map(item => {
                        if (!item) return '';
                        const text = (typeof item === 'object' && item !== null && item.text)
                            ? String(item.text).trim()
                            : (typeof item === 'string' ? String(item).trim() : '');
                        return text;
                    }).filter(text => text !== '');
                } else {
                    questionToSave.correct_answer = [];
                }
            }
            newQuestion = questionToSave;
        }

        let updatedQuestions = [...questions];
        
        if (editingQuestionIndex !== null) {
            // Edit existing question
            updatedQuestions[editingQuestionIndex] = { ...newQuestion };
        } else {
            // Add new question
            updatedQuestions.push({ ...newQuestion });
        }

        this.setState({
            questions: updatedQuestions,
            showAddQuestionModal: false,
            editingQuestionIndex: null,
            newQuestion: this.getDefaultQuestion('multiple_choice', this.state.config),
            error: null,
            success: editingQuestionIndex !== null 
                ? __('Question updated successfully!', 'skillpulse-lms')
                : __('Question added successfully!', 'skillpulse-lms')
        }, () => {
            this.saveQuestions();
        });
    }

    cancelQuestion = () => {
        this.setState({
            showAddQuestionModal: false,
            editingQuestionIndex: null,
            newQuestion: this.getDefaultQuestion('multiple_choice', this.state.config)
        });
    }

    updateQuestionField = (fieldId, value) => {
        let updatedQuestion = {
            ...this.state.newQuestion
        };


        // Handle question type changes - reset fields using config
        if (fieldId === 'type') {
            updatedQuestion = {
                ...this.getDefaultQuestion(value, this.state.config),
                id: updatedQuestion.id || Date.now(),
                type: value
            };
        }
        // Handle settings fields that are nested
        else if (fieldId === 'randomize_options') {
            updatedQuestion.settings = {
                ...updatedQuestion.settings,
                randomize_options: value
            };
        } else if (fieldId === 'case_sensitive') {
            updatedQuestion.settings = {
                ...updatedQuestion.settings,
                case_sensitive: value
            };
        } else if (fieldId === 'partial_credit') {
            updatedQuestion.settings = {
                ...updatedQuestion.settings,
                partial_credit: value
            };
        } else {
            // Regular field update
            // For repeatable-group fields (options, pairs, items), ensure we do a deep copy
            // RepeaterField already handles this, but we ensure the value is properly assigned
            if (fieldId === 'options' || fieldId === 'pairs' || fieldId === 'items') {
                // Ensure value is an array
                if (Array.isArray(value)) {
                    // Deep copy the array to avoid reference issues
                    updatedQuestion[fieldId] = value.map(item => {
                        if (typeof item === 'object' && item !== null) {
                            return { ...item };
                        }
                        return item;
                    });
                } else {
                    updatedQuestion[fieldId] = value;
                }
            } else {
                updatedQuestion[fieldId] = value;
            }
        }

        this.setState({
            newQuestion: updatedQuestion
        });
    }

    getQuestionTypeOptions = () => {
        const { config } = this.state;
        if (!config || !config.types || config.types.length === 0) {
            // Default options if config not loaded
            return [
                { label: __('Multiple Choice', 'skillpulse-lms'), value: 'multiple_choice' },
                { label: __('True/False', 'skillpulse-lms'), value: 'true_false' },
                { label: __('Short Answer', 'skillpulse-lms'), value: 'short_answer' },
                { label: __('Essay', 'skillpulse-lms'), value: 'essay' },
                { label: __('Fill in the Blank', 'skillpulse-lms'), value: 'fill_blank' }
            ];
        }
        return config.types.map(type => ({
            label: translateLabel(type.label) || type.label || type.type,
            value: type.type
        }));
    }
    
    // Get fields for current question type from config
    getCurrentQuestionTypeFields = () => {
        const { config, newQuestion } = this.state;
        const questionType = config.types?.find(t => t.type === newQuestion.type);
        return questionType?.fields || [];
    }

    renderQuestionsList = () => {
        const { questions } = this.state;

        if (questions.length === 0) {
            return (
                <div className="no-questions-message">
                    <SplmsIcon mode="wp" name="format-status" size={48} />
                    <h3>{__('No questions yet', 'skillpulse-lms')}</h3>
                    <p>{__('Click "Add Question" to create your first quiz question.', 'skillpulse-lms')}</p>
                </div>
            );
        }

        return questions.map((question, index) => (
            <Card key={question.id} className="question-item">
                <CardHeader>
                    <div className="question-header">
                        <span className="question-number">#{index + 1}</span>
                        <h4 className="question-title">{question.question}</h4>
                        <div className="question-actions">
                            <Button
                                isSecondary
                                size="small"
                                onClick={() => this.editQuestion(index)}
                            >
                                {__('Edit', 'skillpulse-lms')}
                            </Button>
                            <Button
                                isSecondary
                                size="small"
                                onClick={() => this.duplicateQuestion(index)}
                            >
                                {__('Duplicate', 'skillpulse-lms')}
                            </Button>
                            <Button
                                isDestructive
                                size="small"
                                onClick={() => this.deleteQuestion(index)}
                            >
                                {__('Delete', 'skillpulse-lms')}
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardBody>
                    <p><strong>{__('Type:', 'skillpulse-lms')}</strong> {question.type}</p>
                    <p><strong>{__('Points:', 'skillpulse-lms')}</strong> {question.points}</p>
                    {question.description && (
                        <p><strong>{__('Description:', 'skillpulse-lms')}</strong> {question.description}</p>
                    )}
                    {question.type === 'multiple_choice' && (
                        <div className="question-options">
                            <strong>{__('Options:', 'skillpulse-lms')}</strong>
                            <ul>
                                {question.options.map((option, optionIndex) => (
                                    <li key={optionIndex}>
                                        {option.is_correct && <SplmsIcon mode="wp" name="yes" size={16} />}
                                        {option.text}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </CardBody>
            </Card>
        ));
    }


    render() {
        const { config, configLoading, configError, questions, isLoading, showAddQuestionModal, editingQuestionIndex, newQuestion, isSaving } = this.state;
        const { post } = this.props;

        // Show loading if configuration is still loading
        if (configLoading) {
            return (
                <div className="splms-quiz-question-builder">
                    <div className="question-builder-loading">
                        <Spinner />
                        <p>{__('Loading configuration...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        // Show error if config is invalid
        if (!config) {
            return (
                <div className="splms-quiz-question-builder">
                    <div className="splms-error-message" style={{ padding: '15px', background: '#f8d7da', border: '1px solid #f5c6cb', borderRadius: '4px', color: '#721c24' }}>
                        {__('Configuration Error: Unable to load question builder settings.', 'skillpulse-lms')}
                    </div>
                </div>
            );
        }

        if (isLoading) {
            return (
                <div className="splms-quiz-question-builder">
                    <div className="question-builder-loading">
                        <Spinner />
                        <p>{__('Loading quiz questions...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        if (!post) {
            if (window.skillpulseToast) {
                window.skillpulseToast.error(__('Unable to load quiz data.', 'skillpulse-lms'));
            }
            return (
                <div className="splms-quiz-question-builder">
                    <div className="splms-error-message" style={{ padding: '15px', background: '#f8d7da', border: '1px solid #f5c6cb', borderRadius: '4px', color: '#721c24' }}>
                        {__('Unable to load quiz data.', 'skillpulse-lms')}
                    </div>
                </div>
            );
        }

        return (
            <div className="splms-quiz-question-builder">
                <div className="question-builder-header">
                    <div className="header-content">
                        <SplmsIcon mode="wp" name="editor-help" className="dashicon" size={24} />
                        <div>
                            <h2>{__('Question Builder', 'skillpulse-lms')}</h2>
                            <p>{__('Create and manage quiz questions with different types and options', 'skillpulse-lms')}</p>
                        </div>
                    </div>
                </div>

                <div className="question-builder-container">

                    <div className="question-list">
                        {questions.length === 0 ? (
                            <div className="no-questions">
                                <SplmsIcon mode="wp" name="format-status" size={48} />
                                <h3>{__('No Questions Yet', 'skillpulse-lms')}</h3>
                                <p>{__('Start building your quiz by adding your first question. You can create multiple choice, true/false, and short answer questions.', 'skillpulse-lms')}</p>
                            </div>
                        ) : (
                            questions.map((question, index) => (
                                <div key={question.id || index} className="question-item">
                                    <div className="question-header">
                                        <div className="question-meta">
                                            <span className="question-type">
                                                {this.getQuestionTypeLabel(question.type)}
                                            </span>
                                            <span className="question-points">
                                                {question.points || 1} {__('point(s)', 'skillpulse-lms')}
                                            </span>
                                        </div>
                                        <div className="question-actions">
                                            <Button
                                                isSecondary
                                                isSmall
                                                onClick={() => this.editQuestion(index)}
                                                disabled={isSaving}
                                            >
                                                <SplmsIcon mode="wp" name="edit" size={16} />
                                                {/* {__('Edit', 'skillpulse-lms')} */}
                                            </Button>
                                            <Button
                                                isDestructive
                                                isSmall
                                                onClick={() => this.deleteQuestion(index)}
                                                disabled={isSaving}
                                            >
                                                <SplmsIcon mode="wp" name="trash" size={16} />
                                                {/* {__('Delete', 'skillpulse-lms')} */}
                                            </Button>
                                        </div>
                                    </div>
                                    <div className="question-content">
                                        <div className="question-text">
                                            {question.question || __('Untitled Question', 'skillpulse-lms')}
                                        </div>
                                        {question.description && (
                                            <div className="question-description">
                                                {question.description}
                                            </div>
                                        )}
                                        {(question.type === 'multiple_choice' || question.type === 'multiple_select') && question.options && (
                                            <ul className="question-options">
                                                {question.options.map((option, optIndex) => (
                                                    <li 
                                                        key={optIndex} 
                                                        className={option.is_correct ? 'correct' : ''}
                                                    >
                                                        {option.text}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                        {question.type === 'true_false' && (
                                            <div className="question-answer">
                                                <strong>{__('Correct Answer:', 'skillpulse-lms')}</strong> {question.correct_answer}
                                            </div>
                                        )}
                                        {question.type === 'matching' && question.pairs && (
                                            <ul className="question-pairs">
                                                {question.pairs.map((pair, pairIndex) => (
                                                    <li key={pairIndex}>
                                                        <strong>{pair.left}</strong> ↔ {pair.right}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                        {question.type === 'ordering' && question.items && (
                                            <ol className="question-items">
                                                {question.items.map((item, itemIndex) => (
                                                    <li key={itemIndex}>{item.text}</li>
                                                ))}
                                            </ol>
                                        )}
                                        {question.type === 'file_upload' && (
                                            <div className="question-file-info">
                                                <p><strong>{__('File Upload Question', 'skillpulse-lms')}</strong></p>
                                                <p>{__('Allowed types:', 'skillpulse-lms')} {question.allowed_types || 'pdf,docx,jpg,png'}</p>
                                                <p>{__('Max size:', 'skillpulse-lms')} {question.max_file_size || 10} MB</p>
                                            </div>
                                        )}
                                        {question.explanation && (
                                            <div className="question-explanation">
                                                <strong>{__('Explanation:', 'skillpulse-lms')}</strong> {question.explanation}
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    <div className="add-question-section">
                        <Button
                            className="add-question-button"
                            onClick={() => this.addQuestion()}
                            disabled={isSaving}
                        >
                            <SplmsIcon mode="wp" name="plus" size={16} />
                            {__('Add Question', 'skillpulse-lms')}
                        </Button>
                    </div>
                </div>

                {showAddQuestionModal && (
                    <Modal
                        title=""
                        onRequestClose={this.cancelQuestion}
                        className="question-modal"
                        shouldCloseOnClickOutside={false}
                    >
                        <div className="modal-header">
                            <h2>{editingQuestionIndex !== null ? __('Edit Question', 'skillpulse-lms') : __('Add New Question', 'skillpulse-lms')}</h2>
                            <p className="modal-subtitle">
                                {editingQuestionIndex !== null 
                                    ? __('Modify your question details and options below', 'skillpulse-lms')
                                    : __('Create engaging quiz questions with multiple types and options', 'skillpulse-lms')
                                }
                            </p>
                        </div>
                        
                        <div className="modal-body">
                            
                            {/* Question Type Selector */}
                            <div className="form-section">
                                <div className="section-title">{__('Question Details', 'skillpulse-lms')}</div>
                                <div className="form-row">
                                    <div className="form-field">
                                        <Field
                                            type="select"
                                            id="type"
                                            label={__('Question Type', 'skillpulse-lms')}
                                            value={newQuestion.type}
                                            options={this.getQuestionTypeOptions()}
                                            onChange={(value) => this.updateQuestionField('type', value)}
                                        />
                                    </div>
                                </div>
                            </div>
                            
                            {/* Dynamically render fields from config */}
                            {this.getCurrentQuestionTypeFields().map((fieldConfig) => {
                                // Skip type field as it's rendered separately above
                                if (fieldConfig.id === 'type') {
                                    return null;
                                }
                                
                                // Get field value - handle nested structures
                                let fieldValue = newQuestion[fieldConfig.id];
                                if (fieldConfig.id === 'randomize_options') {
                                    fieldValue = newQuestion.settings?.randomize_options;
                                } else if (fieldConfig.id === 'case_sensitive') {
                                    fieldValue = newQuestion.settings?.case_sensitive;
                                }
                                
                                // Handle value transformation for number fields
                                if (fieldConfig.type === 'number' && fieldValue !== undefined) {
                                    fieldValue = parseInt(fieldValue) || fieldConfig.default || 0;
                                }
                                
                                // Handle matching pairs with MatchingEditor component
                                if (newQuestion.type === 'matching' && fieldConfig.id === 'pairs') {
                                    // Ensure pairs is an array
                                    if (!fieldValue || !Array.isArray(fieldValue)) {
                                        fieldValue = [];
                                    }
                                    
                                    return (
                                        <div key={fieldConfig.id} className="form-section">
                                            <MatchingEditor
                                                value={fieldValue}
                                                onChange={(pairs) => this.updateQuestionField('pairs', pairs)}
                                                label={fieldConfig.label || __('Matching Pairs', 'skillpulse-lms')}
                                                help={fieldConfig.help || __('Add pairs of items that students need to match.', 'skillpulse-lms')}
                                            />
                                        </div>
                                    );
                                }
                                
                                // Handle ordering items with OrderingField component
                                if (newQuestion.type === 'ordering' && fieldConfig.id === 'items') {
                                    // Ensure items is an array
                                    if (!fieldValue || !Array.isArray(fieldValue)) {
                                        fieldValue = [];
                                    }
                                    
                                    return (
                                        <div key={fieldConfig.id} className="form-section">
                                            <OrderingField
                                                value={fieldValue}
                                                onChange={(items) => {
                                                    // Extract text from each item in order
                                                    const correctAnswer = Array.isArray(items) && items.length > 0
                                                        ? items.map(item => {
                                                            if (!item) return '';
                                                            const text = (typeof item === 'object' && item !== null && item.text)
                                                                ? String(item.text).trim()
                                                                : (typeof item === 'string' ? String(item).trim() : '');
                                                            return text;
                                                        }).filter(text => text !== '')
                                                        : [];
                                                    
                                                    // Update both fields in a single state update to avoid race conditions
                                                    const updatedQuestion = {
                                                        ...this.state.newQuestion,
                                                        items: items,
                                                        correct_answer: correctAnswer
                                                    };
                                                    this.setState({ newQuestion: updatedQuestion });
                                                }}
                                                label={fieldConfig.label || __('Ordered Items', 'skillpulse-lms')}
                                                help={fieldConfig.help || __('Drag items to reorder them. The order shown here is the correct answer sequence.', 'skillpulse-lms')}
                                                min={fieldConfig.min || 2}
                                                max={fieldConfig.max || 15}
                                            />
                                        </div>
                                    );
                                }
                                
                                // Handle repeatable-group fields (options, items)
                                if (fieldConfig.type === 'repeatable-group') {
                                    // Value is already an array, no transformation needed
                                    // But ensure it exists
                                    if (!fieldValue || !Array.isArray(fieldValue)) {
                                        fieldValue = [];
                                    }
                                }
                                
                                return (
                                    <div key={fieldConfig.id} className="form-section">
                                        <Field
                                            {...fieldConfig}
                                            value={fieldValue}
                                            onChange={(value) => {
                                                this.updateQuestionField(fieldConfig.id, value);
                                            }}
                                        />
                                    </div>
                                );
                            })}
                        </div>

                        <div className="modal-footer">
                            <div className="footer-info">
                                <span className="info-icon">💡</span>
                                <span>{__('Tip: Mark at least one option as correct for multiple choice questions', 'skillpulse-lms')}</span>
                            </div>
                            <div className="footer-actions">
                                <Button
                                    isSecondary
                                    onClick={this.cancelQuestion}
                                    disabled={isSaving}
                                >
                                    {__('Cancel', 'skillpulse-lms')}
                                </Button>
                                <Button
                                    isPrimary
                                    onClick={this.saveQuestion}
                                    disabled={isSaving || !newQuestion.question.trim()}
                                >
                                    {isSaving ? __('Saving...', 'skillpulse-lms') : (editingQuestionIndex !== null ? __('Update Question', 'skillpulse-lms') : __('Add Question', 'skillpulse-lms'))}
                                </Button>
                            </div>
                        </div>
                    </Modal>
                )}
            </div>
        );
    }

    getQuestionTypeLabel(type) {
        const labels = {
            'multiple_choice': __('Multiple Choice', 'skillpulse-lms'),
            'multiple_select': __('Multiple Select', 'skillpulse-lms'),
            'true_false': __('True/False', 'skillpulse-lms'),
            'short_answer': __('Short Answer', 'skillpulse-lms'),
            'essay': __('Essay', 'skillpulse-lms'),
            'fill_blank': __('Fill in the Blank', 'skillpulse-lms'),
            'matching': __('Matching', 'skillpulse-lms'),
            'ordering': __('Ordering', 'skillpulse-lms'),
            'file_upload': __('File Upload', 'skillpulse-lms')
        };
        return labels[type] || type;
    }
}

export default compose([
    withSelect((select, props) => {
        return {
            post: select('core/editor').getCurrentPost(),
            postMeta: select('core/editor').getEditedPostAttribute('meta'),
        };
    }),
])(QuestionBuilder); 