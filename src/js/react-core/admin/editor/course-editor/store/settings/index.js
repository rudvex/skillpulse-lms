/**
 * Internal dependencies
 */
import * as selectors from './selectors';
import * as actions from './actions';
import * as resolvers from './resolvers';
import * as controls from "./controls";
import reducer from './reducer';

export const StoreKey = "splms/course-settings";
export const StoreConfig = {
    selectors,
    actions,
    reducer,
    resolvers,
    controls: { ...wp.data.controls, ...controls },
};
