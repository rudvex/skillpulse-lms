export const SPLMS_API_NAMESPACE = '/splms/v1';
// New unified config API that includes settings with values
export const SPLMS_API_CONFIG = `${SPLMS_API_NAMESPACE}/config`;
export const SPLMS_API_SETTINGS_CONFIG = `${SPLMS_API_CONFIG}/settings`;
// Old settings API - deprecated in favor of config API
export const SPLMS_API_SETTINGS = `${SPLMS_API_NAMESPACE}/settings`;

export const SPLMS_API_SETTINGS_TAB  = ( tabId ) => `${SPLMS_API_SETTINGS}/${tabId}`;

// Courses API Constants.
export const SPLMS_API_COURSES = `${SPLMS_API_NAMESPACE}/courses`;
export const SPLMS_API_COURSE = ( postId ) => `${SPLMS_API_COURSES}/${postId}`;
export const SPLMS_API_COURSE_SETTINGS = ( postId ) => `${SPLMS_API_COURSE(postId)}/settings`;
export const SPLMS_API_CURRICULUM = ( postId ) => `${SPLMS_API_COURSE(postId)}/curriculum`;

// Sections API Constants.
export const SPLMS_API_SECTIONS = `${SPLMS_API_NAMESPACE}/sections`;
export const SPLMS_API_SECTION = ( postId ) => `${SPLMS_API_NAMESPACE}/section/${postId}`;
export const SPLMS_API_SECTION_SETTINGS = ( postId ) => `${SPLMS_API_SECTION(postId)}/settings`;

// Lessons API Constants.
export const SPLMS_API_LESSONS = `${SPLMS_API_NAMESPACE}/lessons`;
export const SPLMS_API_LESSON = ( postId ) => `${SPLMS_API_LESSONS}/${postId}`;
export const SPLMS_API_LESSON_SETTINGS = ( postId ) => `${SPLMS_API_LESSON(postId)}/settings`;

// Quizzes API Constants.
export const SPLMS_API_QUIZZES = `${SPLMS_API_NAMESPACE}/quizzes`;
export const SPLMS_API_QUIZ = ( postId ) => `${SPLMS_API_QUIZZES}/${postId}`;
export const SPLMS_API_QUIZ_SETTINGS = ( postId ) => `${SPLMS_API_QUIZ(postId)}/settings`;
export const SPLMS_API_QUIZ_QUESTIONS = ( postId ) => `${SPLMS_API_QUIZ(postId)}/questions`;
