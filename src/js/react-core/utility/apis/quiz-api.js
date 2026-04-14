import apiFetch from '@wordpress/api-fetch';
import { 
    SPLMS_API_QUIZ_SETTINGS,
    SPLMS_API_QUIZ_QUESTIONS,
} from "../apiConstant";

/**
 * Get quiz settings
 * @param {number} quizId - Quiz ID
 * @returns {Promise<Object>} Promise resolving to quiz settings
 */
export async function getQuizSettings(quizId) {
    try {
        const response = await apiFetch({
            path: SPLMS_API_QUIZ_SETTINGS(quizId),
        });
        return response || {};
    } catch (error) {
        console.error("Failed to fetch quiz settings:", error);
        throw error;
    }
}

/**
 * Update quiz settings via the WordPress REST API.
 *
 * @param {number} quizId The ID of the quiz.
 * @param {Object} metaData The meta data to update.
 * @returns {Promise<void>} A promise resolving when the update is complete.
 */
export async function updateQuizSettings(quizId, metaData) {
    try {
        await apiFetch({
            path: SPLMS_API_QUIZ_SETTINGS(quizId),
            method: "POST",
            data: {
                settings: metaData,
            },
        });
    } catch (error) {
        console.error("Failed to update quiz settings:", error);
        throw error;
    }
}

/**
 * Get quiz questions
 * @param {number} quizId - Quiz ID
 * @returns {Promise<Object>} Promise resolving to quiz questions
 */
export async function getQuizQuestions(quizId) {
    try {
        const response = await apiFetch({
            path: SPLMS_API_QUIZ_QUESTIONS(quizId),
        });
        return response || {};
    } catch (error) {
        console.error("Failed to fetch quiz questions:", error);
        throw error;
    }
}

/**
 * Update quiz questions
 * @param {number} quizId - Quiz ID
 * @param {Array} questions - Quiz questions
 * @returns {Promise<Object>} Promise resolving to updated questions
 */
export async function updateQuizQuestions(quizId, questions) {
    try {
        const response = await apiFetch({
            path: SPLMS_API_QUIZ_QUESTIONS(quizId),
            method: 'PUT',
            data: { questions },
        });
        return response || {};
    } catch (error) {
        console.error("Failed to update quiz questions:", error);
        throw error;
    }
}
