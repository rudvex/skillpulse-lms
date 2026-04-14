import { SET_ACTIVE_TAB } from './actions';

const DEFAULT_STATE = {
    activeTab: window.location.hash ? window.location.hash.replace('#', '') : 'lesson'
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case SET_ACTIVE_TAB:
            return {
                ...state,
                activeTab: action.tab,
            };
        default:
            return state;
    }
} 