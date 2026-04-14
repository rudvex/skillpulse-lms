import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

// Action Types
export const SET_ENROLLMENTS = 'SET_ENROLLMENTS';
export const SET_COURSES = 'SET_COURSES';
export const SET_USERS = 'SET_USERS';
export const SET_LOADING = 'SET_LOADING';
export const SET_ERROR = 'SET_ERROR';
export const SET_FILTERS = 'SET_FILTERS';
export const ADD_ENROLLMENT = 'ADD_ENROLLMENT';
export const UPDATE_ENROLLMENT = 'UPDATE_ENROLLMENT';
export const DELETE_ENROLLMENT = 'DELETE_ENROLLMENT';

// Action Creators
export function setEnrollments(enrollments, total = 0) {
    return {
        type: SET_ENROLLMENTS,
        enrollments,
        total,
    };
}

export function setCourses(courses) {
    return {
        type: SET_COURSES,
        courses,
    };
}

export function setUsers(users) {
    return {
        type: SET_USERS,
        users,
    };
}

export function setLoading(isLoading) {
    return {
        type: SET_LOADING,
        isLoading,
    };
}

export function setError(error) {
    return {
        type: SET_ERROR,
        error,
    };
}

export function clearError() {
    return {
        type: SET_ERROR,
        error: null,
    };
}

export function setFilters(filters) {
    return {
        type: SET_FILTERS,
        filters,
    };
}

export function addEnrollment(enrollment) {
    return {
        type: ADD_ENROLLMENT,
        enrollment,
    };
}

export function updateEnrollment(enrollmentId, updates) {
    return {
        type: UPDATE_ENROLLMENT,
        enrollmentId,
        updates,
    };
}

export function deleteEnrollment(enrollmentId) {
    return {
        type: DELETE_ENROLLMENT,
        enrollmentId,
    };
}

// Async Actions
export function fetchEnrollments(filters = {}) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const queryParams = new URLSearchParams();
            
            // Add filters to query params
            if (filters.search) queryParams.append('search', filters.search);
            if (filters.course_id) queryParams.append('course_id', filters.course_id);
            if (filters.status) queryParams.append('status', filters.status);
            if (filters.user_id) queryParams.append('user_id', filters.user_id);
            if (filters.enrollment_method) queryParams.append('enrollment_method', filters.enrollment_method);
            if (filters.date_start) queryParams.append('date_start', filters.date_start);
            if (filters.date_end) queryParams.append('date_end', filters.date_end);
            if (filters.sort_by) queryParams.append('sort_by', filters.sort_by);
            if (filters.sort_order) queryParams.append('sort_order', filters.sort_order);
            if (filters.per_page) queryParams.append('per_page', filters.per_page);
            if (filters.page) queryParams.append('page', filters.page);

            // Debug logging
            console.log('API call:', queryParams.toString());
            
            const response = await apiFetch({
                path: `/splms/v1/enrollments?${queryParams.toString()}`,
                method: 'GET',
            });
            
            if (response.success) {
                dispatch(setEnrollments(response.data.enrollments, response.data.total));
                dispatch(setFilters(filters));
            } else {
                dispatch(setError(response.message || __('Failed to fetch enrollments', 'skillpulse-lms')));
            }
            
            return response;
        } catch (error) {
            console.error('Error fetching enrollments:', error);
            dispatch(setError(error.message || __('An error occurred while fetching enrollments', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

export function fetchCourses() {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const response = await apiFetch({
                path: '/splms/v1/courses?per_page=100',
                method: 'GET',
            });
            
            dispatch(setCourses(response));
            return response;
        } catch (error) {
            console.error('Error fetching courses:', error);
            dispatch(setError(error.message || __('An error occurred while fetching courses', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

export function fetchUsers() {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const response = await apiFetch({
                path: '/wp/v2/users?per_page=100',
                method: 'GET',
            });
            
            dispatch(setUsers(response));
            return response;
        } catch (error) {
            console.error('Error fetching users:', error);
            dispatch(setError(error.message || __('An error occurred while fetching users', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

export function createEnrollment(enrollmentData) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const response = await apiFetch({
                path: '/splms/v1/enrollments',
                method: 'POST',
                data: enrollmentData,
            });
            
            if (response.success) {
                dispatch(addEnrollment(response.data));
                return response.data;
            } else {
                dispatch(setError(response.message || __('Failed to create enrollment', 'skillpulse-lms')));
                return null;
            }
        } catch (error) {
            console.error('Error creating enrollment:', error);
            dispatch(setError(error.message || __('An error occurred while creating enrollment', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

export function updateEnrollmentData(enrollmentId, updates) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const response = await apiFetch({
                path: `/splms/v1/enrollments/${enrollmentId}`,
                method: 'PUT',
                data: updates,
            });
            
            if (response.success) {
                dispatch(updateEnrollment(enrollmentId, response.data));
                return response.data;
            } else {
                dispatch(setError(response.message || __('Failed to update enrollment', 'skillpulse-lms')));
                return null;
            }
        } catch (error) {
            console.error('Error updating enrollment:', error);
            dispatch(setError(error.message || __('An error occurred while updating enrollment', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

export function deleteEnrollmentData(enrollmentId) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const response = await apiFetch({
                path: `/splms/v1/enrollments/${enrollmentId}`,
                method: 'DELETE',
            });
            
            if (response.success) {
                dispatch(deleteEnrollment(enrollmentId));
                return true;
            } else {
                dispatch(setError(response.message || __('Failed to delete enrollment', 'skillpulse-lms')));
                return false;
            }
        } catch (error) {
            console.error('Error deleting enrollment:', error);
            dispatch(setError(error.message || __('An error occurred while deleting enrollment', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

export function bulkUpdateEnrollments(enrollmentIds, updates) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const response = await apiFetch({
                path: '/splms/v1/enrollments/bulk',
                method: 'POST',
                data: {
                    action: 'update',
                    enrollment_ids: enrollmentIds,
                    updates: updates,
                },
            });
            
            if (response.success) {
                // Refresh enrollments list
                await dispatch(fetchEnrollments());
                return true;
            } else {
                dispatch(setError(response.message || __('Failed to update enrollments', 'skillpulse-lms')));
                return false;
            }
        } catch (error) {
            console.error('Error bulk updating enrollments:', error);
            dispatch(setError(error.message || __('An error occurred while updating enrollments', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
}

export function bulkDeleteEnrollments(enrollmentIds) {
    return async ({ dispatch }) => {
        try {
            dispatch(setLoading(true));
            
            const response = await apiFetch({
                path: '/splms/v1/enrollments/bulk',
                method: 'POST',
                data: {
                    action: 'delete',
                    enrollment_ids: enrollmentIds,
                },
            });
            
            if (response.success) {
                // Refresh enrollments list
                await dispatch(fetchEnrollments());
                return true;
            } else {
                dispatch(setError(response.message || __('Failed to delete enrollments', 'skillpulse-lms')));
                return false;
            }
        } catch (error) {
            console.error('Error bulk deleting enrollments:', error);
            dispatch(setError(error.message || __('An error occurred while deleting enrollments', 'skillpulse-lms')));
            throw error;
        } finally {
            dispatch(setLoading(false));
        }
    };
} 