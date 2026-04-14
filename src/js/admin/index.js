import { SPLMSBaseModal } from './core/base-modal';
import { SPLMSSignupReviewModal } from './modals/signup-review-modal';
import { SPLMSCourseEditor } from "./modules/courses/course-editor";
import { SPLMSLessonEditor } from "./modules/courses/lesson-editor";
import { SPLMSQuizEditor } from "./modules/courses/quiz-editor";
import { SPLMSSectionEditor } from "./modules/courses/section-editor";
import { SPLMSHelper } from './core/helper';
import { SPLMSAdminAjax } from './core/ajax';
import './trial-upgrade-prompts';
import './trial-admin-restrictions';

const SPLMSCore = {};
SPLMSCore.helper = new SPLMSHelper();
SPLMSCore.BaseModal = SPLMSBaseModal;

jQuery( document ).ready( function () {
    SPLMSCore.adminAjax = new SPLMSAdminAjax();
    SPLMSCore.course_editor = new SPLMSCourseEditor();
    SPLMSCore.lesson_editor = new SPLMSLessonEditor();
    SPLMSCore.quiz_editor = new SPLMSQuizEditor();
    SPLMSCore.section_editor = new SPLMSSectionEditor();

    // Initialize review modals on their respective admin pages.
    SPLMSSignupReviewModal.initializeSignupAdminPage();
} );

window.SPLMSCore = SPLMSCore;