/**
 * Question Renderer
 * Single source of truth for question type templates and rendering
 * @module question-renderer
 */

import { escapeHtml } from './quiz-utils.js';

/**
 * QuestionRenderer class - handles all question rendering logic
 * Centralizes template mapping and rendering to eliminate duplication
 */
export class QuestionRenderer {
    constructor() {
        this.templates = {};
        this.initTemplates();
    }

    /**
     * Initialize WordPress templates
     * Single mapping of question types to templates
     */
    initTemplates() {
        if (typeof wp === 'undefined' || !wp.template) {
            console.warn('WordPress templates not available');
            return;
        }

        // Main question wrapper template
        this.questionTemplate = wp.template('quiz-question');
        this.loadingTemplate = wp.template('quiz-loading');

        // Question type-specific templates - SINGLE SOURCE OF TRUTH
        this.templates = {
            'multiple_choice': wp.template('quiz-question-multiple-choice'),
            'multiple_select': wp.template('quiz-question-multiple-select'),
            'true_false': wp.template('quiz-question-true-false'),
            'short_answer': wp.template('quiz-question-short-answer'),
            'fill_blank': wp.template('quiz-question-short-answer'), // Explicit alias
            'essay': wp.template('quiz-question-essay'),
            'long_answer': wp.template('quiz-question-essay'), // Explicit alias
            'matching': wp.template('quiz-question-matching'),
            'ordering': wp.template('quiz-question-ordering'),
            'file_upload': wp.template('quiz-question-file-upload')
        };
    }

    /**
     * Get template for question type
     * @param {string} questionType - Question type
     * @returns {Function|null} Template function or null
     */
    getTemplate(questionType) {
        return this.templates[questionType] || null;
    }

    /**
     * Render question options HTML using appropriate template
     * @param {Object} questionData - Question data object
     * @returns {string} Rendered HTML
     */
    renderOptions(questionData) {
        const template = this.getTemplate(questionData.type);
        
        if (template) {
            return template(questionData);
        }

        // Template not found - show simple message
        return `<div class="splms-quiz-template-not-found">Template not found for question type: ${escapeHtml(questionData.type)}</div>`;
    }

    /**
     * Render complete question HTML
     * @param {Object} questionData - Question data
     * @param {*} selectedAnswer - Current selected answer
     * @returns {string} Complete question HTML
     */
    render(questionData, selectedAnswer = null) {
        // Prepare question data for template
        const data = {
            id: questionData.id,
            type: questionData.type,
            question: questionData.question || questionData.question_text,
            description: questionData.description || '',
            explanation: questionData.explanation || '',
            points: questionData.points || 1,
            options: Array.isArray(questionData.options) ? questionData.options : [],
            pairs: Array.isArray(questionData.pairs) ? questionData.pairs : [],
            items: Array.isArray(questionData.items) ? questionData.items : [],
            selectedAnswer: selectedAnswer || questionData.selectedAnswer || '',
            questionNumber: questionData.questionNumber || 1,
            totalQuestions: questionData.totalQuestions || 1,
            showExplanation: questionData.showExplanation || false
        };

        // Get options HTML
        const optionsHtml = this.renderOptions(data);

        // Use main question template if available
        if (this.questionTemplate) {
            return this.questionTemplate({
                ...data,
                optionsHtml
            });
        }

        // Template not found - show simple message
        return `<div class="splms-quiz-template-not-found">Question template not found. Please ensure templates are loaded.</div>`;
    }

    /**
     * Format answer for display in results
     * @param {*} answer - Answer value
     * @param {string} questionType - Question type
     * @param {Object} question - Full question object with options
     * @returns {string} Formatted answer HTML
     */
    formatAnswerForDisplay(answer, questionType, question = {}) {
        if (!answer || answer === '' || (Array.isArray(answer) && answer.length === 0)) {
            return 'No answer';
        }

        const getOptionText = (optionId, options) => {
            if (!options || !Array.isArray(options)) return String(optionId);
            const option = options.find(opt => opt.id === String(optionId) || opt.id === optionId);
            return option ? (option.text || option.option_text || String(optionId)) : String(optionId);
        };

        if (questionType === 'multiple_choice' || questionType === 'true_false') {
            if (question.options && Array.isArray(question.options)) {
                return escapeHtml(getOptionText(answer, question.options));
            }
            return escapeHtml(String(answer));
        }

        if (questionType === 'multiple_select') {
            const answers = Array.isArray(answer) ? answer : [answer];
            if (answers.length === 0) return 'No answer';
            
            if (question.options && Array.isArray(question.options)) {
                const answerTexts = answers.map(optionId => getOptionText(optionId, question.options));
                return answerTexts.map(a => escapeHtml(a)).join(', ');
            }
            return answers.map(a => escapeHtml(String(a))).join(', ');
        }

        if (questionType === 'ordering') {
            // Handle null, undefined, or empty values
            if (!answer || answer === null || answer === undefined) {
                return 'No answer';
            }
            
            // Parse JSON string if needed
            let parsedAnswer = answer;
            if (typeof answer === 'string' && answer.trim() !== '') {
                // Check if it's a JSON array string
                if (answer.trim().startsWith('[') && answer.trim().endsWith(']')) {
                    try {
                        parsedAnswer = JSON.parse(answer);
                    } catch (e) {
                        // If parsing fails, treat as regular string
                        parsedAnswer = answer;
                    }
                }
            }
            
            // Answer is array of text values (not IDs)
            if (Array.isArray(parsedAnswer) && parsedAnswer.length > 0) {
                // Answer already contains text values, just format them
                const answerTexts = parsedAnswer
                    .filter(v => v !== null && v !== undefined && v !== '')
                    .map(v => {
                        // Handle different answer formats
                        if (typeof v === 'object' && v !== null && v.text) {
                            return String(v.text);
                        }
                        return String(v);
                    });
                return answerTexts.length > 0 ? answerTexts.map(text => escapeHtml(text)).join(' → ') : 'No answer';
            }
            
            // Handle string format (already formatted with arrows or single value)
            if (typeof parsedAnswer === 'string' && parsedAnswer.trim() !== '') {
                return escapeHtml(parsedAnswer);
            }
            
            return 'No answer';
        }

        if (questionType === 'file_upload') {
            if (typeof answer === 'number' || (typeof answer === 'string' && /^\d+$/.test(answer))) {
                const attachmentId = parseInt(answer);
                return `<a href="#" target="_blank" class="file-upload-link">View Uploaded File (ID: ${attachmentId})</a>`;
            } else if (typeof answer === 'string' && answer.startsWith('http')) {
                return `<a href="${escapeHtml(answer)}" target="_blank" class="file-upload-link">${escapeHtml(answer)}</a>`;
            }
            return escapeHtml(String(answer));
        }

        if (Array.isArray(answer)) {
            return answer.map(a => escapeHtml(String(a))).join(', ');
        }

        return escapeHtml(String(answer));
    }
}

// Export singleton instance
export default new QuestionRenderer();

