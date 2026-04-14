import { createSelector } from '@wordpress/data';

export const getCourseSettings = (state) => state.courseSettings || {};
export const isSaving = (state) => !!state.isSaving;
export const isLoading = (state) => !!state.isLoading;
export const getError = (state) => state.error || null;

// Memoized selector for grouped settings
export const getGroupedSettings = createSelector(
    (state) => state.courseSettings || {},
    (courseSettings) => {
        const grouped = {};
        Object.keys(courseSettings).forEach(key => {
            const value = courseSettings[key];
            if (typeof value === 'object' && value !== null) {
                grouped[key] = value;
            }
        });
        return grouped;
    }
);
