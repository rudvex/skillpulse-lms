/**
 * Reducer for the quiz attempts store
 */

import {
    SET_ATTEMPTS,
    SET_LOADING,
    SET_ERROR,
    ADD_ATTEMPT,
    UPDATE_ATTEMPT,
    DELETE_ATTEMPT,
    SET_FILTERS,
    SET_PAGINATION
} from './actions';

/**
 * Default state
 */
const DEFAULT_STATE = {
    attempts: [],
    isLoading: false,
    error: null,
    filters: {},
    pagination: {
        page: 1,
        per_page: 20,
        total: 0,
        total_pages: 1
    }
};

/**
 * Quiz attempts reducer
 * @param {Object} state Current state
 * @param {Object} action Action object
 * @returns {Object} New state
 */
export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case SET_ATTEMPTS:
            return {
                ...state,
                attempts: action.attempts,
                total: action.total
            };

        case SET_LOADING:
            return {
                ...state,
                isLoading: action.isLoading
            };

        case SET_ERROR:
            return {
                ...state,
                error: action.error
            };

        case ADD_ATTEMPT:
            // Check if attempt already exists
            const existingIndex = state.attempts.findIndex(a => a.id === action.attempt.id);
            if (existingIndex >= 0) {
                return {
                    ...state,
                    attempts: state.attempts.map((attempt, index) =>
                        index === existingIndex ? action.attempt : attempt
                    )
                };
            }
            return {
                ...state,
                attempts: [...state.attempts, action.attempt]
            };

        case UPDATE_ATTEMPT:
            return {
                ...state,
                attempts: state.attempts.map(attempt =>
                    attempt.id === action.attemptId
                        ? { ...attempt, ...action.attemptData }
                        : attempt
                )
            };

        case DELETE_ATTEMPT:
            return {
                ...state,
                attempts: state.attempts.filter(attempt => attempt.id !== action.attemptId)
            };

        case SET_FILTERS:
            return {
                ...state,
                filters: action.filters
            };

        case SET_PAGINATION:
            return {
                ...state,
                pagination: action.pagination
            };

        default:
            return state;
    }
}

