/**
 * Utility functions for formatting quiz answers
 */

/**
 * Format question type for display
 * @param {string} type - Question type
 * @returns {string} Formatted type
 */
export const formatQuestionType = (type) => {
    const typeMap = {
        'multiple_choice': 'Single Choice',
        'multiple_select': 'Multiple Choice',
        'true_false': 'True/False',
        'short_answer': 'Short Answer',
        'fill_blank': 'Fill in the Blank',
        'matching': 'Matching',
        'ordering': 'Ordering',
        'essay': 'Essay',
        'file_upload': 'File Upload',
    };
    
    return typeMap[type] || type;
};

/**
 * Format given answer for display
 * @param {object} question - Question object
 * @param {mixed} answer - User's answer
 * @returns {string} Formatted answer
 */
export const formatGivenAnswer = (question, answer) => {
    if (answer === null || answer === '' || answer === undefined) {
        return '—';
    }

    if (Array.isArray(answer)) {
        return answer.join(', ');
    }

    return String(answer);
};

/**
 * Format correct answer for display
 * @param {object} question - Question object
 * @param {mixed} answer - Correct answer
 * @returns {string} Formatted answer
 */
export const formatCorrectAnswer = (question, answer) => {
    if (answer === null || answer === '' || answer === undefined) {
        return '—';
    }

    if (Array.isArray(answer)) {
        return answer.join(', ');
    }

    return String(answer);
};

/**
 * Check if answer is correct
 * @param {boolean} isCorrect - Whether answer is correct
 * @returns {boolean} True if correct
 */
export const isAnswerCorrect = (isCorrect) => {
    return isCorrect === true;
};

/**
 * Render math if present (placeholder for MathJax support)
 * @param {string} text - Text that may contain LaTeX
 * @returns {string} Text with potential math rendering
 */
export const renderMathIfPresent = (text) => {
    // TODO: Implement MathJax rendering if needed
    return text;
};

