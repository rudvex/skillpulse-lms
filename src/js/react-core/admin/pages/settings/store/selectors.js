
/**
 * Get all settings
 * @param {Object} state Store state
 * @returns {Array} Array of settings
 */
export function getSettings(state) {
    return state.settings || [];
}

/**
 * Get loading state
 * @param {Object} state Store state
 * @returns {boolean} Loading state
 */
export function getLoading(state) {
    return state.isLoading || false;
}

/**
 * Get saving state
 *
 * @param {Object} state Store state
 */
export function getSaving(state) {
    return !!state.isSaving;
}

/**
 * Get error state
 * @param {Object} state Store state
 * @returns {Object|null} Error object or null
 */
export function getError(state) {
    return state.error || null;
}


/**
 * Get the active tab
 * @param {Object} state Store state
 * @returns {string} Active tab identifier
 */
export function getActiveTab(state) {
    return state.activeTab || "general";
}

// export const getSettings = (state) => state.settings || {};
// export const isSaving = (state) => !!state.isSaving;
// export const isLoading = (state) => !!state.isLoading;
// export const getError = (state) => state.error || null;
//
// export const getActiveTab = (state) => state.activeTab || "general";
//
// export const getFields = (state) => state.fields || [];
//

