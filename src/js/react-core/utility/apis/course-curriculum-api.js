import apiFetch from '@wordpress/api-fetch';
import { SPLMS_API_CURRICULUM, SPLMS_API_LESSONS, SPLMS_API_SECTIONS } from "../apiConstant";

/**
 * Fetch course settings from the WordPress REST API.
 *
 * @param {number} courseId The ID of the course.
 * @param {Object} param Optional query parameters.
 * @returns {Promise<Object>} A promise resolving to the course settings.
 */
export async function fetchCourseCurriculum(courseId,param = {}) {
        try {
        // Construct the full path with the query parameters
        const response = await apiFetch({
            path: SPLMS_API_CURRICULUM(courseId),
        });

        return response || {};
    } catch (error) {
        console.error("Failed to fetch course settings:", error);
        throw error;
    }
}

export async function saveCourseCurriculum(courseId, sections) {
    try {
        const response = await apiFetch( {
            path: SPLMS_API_CURRICULUM( courseId ),
            method: "POST",
            data: {
                sections,
            },
        } );

        return response || {};

    }
    catch ( error ) {
        console.error( "Failed to save course curriculum:", error );
        throw error;
    }

}

export async function fetchSidebarLessons(page, search) {
    try {
        const response = await apiFetch({
            path: `${SPLMS_API_LESSONS}?page=${page}&search=${search}`,
            parse: false,
        })

        const data = await response.json();

        // Access headers
        const total = response.headers.get("x-wp-total");
        const totalPages = response.headers.get("x-wp-totalpages");

        return {
            data,
            headers: {
                total: total ? parseInt(total, 10) : 0,
                totalPages: totalPages ? parseInt(totalPages, 10) : 0,
            },
        };

    } catch (error) {
        console.error("Failed to fetch sidebar lessons:", error);
        throw error;
    }

}