import apiFetch from '@wordpress/api-fetch';

const API_QUIZ_ATTEMPTS_PATH = '/splms/v1/quiz-attempts';

/**
 * Fetch quiz attempts data from the API.
 * @param {Object} params - Query parameters
 */
export const fetchQuizAttempts = async (params = {}) => {
    const queryParams = new URLSearchParams();
    Object.keys(params).forEach(key => {
        if (params[key] !== '' && params[key] !== null && params[key] !== undefined) {
            queryParams.append(key, params[key]);
        }
    });
    
    const url = queryParams.toString() ? `${API_QUIZ_ATTEMPTS_PATH}?${queryParams.toString()}` : API_QUIZ_ATTEMPTS_PATH;
    
    return apiFetch({ path: url });
};

/**
 * Fetch single quiz attempt by ID from the API.
 * @param {number} attemptId - Attempt ID
 */
export const fetchQuizAttempt = async (attemptId) => {
    return apiFetch({ path: `${API_QUIZ_ATTEMPTS_PATH}/${attemptId}` });
};

/**
 * Verify a quiz attempt via the API.
 * @param {number} attemptId - Attempt ID
 */
export const verifyQuizAttempt = async (attemptId) => {
    return apiFetch({
        path: `${API_QUIZ_ATTEMPTS_PATH}/${attemptId}/verify`,
        method: 'POST',
    });
};

/**
 * Delete quiz attempt via the API.
 * @param {number} attemptId - Attempt ID
 */
export const deleteQuizAttempt = async (attemptId) => {
    return apiFetch({
        path: `${API_QUIZ_ATTEMPTS_PATH}/${attemptId}`,
        method: 'DELETE',
    });
};

/**
 * Fetch quiz attempt questions with answer comparison.
 * @param {number} attemptId - Attempt ID
 */
export const fetchQuizAttemptQuestions = async (attemptId) => {
    return apiFetch({ path: `${API_QUIZ_ATTEMPTS_PATH}/${attemptId}/questions` });
};

/**
 * Update feedback for a quiz attempt.
 * @param {number} attemptId - Attempt ID
 * @param {string} feedback - Feedback text
 */
export const updateQuizAttemptFeedback = async (attemptId, feedback) => {
    return apiFetch({
        path: `${API_QUIZ_ATTEMPTS_PATH}/${attemptId}/feedback`,
        method: 'POST',
        data: { feedback },
    });
};

/**
 * Grade essay questions and update attempt score.
 * @param {number} attemptId - Attempt ID
 * @param {Object} questionScores - Object mapping question IDs to points awarded
 */
export const gradeQuizAttempt = async (attemptId, questionScores) => {
    return apiFetch({
        path: `${API_QUIZ_ATTEMPTS_PATH}/${attemptId}/grade`,
        method: 'POST',
        data: { question_scores: questionScores },
    });
};

