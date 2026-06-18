import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { v4 as uuidv4 } from "uuid";
import { getAdminUrl } from './url';

/**
 * Generate a local SVG data URI for a user's initials avatar.
 * Replaces the external ui-avatars.com service.
 *
 * @param {string} name User display name.
 * @param {number} size Avatar size in pixels.
 * @return {string} SVG data URI.
 */
export const getInitialsAvatar = ( name, size = 40 ) => {
	const displayName = name || 'Student';
	const parts = displayName.trim().split( /\s+/ );
	const initials = parts.length > 1
		? ( parts[ 0 ][ 0 ] + parts[ parts.length - 1 ][ 0 ] ).toUpperCase()
		: displayName.substring( 0, 2 ).toUpperCase();

	const fontSize = Math.round( size * 0.4 );
	const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${ size }" height="${ size }" viewBox="0 0 ${ size } ${ size }"><rect width="100%" height="100%" fill="#7e75ff"/><text x="50%" y="50%" dy=".1em" fill="#fff" font-family="Arial,sans-serif" font-size="${ fontSize }" font-weight="600" text-anchor="middle" dominant-baseline="central">${ initials }</text></svg>`;

	return 'data:image/svg+xml,' + encodeURIComponent( svg );
};


// Function to extract the text domain and label for translation
export const translateLabel = (text) => {
    if (!text || typeof text !== 'string') {
        return text;
    }

    // Split only on the FIRST colon
    const index = text.indexOf(':');
    if (index > -1) {
        const domain = text.substring(0, index);
        const string = text.substring(index + 1);

        // Ensure domain looks like a text-domain (no slashes, spaces, etc.)
        if (/^[a-z0-9-_]+$/.test(domain.trim())) {
            return __(string, domain.trim());
        }
    }

    // Return text as-is
    return text;
};

/**
 * NoticeMessage Component - Now uses skillpulseToast
 * @param {Object} props
 * @param {string} props.status - 'success' or 'error' for the notice type.
 * @param {string} props.message - The message to display.
 * @param {Function} props.onDismiss - Function to call when notice is dismissed (deprecated, kept for compatibility).
 */
export const NoticeMessage = ({ status, message, onDismiss }) => {
    if (!message) return null;

    // Use skillpulseToast instead of Notice component
    if (window.skillpulseToast) {
        if (status === 'error') {
            window.skillpulseToast.error(message);
        } else {
            window.skillpulseToast.success(message);
        }
    }

    // Return null since toast handles the display
    return null;
};

export const NoticeBar = ( { message, type, mode = 'notice' } ) => {
    // Use skillpulseToast instead of Notice/Snackbar components
    if (window.skillpulseToast && message) {
        if (type === 'error') {
            window.skillpulseToast.error(message);
        } else if (type === 'warning') {
            window.skillpulseToast.warning(message);
        } else if (type === 'success') {
            window.skillpulseToast.success(message);
        } else {
            window.skillpulseToast.info(message);
        }
    }

    // Return null since toast handles the display
    return null;
}

export const getActiveTabConfig = (tabs, activeTab) => {
    return tabs.find(tab => tab.id === activeTab);
};



export const getPostUrl = (href) => {
    return href;
};


export const uniqueId = (preFix = null) => {
    if ( preFix === null ) {
        return uuidv4();
    }

    return `${preFix}-${uuidv4()}`;
}

/**
 * Format date for display
 * @param {string} dateString - Date string to format
 * @return {string} - Formatted date
 */
export function formatDate(dateString) {
    if (!dateString) return '—';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '—';
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Configuration cache for REST API responses
const configCache = new Map();

// Map old configuration keys to new module names
const configModuleMapping = {
    'lesson_settings_config': 'lessons',
    'course_settings_config': 'courses',
    'quiz_settings_config': 'quizzes',
    'section_settings_config': 'sections',
    'settings_config': 'settings',
    'question_builder_config': 'questions'
};

/**
 * Get dynamic REST API URL with fallback logic
 * @param {string} endpoint - Optional endpoint path to append
 * @returns {string} Full REST API URL
 */
export const getRestApiUrl = (endpoint = '') => {
    const apiRoot = window?.wpApiSettings?.root || window?.SPLMSCore_Data?.rest_url || window?.splms_frontend?.rest_url || '/wp-json/';
    const base = apiRoot.endsWith('/') ? apiRoot : `${apiRoot}/`;
    const path = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    return `${base}${path}`;
};

/**
 * Get configuration using new Config Loader REST API with caching
 * @param {string} key - Configuration key (e.g., 'lesson_settings_config')
 * @param {string} context - Context (admin, editor, frontend, api)
 * @param {Object} params - Additional parameters (e.g., { course_id: 123 })
 * @returns {Promise<Object>} Configuration object
 */
export const getConfig = async (key, context = 'admin', params = {}) => {
    // Map old key to module name
    const module = configModuleMapping[key];
    if (!module) {
        console.warn(`Unknown configuration key: ${key}`);
        return {};
    }

    // Create cache key including parameters for course-specific configs
    const paramString = Object.keys(params).length > 0 ? JSON.stringify(params) : '';
    const cacheKey = `${module}_${context}_${paramString}`;
    if (configCache.has(cacheKey)) {
        return configCache.get(cacheKey);
    }

    try {
        // Build query parameters
        const queryParams = new URLSearchParams({ context, ...params });
        const url = getRestApiUrl(`splms/v1/config/${module}?${queryParams.toString()}`);

        // Make REST API call
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'include',
            headers: {
                'X-WP-Nonce': window.wpApiSettings?.nonce || '',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const config = data.config || {};

        // Cache the result
        configCache.set(cacheKey, config);

        return config;
    } catch (error) {
        console.error(`Failed to load configuration for ${key}:`, error);
        return {};
    }
};

/**
 * Get configuration synchronously (for backward compatibility)
 * Uses cached data only
 * @param {string} key - Configuration key
 * @returns {Object} Configuration object
 */
export const getConfigSync = (key) => {
    // Map to module name and check cache
    const module = configModuleMapping[key];
    if (module) {
        const cacheKey = `${module}_admin`;
        if (configCache.has(cacheKey)) {
            return configCache.get(cacheKey);
        }
    }

    return {};
};

/**
 * Get global settings synchronously (for backward compatibility)
 * @returns {Object} Settings object
 */
export const getGlobalSettingsSync = () => {
    // Try old format first
    if (window.SPLMSCore_Data?.global_settings) {
        return window.SPLMSCore_Data.global_settings;
    }

    // Global settings are not stored in config cache - they are actual settings data
    // Config files only contain form structures, not the data itself
    return {};
};

/**
 * Preload configurations for commonly used modules
 * Call this during app initialization to populate cache
 * @param {Array<string>} modules - Module names to preload
 * @param {string} context - Context for preloading
 */
export const preloadConfigs = async (modules = ['courses', 'lessons', 'quizzes', 'settings'], context = 'admin') => {
    const promises = modules.map(module => {
        const cacheKey = `${module}_${context}`;
        if (!configCache.has(cacheKey)) {
            // Convert module name back to old key format for getConfig
            const oldKey = Object.keys(configModuleMapping).find(k => configModuleMapping[k] === module);
            if (oldKey) {
                return getConfig(oldKey, context);
            }
        }
        return Promise.resolve();
    });

    try {
        await Promise.all(promises);
    } catch (error) {
        console.error('Configuration preloading failed:', error);
    }
};

/**
 * Clear configuration cache
 * @param {string} module - Optional specific module to clear
 */
export const clearConfigCache = (module = null) => {
    if (module) {
        // Clear specific module from cache
        for (const key of configCache.keys()) {
            if (key.startsWith(`${module}_`)) {
                configCache.delete(key);
            }
        }
    } else {
        // Clear all cache
        configCache.clear();
    }
};

/**
 * Enhanced isVisibleField function with automatic global settings access
 * @param {Object} field - Field configuration
 * @param {Object} settings - Current settings context
 * @param {Function} getAllFields - Function to get all fields
 * @param {Function} getFieldValueFromSettings - Function to get field value
 * @returns {boolean} Whether field should be visible
 */
export function isVisibleField(field, settings, getAllFields = null, getFieldValueFromSettings = null) {
    if (!field.conditional) {
        return true;
    }

    // Get global settings from localized data (simple and reliable)
    const globalSettings = getGlobalSettingsSync();

    // Check if this is the new multiple-condition format
    if (field.conditional.operator && field.conditional.conditions) {
        return evaluateMultipleConditions(field.conditional, settings, getAllFields, getFieldValueFromSettings, globalSettings);
    }

    // Single-condition format
    return evaluateSingleCondition(field.conditional, settings, getAllFields, getFieldValueFromSettings, globalSettings);
}

/**
 * Evaluate a single condition with cross-store support
 */
function evaluateSingleCondition(conditional, settings, getAllFields, getFieldValueFromSettings, globalSettings = null) {
    const { key, value } = conditional;
    let currentValue;

    // Check if this is a global settings reference (prefixed with 'global:')
    if (key.startsWith('global:')) {
        const globalKey = key.substring(7); // Remove 'global:' prefix
        if (globalSettings) {
            currentValue = findValueInGlobalSettings(globalSettings, globalKey);
        } else {
            return false; // If no global settings provided, hide field
        }
    } else {
        // Try to get related field definition (optional, used in complex cases)
        let conditionalField = null;
        if (typeof getAllFields === 'function') {
            const allFields = getAllFields();
            conditionalField = allFields.find(f => f.id === key);
        }

        // Resolve current value depending on context
        if (conditionalField && typeof getFieldValueFromSettings === 'function') {
            currentValue = getFieldValueFromSettings(settings, conditionalField);
        } else {
            if (key in settings) {
                currentValue = settings[key];
            } else {
                const findValueDeep = (obj, targetKey) => {
                    for (const prop in obj) {
                        if (!Object.prototype.hasOwnProperty.call(obj, prop)) continue;
                        const val = obj[prop];
                        if (prop === targetKey) return val;
                        if (val && typeof val === 'object') {
                            const found = findValueDeep(val, targetKey);
                            if (found !== undefined) return found;
                        }
                    }
                    return undefined;
                };
                currentValue = findValueDeep(settings, key);
            }
        }
    }

    return compareValues(currentValue, value);
}

/**
 * Evaluate multiple conditions with AND/OR operators and cross-store support
 */
function evaluateMultipleConditions(conditional, settings, getAllFields, getFieldValueFromSettings, globalSettings = null) {
    const { operator, conditions } = conditional;
    
    if (!Array.isArray(conditions) || conditions.length === 0) {
        return true;
    }

    const results = conditions.map(condition => 
        evaluateSingleCondition(condition, settings, getAllFields, getFieldValueFromSettings, globalSettings)
    );

    switch (operator.toUpperCase()) {
        case 'AND':
            return results.every(result => result === true);
        case 'OR':
            return results.some(result => result === true);
        default:
            console.warn(`Unknown conditional operator: ${operator}. Defaulting to AND.`);
            return results.every(result => result === true);
    }
}

/**
 * Find value in global settings with nested key support
 * @param {Object} globalSettings - Global settings object
 * @param {string} key - Key to find (supports dot notation like 'certificates.enable_certificates')
 * @returns {any} Found value or undefined
 */
function findValueInGlobalSettings(globalSettings, key) {
    if (!globalSettings || typeof globalSettings !== 'object') {
        return undefined;
    }

    // Support dot notation for nested keys
    const keys = key.split('.');
    let current = globalSettings;
    
    for (const k of keys) {
        if (current && typeof current === 'object' && k in current) {
            current = current[k];
        } else {
            return undefined;
        }
    }
    
    return current;
}

/**
 * Compare two values with type coercion support
 */
function compareValues(currentValue, expectedValue) {
    if (Array.isArray(expectedValue)) {
        return expectedValue.includes(currentValue);
    }

    if (typeof currentValue === 'boolean' && typeof expectedValue === 'string') {
        return currentValue.toString() === expectedValue;
    }

    if (typeof currentValue === 'number' && typeof expectedValue === 'string') {
        return currentValue === Number(expectedValue);
    }

    if (currentValue == null) {
        return false;
    }

    return currentValue === expectedValue;
}



// Simple global access for components that need it
if (typeof window !== 'undefined') {
    window.getConfig = getConfig;
    window.getConfigSync = getConfigSync;
    window.clearConfigCache = clearConfigCache;
    window.preloadConfigs = preloadConfigs;
}
