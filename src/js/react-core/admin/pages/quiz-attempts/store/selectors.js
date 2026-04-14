/**
 * Selectors for the quiz attempts store
 */

/**
 * Get all attempts
 * @param {Object} state Store state
 * @returns {Array} Array of attempts
 */
export function getAttempts(state) {
    return state.attempts || [];
}

/**
 * Get attempt by ID
 * @param {Object} state Store state
 * @param {number} attemptId Attempt ID
 * @returns {Object|null} Attempt object or null
 */
export function getAttemptById(state, attemptId) {
    return state.attempts.find(attempt => attempt.id === attemptId) || null;
}

/**
 * Get loading state
 * @param {Object} state Store state
 * @returns {boolean} Loading state
 */
export function isLoading(state) {
    return state.isLoading || false;
}

/**
 * Get error state
 * @param {Object} state Store state
 * @returns {string|null} Error message or null
 */
export function getError(state) {
    return state.error || null;
}

/**
 * Get current filters
 * @param {Object} state Store state
 * @returns {Object} Current filters
 */
export function getFilters(state) {
    return state.filters || {};
}

/**
 * Get pagination info
 * @param {Object} state Store state
 * @returns {Object} Pagination object
 */
export function getPagination(state) {
    return state.pagination || {
        page: 1,
        per_page: 20,
        total: 0,
        total_pages: 1
    };
}

/**
 * Get filtered attempts
 * @param {Object} state Store state
 * @returns {Array} Filtered attempts
 */
export function getFilteredAttempts(state) {
    return state.attempts || [];
}

/**
 * Get attempts by user
 * @param {Object} state Store state
 * @param {number} userId User ID
 * @returns {Array} Attempts for specific user
 */
export function getAttemptsByUser(state, userId) {
    return state.attempts.filter(attempt => attempt.user_id === userId) || [];
}

/**
 * Get attempts by quiz
 * @param {Object} state Store state
 * @param {number} quizId Quiz ID
 * @returns {Array} Attempts for specific quiz
 */
export function getAttemptsByQuiz(state, quizId) {
    return state.attempts.filter(attempt => attempt.quiz_id === quizId) || [];
}

/**
 * Get attempts by course
 * @param {Object} state Store state
 * @param {number} courseId Course ID
 * @returns {Array} Attempts for specific course
 */
export function getAttemptsByCourse(state, courseId) {
    return state.attempts.filter(attempt => attempt.course_id === courseId) || [];
}

/**
 * Get total attempts count
 * @param {Object} state Store state
 * @returns {number} Total attempts count
 */
export function getTotalAttempts(state) {
    return state.attempts.length || 0;
}


/**
 * Get passed attempts count
 * @param {Object} state Store state
 * @returns {number} Passed attempts count
 */
export function getPassedAttemptsCount(state) {
    return state.attempts.filter(attempt => attempt.passed).length || 0;
}

