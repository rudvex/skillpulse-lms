export const SET_ACTIVE_TAB = 'SET_ACTIVE_TAB';

export default {
    setActiveTab(tab) {
        return {
            type: SET_ACTIVE_TAB,
            tab,
        };
    },
}; 