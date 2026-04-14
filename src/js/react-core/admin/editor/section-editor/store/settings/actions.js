import { getSectionSettings, updateSectionSettings } from "../../../../../utility/apis/section-api";

export const fetchSettingsRequest = () => ({
    type: "FETCH_SETTINGS_REQUEST",
});

export const fetchSettingsSuccess = (settings, postId) => ({
    type: "FETCH_SETTINGS_SUCCESS",
    settings,
    postId,
});

export const fetchSettingsFailure = (error) => ({
    type: "FETCH_SETTINGS_FAILURE",
    error,
});

export const setIsSaving = (isSaving) => ({
    type: "SET_IS_SAVING",
    isSaving,
});

export const fetchSettings = (postId) => {
    return async ({dispatch}) => {
        dispatch(fetchSettingsRequest());
        try {
            const sectionSettings = await getSectionSettings(postId);
            if (sectionSettings) {
                dispatch(fetchSettingsSuccess(sectionSettings, postId));
            }
        } catch (error) {
            console.error("Action: Error fetching section settings:", error);
            dispatch(fetchSettingsFailure(error));
        }
    };
};

export const updateSetting = (postId, setting, value, groupKey = null) => {
    return async ({dispatch}) => {
        try {
            dispatch({
                type: "UPDATE_SETTINGS_SUCCESS",
                postId,
                setting,
                value,
                groupKey,
            });
        } catch (error) {
            console.error("Action: Error updating section settings:", error);
        }
    };
};

/**
 * Update all section settings via the WordPress REST API.
 *
 * @param {number} postId - The ID of the post.
 * @param {any} settings - The settings to update.
 * @returns {Promise<void>} A promise that resolves when the update is complete.
 */
export async function updateAllSectionSetting(postId, settings) {
    return async ( { dispatch }) => {
        dispatch(setIsSaving(true));
        try {
            await updateSectionSettings(postId, settings);
        } catch (error) {
            await dispatch(fetchSettingsFailure(error));
        }
    };
}
