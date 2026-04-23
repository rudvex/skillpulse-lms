/**
 * SkillPulse LMS Questions Handler (Refactored)
 * Handles question rendering, display, and answer management
 * Works in conjunction with the main Quiz handler
 * 
 * @class SPLMSQuestions
 * @module quizzes/questions
 */
import { escapeHtml } from './quiz-utils.js';
import QuestionRenderer from './question-renderer.js';

class SPLMSQuestions {
    /**
     * @param {Object} quizInstance - Reference to main quiz instance
     */
    constructor(quizInstance) {
        this.quiz = quizInstance;
        this.questionRenderer = QuestionRenderer;
        this.loadingTemplate = null;
        
        this.initTemplates();
    }

    /**
     * Initialize WordPress templates if available
     * @private
     */
    initTemplates() {
        if (typeof wp !== 'undefined' && wp.template) {
            this.loadingTemplate = wp.template('quiz-loading');
            this.questionReviewTemplate = wp.template('quiz-question-review');
        }
    }
    
    /**
     * Get options HTML for a specific question type using QuestionRenderer
     * @param {Object} questionData - Question data object
     * @returns {string} Rendered options HTML
     */
    getQuestionOptionsHtml(questionData) {
        return this.questionRenderer.renderOptions(questionData);
    }

    /**
     * Render the current question
     */
    renderCurrentQuestion() {
        
        if (!this.quiz.questionsData || this.quiz.questionsData.length === 0) {
            return;
        }

        const currentQuestionIndex = this.quiz.currentQuestion;
        const question = this.quiz.questionsData[currentQuestionIndex];
        
        if (!question) {
            console.error('Question not found at index:', currentQuestionIndex);
            return;
        }

        // Prepare question data for template
        const questionSettings = question.settings || {};
        // Ensure pairs and items are arrays
        const pairs = Array.isArray(question.pairs) ? question.pairs : [];
        let items = Array.isArray(question.items) ? question.items : [];
        const options = Array.isArray(question.options) ? question.options : [];
        
        // For ordering questions, build items from options if items not available
        if (question.type === 'ordering' && items.length === 0 && options.length > 0) {
            items = options.map(option => ({
                id: option.text || option.option_text || '',
                text: option.text || option.option_text || ''
            }));
        }
        
        const questionData = {
            id: question.id,
            type: question.type,
            question: question.question,
            description: question.description || '',
            explanation: question.explanation || '',
            points: question.points || 1,
            options: options,
            pairs: pairs,
            items: items,
            allowed_types: question.allowed_types || 'pdf,docx,jpg,png',
            max_file_size: question.max_file_size || 10,
            randomize_options: questionSettings.randomize_options || false,
            questionNumber: currentQuestionIndex + 1,
            totalQuestions: this.quiz.questionsData.length,
            selectedAnswer: this.quiz.answers[question.id] || '',
            showExplanation: false // Only show during review
        };
        // Ensure selectedAnswer is properly formatted for template
        if (questionData.selectedAnswer && typeof questionData.selectedAnswer === 'string') {
            // Trim whitespace for text-based answers
            questionData.selectedAnswer = questionData.selectedAnswer.trim();
        }

        // Render question using template
        const questionHtml = this.buildQuestionHtml(questionData);
        // Insert into container
        const $container = jQuery('#splms-quiz-interface .splms-quiz-questions-container');
        $container.html(questionHtml);
        
        // Initialize matching question handler if needed
        if (questionData.type === 'matching') {
            this.initMatchingQuestion($container, questionData);
        }
        
        // Initialize ordering question handler if needed
        if (questionData.type === 'ordering') {
            this.initOrderingQuestion($container, questionData);
        }
        
        // Bind events for this question
        this.bindQuestionEvents();
    }

    /**
     * Build question HTML using QuestionRenderer
     * @param {Object} questionData - Question data object
     * @returns {string} Complete question HTML
     */
    buildQuestionHtml(questionData) {
        // Use QuestionRenderer for rendering (single source of truth)
        // QuestionRenderer handles template-based rendering
        return this.questionRenderer.render(questionData, questionData.selectedAnswer);
    }


    /**
     * Bind events for question interactions
     */
    /**
     * Initialize matching question drag-and-drop interface
     */
    initOrderingQuestion($container, questionData) {
        // Find the ordering container
        const $orderingContainer = $container.find('.splms-ordering-container');
        
        if ($orderingContainer.length === 0) {
            return;
        }
        
        // Import and initialize ordering handler
        if (typeof SPLMSQuestionOrdering !== 'undefined') {
            // Parse selectedAnswer if it's a JSON string
            let selectedAnswer = questionData.selectedAnswer;
            if (typeof selectedAnswer === 'string' && selectedAnswer.trim().startsWith('[')) {
                try {
                    selectedAnswer = JSON.parse(selectedAnswer);
                } catch (e) {
                    selectedAnswer = [];
                }
            }
            
            questionData.selectedAnswer = Array.isArray(selectedAnswer) ? selectedAnswer : [];
            
            // Initialize ordering handler with quiz instance reference
            this.orderingHandler = new SPLMSQuestionOrdering($orderingContainer, questionData, this.quiz);
        } else {
            console.warn('SPLMSQuestionOrdering not available. Make sure question-ordering.js is loaded.');
        }
    }
    
    initMatchingQuestion($container, questionData) {
        // Find the matching container
        const $matchingContainer = $container.find('.splms-matching-container');

        if ($matchingContainer.length === 0) {
            return;
        }

        // Import and initialize matching handler
        // Note: This assumes question-matching.js is loaded
        if (typeof SPLMSQuestionMatching !== 'undefined') {
            // Parse selectedAnswer if it's a JSON string
            let selectedAnswer = questionData.selectedAnswer;
            if (typeof selectedAnswer === 'string' && selectedAnswer.trim().startsWith('[')) {
                try {
                    selectedAnswer = JSON.parse(selectedAnswer);
                } catch (e) {
                    selectedAnswer = [];
                }
            }

            questionData.selectedAnswer = Array.isArray(selectedAnswer) ? selectedAnswer : [];

            // Initialize matching handler with quiz instance reference
            this.matchingHandler = new SPLMSQuestionMatching($matchingContainer, questionData, this.quiz);
        } else {
            console.warn('SPLMSQuestionMatching not available. Make sure question-matching.js is loaded.');
        }
    }
    
    bindQuestionEvents() {
        const self = this;
        
        // Bind radio button change events (single select)
        jQuery('#splms-quiz-interface input[type="radio"]').off('change').on('change', function() {
            const questionId = jQuery(this).data('question-id');
            const value = jQuery(this).val(); // This is now the option ID
            
            // Update selected state visually
            const $option = jQuery(this).closest('.answer-option');
            $option.addClass('selected').siblings('.answer-option').removeClass('selected');
            
            // Store answer as option ID
            self.quiz.answers[questionId] = value;
            self.quiz.saveQuizState();
        });
        
        // Bind checkbox change events (multiple select)
        jQuery('#splms-quiz-interface input[type="checkbox"]').off('change').on('change', function() {
            const questionId = jQuery(this).data('question-id');
            const $question = jQuery(this).closest('.splms-quiz-question-card');
            const $checkboxes = $question.find('input[type="checkbox"][data-question-id="' + questionId + '"]');

            // Update selected state visually
            const $option = jQuery(this).closest('.answer-option');
            if (jQuery(this).is(':checked')) {
                $option.addClass('selected');
            } else {
                $option.removeClass('selected');
            }
            // Collect all selected option IDs (checkbox value is now option ID)
            const selectedValues = [];
            $checkboxes.filter(':checked').each(function() {
                // Use option ID from value attribute (which is now set to option ID)
                const optionId = jQuery(this).val();
                if (optionId) {
                    selectedValues.push(optionId);
                }
            });
            
            // Store answer as array of option IDs for multiple select
            // Only update if we have selected values, or if user explicitly unchecked (to allow clearing)
            // But preserve existing answer if checkboxes aren't found (might be on different page)
            if ($checkboxes.length > 0) {
                self.quiz.answers[questionId] = selectedValues.length > 0 ? selectedValues : '';
                self.quiz.saveQuizState();
            }
        });
        
        // Bind textarea change events for essay questions
        jQuery('#splms-quiz-interface textarea').off('input').on('input', function() {
            const questionId = jQuery(this).data('question-id');
            const value = jQuery(this).val().trim();
            
            self.quiz.answers[questionId] = value;
            self.quiz.saveQuizState();
        });

        // Bind text input events for short answer questions
        jQuery('#splms-quiz-interface input[type="text"]').off('input').on('input', function() {
            const questionId = jQuery(this).data('question-id');
            const value = jQuery(this).val().trim();
            
            self.quiz.answers[questionId] = value;
            self.quiz.saveQuizState();
        });
        
        // Bind ordering question answer change events (drag-and-drop)
        // Use event delegation to catch dynamically created inputs
        jQuery('#splms-quiz-interface').off('change', 'input[name^="question_"][id^="ordering_input_"]').on('change', 'input[name^="question_"][id^="ordering_input_"]', function() {
            const questionId = jQuery(this).data('question-id');
            const answerValue = jQuery(this).val();
            
            // Parse JSON string to array
            let answerArray = [];
            if (answerValue) {
                try {
                    answerArray = JSON.parse(answerValue);
                } catch (e) {
                    answerArray = [];
                }
            }
            
            // Update quiz answers
            self.quiz.answers[questionId] = answerArray;
            
            // Save quiz state
            if (typeof self.quiz.saveQuizState === 'function') {
                self.quiz.saveQuizState();
            }
        });
        
        // Bind matching question answer change events (drag-and-drop)
        // Use event delegation to catch dynamically created inputs
        jQuery('#splms-quiz-interface').off('change', 'input[name^="question_"][id^="matching-answer-"]').on('change', 'input[name^="question_"][id^="matching-answer-"]', function() {
            const questionId = jQuery(this).data('question-id');
            const answerValue = jQuery(this).val();
            
            // Parse JSON string to array
            let answerArray = [];
            if (answerValue) {
                try {
                    answerArray = JSON.parse(answerValue);
                } catch (e) {
                    // If not JSON, treat as string
                    answerArray = answerValue;
                }
            }
            
            // Update quiz answers
            self.quiz.answers[questionId] = answerArray;
            
            // Save quiz state
            if (typeof self.quiz.saveQuizState === 'function') {
                self.quiz.saveQuizState();
            }
        });
        
        // Initialize sortable for ordering questions (requires jQuery UI Sortable)
        if (typeof jQuery.fn.sortable !== 'undefined') {
            jQuery('#splms-quiz-interface .ordering-list').each(function() {
                const $list = jQuery(this);
                const questionId = $list.closest('.quiz-question-card').data('question-id');
                
                if (!$list.hasClass('ui-sortable')) {
                    $list.sortable({
                        handle: '.ordering-handle',
                        update: function() {
                            const order = [];
                            $list.find('.ordering-item').each(function() {
                                order.push(parseInt(jQuery(this).data('item-index')));
                            });
                            
                            self.quiz.answers[questionId] = order;
                            jQuery('#ordering_input_' + questionId).val(JSON.stringify(order));
                            self.quiz.saveQuizState();
                        }
                    });
                }
            });
        } else {
            // Manual reordering with up/down buttons
            jQuery('#splms-quiz-interface .ordering-item').each(function() {
                const $item = jQuery(this);
                if (!$item.find('.ordering-controls').length) {
                    $item.append(`
                        <div class="ordering-controls">
                            <button type="button" class="ordering-up">↑</button>
                            <button type="button" class="ordering-down">↓</button>
                        </div>
                    `);
                }
            });
            
            jQuery('#splms-quiz-interface .ordering-up').off('click').on('click', function() {
                const $item = jQuery(this).closest('.ordering-item');
                const $prev = $item.prev();
                if ($prev.length) {
                    $item.insertBefore($prev);
                    self.updateOrderingAnswer($item.closest('.ordering-container'));
                }
            });
            
            jQuery('#splms-quiz-interface .ordering-down').off('click').on('click', function() {
                const $item = jQuery(this).closest('.ordering-item');
                const $next = $item.next();
                if ($next.length) {
                    $item.insertAfter($next);
                    self.updateOrderingAnswer($item.closest('.ordering-container'));
                }
            });
        }
        
        // Bind file upload change events
        jQuery('#splms-quiz-interface input[type="file"]').off('change').on('change', function() {
            const questionId = jQuery(this).data('question-id');
            const file = this.files[0];
            const $label = jQuery(this).siblings('.file-upload-label').find('.file-upload-text');
            
            if (file) {
                $label.text(file.name);
                
                // Validate file size
                const maxSize = parseInt(jQuery(this).data('max-size'));
                if (file.size > maxSize) {
                    alert('File size exceeds maximum allowed size.');
                    jQuery(this).val('');
                    $label.text('No file chosen');
                    // Remove from answers and fileObjects
                    delete self.quiz.answers[questionId];
                    delete self.quiz.fileObjects[questionId];
                    self.quiz.saveQuizState();
                    return;
                }
                
                // Store file name in answers (for state saving)
                self.quiz.answers[questionId] = file.name;
                // Store File object in memory (for final submission)
                self.quiz.fileObjects[questionId] = file;
            } else {
                $label.text('No file chosen');
                // Remove from answers and fileObjects
                delete self.quiz.answers[questionId];
                delete self.quiz.fileObjects[questionId];
            }
            
            // Save state on file selection/removal
            self.quiz.saveQuizState();
        });
        
        // Bind remove file button
        jQuery('#splms-quiz-interface .remove-file-btn').off('click').on('click', function() {
            const questionId = jQuery(this).data('question-id');
            const $container = jQuery(this).closest('.file-upload-container');
            const $input = $container.find('input[type="file"]');
            
            $input.val('');
            $container.find('.file-upload-text').text('No file chosen');
            $container.find('.file-upload-preview').remove();
            
            self.quiz.answers[questionId] = '';
            self.quiz.saveQuizState();
        });
    }
    
    /**
     * Helper to update ordering answer
     */
    updateOrderingAnswer($container) {
        const questionId = $container.data('question-id');
        const $list = $container.find('.ordering-list');
        const order = [];
        
        $list.find('.ordering-item').each(function() {
            order.push(parseInt(jQuery(this).data('item-index')));
        });
        
        this.quiz.answers[questionId] = order;
        jQuery('#ordering_input_' + questionId).val(JSON.stringify(order));
        this.quiz.saveQuizState();
    }

    /**
     * Restore answers in the UI from saved state
     */
    restoreAnswersInUI() {
        // Ensure we have answers to restore
        if (!this.quiz.answers || Object.keys(this.quiz.answers).length === 0) {
            return;
        }

        Object.keys(this.quiz.answers).forEach(questionId => {
            const answer = this.quiz.answers[questionId];
            
            // Skip empty answers
            if (!answer || answer === '' || (Array.isArray(answer) && answer.length === 0)) {
                return;
            }
            
            // Try multiple selectors to find the question card
            const $question = jQuery(`.splms-quiz-question-card[data-question-id="${questionId}"]`);
            
            if ($question.length === 0) {
                return;
            }
            
            const questionType = $question.data('question-type');
            
            if (questionType === 'multiple_select') {
                // Handle multiple select - array of option IDs
                const selectedAnswers = Array.isArray(answer) ? answer : (answer ? [answer] : []);
                // Store the answer before restoring to prevent change handler from clearing it
                const savedAnswer = this.quiz.answers[questionId];
                
                selectedAnswers.forEach(selectedValue => {
                    if (!selectedValue) return;
                    
                    const normalizedValue = String(selectedValue).trim();
                    
                    // Match by checkbox value (option ID)
                    const $checkbox = $question.find(`input[type="checkbox"][value="${escapeHtml(normalizedValue)}"]`);
                    
                    if ($checkbox.length > 0) {
                        $checkbox.prop('checked', true);
                        $checkbox.closest('.answer-option, .option-label').addClass('selected');
                        // Don't trigger change event during restoration - it can clear answers
                        // The checked state is already set, so the visual state is correct
                    }
                });
                
                // Ensure the answer is preserved after restoration
                if (savedAnswer && (Array.isArray(savedAnswer) ? savedAnswer.length > 0 : savedAnswer !== '')) {
                    this.quiz.answers[questionId] = savedAnswer;
                }
            } else if (questionType === 'multiple_choice' || questionType === 'true_false') {
                // Handle single select - radio buttons
                const answerValue = Array.isArray(answer) ? answer[0] : answer;
                if (!answerValue) return;
                const normalizedAnswer = String(answerValue).trim();
                
                // Match by radio value (option ID)
                const $radio = $question.find(`input[type="radio"][value="${escapeHtml(normalizedAnswer)}"]`);
                
                if ($radio.length > 0) {
                    // Store the answer before restoring to prevent change handler from clearing it
                    const savedAnswer = this.quiz.answers[questionId];
                    
                    $radio.prop('checked', true);
                    $radio.closest('.answer-option, .option-label').addClass('selected');
                    $question.find(`input[type="radio"][name="${$radio.attr('name')}"]`).not($radio).prop('checked', false);
                    // Don't trigger change event during restoration - it can clear answers
                    // The checked state is already set, so the visual state is correct
                    
                    // Ensure the answer is preserved after restoration
                    if (savedAnswer) {
                        this.quiz.answers[questionId] = savedAnswer;
                    }
                }
            } else if (questionType === 'short_answer' || questionType === 'fill_blank') {
                // Handle text input
                const answerValue = Array.isArray(answer) ? answer[0] : answer;
                const $input = $question.find('input[type="text"]');
                if ($input.length > 0) {
                    $input.val(answerValue || '');
                }
            } else if (questionType === 'essay' || questionType === 'long_answer') {
                // Handle textarea
                const answerValue = Array.isArray(answer) ? answer[0] : answer;
                const $textarea = $question.find('textarea');
                if ($textarea.length > 0) {
                    $textarea.val(answerValue || '');
                }
            } else if (questionType === 'matching') {
                // Handle matching - restore selected pairs
                if (typeof answer === 'object' && answer !== null) {
                    Object.keys(answer).forEach(pairIndex => {
                        const $select = $question.find(`.matching-select[data-pair-index="${pairIndex}"]`);
                        if ($select.length > 0) {
                            $select.val(answer[pairIndex]);
                        }
                    });
                }
            } else if (questionType === 'ordering') {
                // Handle ordering - restore item order using option IDs
                if (Array.isArray(answer) && answer.length > 0) {
                    const $list = $question.find('.splms-ordering-list, .ordering-list');
                    const items = [];
                    
                    $list.find('.splms-ordering-item, .ordering-item').each(function() {
                        const $item = jQuery(this);
                        const optionId = $item.data('option-id') || $item.attr('data-option-id');
                        if (optionId) {
                            items.push({
                                optionId: optionId,
                                element: $item
                            });
                        }
                    });
                    
                    // Sort items by answer order (answer contains option IDs)
                    items.sort((a, b) => {
                        const aPos = answer.indexOf(a.optionId);
                        const bPos = answer.indexOf(b.optionId);
                        return aPos - bPos;
                    });
                    
                    // Reorder DOM elements
                    items.forEach(item => {
                        $list.append(item.element);
                    });
                    
                    // Update hidden input with option IDs
                    jQuery('#ordering_input_' + questionId).val(JSON.stringify(answer));
                }
            } else if (questionType === 'file_upload') {
                // Handle file upload - show uploaded file name
                const answerValue = Array.isArray(answer) ? answer[0] : answer;
                if (answerValue) {
                    const $container = $question.find('.file-upload-container');
                    if (!$container.find('.file-upload-preview').length) {
                        $container.find('.file-upload-field').before(`
                            <div class="file-upload-preview">
                                <p>Current file: <strong>${escapeHtml(answerValue)}</strong></p>
                                <button type="button" class="splms-btn splms-btn-secondary remove-file-btn" data-question-id="${questionId}">
                                    Remove File
                                </button>
                            </div>
                        `);
                    }
                    $container.find('.file-upload-text').text(answerValue);
                }
            }
        });
        
    }

    /**
     * Populate detailed results for question review
     */
    populateDetailedResults($container) {
        if (!this.quiz.currentResults) {
            console.error('No current results available');
            $container.html('<div class="no-detailed-results">No quiz results available.</div>');
            return;
        }
        
        if (!this.quiz.questionsData || this.quiz.questionsData.length === 0) {
            console.error('No questions data available');
            $container.html('<div class="no-detailed-results">No questions data available.</div>');
            return;
        }

        let resultsHtml = '';

        // Use server-provided detailed results
        if (!this.quiz.currentResults.detailed_results || !Array.isArray(this.quiz.currentResults.detailed_results)) {
            $container.html('<div class="no-detailed-results">No detailed results available.</div>');
            return;
        }

        this.quiz.currentResults.detailed_results.forEach((result, index) => {
            const questionNumber = index + 1;
            // Match question by ID (handle both string and number types)
            const question = this.quiz.questionsData.find(q => {
                const qId = parseInt(q.id);
                const rId = parseInt(result.question_id);
                return qId === rId || String(q.id) === String(result.question_id);
            });
            
            if (!question) {
                console.warn('Question not found for result:', result.question_id, 'Available questions:', this.quiz.questionsData.map(q => q.id));
                return;
            }
            
            const isCorrect = result.is_correct;
            // Get user answer - handle different formats
            let userAnswer = result.user_answer;
            if (userAnswer === null || userAnswer === undefined || userAnswer === '') {
                userAnswer = null; // Will be formatted as 'No answer'
            } else if (typeof userAnswer === 'string' && userAnswer.trim().startsWith('[')) {
                // Parse JSON string for ordering/matching questions
                try {
                    userAnswer = JSON.parse(userAnswer);
                } catch (e) {
                    // Keep as string if parsing fails
                }
            }
            
            const correctAnswer = result.correct_answer || 'N/A';
            
            // Pass needs_manual_review flag from result to question object
            if (result.needs_manual_review !== undefined) {
                question.needs_manual_review = result.needs_manual_review;
            }
            
            // Pass feedback from result to question object
            if (result.feedback !== undefined) {
                question.feedback = result.feedback;
            }

            resultsHtml += this.buildResultHtml(questionNumber, question, userAnswer, correctAnswer, isCorrect);
        });

        if (resultsHtml.length === 0) {
            resultsHtml = '<div class="no-detailed-results">No detailed results could be generated.</div>';
        }

        $container.html(resultsHtml);
    }

    /**
     * Helper function to build result HTML for a single question
     * Uses wp.template for consistent templating
     */
    buildResultHtml(questionNumber, question, userAnswer, correctAnswer, isCorrect) {
        let userAnswerDisplay = this.formatAnswerForDisplay(userAnswer, question.type, question);
        let correctAnswerDisplay = this.formatAnswerForDisplay(correctAnswer, question.type, question);
        
        // Special handling for matching questions - show pairs with correct/incorrect highlighting
        if (question.type === 'matching') {
            const pairs = Array.isArray(question.pairs) ? question.pairs : [];
            
            // Parse matching answer to array format
            const parseMatchingAnswer = (answer) => {
                // Backend returns comma-separated string (e.g., "Server, Browser, Database")
                if (typeof answer === 'string') {
                    return answer.split(',').map(val => val.trim()).filter(val => val !== '');
                }
                // Object format {left_text: right_text}
                if (typeof answer === 'object' && answer !== null) {
                    return pairs.map((pair) => {
                        const leftText = pair?.left || '';
                        return answer[leftText] || '';
                    });
                }
                // Array format
                if (Array.isArray(answer)) {
                    return answer;
                }
                return [];
            };
            
            const userAnswerArray = parseMatchingAnswer(userAnswer);

            // Build matching pairs data for template
            const matchingPairs = pairs.map((pair, index) => {
                const leftText = pair?.left || '';
                const correctRight = pair?.right || '';
                const userRight = userAnswerArray[index] || '';
                
                // Compare answers
                const normalize = (text) => String(text || '').toLowerCase().trim();
                const caseSensitive = question.settings?.case_sensitive || false;
                const isMatchCorrect = caseSensitive 
                    ? String(userRight).trim() === String(correctRight).trim()
                    : normalize(userRight) === normalize(correctRight);
                
                return {
                    leftText: leftText,
                    userRight: userRight,
                    correctRight: correctRight,
                    isCorrect: isMatchCorrect
                };
            });
            
            // Use template for matching display
            if (typeof wp !== 'undefined' && wp.template) {
                const matchingTemplate = wp.template('quiz-matching-results');
                userAnswerDisplay = matchingTemplate({ pairs: matchingPairs });
            } else {
                // Fallback if template not available
                userAnswerDisplay = '<div class="splms-matching-results-container">Matching results unavailable</div>';
            }
            
            correctAnswerDisplay = ''; // Don't show separate correct answer for matching
        }
        
        // Special handling for essay questions
        if (question.type === 'essay' || question.type === 'long_answer') {
            const needsReview = question.needs_manual_review !== false; // Default to true for essays
            if (needsReview) {
                correctAnswerDisplay = 'Pending Review';
                // Show a special status indicator for pending review
                isCorrect = false; // Essays are not marked correct until reviewed
            }
        }
        
        // Special handling for file upload
        if (question.type === 'file_upload') {
            if (userAnswer) {
                // Check if we have a URL from server (from detailed_results)
                if (question.user_answer_url) {
                    userAnswerDisplay = `<a href="${escapeHtml(question.user_answer_url)}" target="_blank" class="file-upload-link">View Uploaded File</a>`;
                } else if (typeof userAnswer === 'string' && (userAnswer.startsWith('http') || userAnswer.startsWith('/'))) {
                    // It's already a URL
                    userAnswerDisplay = `<a href="${escapeHtml(userAnswer)}" target="_blank" class="file-upload-link">View Uploaded File</a>`;
                } else if (typeof userAnswer === 'number' || (typeof userAnswer === 'string' && /^\d+$/.test(userAnswer))) {
                    // It's an attachment ID
                    const attachmentId = parseInt(userAnswer);
                    userAnswerDisplay = `File uploaded (Attachment ID: ${attachmentId})`;
                } else {
                    userAnswerDisplay = this.formatAnswerForDisplay(userAnswer, question.type, question);
                }
            } else {
                userAnswerDisplay = 'No file uploaded';
            }
            const needsReview = question.needs_manual_review !== false; // Default to true for file uploads
            correctAnswerDisplay = needsReview ? 'Pending Review' : 'Requires manual grading';
        }
        
        // Check if question needs manual review
        const needsReview = question.needs_manual_review === true || 
                           (question.type === 'essay' || question.type === 'long_answer' || question.type === 'file_upload');
        const reviewIcon = needsReview ? '⏳' : (isCorrect ? '✅' : '❌');
        
        // Check if feedback should be shown (default to false if not set)
        const feedbackEnabled = this.quiz.currentResults?.feedback_enabled === true;
        const showFeedback = feedbackEnabled && question.feedback;
        
        // Use wp.template
        const templateData = {
            questionId: question.id,
            questionNumber: questionNumber,
            questionText: question.question || question.question_text || `Question ${questionNumber}`,
            points: question.points || 1,
            isCorrect: isCorrect,
            needsReview: needsReview,
            reviewIcon: reviewIcon,
            userAnswerDisplay: userAnswerDisplay,
            correctAnswerDisplay: correctAnswerDisplay,
            explanation: question.explanation || '',
            questionType: question.type,
            feedback: question.feedback || '',
            showFeedback: showFeedback
        };
        
        return this.questionReviewTemplate(templateData);
    }
    
    /**
     * Format answer for display based on question type
     * Maps option IDs to option texts when needed
     * @param {*} answer - Answer value
     * @param {string} questionType - Question type
     * @param {Object} question - Full question object with options
     * @returns {string} Formatted answer HTML
     */
    formatAnswerForDisplay(answer, questionType, question) {
        // Use QuestionRenderer for consistent formatting
        return this.questionRenderer.formatAnswerForDisplay(answer, questionType, question);
    }

    /**
     * Handle review item click to navigate to specific question
     */
    handleReviewItemClick(e) {
        e.preventDefault();
        
        if (!this.quiz.settings.allowNavigation) return;
        
        const $item = jQuery(e.currentTarget);
        const questionNum = parseInt($item.data('question'));
        
        // Calculate which page this question is on
        const targetPage = Math.ceil(questionNum / this.quiz.settings.questionsPerPage);
        
        // Go back to quiz and navigate to that page
        this.quiz.handleBackToQuiz(e);
        
        if (targetPage !== this.quiz.currentPage) {
            this.quiz.currentPage = targetPage;
            this.quiz.currentQuestion = questionNum - 1;
            this.renderCurrentQuestion();
            this.quiz.updateProgress();
            this.quiz.updateNavigationButtons();
        }
    }
}

// Export for use by quiz.js
window.SPLMSQuestions = SPLMSQuestions; 