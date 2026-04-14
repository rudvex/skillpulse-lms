import { uniqueId } from "../../../../../utility/helper";

// Helper function to safely get post types at runtime
const getPostTypes = () => {
    if (typeof window !== 'undefined' && window.SPLMSCore_Data && window.SPLMSCore_Data.all_sp_post_types) {
        return window.SPLMSCore_Data.all_sp_post_types;
    }
    // Fallback values if SPLMSCore_Data is not available
    return {
        section: 'sp-section',
        lesson: 'sp-lesson',
        quiz: 'sp-quiz',
    };
};

// Create initial state function to compute at runtime
const getInitialState = () => {
    const postTypes = getPostTypes();
    return {
        sections: [
            {
                id: uniqueId('section'),
                title: 'Section 1',
                type: postTypes.section,
                children: [
                    { id: uniqueId('lesson'), title: 'Lesson 1', type: postTypes.lesson },
                    { id: uniqueId('quiz'), title: 'Quiz 1', type: postTypes.quiz },
                ],
            },
            {
                id: uniqueId('section'),
                title: 'Section 2',
                type: postTypes.section,
                children: [
                    { id: uniqueId('lesson'), title: 'Lesson 2', type: postTypes.lesson },
                    { id: uniqueId('quiz'), title: 'Quiz 2', type: postTypes.quiz },
                ],
            },
        ],
        editingId: null,
        editingName: '',
        isFetching: false,
        isSaving: false,
        saveSuccess: false,
        error: null,
        sidebarLessonData: {
            lessons: [],
            total: 0,
            totalPages: 0,
        }
    };
};

const initialState = getInitialState();

const reducer = (state = initialState, action) => {
    switch (action.type) {
        case 'FETCH_CURRICULUM_DATA_START':
            return { ...state, isFetching: true, error: null };
        case 'FETCH_CURRICULUM_DATA_SUCCESS':
            if ( undefined === action.sections ) {
                action.sections = [];
            }
            return { ...state, isFetching: false, sections: action.sections };
        case 'FETCH_CURRICULUM_DATA_ERROR':
            return { ...state, isFetching: false, error: action.error };
        case 'SAVE_SECTION':
            return {
                ...state,
                sections: [
                    ...state.sections,
                    {
                        id: uniqueId('section'),
                        title: action.sectionTitle,
                        type: getPostTypes().section,
                        children: [],
                    },
                ],
            }
        case 'DELETE_SECTION':
            return { ...state, sections: state.sections.filter((section) => section.id !== action.id), };
        case 'START_EDITING_ITEM':
            return {
                ...state,
                editingId: action.id,
                editingName: action.title,
            };
        case 'UPDATE_EDITING_ITEM':
            return {
                ...state,
                editingName: action.title,
                sections: state.sections.map((section) => {
                    return {
                        ...section,
                        title: section.id === action.id ? action.title : section.title,
                        children: section.children.map((item) => ({
                            ...item,
                            title: item.id === action.id ? action.title : item.title,
                        })),
                    };
                }),
            };
        case 'BLUR_EDITING_ITEM':
            return {
                ...state,
                editingId: null,
                editingName: '',
            };
        case 'DELETE_ITEM':
            return {
                ...state,
                sections: state.sections.map((section) => {
                    return {
                        ...section,
                        children: section.children.filter((item) => item.id !== action.id),
                    };
                }),
            };
        case 'ADD_LESSON':
            return {
                ...state,
                sections: state.sections.map((section) =>
                    section.id === action.sectionId ? {
                            ...section,
                            children: [
                                ...section.children,
                                { id: uniqueId('lesson'), title: 'Lesson Name', type: getPostTypes().lesson },
                            ],
                        }
                        : section
                ),
            };
        case 'ADD_QUIZ':
            return {
                ...state,
                sections: state.sections.map((section) =>
                    section.id === action.sectionId ? {
                            ...section,
                            children: [
                                ...section.children,
                                { id: uniqueId('quiz'), title: 'Quiz Name', type: getPostTypes().quiz },
                            ],
                        }
                        : section
                ),
            };
        case 'MOVE_SECTION':
            const { oldIndex, newIndex } = action;
            const newSections = [...state.sections];
            const oldDraggedIndex = newSections.findIndex((section) => section.id === oldIndex);
            const newTargetIndex = newSections.findIndex((section) => section.id === newIndex);

            if (oldDraggedIndex === -1 || newTargetIndex === -1) {
                console.warn('Invalid section indices for MOVE_SECTION:', oldIndex, newIndex);
                return state; // Prevent state update if indices are invalid
            }

            const [draggedSection] = newSections.splice(oldDraggedIndex, 1);
            newSections.splice(newTargetIndex, 0, draggedSection);

            return {
                ...state,
                sections: newSections,
            };
        case 'MOVE_CHILDREN':
            const { oldSectionId, newSectionId, draggedId, targetId } = action;

            const newSectionsChildren = state.sections.map((section) =>
                section.id === oldSectionId || section.id === newSectionId
                    ? { ...section, children: [...section.children] }
                    : section
            );

            const fromSection = newSectionsChildren.find((section) => section.id === oldSectionId);
            const toSection = newSectionsChildren.find((section) => section.id === newSectionId);

            if (!fromSection || !toSection) {
                console.warn('Invalid section IDs for MOVE_CHILDREN:', oldSectionId, newSectionId);
                return state; // Prevent state update if sections are invalid
            }

            const draggedIndex = fromSection.children.findIndex((item) => item.id === draggedId);
            const targetIndex = toSection.children.findIndex((item) => item.id === targetId);

            if (draggedIndex === -1 || targetIndex === -1) {
                console.warn('Invalid children indices for MOVE_CHILDREN:', draggedId, targetId);
                return state; // Prevent state update if indices are invalid
            }

            const [draggedItem] = fromSection.children.splice(draggedIndex, 1);
            toSection.children.splice(targetIndex, 0, draggedItem);

            return {
                ...state,
                sections: newSectionsChildren,
            };
        case 'SAVE_CURRICULUM_DATA_SUCCESS':
            return {
                ...state,
                isSaving: false,
                saveSuccess: true,
            };
        case 'SAVE_CURRICULUM_DATA_ERROR':
            return {
                ...state,
                isSaving: false,
                error: action.error,
            };
        case 'SET_IS_SAVING':
            return {
                ...state,
                isSavingPost: action.isSaving,
            };

        case 'SET_SIDEBAR_LESSONS':
            return {
                ...state,
                sidebarLessonData: {
                    ...state.sidebarLessonData,
                    lessons: action.payload.lessons || [],
                    total: action.payload.headers.total || 0,
                    totalPages: action.payload.headers.totalPages || 0,
                }
            };

        default:
            return state;
    }
};

export default reducer;
