const initialState = {
    quizSettings: {},
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
                quizSettings: action.settings,
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
            const updatedSettings = { ...state.quizSettings };
            
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
                quizSettings: updatedSettings,
            };

        default:
            return state;
    }
};

export default reducer;
