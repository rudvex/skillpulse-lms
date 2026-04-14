import apiFetch from '@wordpress/api-fetch';
import { 
    SPLMS_API_LESSON_SETTINGS,
} from "../apiConstant";

/**
 * Get lesson settings
 * @param {number} lessonId - Lesson ID
 * @returns {Promise<Object>} Promise resolving to lesson settings
 */
export async function getLessonSettings(lessonId) {
    try {
        const response = await apiFetch({
            path: SPLMS_API_LESSON_SETTINGS(lessonId),
        });
        return response || {};
    } catch (error) {
        console.error("Failed to fetch lesson settings:", error);
        throw error;
    }
}

/**
 * Update lesson settings via the WordPress REST API.
 *
 * @param {number} lessonId The ID of the lesson.
 * @param {Object} metaData The meta data to update.
 * @returns {Promise<void>} A promise resolving when the update is complete.
 */
export async function updateLessonSettings(lessonId, metaData) {
    try {
        await apiFetch({
            path: SPLMS_API_LESSON_SETTINGS(lessonId),
            method: "POST",
            data: {
                settings: metaData,
            },
        });
    } catch (error) {
        console.error("Failed to update lesson settings:", error);
        throw error;
    }
}