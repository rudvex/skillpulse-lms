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
    enrollments: [],
    courses: [],
    users: [],
    totalEnrollments: 0,
    isLoading: false,
    error: null,
    filters: {},
};

// Configure apiFetch with the nonce
apiFetch.use((options, next) => {
    if (!options.headers) {
        options.headers = {};
    }
    if (window.skillpulseLmsEnrollments && window.skillpulseLmsEnrollments.nonce) {
        options.headers['X-WP-Nonce'] = window.skillpulseLmsEnrollments.nonce;
    }
    return next(options);
});

const StoreKey = "splms/enrollments";
const StoreConfig = {
    reducer,
    actions,
    selectors,
    resolvers,
};

// Create and register the store
const store = createReduxStore(StoreKey, StoreConfig);

register(store);

export default store; 