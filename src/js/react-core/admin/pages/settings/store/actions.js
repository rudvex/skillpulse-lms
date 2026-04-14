import apiFetch from '@wordpress/api-fetch';
import { SPLMS_API_SETTINGS_TAB, SPLMS_API_SETTINGS_CONFIG } from "../../../../utility/apiConstant";
// Action Types
export const SET_SETTINGS = 'SET_SETTINGS';
export const SET_LOADING = 'SET_LOADING';
export const SET_IS_SAVING = 'SET_IS_SAVING';
export const SET_ERROR = 'SET_ERROR';
export const UPDATE_TAB = 'UPDATE_TAB';

export const UPDATE_SETTINGS_STATE = 'UPDATE_SETTINGS_STATE';

// Action Creators
export function setSettings(settings) {
    return {
        type: SET_SETTINGS,
        settings
    };
}

export function setLoading(isLoading) {
    return {
        type: SET_LOADING,
        isLoading
    };
}
export const setIsSaving = (isSaving) => ({
    type: SET_IS_SAVING,
    isSaving,
});
export function setError(error) {
    return {
        type: SET_ERROR,
        error
    };
}

export function clearError() {
    return {
        type: SET_ERROR,
        error: null
    };
}

export function updateTab(tab) {
    return {
        type: UPDATE_TAB,
        tab
    };
}

export const updateSettingState = (setting, value, sectionId, tab) => {
    return async ({dispatch}) => {

        try {
            // Optionally update the local state after saving
            dispatch({
                type: UPDATE_SETTINGS_STATE,
                setting,
                value,
                sectionId,
                tab,
            });
        } catch (error) {
            console.error("Action: Error updating settings:", error);
        }
    };
};

export function updateSetting(tabId, settingData) {
    return async ({ dispatch }) => {
        try {
            dispatch(setIsSaving(true));
            const response = await apiFetch({
                path: SPLMS_API_SETTINGS_TAB(tabId),
                method: 'PUT',
                data: settingData
            });
            
            // Don't update settings state during save to prevent screen blink
            // The current form values are already in the component state
            // and will be persisted on the server
            
            return response;
        } catch (error) {
            dispatch(setError(error.message));
            throw error;
        } finally {
            dispatch(setIsSaving(false));
        }
    };
}

