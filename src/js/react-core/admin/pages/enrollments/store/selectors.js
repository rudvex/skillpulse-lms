/**
 * Get all enrollments
 * @param {Object} state Store state
 * @returns {Array} Array of enrollments
 */
export function getEnrollments(state) {
    return state.enrollments || [];
}

/**
 * Get enrollment by ID
 * @param {Object} state Store state
 * @param {string} enrollmentId Enrollment ID
 * @returns {Object|null} Enrollment object or null
 */
export function getEnrollment(state, enrollmentId) {
    return state.enrollments.find(enrollment => enrollment.id === enrollmentId) || null;
}

/**
 * Get all courses
 * @param {Object} state Store state
 * @returns {Array} Array of courses
 */
export function getCourses(state) {
    return state.courses || [];
}

/**
 * Get course by ID
 * @param {Object} state Store state
 * @param {string} courseId Course ID
 * @returns {Object|null} Course object or null
 */
export function getCourse(state, courseId) {
    return state.courses.find(course => course.id === courseId) || null;
}

/**
 * Get all users
 * @param {Object} state Store state
 * @returns {Array} Array of users
 */
export function getUsers(state) {
    return state.users || [];
}

/**
 * Get user by ID
 * @param {Object} state Store state
 * @param {string} userId User ID
 * @returns {Object|null} User object or null
 */
export function getUser(state, userId) {
    return state.users.find(user => user.id === userId) || null;
}

/**
 * Get total enrollments count
 * @param {Object} state Store state
 * @returns {number} Total enrollments count
 */
export function getTotalEnrollments(state) {
    return state.totalEnrollments || 0;
}

/**
 * Get loading state
 * @param {Object} state Store state
 * @returns {boolean} Loading state
 */
export function getLoading(state) {
    return state.isLoading || false;
}

/**
 * Get error state
 * @param {Object} state Store state
 * @returns {string|null} Error message or null
 */
export function getError(state) {
    return state.error || null;
}

/**
 * Get current filters
 * @param {Object} state Store state
 * @returns {Object} Current filters
 */
export function getFilters(state) {
    return state.filters || {};
}

/**
 * Get filtered enrollments
 * @param {Object} state Store state
 * @returns {Array} Filtered enrollments
 */
export function getFilteredEnrollments(state) {
    return state.enrollments || [];
}

/**
 * Get enrollment statistics
 * @param {Object} state Store state
 * @returns {Object} Statistics object
 */
export function getEnrollmentStats(state) {
    const enrollments = state.enrollments || [];
    
    return {
        total: enrollments.length,
        active: enrollments.filter(e => e.status === 'active').length,
        completed: enrollments.filter(e => e.status === 'completed').length,
        suspended: enrollments.filter(e => e.status === 'suspended').length,
    };
} 