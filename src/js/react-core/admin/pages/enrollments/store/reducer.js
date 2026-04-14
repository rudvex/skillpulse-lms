import { 
    SET_ENROLLMENTS, 
    SET_COURSES, 
    SET_USERS, 
    SET_LOADING, 
    SET_ERROR, 
    SET_FILTERS,
    ADD_ENROLLMENT,
    UPDATE_ENROLLMENT,
    DELETE_ENROLLMENT
} from './actions';

const DEFAULT_STATE = {
    enrollments: [],
    courses: [],
    users: [],
    totalEnrollments: 0,
    isLoading: false,
    error: null,
    filters: {},
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case SET_ENROLLMENTS:
            return {
                ...state,
                enrollments: action.enrollments,
                totalEnrollments: action.total,
                isLoading: false,
                error: null,
            };

        case SET_COURSES:
            return {
                ...state,
                courses: action.courses,
                isLoading: false,
                error: null,
            };

        case SET_USERS:
            return {
                ...state,
                users: action.users,
                isLoading: false,
                error: null,
            };

        case SET_LOADING:
            return {
                ...state,
                isLoading: action.isLoading,
            };

        case SET_ERROR:
            return {
                ...state,
                error: action.error,
                isLoading: false,
            };

        case SET_FILTERS:
            return {
                ...state,
                filters: action.filters,
            };

        case ADD_ENROLLMENT:
            return {
                ...state,
                enrollments: [...state.enrollments, action.enrollment],
                totalEnrollments: state.totalEnrollments + 1,
            };

        case UPDATE_ENROLLMENT:
            return {
                ...state,
                enrollments: state.enrollments.map(enrollment =>
                    enrollment.id === action.enrollmentId
                        ? { ...enrollment, ...action.updates }
                        : enrollment
                ),
            };

        case DELETE_ENROLLMENT:
            return {
                ...state,
                enrollments: state.enrollments.filter(
                    enrollment => enrollment.id !== action.enrollmentId
                ),
                totalEnrollments: state.totalEnrollments - 1,
            };

        default:
            return state;
    }
} 