import apiFetch from '@wordpress/api-fetch';
import { SPLMS_API_COURSE_SETTINGS } from "../../../../../utility/apiConstant";

/**
 * Update course settings via the WordPress REST API.
 */
async function updateCourseSettings(postId, metaData) {
    try {
        await apiFetch({
            path: SPLMS_API_COURSE_SETTINGS(postId),
            method: "POST",
            data: {
                settings: metaData,
            },
        });
    } catch (error) {
        console.error("Failed to update course settings:", error);
        throw error;
    }
}

export const fetchSettingsFailure = (error) => ({
    type: "FETCH_SETTINGS_FAILURE",
    error,
});

export const setIsSaving = (isSaving) => ({
    type: "SET_IS_SAVING",
    isSaving,
});

export const setSettings = (settings) => ({
    type: "SET_SETTINGS",
    settings,
});

export const updateSetting = (postId, setting, value, groupKey = null) => {
    return async ({dispatch}) => {
        try {
            // Optionally update the local state after saving
            dispatch({
                type: "UPDATE_SETTINGS_SUCCESS",
                postId,
                setting,
                value,
                groupKey,
            });
        } catch (error) {
            console.error("Action: Error updating course settings:", error);
        }
    };
};


/**
 * Update a specific course setting via the WordPress REST API.
 *
 * @param {number} postId - The ID of the post.
 * @param {any} settings - The name of the setting to update.
 * @returns {Promise<void>} A promise that resolves when the update is complete.
 */
export async function updateAllCourseSetting(postId, settings) {
    return async ( { dispatch }) => {
        dispatch(setIsSaving(true));
        try {
            await updateCourseSettings(postId, settings);
        } catch (error) {
            await dispatch(fetchSettingsFailure(error));
        }
    };
}
