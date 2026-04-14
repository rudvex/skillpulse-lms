import { fetchCourseCurriculum, saveCourseCurriculum, fetchSidebarLessons } from "../../../../../utility/apis/course-curriculum-api";

/** Sections **/

export const fetchCurriculumDataStart = () => ({
    type: "FETCH_CURRICULUM_DATA_START",
});

export const fetchCurriculumDataSuccess = (sections, postId) => ({
    type: "FETCH_CURRICULUM_DATA_SUCCESS",
    sections,
    postId,
});

export const fetchCurriculumDataError = (error) => ({
    type: "FETCH_CURRICULUM_DATA_ERROR",
    error,
});

export const saveSection = (sectionTitle) => ({
    type: "SAVE_SECTION",
    sectionTitle,
});

export const deleteSection = (id) => ({
    type: "DELETE_SECTION",
    id,
});

export const startEditingItem = (id, title) => ({
    type: "START_EDITING_ITEM",
    id,
    title,
});

export const updateEditingItem = (id, title) => ({
    type: "UPDATE_EDITING_ITEM",
    id,
    title,
});

export const blurEditingItem = () => ({
    type: "BLUR_EDITING_ITEM",
});

export const deleteItem = (id) => ({
    type: "DELETE_ITEM",
    id,
});

/** Items **/

export const addLesson = (sectionId) => ({
    type: "ADD_LESSON",
    sectionId,
});

export const addQuiz = (sectionId) => ({
    type: "ADD_QUIZ",
    sectionId,
});

export const moveSection = (oldIndex, newIndex) => ({
    type: "MOVE_SECTION",
    oldIndex,
    newIndex,
});

export const moveChildren = (oldSectionId, newSectionId, draggedId, targetId) => ({
    type: "MOVE_CHILDREN",
    oldSectionId,
    newSectionId,
    draggedId,
    targetId
});

export const setIsSaving = (isSaving) => ({
    type: "SET_IS_SAVING",
    isSaving,
});

export const setSidebarLessons = (payload) => ({
    type: "SET_SIDEBAR_LESSONS",
    payload,
} );

export const setSidebarLessonsError = (error) => ({
    type: "SET_SIDEBAR_LESSONS_ERROR",
    error,
} );

/** Data processing methods **/

export const saveCurriculumData = (postId, sections) => {
    return async ({dispatch}) => {
        dispatch(setIsSaving(true));

        try {
            // Save the course curriculum
            await saveCourseCurriculum(postId, sections);

            // Fetch the updated course curriculum
            dispatch(fetchCurriculumData(postId));
        } catch (error) {
            console.error("Action: Error saving course curriculum:", error);
            dispatch(setIsSaving(false));
        }
    }
};

export const fetchCurriculumData = (postId) => {
    return async ({dispatch}) => {
        dispatch(fetchCurriculumDataStart());

        try {
            const courseCurriculum = await fetchCourseCurriculum(postId);

            // Always dispatch success, even if empty array
            // The backend returns an empty array for new courses
            dispatch(fetchCurriculumDataSuccess(courseCurriculum || [], postId));
            
        } catch (error) {
            console.error("Action: Error fetching curriculum:", error);
            
            // For new courses or API errors, use empty array
            dispatch(fetchCurriculumDataSuccess([], postId));
        }
    };
};

export const refreshSidebarLessons = (page = 1, search = '') => {
    return async ({dispatch}) => {
        try {
            const { data: lessons, headers } = await fetchSidebarLessons(page, search);

            if (lessons) {
                dispatch(setSidebarLessons({
                    lessons,
                    headers,
                }));
            } else {
                console.warn("No lessons found for the provided criteria.");
            }
        } catch (error) {
            console.error("Action: Error fetching sidebar lessons:", error);

            // Optionally, dispatch an error action or handle errors in the state
            dispatch(setSidebarLessonsError(error.message));
        }
    };
}