/**
 * Actions for the quiz attempts store
 */

import * as QuizAttemptsAPI from '../api';

// Action types
export const SET_ATTEMPTS = 'SET_ATTEMPTS';
export const SET_LOADING = 'SET_LOADING';
export const SET_ERROR = 'SET_ERROR';
export const ADD_ATTEMPT = 'ADD_ATTEMPT';
export const UPDATE_ATTEMPT = 'UPDATE_ATTEMPT';
export const DELETE_ATTEMPT = 'DELETE_ATTEMPT';
export const SET_FILTERS = 'SET_FILTERS';
export const SET_PAGINATION = 'SET_PAGINATION';

/**
 * Set attempts data
 * @param {Array} attempts Attempts array
 * @param {number} total Total count
 */
export function setAttempts(attempts, total = 0) {
    return {
        type: SET_ATTEMPTS,
        attempts,
        total,
    };
}

/**
 * Set loading state
 * @param {boolean} isLoading Loading state
 */
export function setLoading(isLoading) {
    return {
        type: SET_LOADING,
        isLoading,
    };
}

/**
 * Set error state
 * @param {string|null} error Error message
 */
export function setError(error) {
    return {
        type: SET_ERROR,
        error,
    };
}

/**
 * Add new attempt
 * @param {Object} attempt Attempt object
 */
export function addAttempt(attempt) {
    return {
        type: ADD_ATTEMPT,
        attempt,
    };
}

/**
 * Update existing attempt
 * @param {number} attemptId Attempt ID
 * @param {Object} attemptData Attempt data
 */
export function updateAttempt(attemptId, attemptData) {
    return {
        type: UPDATE_ATTEMPT,
        attemptId,
        attemptData,
    };
}

/**
 * Delete attempt
 * @param {number} attemptId Attempt ID
 */
export function deleteAttempt(attemptId) {
    return {
        type: DELETE_ATTEMPT,
        attemptId,
    };
}

/**
 * Set filters
 * @param {Object} filters Filters object
 */
export function setFilters(filters) {
    return {
        type: SET_FILTERS,
        filters,
    };
}

/**
 * Set pagination
 * @param {Object} pagination Pagination object
 */
export function setPagination(pagination) {
    return {
        type: SET_PAGINATION,
        pagination,
    };
}

/**
 * Fetch attempts from API
 * @param {Object} params Query parameters
 */
export function fetchAttempts(params = {}) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            dispatch(setError(null));
            
            const response = await QuizAttemptsAPI.fetchQuizAttempts(params);
            
            if (response && response.success) {
                dispatch(setAttempts(response.attempts || [], response.total_items || 0));
                dispatch(setPagination({
                    page: response.current_page || 1,
                    per_page: response.per_page || 20,
                    total: response.total_items || 0,
                    total_pages: response.total_pages || 1,
                }));
            } else {
                dispatch(setError(response?.message || 'Failed to fetch quiz attempts'));
            }
            
            return response;
        } catch (error) {
            console.error('Error fetching quiz attempts:', error);
            dispatch(setError(error.message || 'Failed to fetch quiz attempts'));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

/**
 * Fetch single attempt from API
 * @param {number} attemptId Attempt ID
 */
export function fetchAttempt(attemptId) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            dispatch(setError(null));
            
            const response = await QuizAttemptsAPI.fetchQuizAttempt(attemptId);
            
            if (response && response.success && response.attempt) {
                dispatch(addAttempt(response.attempt));
            } else {
                dispatch(setError(response?.message || 'Failed to fetch quiz attempt'));
            }
            
            return response;
        } catch (error) {
            console.error('Error fetching quiz attempt:', error);
            dispatch(setError(error.message || 'Failed to fetch quiz attempt'));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

/**
 * Verify attempt via API
 * @param {number} attemptId Attempt ID
 */
export function verifyAttempt(attemptId) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            dispatch(setError(null));
            
            const response = await QuizAttemptsAPI.verifyQuizAttempt(attemptId);
            
            if (response && response.success) {
                dispatch(updateAttempt(attemptId, { status: 'graded', graded_time: new Date().toISOString() }));
            } else {
                dispatch(setError(response?.message || 'Failed to update quiz attempt status'));
            }
            
            return response;
        } catch (error) {
            console.error('Error updating quiz attempt status:', error);
            dispatch(setError(error.message || 'Failed to update quiz attempt status'));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

/**
 * Delete attempt via API
 * @param {number} attemptId Attempt ID
 */
export function deleteAttemptAction(attemptId) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            dispatch(setError(null));
            
            const response = await QuizAttemptsAPI.deleteQuizAttempt(attemptId);
            
            if (response && response.success) {
                dispatch(deleteAttempt(attemptId));
            } else {
                dispatch(setError(response?.message || 'Failed to delete quiz attempt'));
            }
            
            return response;
        } catch (error) {
            console.error('Error deleting quiz attempt:', error);
            dispatch(setError(error.message || 'Failed to delete quiz attempt'));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

