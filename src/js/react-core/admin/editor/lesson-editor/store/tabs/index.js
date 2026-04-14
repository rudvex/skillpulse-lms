export const StoreKey = 'splms/lesson-tabs';

import actions from './actions';
import reducer from './reducer';

export const StoreConfig = {
    reducer,
    actions,
    selectors: {
        getActiveTab(state) {
            return state.activeTab;
        },
    },
}; 