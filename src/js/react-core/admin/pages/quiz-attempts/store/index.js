import { createReduxStore, register } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
/**
 * Internal dependencies
 */
import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
import * as resolvers from './resolvers';

// Define default state
const DEFAULT_STATE = {
    attempts: [],
    isLoading: false,
    error: null,
    filters: {},
    pagination: {
        page: 1,
        per_page: 20,
        total: 0,
        total_pages: 1
    }
};

// Configure apiFetch with the nonce
apiFetch.use((options, next) => {
    if (!options.headers) {
        options.headers = {};
    }
    if (window.SPLMSCore_Data && window.SPLMSCore_Data.restNonce) {
        options.headers['X-WP-Nonce'] = window.SPLMSCore_Data.restNonce;
    }
    return next(options);
});

const StoreKey = "splms/quiz-attempts";
const StoreConfig = {
    reducer,
    actions,
    selectors,
    resolvers,
};
// Create and register the store
const store = createReduxStore( StoreKey, StoreConfig );

register(store);

export default store;

