import { SPLMSHelper } from './core/helper';
import { SPLMSFrontendAjax } from './core/ajax';
import { SPLMSBaseModal } from './core/base-modal';
import { SPLMSUser } from './core/user';
import { SPLMSCourseActions } from './modules/courses/course-actions';
import { SPLMSCourseFilters } from './modules/courses/course';
import { SPLMSSearchFilter } from './modules/search/search-filter';
import { SPLMSUIEnhancements } from './components/ui-enhancements';
import { SPLMSAvatarUpload } from './components/avatar-upload';
//import './templates/instructor-template';
import './modules/curriculum/curriculum';
import './modules/quizzes/questions';
import './modules/quizzes/quiz';
import './modules/question-matching';
import './modules/question-ordering';
import './modules/auth/auth';
import './modules/auth/signup.validation';
import './components/notifications'; // Unified notification system
import './components/header-interactions'; // Mobile menu and user dropdown only
// Removed: notifications-page.js (functionality merged into notifications.js)
// Removed: nav-menu-profile.js (notification functionality moved to notifications.js)

const SPLMSCore = {};
SPLMSCore.helper = new SPLMSHelper();
SPLMSCore.frontendAjax = new SPLMSFrontendAjax();
SPLMSCore.user = new SPLMSUser();
SPLMSCore.BaseModal = SPLMSBaseModal;

jQuery(document).ready(function() {
    'use strict';

    // Initialize all frontend modules
    SPLMSCore.courseActions = new SPLMSCourseActions();
    
    // Initialize course filters only on archive pages
    SPLMSCore.courseFilters = new SPLMSCourseFilters();

    SPLMSCore.searchFilter = new SPLMSSearchFilter();
    SPLMSCore.uiEnhancements = new SPLMSUIEnhancements();
    SPLMSCore.avatarUpload = new SPLMSAvatarUpload();
    
    
    // Initialize certificate display
    // Re-initialize on AJAX content load
    jQuery(document).on('splms_content_loaded', function() {
        // Re-initialize modules that need to bind to new content
        if (SPLMSCore.courseActions) {
            SPLMSCore.courseActions.init();
        }
        if (SPLMSCore.courseFilters) {
            SPLMSCore.courseFilters.reinitialize();
        }
        if (SPLMSCore.searchFilter) {
            SPLMSCore.searchFilter.init();
        }
    });
});

// Make SPLMSCore globally available
window.SPLMSCore = SPLMSCore; 