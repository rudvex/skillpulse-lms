const initialState = {
    courseSettings: {},
    isSaving: false,
    isLoading: false,
    error: null,
};

const reducer = (state = initialState, action) => {
    switch (action.type) {
        case "FETCH_SETTINGS_FAILURE":
            return {
                ...state,
                isLoading: false,
                error: action.error,
            };

        case "SET_SETTINGS":
            return {
                ...state,
                courseSettings: action.settings,
                isLoading: false,
                error: null,
            };

        case "SET_IS_SAVING":
            return {
                ...state,
                isSaving: action.isSaving,
            };

        case "UPDATE_SETTINGS_SUCCESS":
            const updatedSettings = { ...state.courseSettings };
            
            if (action.groupKey) {
                // Handle grouped field update
                if (!updatedSettings[action.groupKey]) {
                    updatedSettings[action.groupKey] = {};
                }
                updatedSettings[action.groupKey] = {
                    ...updatedSettings[action.groupKey],
                    [action.setting]: action.value
                };
            } else {
                // Handle non-grouped field update
                updatedSettings[action.setting] = action.value;
            }
            
            return {
                ...state,
                courseSettings: updatedSettings,
            };

        default:
            return state;
    }
};

export default reducer;
