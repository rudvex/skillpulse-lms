export const StoreKey = 'splms/section-tabs';

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
