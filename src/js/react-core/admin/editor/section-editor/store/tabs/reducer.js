const initialState = {
    activeTab: 'settings',
};

const reducer = (state = initialState, action) => {
    switch (action.type) {
        case 'SET_ACTIVE_TAB':
            return {
                ...state,
                activeTab: action.tab,
            };
        default:
            return state;
    }
};

export default reducer;
