import apiFetch from '@wordpress/api-fetch';
import {
    SPLMS_API_SECTION_SETTINGS,
} from "../apiConstant";


/**
 * Get section settings
 * @param {number} sectionId - Section ID
 * @returns {Promise<Object>} Promise resolving to section settings
 */
export async function getSectionSettings(sectionId) {
    try {
        const response = await apiFetch({
            path: SPLMS_API_SECTION_SETTINGS(sectionId),
        });
        return response || {};
    } catch (error) {
        console.error("Failed to fetch section settings:", error);
        throw error;
    }
}

/**
 * Update section settings via the WordPress REST API.
 *
 * @param {number} sectionId The ID of the section.
 * @param {Object} metaData The meta data to update.
 * @returns {Promise<void>} A promise resolving when the update is complete.
 */
export async function updateSectionSettings(sectionId, metaData) {
    try {
        await apiFetch({
            path: SPLMS_API_SECTION_SETTINGS(sectionId),
            method: "POST",
            data: {
                settings: metaData,
            },
        });
    } catch (error) {
        console.error("Failed to update section settings:", error);
        throw error;
    }
}