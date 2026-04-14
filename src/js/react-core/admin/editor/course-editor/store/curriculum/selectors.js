
import { createSelector } from '@wordpress/data';

export const isFetching = (state) => {
    return state.isFetching || false;
}
export const isSaving = (state) => {
    return state.isSaving || false;
}
export const getError = (state) => {
    return state.error || null;
}

export const getEditingItem = (state) => {
    return {
        id: state.editingId,
        title: state.editingName,
    };
}

export const getCurriculumData = (state) => {
    return state.sections || [];
}

// Memoized selector for better performance
export const getSection = createSelector(
    (state, sectionId) => {
        if (!sectionId) {
            return null;
        }
        return state.sections.find((section) => section.id === sectionId) || null;
    },
    (state, sectionId) => [state.sections, sectionId]
);

// Memoized selector for section children
export const getSectionChildren = createSelector(
    (state, sectionId) => {
        if (!sectionId) {
            return [];
        }
        const section = getSection(state, sectionId);
        return section ? section.children : [];
    },
    (state, sectionId) => [state.sections, sectionId]
);

export const getSearchedLessonsData = (state) => {
    return state.sidebarLessonData || [];
}