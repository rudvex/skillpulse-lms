const initialState = {
    sectionSettings: {},
    isSaving: false,
    isLoading: false,
    error: null,
};

const reducer = (state = initialState, action) => {
    switch (action.type) {
        case "FETCH_SETTINGS_REQUEST":
            return {
                ...state,
                isLoading: true,
                error: null,
            };

        case "FETCH_SETTINGS_SUCCESS":
            return {
                ...state,
                isLoading: false,
                sectionSettings: action.settings,
            };

        case "FETCH_SETTINGS_FAILURE":
            return {
                ...state,
                isLoading: false,
                error: action.error,
            };

        case "SET_IS_SAVING":
            return {
                ...state,
                isSaving: action.isSaving,
            };

        case "UPDATE_SETTINGS_SUCCESS":
            const updatedSettings = { ...state.sectionSettings };

            if (action.groupKey) {
                // Handle grouped field update.
                if (!updatedSettings[action.groupKey]) {
                    updatedSettings[action.groupKey] = {};
                }
                updatedSettings[action.groupKey] = {
                    ...updatedSettings[action.groupKey],
                    [action.setting]: action.value
                };
            } else {
                // Handle non-grouped field update.
                updatedSettings[action.setting] = action.value;
            }

            return {
                ...state,
                sectionSettings: updatedSettings,
            };

        default:
            return state;
    }
};

export default reducer;
