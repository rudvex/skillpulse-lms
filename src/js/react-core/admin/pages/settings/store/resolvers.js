import apiFetch from '@wordpress/api-fetch';
import { setLoading, setSettings, setError } from './actions';
import { SPLMS_API_SETTINGS_CONFIG } from "../../../../utility/apiConstant";

/**
 * Get settings using the new unified config API.
 * This replaces separate config and settings loading.
 */
export const getSettingsConfig = (context = 'admin') => async ({ dispatch }) => {
    try {
        dispatch(setLoading(true));
        const response = await apiFetch({
            path: `${SPLMS_API_SETTINGS_CONFIG}?context=${context}`
        });

        // Transform the config response to match the expected settings structure
        const settingsData = {};
        if (response.tabs) {
            response.tabs.forEach(tab => {
                settingsData[tab.id] = {};
                tab.sections?.forEach(section => {
                    settingsData[tab.id][section.id] = {};
                    section.fields?.forEach(field => {
                        if (field.id && field.value !== undefined) {
                            settingsData[tab.id][section.id][field.id] = field.value;
                        }
                    });
                });
            });
        }

        dispatch(setSettings(settingsData));
        return response; // Return full config response
    } catch (error) {
        dispatch(setError(error.message));
    } finally {
        dispatch(setLoading(false));
    }
};




