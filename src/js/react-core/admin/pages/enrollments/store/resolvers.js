import { fetchEnrollments, fetchCourses, fetchUsers } from './actions';

export const getEnrollments = () => async ({ dispatch }) => {
    await dispatch(fetchEnrollments());
};

export const getCourses = () => async ({ dispatch }) => {
    await dispatch(fetchCourses());
};

export const getUsers = () => async ({ dispatch }) => {
    await dispatch(fetchUsers());
}; 