import { SET_SETTINGS, SET_LOADING,SET_IS_SAVING, SET_ERROR, UPDATE_TAB, UPDATE_SETTINGS_STATE } from './actions';

const DEFAULT_STATE = {
    activeTab: null, // Will be set dynamically based on URL hash and available tabs
    settings: {},
    isSaving: false,
    isLoading: false,
    fields: [],
    error: null,
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case SET_SETTINGS:
            console.log('SET_SETTINGS:', {
                previousSettings: state.settings,
                newSettings: action.settings,
                settingsKeys: Object.keys(action.settings || {})
            });
            return {
                ...state,
                settings: action.settings,
                isLoading: false,
                error: null
            };

        case SET_LOADING:
            return {
                ...state,
                isLoading: action.isLoading
            };
        case SET_IS_SAVING:
            return {
                ...state,
                isSaving: action.isSaving,
            };

        case SET_ERROR:
            return {
                ...state,
                error: action.error,
                isLoading: false
            };

        case UPDATE_TAB:
            return {
                ...state,
                activeTab: action.tab,
            };

        case UPDATE_SETTINGS_STATE:
            // Ensure safe access to nested state structures
            const currentTab = state.settings[action.tab] || {};
            const currentSection = currentTab[action.sectionId] || {};

            const updatedSettings = {
                ...state.settings,
                [action.tab]: {
                    ...currentTab,
                    [action.sectionId]: {
                        ...currentSection,
                        [action.setting]: action.value,
                    },
                },
            };

            // Add debugging to track state updates
            console.log('UPDATE_SETTINGS_STATE:', {
                tab: action.tab,
                sectionId: action.sectionId,
                setting: action.setting,
                value: action.value,
                updatedSettings
            });

            return {
                ...state,
                settings: updatedSettings,
            };

        default:
            return state;
    }
}