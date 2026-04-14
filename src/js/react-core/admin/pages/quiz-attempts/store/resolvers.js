/**
 * Resolvers for the quiz attempts store
 */

import * as QuizAttemptsAPI from '../api';
import { addAttempt } from './actions';
import { fetchAttempts } from './actions';

/**
 * Resolver for getAttempts selector
 *
 * NOTE: This resolver is intentionally disabled to prevent duplicate API calls.
 * The QuizAttemptsPage component explicitly calls loadAttempts() in componentDidMount()
 * with proper parameters, so we don't need the resolver to auto-fetch.
 *
 * Keeping this resolver commented but present for documentation purposes.
 * If you need auto-fetching in other contexts, uncomment and test carefully.
 *
 * @param {Object} state Current state
 * @param {Object} selectorArgs Selector arguments
 * @param {Object} registry Registry object
 */
// export const getAttempts = () => async ({ dispatch, select }) => {
//     const attempts = select.getAttempts();
//     if (!attempts || attempts.length === 0) {
//         await dispatch(fetchAttempts({
//             page: 1,
//             per_page: 20,
//             order_by: 'attempt_time',
//             order: 'DESC'
//         }));
//     }
// };

/**
 * Resolver for getAttemptById selector
 * @param {Object} state Current state
 * @param {Object} selectorArgs Selector arguments
 * @param {Object} registry Registry object
 */
export const getAttemptById = (attemptId) => async ({ dispatch, select }) => {
    const attempts = select.getAttempts();
    const existingAttempt = attempts.find(attempt => attempt.id === attemptId);
    
    if (!existingAttempt) {
        // Fetch single attempt if not in store
        try {
            const response = await QuizAttemptsAPI.fetchQuizAttempt(attemptId);
            
            if (response && response.attempt) {
                dispatch(addAttempt(response.attempt));
            }
        } catch (error) {
            console.error('Error fetching quiz attempt:', error);
        }
    }
};

