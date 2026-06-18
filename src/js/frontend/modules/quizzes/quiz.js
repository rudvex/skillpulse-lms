/**
 * SkillPulse LMS Quiz Handler (Refactored)
 * Handles quiz taking, timer management, and quiz completion
 * Works with SPLMSQuestions class for question-specific functionality
 * 
 * @class SPLMSQuiz
 * @module quizzes/quiz
 */
import { 
    formatTime, 
    formatTimerTime, 
    debounce, 
    saveToStorage, 
    loadFromStorage, 
    clearStorage,
    dispatchEvent 
} from './quiz-utils.js';
import { getAjaxUrl } from '../../../react-core/utility/url';

class SPLMSQuiz {
    constructor() {
        this.currentPage = 1;
        this.currentQuestion = 0;
        this.questions = [];
        this.questionsData = [];
        this.answers = {};
        this.fileObjects = {}; // Store File objects in memory (questionId => File object)
        this.timeRemaining = 0;
        this.timerInterval = null;
        this.isQuizActive = false;
        this.quizData = {};
        this.startTime = null;
        this.settings = {};
        this.attemptId = null;
        this.currentResults = null;
        this.autoSaveInterval = null;
        this.isSubmitting = false; // Prevent duplicate submissions
        
        // Initialize questions handler
        this.questionsHandler = new SPLMSQuestions(this);
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadQuestionsData();
    }

    bindEvents() {
        // Start quiz button
        jQuery(document).on('click', '.start-quiz-btn', this.handleStartQuiz.bind(this));
        
        // Resume and restart quiz buttons
        jQuery(document).on('click', '.resume-quiz-btn', this.handleResumeQuiz.bind(this));
        jQuery(document).on('click', '.restart-quiz-btn', this.handleRestartQuiz.bind(this));
        
        // Quiz navigation buttons  
        jQuery(document).on('click', '#prev-question-btn', this.handlePreviousPage.bind(this));
        jQuery(document).on('click', '#next-question-btn', this.handleNextPage.bind(this));
        jQuery(document).on('click', '#submit-quiz-btn', this.handleSubmitQuiz.bind(this));
        jQuery(document).on('click', '#final-submit-btn', this.handleFinalSubmit.bind(this));
        
        // Page navigation dots
        jQuery(document).on('click', '.page-dot', this.handlePageDotClick.bind(this));
        
        // Review functionality
        jQuery(document).on('click', '.review-item', this.questionsHandler.handleReviewItemClick.bind(this.questionsHandler));
        jQuery(document).on('click', '#back-to-quiz-btn', this.handleBackToQuiz.bind(this));
        
        // Results actions
        // Note: Retake quiz functionality is now handled in handleRetakeQuizFromPHPResults()
        jQuery(document).on('click', '.view-answers-btn', this.handleViewAnswers.bind(this));
        jQuery(document).on('click', '.next-btn', this.handleNext.bind(this));
        jQuery(document).on('click', '.view-course-btn', this.handleViewCourse.bind(this));
        
        // Keyboard navigation
        jQuery(document).on('keydown', this.handleKeyboardEvents.bind(this));
        
        // Prevent accidental page leave during quiz
        jQuery(window).on('beforeunload', this.handlePageLeave.bind(this));
    }

    loadQuestionsData() {
        // Load questions data from the script tag
        const questionsScript = document.getElementById('quiz-questions-data');
        if (questionsScript && questionsScript.textContent) {
            try {
                this.questionsData = JSON.parse(questionsScript.textContent);
            } catch (e) {
                console.error('Failed to parse quiz questions data:', e);
                this.questionsData = [];
            }
        }
    }

    handleStartQuiz(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const quizId = $button.data('quiz-id');
        const courseId = $button.data('course-id');
        // HTML data attributes are strings, so check for string 'true' or boolean true.
        const previewModeValue = $button.data('preview-mode');
        const isPreviewMode = previewModeValue === true || previewModeValue === 'true';
        
        if (!quizId) {
            console.error('Quiz ID not found on button');
            SPLMSCore.helper.showNotification('Quiz ID not found', 'error');
            return;
        }
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        const originalText = $button.text();
        $button.text(isPreviewMode ? '🚀 Starting Preview...' : '🚀 Starting Quiz...');

        // Load quiz data via AJAX
        this.loadQuizData(quizId, courseId, isPreviewMode).then(() => {
            this.startQuiz();
            $button.removeClass('loading').prop('disabled', false).text(originalText);
        }).catch((error) => {
            console.error('Failed to load quiz data:', error);
            // Check if error is due to missing question type handler
            let errorMessage = 'Failed to load quiz. Please try again.';
            if (error && error.message && error.message.includes('question type')) {
                errorMessage = 'Unsupported question type detected. Please contact support.';
            } else if (error && error.responseJSON && error.responseJSON.data) {
                errorMessage = error.responseJSON.data;
            }
            SPLMSCore.helper.showNotification(errorMessage, 'error');
            $button.removeClass('loading').prop('disabled', false).text(originalText);
        });
    }

    handleResumeQuiz(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const quizId = $button.data('quiz-id');
        const courseId = $button.data('course-id');
        
        if (!quizId) {
            SPLMSCore.helper.showNotification('Quiz ID not found', 'error');
            return;
        }
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        const originalText = $button.text();
        $button.text('▶️ Resuming Quiz...');

        // Load quiz data and resume from saved state
        Promise.all([
            this.loadQuizData(quizId, courseId),
            this.loadSavedQuizState(quizId)
        ]).then(([quizData, savedState]) => {
            this.resumeQuiz(savedState);
            $button.removeClass('loading').prop('disabled', false).text(originalText);
        }).catch((error) => {
            console.error('Failed to resume quiz:', error);
            SPLMSCore.helper.showNotification('Failed to resume quiz. Please try again.', 'error');
            $button.removeClass('loading').prop('disabled', false).text(originalText);
        });
    }

    handleRestartQuiz(e) {
        e.preventDefault();
        
        const $button = jQuery(e.currentTarget);
        const quizId = $button.data('quiz-id');
        const courseId = $button.data('course-id');
        
        if (!quizId) {
            SPLMSCore.helper.showNotification('Quiz ID not found', 'error');
            return;
        }
        
        // Confirm restart
        if (!confirm('Are you sure you want to start a new attempt? Your current progress will be lost.')) {
            return;
        }
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        const originalText = $button.text();
        $button.text('🔄 Starting New Attempt...');

        // Clear saved state and start fresh
        this.clearSavedQuizState(quizId).then(() => {
            return this.loadQuizData(quizId, courseId);
        }).then(() => {
            this.startQuiz();
            $button.removeClass('loading').prop('disabled', false).text(originalText);
        }).catch((error) => {
            console.error('Failed to restart quiz:', error);
            SPLMSCore.helper.showNotification('Failed to start new attempt. Please try again.', 'error');
            $button.removeClass('loading').prop('disabled', false).text(originalText);
        });
    }

    async loadQuizData(quizId, courseId, isPreviewMode = false) {
        try {
            // Validate required parameters
            if (!quizId) {
                throw new Error('Quiz ID is required');
            }

            // Get frontend data
            const frontend = window.splms_frontend;
            if (!frontend || !frontend.ajax_url || !frontend.nonces?.splms_nonce) {
                throw new Error('SkillPulse LMS frontend object not found. Please refresh the page.');
            }

            // Load quiz data via AJAX (start quiz handler gets everything)
            const response = await jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'splms_start_quiz',
                    quiz_id: quizId,
                    course_id: courseId,
                    preview_mode: isPreviewMode ? 'true' : 'false',
                    nonce: frontend.nonces?.splms_nonce || frontend.nonce || ''
                }
            });

            if (!response.success) {
                throw new Error(response.data || 'Failed to load quiz data');
            }

            const quizData = response.data;
            
            // Validate response structure
            if (!quizData || !Array.isArray(quizData.questions)) {
                throw new Error('Invalid quiz data structure received');
            }
            
            // Store loaded data
            this.questionsData = quizData.questions;

            // Get quiz settings
            const settings = quizData.quiz_settings || {};

            this.settings = {
                quizId: quizId,
                courseId: courseId,
                timeLimit: settings.time_limit || 0,
                timeLimitEnabled: settings.time_limit_enabled || false,
                totalQuestions: quizData.questions.length,
                questionsPerPage: settings.question_per_page || 1,
                totalPages: Math.ceil(quizData.questions.length / (settings.question_per_page || 1)),
                allowNavigation: settings.allow_navigation !== false,
                allowReview: settings.allow_review !== false,
                passingGrade: settings.passing_grade || 70,
                isPreviewMode: quizData.is_preview_mode || false
            };
            this.attemptId = quizData.attempt_id;
            
            // Populate quiz interface with questions
            this.populateQuizInterface();
            
        } catch (error) {
            console.error('Error loading quiz data:', error);
            throw error;
        }
    }

    populateQuizInterface() {
        if (!this.questionsData || this.questionsData.length === 0) {
            const $interface = jQuery('#splms-quiz-interface');
            $interface.find('.splms-quiz-questions-container').html(
                '<div class="no-questions-notice">' +
                '<h3>No Questions Available</h3>' +
                '<p>This quiz does not have any questions yet.</p>' +
                '</div>'
            );
            return;
        }

        // Initialize template functions
        this.navigationTemplate = wp.template('quiz-navigation');
        this.timerTemplate = wp.template('quiz-timer');
        
        // Render the current question using questions handler
        this.questionsHandler.renderCurrentQuestion();
        
        // Render navigation
        this.renderNavigation();
        
        // Render timer if enabled
        if (this.settings.timeLimitEnabled && this.timeRemaining > 0) {
            this.renderTimer();
        }
    }

    renderNavigation() {
        const questionsPerPage = this.settings.questionsPerPage || 1;
        const totalPages = Math.ceil(this.questionsData.length / questionsPerPage);
        const currentPage = this.currentPage;
        const progressPercent = Math.round((currentPage / totalPages) * 100);

        const navigationData = {
            currentPage: currentPage,
            totalPages: totalPages,
            progressPercent: progressPercent
        };

        // Render navigation using template
        if (this.navigationTemplate) {
            const navigationHtml = this.navigationTemplate(navigationData);
            
            // Insert into container
            const $container = jQuery('#splms-quiz-interface .splms-quiz-navigation-container');
            if ($container.length === 0) {
                jQuery('#splms-quiz-interface').append('<div class="splms-quiz-navigation-container"></div>');
            }
            jQuery('#splms-quiz-interface .splms-quiz-navigation-container').html(navigationHtml);
        }
        
        // Bind navigation events
        this.bindNavigationEvents();
    }

    /**
     * Render timer display
     * Uses utility function for consistent time formatting
     */
    renderTimer() {
        if (!this.settings.timeLimitEnabled || this.timeRemaining <= 0) {
            return;
        }

        // Use utility function for consistent formatting
        const timeDisplay = formatTimerTime(this.timeRemaining);

        const timerData = {
            timeDisplay: timeDisplay
        };

        // Render timer using template
        if (this.timerTemplate) {
            const timerHtml = this.timerTemplate(timerData);
            
            // Insert into container
            const $container = jQuery('#splms-quiz-interface .splms-quiz-timer-container');
            if ($container.length === 0) {
                jQuery('#splms-quiz-interface').prepend('<div class="splms-quiz-timer-container"></div>');
            }
            jQuery('#splms-quiz-interface .splms-quiz-timer-container').html(timerHtml);
        }
    }

    bindNavigationEvents() {
        // Navigation events are already bound in bindEvents() using document delegation
        // We don't need to bind them again here to avoid double-firing
    }

    /**
     * Navigate to previous question
     * Saves current answer and dispatches question change event
     */
    /**
     * Navigate to previous question
     * Saves current state before navigation
     */
    previousQuestion() {
        // Save current state before navigating
        this.saveCurrentAnswer();
        if (this.currentQuestion > 0) {
            this.saveCurrentAnswer();
            this.currentQuestion--;
            this.updateCurrentPage();
            this.questionsHandler.renderCurrentQuestion();
            this.renderNavigation();
            
            // Dispatch custom event
            dispatchEvent('splms:quiz:questionChanged', {
                quizId: this.settings.quizId,
                questionIndex: this.currentQuestion,
                questionId: this.questionsData[this.currentQuestion]?.id,
                direction: 'previous'
            });
        }
    }

    /**
     * Navigate to next question
     * Saves current answer and dispatches question change event
     */
    nextQuestion() {
        if (this.currentQuestion < this.questionsData.length - 1) {
            this.saveCurrentAnswer();
            this.currentQuestion++;
            this.updateCurrentPage();
            this.questionsHandler.renderCurrentQuestion();
            this.renderNavigation();
            
            // Dispatch custom event
            dispatchEvent('splms:quiz:questionChanged', {
                quizId: this.settings.quizId,
                questionIndex: this.currentQuestion,
                questionId: this.questionsData[this.currentQuestion]?.id,
                direction: 'next'
            });
        }
    }
    
    /**
     * Save current question answer before navigation
     * @private
     */
    saveCurrentAnswer() {
        // Answers are saved automatically via event handlers
        // Trigger debounced save to server
        if (this.debouncedServerSave) {
            this.debouncedServerSave();
        }
        // Also save to localStorage immediately
        const timeTaken = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;
        const state = {
            answers: this.answers,
            currentQuestion: this.currentQuestion,
            timeRemaining: this.timeRemaining,
            startTime: this.startTime,
            timeTaken: timeTaken,
            attemptId: this.attemptId
        };
        saveToStorage(this.settings.quizId, state);
    }

    async loadSavedQuizState(quizId) {
        const frontend = window.splms_frontend;
        try {
            const response = await jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'splms_get_quiz_state',
                    quiz_id: quizId,
                    nonce: frontend.nonces.splms_nonce
                }
            });
            
            if (response.success) {
                return response.data;
            } else {
                throw new Error(response.data || 'Failed to load saved quiz state');
            }
        } catch (error) {
            console.error('Error loading saved quiz state:', error);
            throw error;
        }
    }

    /**
     * Clear saved quiz state from both server and localStorage
     * @param {number|string} quizId - Quiz ID
     * @returns {Promise} Promise that resolves when state is cleared
     */
    async clearSavedQuizState(quizId) {
        // Clear localStorage
        clearStorage(quizId);
        
        const frontend = window.splms_frontend;
        try {
            const response = await jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'splms_clear_quiz_state',
                    quiz_id: quizId,
                    nonce: frontend.nonces.splms_nonce
                }
            });
            
            if (!response.success) {
                throw new Error(response.data || 'Failed to clear saved quiz state');
            }
        } catch (error) {
            console.error('Error clearing saved quiz state:', error);
            throw error;
        }
    }

    /**
     * Resume quiz from saved state
     * Loads state from server and localStorage, restores UI
     * @param {Object} savedState - Saved quiz state from server
     */
    resumeQuiz(savedState) {
        // Try to load from localStorage first (faster, more recent)
        const localState = loadFromStorage(this.settings.quizId);
        
        // Merge server state with localStorage state (localStorage takes precedence for answers)
        let answers = localState?.answers || savedState.answers || {};
        if (typeof answers === 'string') {
            try {
                answers = JSON.parse(answers);
            } catch (e) {
                console.error('Failed to parse saved answers:', e);
                answers = {};
            }
        }
        
        // Initialize quiz state from saved data
        this.answers = answers;
        this.fileObjects = {}; // Reset fileObjects on resume (files need to be re-selected after page refresh)
        this.attemptId = savedState.attempt_id || localState?.attemptId;
        this.currentQuestion = localState?.currentQuestion ?? savedState.currentQuestion ?? 0;
        this.updateCurrentPage();
        this.isQuizActive = true;
        this.startTime = localState?.startTime ?? (savedState.start_time ? new Date(savedState.start_time).getTime() : Date.now());
        
        // Set up timer if applicable
        if (this.settings.timeLimitEnabled && this.settings.timeLimit > 0) {
            const elapsed = localState?.timeTaken ?? savedState.time_taken ?? 0;
            this.timeRemaining = localState?.timeRemaining ?? Math.max(0, (this.settings.timeLimit * 60) - elapsed);
            if (this.timeRemaining > 0) {
                this.startTimer();
            } else {
                // Time's up - auto submit
                SPLMSCore.helper.showNotification('Time is up! Quiz will be auto-submitted.', 'warning');
                setTimeout(() => {
                    this.handleFinalSubmit();
                }, 1000);
                return;
            }
        }
        
        // Hide specific quiz content sections but keep the interface visible
        jQuery('.splms-quiz-stats').hide();
        jQuery('.splms-quiz-description').hide();
        jQuery('.splms-quiz-actions').hide();
        
        // Show quiz interface
        jQuery('#splms-quiz-interface').show();
        
        // Render the current question first (so DOM elements exist)
        this.questionsHandler.renderCurrentQuestion();
        
        // Then restore answers in the UI (after rendering)
        // Use setTimeout to ensure DOM is ready
        setTimeout(() => {
            this.questionsHandler.restoreAnswersInUI();
        }, 100);
        
        // Render navigation
        this.renderNavigation();
        
        // Start auto-saving
        this.startAutoSave();
        
        // Dispatch custom event
        dispatchEvent('splms:quiz:resumed', {
            quizId: this.settings.quizId,
            attemptId: this.attemptId,
            currentQuestion: this.currentQuestion,
            timeRemaining: this.timeRemaining
        });
        
        SPLMSCore.helper.showNotification('Quiz resumed successfully!', 'success');
    }

    /**
     * Save quiz state to both localStorage and server
     * Uses debounced save for localStorage to reduce writes
     */
    saveQuizState() {
        // Skip saving in preview mode
        if (this.settings.isPreviewMode) {
            return;
        }
        
        // Save current quiz state to database
        if (!this.isQuizActive || !this.settings.quizId || !this.attemptId) {
            return;
        }
        
        const timeTaken = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;
        
        // Save to localStorage for fast resume
        const state = {
            answers: this.answers,
            currentQuestion: this.currentQuestion,
            timeRemaining: this.timeRemaining,
            startTime: this.startTime,
            timeTaken: timeTaken,
            attemptId: this.attemptId
        };
        saveToStorage(this.settings.quizId, state);
        
        // Save to server (debounced if method exists, otherwise direct save)
        if (this.debouncedServerSave) {
            this.debouncedServerSave();
        } else {
            // Direct save to server
            const frontend = window.splms_frontend;
            jQuery.ajax({
                url: frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'splms_save_quiz_state',
                    quiz_id: this.settings.quizId,
                    attempt_id: this.attemptId,
                    answers: this.answers,
                    time_taken: timeTaken,
                    nonce: frontend.nonces.splms_nonce
                },
                success: function(response) {
                    // Silent save - don't show notifications
                },
                error: function(xhr, status, error) {
                    console.error('Failed to save quiz state:', error);
                }
            });
        }
    }

    /**
     * Start the quiz
     * Initializes quiz state, starts timer, and dispatches start event
     */
    startQuiz() {
        // Initialize quiz state
        this.answers = {};
        this.currentQuestion = 0;
        this.updateCurrentPage();
        this.isQuizActive = true;
        this.startTime = Date.now();
        
        // Set up timer if applicable
        if (this.settings.timeLimitEnabled && this.settings.timeLimit > 0) {
            this.timeRemaining = this.settings.timeLimit * 60; // Convert minutes to seconds
            this.startTimer();
        }
        
        // Hide specific quiz content sections but keep the interface visible
        jQuery('.splms-quiz-stats').hide();
        jQuery('.splms-quiz-description').hide();
        jQuery('.splms-quiz-actions').hide();
        
        // Show quiz interface
        jQuery('#splms-quiz-interface').show();

        // Render the first question
        this.questionsHandler.renderCurrentQuestion();

        // Render navigation
        this.renderNavigation();

        // Start auto-saving quiz state
        this.startAutoSave();

        // Dispatch custom event
        dispatchEvent('splms:quiz:started', {
            quizId: this.settings.quizId,
            attemptId: this.attemptId,
            courseId: this.settings.courseId
        });
    }

    /**
     * Start auto-saving quiz state
     * Uses debounced save to reduce server calls
     */
    startAutoSave() {
        // Clear existing interval
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
        }
        
        // Create debounced server save function
        const frontend = window.splms_frontend;
        this.debouncedServerSave = debounce(() => {
            if (!this.settings.isPreviewMode && this.isQuizActive && this.settings.quizId && this.attemptId) {
                const timeTaken = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;
                jQuery.ajax({
                    url: frontend.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'splms_save_quiz_state',
                        quiz_id: this.settings.quizId,
                        attempt_id: this.attemptId,
                        answers: this.answers,
                        time_taken: timeTaken,
                        nonce: frontend.nonces.splms_nonce
                    },
                    success: function(response) {
                        // Silent save - don't show notifications
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to save quiz state:', error);
                    }
                });
            }
        }, 2000); // Debounce to 2 seconds
        
        // Auto-save quiz state every 30 seconds (server save)
        this.autoSaveInterval = setInterval(() => {
            this.saveQuizState();
        }, 30000); // 30 seconds
    }

    startTimer() {
        this.timerInterval = setInterval(() => {
            this.timeRemaining--;
            this.renderTimer();
            
            // Check if time is running out
            if (this.timeRemaining <= 60) { // Last minute
                jQuery('.quiz-timer').addClass('danger');
            }
            
            // Auto-submit when time runs out
            if (this.timeRemaining <= 0) {
                SPLMSCore.helper.showNotification('Time is up! Quiz will be auto-submitted.', 'warning');
                setTimeout(() => {
                    this.handleFinalSubmit();
                }, 1000);
            }
        }, 1000);
    }

    handlePreviousPage(e) {
        e.preventDefault();
        this.previousQuestion();
    }

    handleNextPage(e) {
        e.preventDefault();
        this.nextQuestion();
    }

    handlePageDotClick(e) {
        e.preventDefault();
        // Page dots navigation implementation
    }

    handleSubmitQuiz(e) {
        e.preventDefault();
        this.handleFinalSubmit(e);
    }

    handleFinalSubmit(e) {
        if (e) e.preventDefault();
        
        // Prevent duplicate submissions
        if (this.isSubmitting) {
            console.warn('Quiz submission already in progress. Ignoring duplicate submit.');
            return;
        }
        
        this.isSubmitting = true;
        
        const $submitBtn = jQuery('#submit-quiz-btn, #final-submit-btn');
        
        // Add loading state
        $submitBtn.addClass('loading').prop('disabled', true);
        const originalText = $submitBtn.text();
        $submitBtn.text(this.settings.isPreviewMode ? '📤 Calculating Results...' : '📤 Submitting...');

        // Calculate time taken
        const timeTaken = this.startTime ? Math.floor((Date.now() - this.startTime) / 1000) : 0;

        // Handle preview mode differently
        if (this.settings.isPreviewMode) {
            this.handlePreviewSubmission(timeTaken, $submitBtn, originalText);
            return;
        }

        // Prepare FormData for file uploads
        const formData = new FormData();
        const fileUploads = {};
        
        // Simple approach: Collect files from fileObjects (stored in memory when files were selected)
        // This ensures we get all files regardless of which page they're on
        Object.keys(this.fileObjects).forEach(questionId => {
            const file = this.fileObjects[questionId];
            if (file && file instanceof File) {
                formData.append('file_' + questionId, file);
                fileUploads[questionId] = file.name;
            }
        });
        
        // Also check current file inputs (in case fileObjects is missing some - fallback)
        // This handles edge cases where file might have been selected but not stored in fileObjects
        const self = this;
        jQuery('#splms-quiz-interface input[type="file"]').each(function() {
            const questionId = jQuery(this).data('question-id');
            const file = this.files && this.files.length > 0 ? this.files[0] : null;
            
            if (file && questionId) {
                const qId = String(questionId);
                // Only add if not already in fileUploads (fileObjects takes precedence)
                if (!fileUploads[qId]) {
                    formData.append('file_' + qId, file);
                    fileUploads[qId] = file.name;
                    // Also store in fileObjects for consistency
                    self.fileObjects[qId] = file;
                }
            }
        });
        
        // Clean and validate answers before submission
        // Ensure answers are in the correct format expected by the evaluator
        const cleanedAnswers = this.cleanAnswersForSubmission(this.answers);
        
        // Validate that we have answers for all questions
        if (this.questionsData && this.questionsData.length > 0) {
            const unansweredQuestions = [];
            this.questionsData.forEach(q => {
                if (!cleanedAnswers[q.id] || cleanedAnswers[q.id] === '' || 
                    (Array.isArray(cleanedAnswers[q.id]) && cleanedAnswers[q.id].length === 0)) {
                    unansweredQuestions.push({ id: q.id, type: q.type });
                }
            });
            if (unansweredQuestions.length > 0) {
                console.warn('Unanswered questions:', unansweredQuestions);
            }
        }
        
        // Add other data to FormData
        const frontend = window.splms_frontend;
        formData.append('action', 'splms_submit_quiz_final');
        formData.append('quiz_id', this.settings.quizId);
        formData.append('answers', JSON.stringify(cleanedAnswers));
        formData.append('file_uploads', JSON.stringify(fileUploads));
        formData.append('time_taken', timeTaken);
        formData.append('nonce', frontend.nonces.splms_nonce);
        formData.append('attempt_id', this.attemptId);
        
        // Submit quiz via AJAX
        jQuery.ajax({
            url: frontend.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success && response.data) {
                    // Verify attempt_id is in response (confirms database save)
                    if (response.data.attempt_id) {
                        
                        // Mark submission as complete
                        this.isSubmitting = false;
                        
                        // IMPORTANT: Do NOT call clearSavedQuizState() after successful submission
                        // This would trigger splms_clear_quiz_state which could delete the attempt
                        // Only clear browser localStorage (client-side only)
                        clearStorage(this.settings.quizId);
                        
                        // Stop auto-saving
                        if (this.autoSaveInterval) {
                            clearInterval(this.autoSaveInterval);
                            this.autoSaveInterval = null;
                        }
                        
                        // Dispatch custom event
                        dispatchEvent('splms:quiz:submitted', {
                            quizId: this.settings.quizId,
                            attemptId: response.data.attempt_id || this.attemptId,
                            results: response.data
                        });
                        
                        // Show results (this will load attempt history after a delay to ensure DB commit)
                        this.showQuizResults(response.data);
                        
                        // If quiz is passed, mark it as complete in the curriculum
                        if (response.data.passed) {
                            this.markQuizAsComplete(response.data.progress);
                        }
                    } else {
                        this.isSubmitting = false; // Reset on failure
                        console.warn('Quiz submission response missing attempt_id. Attempt may not have been saved.');
                        // Don't clear localStorage if we can't verify the save
                        SPLMSCore.helper.showNotification('Warning: Could not verify quiz submission. Please check your attempt history.', 'warning');
                    }
                } else {
                    // Handle specific error types
                    if (response.data && typeof response.data === 'object' && response.data.time_limit_exceeded) {
                        SPLMSCore.helper.showNotification('⏰ Time limit exceeded! Your quiz submission has been rejected. Please try again if attempts remain.', 'error');
                        // Force clear the timer
                        if (this.timerInterval) {
                            clearInterval(this.timerInterval);
                            this.timerInterval = null;
                        }
                        // Show quiz results with failed status due to time limit
                        this.showQuizResults({
                            passed: false,
                            percentage: 0,
                            correct_answers: 0,
                            total_questions: this.questionsData.length,
                            time_taken: response.data.time_taken || 0,
                            passing_grade: this.settings.passingGrade,
                            attempts_remaining: response.data.attempts_remaining || 0,
                            show_answers: false,
                            time_limit_exceeded: true
                        });
                    } else {
                        // Extract error message properly (handle both string and object responses)
                        let errorMessage = 'Failed to submit quiz';
                        if (response.data) {
                            if (typeof response.data === 'string') {
                                errorMessage = response.data;
                            } else if (typeof response.data === 'object' && response.data.message) {
                                errorMessage = response.data.message;
                            } else if (typeof response.data === 'object') {
                                // Try to stringify if it's an object without message property
                                errorMessage = JSON.stringify(response.data);
                            }
                        }
                        SPLMSCore.helper.showNotification(errorMessage, 'error');
                    }
                }
            },
            error: (xhr) => {
                this.isSubmitting = false; // Reset on error
                let errorMessage = 'Something went wrong. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data) {
                    const errorData = xhr.responseJSON.data;
                    if (typeof errorData === 'string') {
                        errorMessage = errorData;
                    } else if (typeof errorData === 'object' && errorData.message) {
                        errorMessage = errorData.message;
                    } else if (typeof errorData === 'object') {
                        errorMessage = JSON.stringify(errorData);
                    }
                }
                SPLMSCore.helper.showNotification(errorMessage, 'error');
            },
            complete: () => {
                // Only reset submitting flag if not successful (success is handled above)
                if (!this.isSubmitting) {
                    // Already handled in success
                } else {
                    this.isSubmitting = false;
                }
                $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
            }
        });
    }

    handlePreviewSubmission(timeTaken, $submitBtn, originalText) {
        // Calculate score locally for preview mode
        let correctAnswers = 0;
        let totalQuestions = this.questionsData.length;
        
        this.questionsData.forEach((question, index) => {
            const userAnswer = this.answers[index];
            if (userAnswer && userAnswer === question.correct_answer) {
                correctAnswers++;
            }
        });
        
        const score = totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) : 0;
        const passed = score >= this.settings.passingGrade;
        
        // Option 1: Store as guest attempt in database
        this.submitGuestAttempt(score, correctAnswers, totalQuestions, passed, timeTaken, $submitBtn, originalText);
    }

    submitGuestAttempt(score, correctAnswers, totalQuestions, passed, timeTaken, $submitBtn, originalText) {
        // Submit guest attempt to server for server-side evaluation
        // Server will evaluate answers and respect Guest Quiz Storage Mode setting
        
        // Clean and format answers for submission (same as logged-in users)
        const cleanedAnswers = this.cleanAnswersForSubmission(this.answers);
        const frontend = window.splms_frontend;
        
        jQuery.ajax({
            url: frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'splms_submit_guest_quiz_attempt',
                quiz_id: this.settings.quizId,
                course_id: this.settings.courseId,
                answers: JSON.stringify(cleanedAnswers), // Send as JSON string
                time_taken: timeTaken,
                nonce: frontend.nonces.splms_nonce
            },
            success: (response) => {
                // Stop auto-saving
                if (this.autoSaveInterval) {
                    clearInterval(this.autoSaveInterval);
                    this.autoSaveInterval = null;
                }
                
                if (response.success && response.data) {
                    // Use server-evaluated results
                    const previewResults = {
                        score: response.data.points_earned || response.data.score || 0,
                        percentage: response.data.percentage || 0,
                        total_questions: response.data.total_questions || 0,
                        correct_answers: response.data.correct_answers || 0,
                        passed: response.data.passed || false,
                        time_taken: response.data.time_taken || timeTaken,
                        is_preview_mode: true,
                        is_guest_attempt: true,
                        attempt_id: response.data.attempt_id || null,
                        passing_grade: response.data.passing_grade || this.settings.passingGrade || 70,
                        show_answers: response.data.show_answers !== false,
                        detailed_results: response.data.detailed_results || [],
                        pending_review: response.data.pending_review || false,
                        has_manual_review: response.data.has_manual_review || false,
                        message: response.data.message || 'This was a preview attempt. Results have been saved as guest attempt.'
                    };
                    
                    // Show results
                    this.showQuizResults(previewResults);
                } else {
                    // Fallback to client-side results if server response is invalid
                    const previewResults = {
                        score: score,
                        percentage: score,
                        total_questions: totalQuestions,
                        correct_answers: correctAnswers,
                        passed: passed,
                        time_taken: timeTaken,
                        is_preview_mode: true,
                        message: 'This was a preview attempt. Results are not saved.'
                    };
                    
                    this.showQuizResults(previewResults);
                }
                
                // Reset button
                $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
            },
            error: (xhr, status, error) => {
                console.warn('Failed to save guest attempt, showing results anyway:', error);
                
                // Stop auto-saving
                if (this.autoSaveInterval) {
                    clearInterval(this.autoSaveInterval);
                    this.autoSaveInterval = null;
                }
                
                // Create preview results object (without database storage)
                const previewResults = {
                    score: score,
                    percentage: score,
                    total_questions: totalQuestions,
                    correct_answers: correctAnswers,
                    passed: passed,
                    time_taken: timeTaken,
                    is_preview_mode: true,
                    message: 'This was a preview attempt. Results are not saved.'
                };
                
                // Show results
                this.showQuizResults(previewResults);
                
                // Reset button
                $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
            }
        });
    }

    // Storage mode is now handled server-side in handle_submit_guest_quiz_attempt

    showPreviewResultsOnly(score, correctAnswers, totalQuestions, passed, timeTaken, $submitBtn, originalText) {
        // Stop auto-saving
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
            this.autoSaveInterval = null;
        }
        
        // Create preview results object (no database storage)
        const previewResults = {
            score: score,
            total_questions: totalQuestions,
            correct_answers: correctAnswers,
            passed: passed,
            time_taken: timeTaken,
            is_preview_mode: true,
            message: 'This was a preview attempt. Results are not saved.',
            is_guest_attempt: false
        };
        
        // Show results
        this.showQuizResults(previewResults);
        
        // Reset button
        $submitBtn.removeClass('loading').prop('disabled', false).text(originalText);
    }

    handleBackToQuiz(e) {
        e.preventDefault();
        
        // Hide review and show quiz interface
        jQuery('.quiz-review').hide();
        jQuery('.splms-quiz-questions-container, .splms-quiz-navigation').show();
    }

    /**
     * Show quiz results
     * Stops timer, hides quiz interface, displays results
     * @param {Object} results - Quiz results data
     */
    showQuizResults(results) {
        
        // Stop timer
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
        
        this.isQuizActive = false;
        
        // Hide quiz interface, quiz actions, and attempt history, show results
        jQuery('#splms-quiz-interface').hide();
        jQuery('.splms-quiz-actions').hide();
        
        // Note: Attempt history will be populated dynamically by loadAttemptHistoryWithRetry()
        
        // Show results
        const $resultsContainer = jQuery('#splms-quiz-results');
        $resultsContainer.show();
        
        // Store results data for view answers functionality
        this.currentResults = results;
        
        // Check if results should be shown based on results_timing setting
        const resultsTiming = results.results_timing || 'after_passing';
        const shouldShow = results.should_show_results === true; // Only show if explicitly true
        
        if (!shouldShow) {
            // Show message based on timing
            let message = 'Your quiz results are not available yet.';
            if (resultsTiming === 'after_passing') {
                message = 'Your quiz results will be shown after you pass the quiz.';
            } else if (resultsTiming === 'manual') {
                message = 'Your quiz results will be available after instructor review.';
            }
            
            this.showResultsPlaceholder(message);
            return;
        }
        
        // Populate results using the template
        this.populateResults(results);
        
        // Hide the detailed results section initially
        const $detailedResults = $resultsContainer.find('.splms-quiz-detailed-results');
        if ($detailedResults.length > 0) {
            $detailedResults.hide();
        }
        
        // Scroll to results
        if ($resultsContainer.length > 0) {
            $resultsContainer[0].scrollIntoView({ behavior: 'smooth' });
        }
        
        // Dispatch custom event
        dispatchEvent('splms:quiz:resultShown', {
            quizId: this.settings.quizId,
            results: results
        });
    }

    showResultsPlaceholder(message) {
        const $container = jQuery('#splms-quiz-results');
        $container.html(`
            <div class="splms-quiz-results-placeholder">
                <div class="splms-quiz-results-icon">⏳</div>
                <h3>Results Pending</h3>
                <p>${message}</p>
            </div>
        `);

    }

    shouldShowAnswers(timing, results) {
        if (!results) return false;
        
        if (timing === 'after_completion') {
            return true;
        } else if (timing === 'after_passing') {
            return results.passed === true;
        } else if (timing === 'after_all_attempts') {
            return (results.attempts_remaining || 0) === 0;
        }
        
        return false;
    }

    populateResults(results) {
        
        const $container = jQuery('#splms-quiz-results');
        
        // Get quiz type from results
        const quizType = results.quiz_type || 'graded';

        // Map API response to template data - use exact field names from API response
        const resultsData = {
            score_percentage: results.percentage || 0,
            correct_answers: results.correct_answers || 0,
            total_questions: results.total_questions || 0,
            time_taken: results.time_taken || 0,
            passed: results.passed !== undefined ? results.passed : false,
            passing_grade: results.passing_grade || 70,
            attempts_used: results.attempts_used || 0,
            attempts_remaining: results.attempts_remaining || 0,
            max_attempts: results.max_attempts || 0,
            show_answers: results.show_answers !== false,
            detailed_results: results.detailed_results || [],
            pending_review: results.pending_review || false,
            has_manual_review: results.has_manual_review || false,
            manual_review_points: results.manual_review_points || 0,
            quiz_type: quizType,
            is_practice: quizType === 'practice',
            is_survey: quizType === 'survey',
            is_graded: quizType === 'graded'
        };
        
        // Format time taken for display
        const formattedTime = formatTime(resultsData.time_taken);
        
        // Check if quiz has manual review questions (essay, file upload)
        const hasManualReview = resultsData.has_manual_review || resultsData.pending_review;
        
        // Calculate derived values for template
        // If there are manual review questions, use "pending" status instead of "failed"
        let statusClass, statusIcon, heading, subtitle, statusMessage;
        
        if (hasManualReview && !resultsData.passed) {
            // Quiz has essay/file upload questions pending review
            statusClass = 'pending';
            statusIcon = '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
            heading = 'Quiz Submitted';
            subtitle = 'Your quiz has been submitted. Essay questions are pending instructor review.';
            statusMessage = '<span class="splms-icon-clock"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> Pending Review';
        } else {
            statusClass = resultsData.passed ? 'passed' : 'failed';
            if (results.is_preview_mode) {
                statusIcon = '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                heading = 'Preview Results';
                if (results.is_guest_attempt) {
                    subtitle = 'This was a preview attempt. Results have been saved as guest attempt.';
                } else {
                    subtitle = 'This was a preview attempt. Results are not saved.';
                }
                statusMessage = resultsData.passed ? '<span class="splms-icon-check"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> Would Pass!' : '<span class="splms-icon-close"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> Would Not Pass';
            } else {
                statusIcon = resultsData.passed ? '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' : '<svg class="splms-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                heading = resultsData.passed ? 'Congratulations!' : 'Quiz Complete';
                subtitle = resultsData.passed ? 'You have passed the quiz!' : 'Keep studying and try again.';
                statusMessage = resultsData.passed ? '<span class="splms-icon-check"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> You Passed!' : '<span class="splms-icon-close"><svg class="splms-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9L9 15M9 9L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span> You Did Not Pass';
            }
        }
        
        // Calculate retake availability
        // For practice quizzes, always allow retake (encourage practice)
        let retakeAvailable;
        if (resultsData.is_practice) {
            retakeAvailable = true; // Practice quizzes encourage retaking
        } else {
            retakeAvailable = resultsData.attempts_remaining > 0 && !resultsData.passed;
        }
        
        // Prepare template data object - merge API response data with derived values
        const templateData = {
            ...resultsData, // Include all API response fields
            statusClass: statusClass,
            statusIcon: statusIcon,
            heading: heading,
            subtitle: subtitle,
            statusMessage: statusMessage,
            time_taken: formattedTime, // Override with formatted time
            retake_available: retakeAvailable
        };
        
        // Store results data for view answers functionality
        this.currentResults = results;
        
        // Use WordPress template if available
        let resultsHtml;
        if (typeof wp !== 'undefined' && wp.template) {
            const template = wp.template('splms-quiz-results');
            resultsHtml = template(templateData);
        }
        
        // Set container classes and inject HTML
        $container
            .addClass('splms-quiz-results splms-quiz-animation-enter')
            .html(resultsHtml);
        
        // Show action buttons based on results (using new selectors)
        this.updateResultActions($container, results, resultsData.passed, resultsData.score_percentage);
        
        // Load attempt history with retry mechanism for database transaction completion
        this.loadAttemptHistoryWithRetry($container, results, 0);

        // Update quiz status badge if quiz was passed
        if (resultsData.passed) {
            this.updateQuizStatusBadge('completed', 'Passed');
        }
    }

    handleViewAnswers(e) {
        e.preventDefault();

        // If we don't have currentResults (e.g., results loaded from PHP template after refresh),
        // fetch the results via AJAX first
        if (!this.currentResults) {
            this.fetchQuizResultsForAnswerView(e);
            return;
        }

        // Security: Check quiz type first - never show answers for graded quizzes
        const quizType = this.currentResults.quiz_type || 'graded';
        if (quizType !== 'practice') {
            if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
                SPLMSCore.helper.showNotification('Answers are not available for graded quizzes.', 'info');
            } else {
                alert('Answers are not available for graded quizzes.');
            }
            return;
        }

        // Check if answers are enabled and timing setting before allowing view
        const showAnswers = this.currentResults.show_answers !== false;
        if (!showAnswers) {
            if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
                SPLMSCore.helper.showNotification('Answers are not available for this quiz.', 'info');
            } else {
                alert('Answers are not available for this quiz.');
            }
            return;
        }

        const showAnswersTiming = this.currentResults.show_answers_timing || 'after_completion';
        const shouldShow = this.shouldShowAnswers(showAnswersTiming, this.currentResults);

        if (!shouldShow) {
            if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
                SPLMSCore.helper.showNotification('Answers are not available yet based on quiz settings.', 'info');
            } else {
                alert('Answers are not available yet based on quiz settings.');
            }
            return;
        }
        
        const $container = jQuery('#splms-quiz-results');
        const $button = jQuery(e.currentTarget);
        let $detailedResults = $container.find('.splms-quiz-detailed-results');
        
        // Ensure detailed results section exists
        if ($detailedResults.length === 0) {
            const detailedHtml = `
                <div class="splms-quiz-detailed-results" style="display: none;">
                    <h3>Question Review</h3>
                    <div class="splms-quiz-questions-review"></div>
                </div>
            `;
            $container.find('.splms-quiz-result-actions').before(detailedHtml);
            $detailedResults = $container.find('.splms-quiz-detailed-results');
        }
        
        const $questionsReview = $detailedResults.find('.splms-quiz-questions-review');
        
        // Check if container is truly empty (no meaningful content)
        const hasQuestionItems = $questionsReview.find('.splms-quiz-question-review-item, .question-review-item, .question-result-item').length > 0;
        
        // Force populate if we don't have actual question items
        if ($questionsReview.length > 0 && !hasQuestionItems) {
            this.questionsHandler.populateDetailedResults($questionsReview);
        }
        
        // Toggle visibility
        if ($detailedResults.is(':visible')) {
            $detailedResults.slideUp(300);
            $button.html('<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> View Answers');
        } else {
            // ALWAYS populate detailed results when showing - regardless of previous checks
            this.questionsHandler.populateDetailedResults($questionsReview);
            $detailedResults.slideDown(300);
            $button.html('<svg class="splms-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 14L12 9L7 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Hide Answers');
        }
    }

    /**
     * Fetch quiz results via AJAX for answer view when currentResults is not available
     */
    fetchQuizResultsForAnswerView(originalEvent) {
        const $button = jQuery(originalEvent.currentTarget);
        const quizId = this.settings.quizId || jQuery('[data-quiz-id]').first().data('quiz-id');

        if (!quizId) {
            console.error('Quiz ID not found for fetching results');
            if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
                SPLMSCore.helper.showNotification('Unable to load quiz answers.', 'error');
            }
            return;
        }

        // Show loading state
        const originalButtonHtml = $button.html();
        $button.prop('disabled', true).html('<svg class="splms-icon splms-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 2V6" stroke="currentColor" stroke-width="2"/></svg> Loading...');

        // Fetch quiz attempts with detailed answers
        jQuery.ajax({
            url: getAjaxUrl(),
            type: 'POST',
            data: {
                action: 'splms_get_quiz_attempts',
                quiz_id: quizId,
                include_answers: true,
                nonce: window.splms_frontend?.nonces?.splms_frontend_nonce || ''
            },
            success: (response) => {
                if (response.success && response.attempts && response.attempts.length > 0) {
                    // Use the most recent attempt
                    const latestAttempt = response.attempts[0];

                    // Convert attempt data to currentResults format
                    this.currentResults = {
                        passed: latestAttempt.passed || false,
                        percentage: latestAttempt.percentage || 0,
                        score: latestAttempt.score || 0,
                        max_score: latestAttempt.max_score || 0,
                        time_taken: latestAttempt.time_taken || 0,
                        detailed_results: latestAttempt.answers || [],
                        show_answers: latestAttempt.quiz_type === 'practice', // Security: Only practice quizzes
                        show_answers_timing: 'after_completion',
                        attempt_id: latestAttempt.id,
                        quiz_type: latestAttempt.quiz_type || 'graded'
                    };

                    // Restore button and trigger handleViewAnswers again
                    $button.prop('disabled', false).html(originalButtonHtml);
                    this.handleViewAnswers(originalEvent);
                } else {
                    console.error('Failed to fetch quiz attempts:', response);
                    $button.prop('disabled', false).html(originalButtonHtml);
                    if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
                        SPLMSCore.helper.showNotification('No quiz results found.', 'info');
                    } else {
                        alert('No quiz results found.');
                    }
                }
            },
            error: (xhr, status, error) => {
                console.error('AJAX error fetching quiz results:', error);
                $button.prop('disabled', false).html(originalButtonHtml);
                if (typeof SPLMSCore !== 'undefined' && SPLMSCore.helper && SPLMSCore.helper.showNotification) {
                    SPLMSCore.helper.showNotification('Failed to load quiz answers.', 'error');
                } else {
                    alert('Failed to load quiz answers.');
                }
            }
        });
    }

    // Result action handlers
    // NOTE: handleRetakeQuiz is deprecated - retake functionality now handled in handleRetakeQuizFromPHPResults()
    handleRetakeQuiz(e) {
        e.preventDefault();
        console.warn('⚠️ Deprecated handleRetakeQuiz called - retake functionality moved to handleRetakeQuizFromPHPResults()');
        // Fallback to old behavior if new handler fails
        if (confirm('Are you sure you want to retake this quiz? Your current results will remain in your history.')) {
            window.location.reload();
        }
    }

    handleNext(e) {
        e.preventDefault();
        // Simple next action - go to course overview or next section
        const courseUrl = jQuery('.splms-quiz-breadcrumb a').last().attr('href');
        if (courseUrl) {
            window.location.href = courseUrl;
        } else {
            window.location.reload();
        }
    }

    handleViewCourse(e) {
        e.preventDefault();
        // Navigate to course overview/main page
        const courseUrl = jQuery('.splms-quiz-breadcrumb a').last().attr('href');
        if (courseUrl) {
            window.location.href = courseUrl;
        } else {
            window.location.reload();
        }
    }

    // Utility methods
    updateResultActions($container, results, passed, percentage) {
        const attemptsRemaining = results.attempts_remaining || 0;
        const attemptsUsed = results.attempts_used || 0;
        const maxAttempts = results.max_attempts || 3;
        const showAnswers = results.show_answers !== false;
        const passingGrade = results.passing_grade || this.settings.passingGrade || 70;
        const quizType = results.quiz_type || 'graded';
        const isPractice = quizType === 'practice';
        const isSurvey = quizType === 'survey';
        
        // Get button references
        const $retakeBtn = $container.find('.retake-quiz-btn');
        const $viewAnswersBtn = $container.find('.view-answers-btn');
        const $nextBtn = $container.find('.next-btn');
        const $viewCourseBtn = $container.find('.view-course-btn');

        // Hide all buttons first
        $retakeBtn.hide();
        $viewAnswersBtn.hide();
        $nextBtn.hide();
        $viewCourseBtn.hide();
        
        // Show retake button logic
        if (isPractice) {
            // Practice quizzes: Always show retake button (encourage practice)
            $retakeBtn.show().text('🔄 Practice Again').removeClass('disabled');
        } else if (attemptsRemaining > 0 && !passed) {
            // Graded quizzes: Show retake if attempts remaining and failed
            $retakeBtn.show().text('🔄 Retake Quiz');
        }
        
        // Security: Only show view answers button for practice quizzes
        // For graded/assessment quizzes, hide answers to maintain academic integrity
        if (showAnswers && isPractice) {
            const showAnswersTiming = results.show_answers_timing || 'after_completion';
            const shouldShow = this.shouldShowAnswers(showAnswersTiming, results);

            if (shouldShow) {
                $viewAnswersBtn.show().text('👁️ View Answers');
            }
        }
        // Note: View Answers button remains hidden for graded quizzes (security)
        
        // Show navigation buttons when quiz is completed (passed) OR no attempts remaining
        if (passed || attemptsRemaining === 0) {
            $nextBtn.show();
            $viewCourseBtn.show();
        }
    }

    /**
     * Update the quiz status badge in the header
     */
    updateQuizStatusBadge(statusClass, statusText) {
        const $statusBadge = jQuery('.splms-status-badge');
        if ($statusBadge.length > 0) {
            // Remove existing status classes
            $statusBadge.removeClass('splms-status-in-progress splms-status-completed');

            // Add new status class
            $statusBadge.addClass('splms-status-' + statusClass);

            // Update status text
            $statusBadge.text(statusText);
        }
    }

    /**
     * Load attempt history with retry mechanism for database transaction completion
     */
    loadAttemptHistoryWithRetry($container, results, retryCount = 0) {
        const maxRetries = 3;
        const baseDelay = 500;
        const retryDelay = baseDelay + (retryCount * 500); // Progressive delay: 500ms, 1s, 1.5s

        setTimeout(() => {
            this.loadAttemptHistory($container, results, (attempts) => {
                // Check if the current attempt is included in the results
                const currentAttemptId = results.attempt_id || results.id;
                const attemptFound = currentAttemptId && attempts && attempts.find(a => a.id == currentAttemptId);

                if (!attemptFound && retryCount < maxRetries && currentAttemptId) {
                    this.loadAttemptHistoryWithRetry($container, results, retryCount + 1);
                } else if (retryCount > 0) {
                }
            });
        }, retryDelay);
    }

    /**
     * Load and display attempt history
     */
    loadAttemptHistory($container, results, callback = null) {
        // Try multiple selectors to find the attempt history container
        let $attemptHistory = $container.find('.splms-quiz-attempt-history .splms-quiz-attempts-list');
        if ($attemptHistory.length === 0) {
            $attemptHistory = $container.find('.splms-quiz-attempts-list');
        }
        if ($attemptHistory.length === 0) {
            $attemptHistory = $container.find('.attempt-history .attempts-list');
        }
        if ($attemptHistory.length === 0) {
            // Try to find or create the attempt history section
            let $attemptSection = $container.find('.splms-quiz-attempt-history');
            if ($attemptSection.length === 0) {
                // Create the section if it doesn't exist - insert before result actions
                $attemptSection = jQuery(`
                    <div class="splms-quiz-attempt-history">
                        <h4>Attempt History</h4>
                        <div class="splms-quiz-attempts-list"></div>
                    </div>
                `);
                // Insert before result actions or at the end
                const $actions = $container.find('.splms-quiz-result-actions');
                if ($actions.length > 0) {
                    $actions.before($attemptSection);
                } else {
                    $container.append($attemptSection);
                }
            }
            $attemptHistory = $attemptSection.find('.splms-quiz-attempts-list');
        }
        
        if ($attemptHistory.length === 0) {
            console.warn('Could not find or create attempt history container');
            return;
        }
        
        // Get attempt history from server
        // Try multiple sources for quiz ID
        let quizId = this.settings.quizId;
        if (!quizId && results) {
            quizId = results.quiz_id || results.quizId;
        }
        if (!quizId && this.quizData) {
            quizId = this.quizData.quiz_id || this.quizData.quizId;
        }
        
        if (!quizId) {
            console.warn('No quiz ID available for loading attempt history. Settings:', this.settings, 'Results:', results);
            $attemptHistory.html('<div class="no-attempts-message">Unable to load attempt history.</div>');
            return;
        }
        
        const frontend = window.splms_frontend;
        jQuery.ajax({
            url: frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'splms_get_quiz_attempts',
                quiz_id: quizId,
                nonce: frontend.nonces.splms_nonce
            },
            success: (response) => {
                let attempts = [];
                if (response.success && response.data) {
                    attempts = Array.isArray(response.data) ? response.data : [];
                    if (attempts.length > 0) {
                        this.renderAttemptHistory($container, attempts);
                    } else {
                        // Show message if no attempts found
                        $attemptHistory.html('<div class="no-attempts-message">No previous attempts found.</div>');
                    }
                } else {
                    console.warn('Attempt history response error:', response);
                    $attemptHistory.html('<div class="no-attempts-message">Unable to load attempt history.</div>');
                }

                // Call callback if provided for retry mechanism
                if (callback && typeof callback === 'function') {
                    callback(attempts);
                }
            },
            error: (xhr, status, error) => {
                $attemptHistory.html('<div class="no-attempts-message">Failed to load attempt history. Please refresh the page.</div>');

                // Call callback with empty array if provided for retry mechanism
                if (callback && typeof callback === 'function') {
                    callback([]);
                }
            }
        });
    }
    
    /**
     * Render attempt history list
     */
    renderAttemptHistory($container, attempts) {
        // Try multiple selectors to find the attempts list
        let $attemptsList = $container.find('.splms-quiz-attempts-list');
        if ($attemptsList.length === 0) {
            $attemptsList = $container.find('.attempts-list');
        }
        if ($attemptsList.length === 0) {
            $attemptsList = $container.find('.splms-quiz-attempt-history .splms-quiz-attempts-list');
        }
        
        if ($attemptsList.length === 0) {
            console.warn('Could not find attempts list container in renderAttemptHistory');
            return;
        }
        
        if (!Array.isArray(attempts) || attempts.length === 0) {
            $attemptsList.html(`
                <div class="splms-no-attempts-message">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <p>No previous attempts found.</p>
                </div>
            `);
            return;
        }

        let attemptsHtml = '';
        attempts.forEach((attempt, index) => {
            const attemptNumber = attempts.length - index; // Reverse order (latest first)
            const passed = attempt.passed === 1 || attempt.passed === true || attempt.passed === '1';
            const score = attempt.score !== undefined ? Math.round(parseFloat(attempt.score)) : 0;
            const maxScore = attempt.max_score !== undefined ? parseFloat(attempt.max_score) : 100;
            const percentage = maxScore > 0 ? Math.round((score / maxScore) * 100) : score;

            // Check if this attempt is pending review
            const pendingReview = attempt.pending_review === true || attempt.pending_review === 1 || attempt.pending_review === '1';
            const hasManualReview = attempt.has_manual_review === true || attempt.has_manual_review === 1 || attempt.has_manual_review === '1';

            // Try multiple date fields and format them properly
            const date = attempt.attempt_time || attempt.attempt_date || attempt.date || attempt.created_at || '';
            let formattedDate = '';
            if (date) {
                try {
                    const dateObj = new Date(date);
                    if (!isNaN(dateObj.getTime())) {
                        // Format to match PHP template: "Jan 22, 2026 at 11:50 AM"
                        formattedDate = dateObj.toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        }) + ' at ' + dateObj.toLocaleTimeString('en-US', {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    } else {
                        formattedDate = date; // Use as-is if parsing fails
                    }
                } catch (e) {
                    formattedDate = date;
                }
            }

            // Determine status classes and icons to match PHP template
            let historyStatusClass, historyStatusText, historyStatusIcon;
            if (pendingReview || (hasManualReview && !passed && score === 0)) {
                historyStatusClass = 'attempt-pending';
                historyStatusText = 'Pending Review';
                historyStatusIcon = '⏳';
            } else if (passed) {
                historyStatusClass = 'attempt-passed';
                historyStatusText = 'Passed';
                historyStatusIcon = '✅';
            } else {
                historyStatusClass = 'attempt-failed';
                historyStatusText = 'Failed';
                historyStatusIcon = '❌';
            }

            // Calculate time taken display
            const timeTaken = attempt.time_taken || 0;
            const timeDisplay = timeTaken > 0 ? `${Math.floor(timeTaken / 60)}:${String(timeTaken % 60).padStart(2, '0')}` : '0:00';

            // Generate HTML structure matching PHP template exactly
            attemptsHtml += `
                <div class="splms-quiz-attempt-item ${historyStatusClass}">
                    <div class="splms-attempt-header">
                        <div class="splms-attempt-number">
                            <span class="splms-attempt-badge">#${attemptNumber}</span>
                        </div>
                        <div class="splms-attempt-status-badge ${historyStatusClass}">
                            <span class="splms-status-icon">${historyStatusIcon}</span>
                            <span class="splms-status-text">${historyStatusText}</span>
                        </div>
                    </div>
                    <div class="splms-attempt-details">
                        <div class="splms-attempt-detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
                                <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
                                <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <span class="splms-attempt-date">${formattedDate}</span>
                        </div>
                        <div class="splms-attempt-detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 11H15M9 15H15M17 21L12 16L7 21V5C7 3.89543 7.89543 3 9 3H15C16.1046 3 17 3.89543 17 5V21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="splms-attempt-score">${percentage}% (${score}/${maxScore})</span>
                        </div>
                        <div class="splms-attempt-detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="splms-attempt-time">${timeDisplay}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        $attemptsList.html(attemptsHtml);
    }


    markQuizAsComplete(progressData) {
        // Find the current quiz item in the curriculum
        const quizId = this.settings.quizId;
        let $quizItem = jQuery(`a[data-lesson-id="${quizId}"][data-item-type="quiz"]`).closest('.curriculum-item');
        
        // Alternative selectors if first one doesn't work
        if (!$quizItem.length) {
            $quizItem = jQuery(`a[data-lesson-id="${quizId}"]`).closest('.curriculum-item');
        }
        if (!$quizItem.length) {
            $quizItem = jQuery(`a[href*="post=${quizId}"]`).closest('.curriculum-item');
        }
        if (!$quizItem.length) {
            $quizItem = jQuery('.curriculum-item.current').filter(function() {
                return jQuery(this).find('a').attr('href') && jQuery(this).find('a').attr('href').includes(quizId);
            });
        }
        
        if ($quizItem.length) {
            // Add completed class and remove current class
            $quizItem.addClass('completed').removeClass('current');
            
            // Update status text/icon
            const $statusElement = $quizItem.find('.item-status');
            if ($statusElement.length) {
                $statusElement.html('✓');
            } else {
                $quizItem.append('<span class="completion-check">✓</span>');
            }
            
            // Add completion animation
            $quizItem.addClass('completion-animation');
            setTimeout(() => {
                $quizItem.removeClass('completion-animation');
            }, 1000);
            
            // Update progress bar using server-side calculated progress
            if (window.SPLMSCurriculum && window.SPLMSCurriculum.updateProgress && progressData) {
                window.SPLMSCurriculum.updateProgress(progressData);
            }
            
            // Show notification
            SPLMSCore.helper.showNotification('Quiz completed! 🎉', 'success');
        }
    }


    handleKeyboardEvents(e) {
        if (!this.isQuizActive) return;
        
        // Escape key to close (with confirmation)
        if (e.keyCode === 27) {
            this.handleCloseQuiz(e);
        }
        
        // Arrow keys for navigation
        if (e.keyCode === 37) { // Left arrow
            this.handlePreviousPage(e);
        } else if (e.keyCode === 39) { // Right arrow
            this.handleNextPage(e);
        }
    }

    handlePageLeave(e) {
        if (this.isQuizActive) {
            const message = 'You have an active quiz. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    }

    handleCloseQuiz(e) {
        e.preventDefault();
        
        if (this.isQuizActive) {
            const confirmClose = confirm('Are you sure you want to close the quiz? Your progress will be lost.');
            if (!confirmClose) {
                return;
            }
        }
        
        this.hideQuizModal();
    }

    hideQuizModal() {
        const $modal = jQuery('#splms-quiz-modal');
        $modal.fadeOut(300);
        jQuery('body').removeClass('quiz-modal-open');
        
        // Clear timer
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
        
        this.isQuizActive = false;
    }

    // Public method to check if quiz is active
    isActive() {
        return this.isQuizActive;
    }

    // Public method to get current quiz data
    getCurrentQuizData() {
        return this.quizData;
    }

    // Public method to get current answers
    getCurrentAnswers() {
        return this.answers;
    }

    // Helper method to calculate current page based on current question
    calculateCurrentPage() {
        const questionsPerPage = this.settings.questionsPerPage || 1;
        return Math.ceil((this.currentQuestion + 1) / questionsPerPage);
    }

    // Helper method to update current page consistently
    updateCurrentPage() {
        this.currentPage = this.calculateCurrentPage();
    }

    /**
     * Clean and validate answers before submission
     * Ensures answers are in the format expected by the evaluator:
     * - multiple_choice/true_false: option ID string (not array, not object)
     * - multiple_select: array of option IDs (not empty array, not object)
     * - short_answer/fill_blank/essay: text string
     * - ordering: array of option IDs
     * - matching: object with leftId: rightId pairs
     * - file_upload: file name string
     * 
     * @param {Object} rawAnswers - Raw answers object from this.answers
     * @returns {Object} Cleaned answers object
     */
    cleanAnswersForSubmission(rawAnswers) {
        const cleaned = {};
        
        // Get question data to determine question types
        const questionsMap = {};
        if (this.questionsData && Array.isArray(this.questionsData)) {
            this.questionsData.forEach(q => {
                questionsMap[q.id] = q;
            });
        }
        
        Object.keys(rawAnswers).forEach(questionId => {
            const answer = rawAnswers[questionId];
            const question = questionsMap[questionId];
            const questionType = question ? question.type : 'unknown';
            
            // Skip if answer is null or undefined
            if (answer === null || answer === undefined) {
                return; // Don't include in cleaned answers
            }
            
            // Handle different question types
            switch (questionType) {
                case 'multiple_choice':
                case 'true_false':
                    // Single select - must be option text string
                    if (Array.isArray(answer)) {
                        // If it's an array, take the first element (should be option text)
                        cleaned[questionId] = answer.length > 0 ? String(answer[0]) : '';
                    } else if (typeof answer === 'object' && answer !== null) {
                        // If it's an object, try to extract text
                        cleaned[questionId] = answer.text ? String(answer.text) : '';
                    } else {
                        // Should be option text string
                        cleaned[questionId] = answer ? String(answer) : '';
                    }
                    break;
                    
                case 'multiple_select':
                    // Multiple select - must be array of option text values
                    if (Array.isArray(answer)) {
                        // Filter out empty values and convert to strings
                        const optionTexts = answer
                            .filter(v => v !== null && v !== undefined && v !== '')
                            .map(v => {
                                // If array element is an object, extract text
                                if (typeof v === 'object' && v !== null && v.text) {
                                    return String(v.text);
                                }
                                return String(v);
                            });
                        cleaned[questionId] = optionTexts.length > 0 ? optionTexts : '';
                    } else if (typeof answer === 'object' && answer !== null) {
                        // If it's an object, try to extract texts
                        cleaned[questionId] = '';
                    } else {
                        // Single value - convert to array
                        cleaned[questionId] = answer ? [String(answer)] : '';
                    }
                    break;
                    
                case 'short_answer':
                case 'fill_blank':
                case 'essay':
                case 'long_answer':
                    // Text input - must be string
                    if (Array.isArray(answer)) {
                        cleaned[questionId] = answer.length > 0 ? String(answer[0]) : '';
                    } else if (typeof answer === 'object' && answer !== null) {
                        cleaned[questionId] = '';
                    } else {
                        cleaned[questionId] = answer ? String(answer).trim() : '';
                    }
                    break;
                    
                case 'ordering':
                    // Ordering - must be array of option text values (not indices)
                    if (Array.isArray(answer)) {
                        // Convert all elements to strings (option text values)
                        const optionTexts = answer
                            .filter(v => v !== null && v !== undefined && v !== '')
                            .map(v => {
                                if (typeof v === 'object' && v !== null && v.text) {
                                    return String(v.text);
                                }
                                return String(v);
                            });
                        cleaned[questionId] = optionTexts;
                    } else if (typeof answer === 'object' && answer !== null) {
                        cleaned[questionId] = [];
                    } else {
                        cleaned[questionId] = answer ? [String(answer)] : [];
                    }
                    break;
                    
                case 'matching':
                    // Matching - must be object with leftText: rightText pairs
                    // Backend expects: {left_text: right_text}
                    if (typeof answer === 'object' && answer !== null && !Array.isArray(answer)) {
                        // Already in object format - clean it (assume keys and values are text)
                        const cleanedPairs = {};
                        Object.keys(answer).forEach(key => {
                            const value = answer[key];
                            if (value !== null && value !== undefined && value !== '') {
                                cleanedPairs[String(key)] = String(value);
                            }
                        });
                        cleaned[questionId] = Object.keys(cleanedPairs).length > 0 ? cleanedPairs : '';
                    } else if (Array.isArray(answer)) {
                        // Array format: [rightText0, rightText1, ...] where index = pair index
                        // Convert to: {left_text: right_text} using question pairs
                        const cleanedPairs = {};
                        const question = questionsMap[questionId];
                        
                        if (question && question.pairs && Array.isArray(question.pairs) && question.pairs.length > 0) {
                            const pairs = question.pairs;
                            
                            answer.forEach((rightText, index) => {
                                if (rightText && pairs[index]) {
                                    const pair = pairs[index];
                                    const leftText = pair.left ? String(pair.left) : '';
                                    const rightValue = String(rightText);
                                    
                                    if (leftText && rightValue) {
                                        cleanedPairs[leftText] = rightValue;
                                    }
                                }
                            });
                        }
                        
                        cleaned[questionId] = Object.keys(cleanedPairs).length > 0 ? cleanedPairs : '';
                    } else {
                        cleaned[questionId] = '';
                    }
                    break;
                    
                case 'file_upload':
                    // File upload - must be file name string (or URL after upload)
                    if (Array.isArray(answer)) {
                        cleaned[questionId] = answer.length > 0 ? String(answer[0]) : '';
                    } else if (typeof answer === 'object' && answer !== null) {
                        cleaned[questionId] = '';
                    } else {
                        cleaned[questionId] = answer ? String(answer) : '';
                    }
                    break;
                    
                default:
                    // Unknown type - try to clean as best we can
                    if (Array.isArray(answer)) {
                        cleaned[questionId] = answer.length > 0 ? answer : '';
                    } else if (typeof answer === 'object' && answer !== null) {
                        cleaned[questionId] = '';
                    } else {
                        cleaned[questionId] = answer ? String(answer) : '';
                    }
            }
        });
        
        return cleaned;
    }

    /**
     * Comprehensive reset for retaking quiz
     * Clears all quiz state and prepares for fresh start
     */
    resetQuizForRetake() {
        // 1. Clear browser storage
        if (this.settings && this.settings.quizId) {
            clearStorage(this.settings.quizId);
        }

        // 2. Reset instance properties
        this.attemptId = null;
        this.currentResults = null;
        this.savedAnswers = {};
        this.isQuizActive = false;
        this.isSubmitting = false;
        this.questionsData = null;
        this.currentQuestionIndex = 0;

        // 3. Clear timer state
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
            this.autoSaveInterval = null;
        }

        // 4. Reset UI elements
        jQuery('#splms-quiz-interface').hide();
        jQuery('#splms-quiz-results').hide();

        // Clear any error messages or notifications
        jQuery('.splms-quiz-error, .splms-quiz-notification').remove();

        // Reset form elements in quiz interface
        jQuery('#splms-quiz-interface input, #splms-quiz-interface textarea, #splms-quiz-interface select').val('');
        jQuery('#splms-quiz-interface input[type="radio"], #splms-quiz-interface input[type="checkbox"]').prop('checked', false);

        // 5. Reset navigation and progress
        this.pagination = {
            currentPage: 1,
            totalPages: 1,
            questionsPerPage: 1
        };

        // 6. Show quiz interface elements
        jQuery('.splms-quiz-stats').show();
        jQuery('.splms-quiz-description').show();
        jQuery('.splms-quiz-actions').show();

        // 7. Reset and enable start/retake buttons
        const startButtons = jQuery('.start-quiz-btn, .restart-quiz-btn');
        startButtons.prop('disabled', false)
                   .removeClass('loading')
                   .each(function() {
                       const $btn = jQuery(this);
                       if ($btn.hasClass('restart-quiz-btn')) {
                           $btn.text($btn.data('original-text') || 'Start New Attempt');
                       } else {
                           $btn.text($btn.data('original-text') || 'Start Quiz');
                       }
                   });


        // 8. Verify reset state
        this.verifyResetState();

        // 9. Dispatch reset event for any listeners
        dispatchEvent('splms:quiz:reset', {
            quizId: this.settings?.quizId
        });
    }

    /**
     * Verify that quiz state has been properly reset
     */
    verifyResetState() {

        const checks = {
            'Attempt ID cleared': this.attemptId === null,
            'Current results cleared': this.currentResults === null,
            'Saved answers cleared': Object.keys(this.savedAnswers).length === 0,
            'Quiz not active': this.isQuizActive === false,
            'Not submitting': this.isSubmitting === false,
            'Questions data cleared': this.questionsData === null,
            'Question index reset': this.currentQuestionIndex === 0,
            'Timer intervals cleared': this.timerInterval === null && this.autoSaveInterval === null,
            'Results container hidden': jQuery('#splms-quiz-results').is(':hidden'),
            'Quiz interface hidden': jQuery('#splms-quiz-interface').is(':hidden'),
            'Stats visible': jQuery('.splms-quiz-stats').is(':visible'),
            'Description visible': jQuery('.splms-quiz-description').is(':visible'),
            'Actions visible': jQuery('.splms-quiz-actions').is(':visible')
        };

        let allPassed = true;
        Object.entries(checks).forEach(([checkName, passed]) => {
            const status = passed ? '✅' : '❌';
            if (!passed) allPassed = false;
        });

        if (allPassed) {
        } else {
            console.warn('⚠️ Some reset state checks failed - quiz may not be fully reset');
        }

        return allPassed;
    }
}

// Initialize when document is ready
jQuery(document).ready(function() {
    // Only initialize on quiz pages
    if (jQuery('.splms-quiz-container').length || jQuery('#splms-quiz-modal').length) {
        window.SPLMSQuiz = new SPLMSQuiz();

        // Handle retake quiz functionality with PHP-rendered results
        handleRetakeQuizFromPHPResults();
    }
});

/**
 * Handle retake quiz functionality when results are rendered by PHP
 */
function handleRetakeQuizFromPHPResults() {
    // Listen for retake quiz button clicks in PHP-rendered results
    jQuery(document).on('click', '.retake-quiz-btn', function(e) {
        e.preventDefault();


        // Show confirmation dialog
        if (!confirm('Are you sure you want to retake this quiz? Your current results will remain in your history.')) {
            return;
        }

        // Get quiz and course IDs
        const quizId = jQuery(this).data('quiz-id');
        const courseId = jQuery(this).data('course-id');

        if (!quizId) {
            console.error('❌ Quiz ID not found for retake');
            return;
        }

        // Show loading state on the retake button
        const $retakeBtn = jQuery(this);
        const originalText = $retakeBtn.text();
        $retakeBtn.text('Resetting Quiz...').prop('disabled', true);

        // Get the SPLMSQuiz instance
        if (!window.SPLMSQuiz) {
            console.error('❌ SPLMSQuiz instance not found');
            $retakeBtn.text(originalText).prop('disabled', false);
            return;
        }

        // Set quiz settings if needed
        if (!window.SPLMSQuiz.settings || !window.SPLMSQuiz.settings.quizId) {
            window.SPLMSQuiz.settings = window.SPLMSQuiz.settings || {};
            window.SPLMSQuiz.settings.quizId = quizId;
            window.SPLMSQuiz.settings.courseId = courseId;
        }

        // Perform comprehensive reset
        try {
            window.SPLMSQuiz.resetQuizForRetake();

            // Smooth scroll to quiz actions after reset
            setTimeout(() => {
                const actionsElement = document.querySelector('.splms-quiz-actions');
                if (actionsElement) {
                    actionsElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }

                // Show success message briefly
                const startButton = jQuery('.start-quiz-btn');
                if (startButton.length) {
                    startButton.text('Ready to Start New Attempt!').addClass('pulse-animation');
                    setTimeout(() => {
                        startButton.text('Start Quiz').removeClass('pulse-animation');
                    }, 2000);
                }

            }, 500);


        } catch (error) {
            console.error('❌ Error during quiz reset:', error);

            // Fallback - simple show/hide if reset fails
            jQuery('#splms-quiz-results').hide();
            jQuery('.splms-quiz-stats, .splms-quiz-description, .splms-quiz-actions').show();
        }

        // Re-enable retake button
        $retakeBtn.text(originalText).prop('disabled', false);
    });
}

// Additional styles for quiz modal and detailed results
jQuery(document).ready(function() {
    if (!jQuery('#splms-quiz-styles').length) {
        jQuery('head').append(`
            <style id="splms-quiz-styles">
                body.quiz-modal-open {
                    overflow: hidden;
                }

                /* Pulse animation for retake success feedback */
                .pulse-animation {
                    animation: pulseGlow 2s ease-in-out;
                }

                @keyframes pulseGlow {
                    0% { box-shadow: 0 0 0 0 var(--splms-primary-alpha-7, rgba(126, 117, 255, 0.7)); }
                    50% { box-shadow: 0 0 0 10px var(--splms-primary-alpha-0, rgba(126, 117, 255, 0)); }
                    100% { box-shadow: 0 0 0 0 var(--splms-primary-alpha-0, rgba(126, 117, 255, 0)); }
                }
                
                .quiz-modal-open .splms-quiz-modal {
                    display: flex !important;
                }
                
                .option.selected {
                    background-color: var(--splms-warning-bg, #fef3c7) !important;
                    border-color: var(--splms-warning, #f59e0b) !important;
                }
                
                .quiz-timer.danger {
                    animation: timerPulse 1s infinite;
                    color: var(--splms-danger, #ef4444) !important;
                }
                
                @keyframes timerPulse {
                    0% { opacity: 1; }
                    50% { opacity: 0.6; }
                    100% { opacity: 1; }
                }
                
                .quiz-nav-btn.loading,
                .quiz-submit-btn.loading,
                .start-quiz-btn.loading {
                    opacity: 0.7;
                    cursor: not-allowed;
                }
                
                /* Detailed Results Styles */
                .detailed-results {
                    margin: 1.5rem 0;
                    padding: 1rem;
                    border: 1px solid var(--splms-border-color, #e5e7eb);
                    border-radius: var(--splms-border-radius, 0.5rem);
                    background-color: var(--splms-background-secondary, #f9fafb);
                }
                
                .detailed-results-header h3 {
                    margin: 0 0 1rem 0;
                    color: var(--splms-text-color, #374151);
                    font-size: 1.1rem;
                }
                
                .question-result-item {
                    margin-bottom: 1rem;
                    padding: 1rem;
                    border-radius: 0.375rem;
                    background-color: white;
                    border-left: 4px solid var(--splms-border-color, #e5e7eb);
                }
                
                .question-result-item.correct {
                    border-left-color: var(--splms-success, #10b981);
                    background-color: var(--splms-success-bg, #f0fdf4);
                }
                
                .question-result-item.incorrect {
                    border-left-color: var(--splms-danger, #ef4444);
                    background-color: var(--splms-danger-bg, #fef2f2);
                }
                
                .question-result-header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 0.75rem;
                    gap: 0.5rem;
                }
                
                .question-number {
                    font-weight: 600;
                    color: var(--splms-text-color, #374151);
                }
                
                .result-icon {
                    font-size: 1.1rem;
                }
                
                .question-points {
                    margin-left: auto;
                    font-size: 0.875rem;
                    color: var(--splms-text-muted, #6b7280);
                    background-color: var(--splms-background-tertiary, #f3f4f6);
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.25rem;
                }
                
                .question-text {
                    font-weight: 500;
                    margin-bottom: 0.75rem;
                    color: var(--splms-text-color, #374151);
                }
                
                .answer-comparison {
                    margin-bottom: 0.75rem;
                }
                
                .user-answer, .correct-answer {
                    margin-bottom: 0.5rem;
                }
                
                .answer-value {
                    display: inline-block;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.25rem;
                    font-weight: 500;
                    margin-left: 0.5rem;
                }
                
                .answer-value.correct-answer {
                    background-color: var(--splms-success-light, #d1fae5);
                    color: var(--splms-success-dark, #065f46);
                }
                
                .answer-value.incorrect-answer {
                    background-color: var(--splms-danger-light, #fee2e2);
                    color: var(--splms-danger-dark, #991b1b);
                }
                
                .question-explanation {
                    margin-top: 0.75rem;
                    padding: 0.75rem;
                    background-color: var(--splms-info-bg, #f0f9ff);
                    border-radius: var(--splms-border-radius-sm, 0.375rem);
                    border-left: 3px solid var(--splms-info, #0ea5e9);
                }
                
                .question-explanation p {
                    margin: 0.5rem 0 0 0;
                    color: var(--splms-text-color, #374151);
                    line-height: 1.5;
                }
                
                .no-detailed-results {
                    text-align: center;
                    padding: 2rem;
                    color: var(--splms-text-muted, #6b7280);
                    font-style: italic;
                }
                
                @media (max-width: 768px) {
                    .splms-quiz-modal {
                        padding: 0.5rem;
                    }
                    
                    .quiz-modal-content {
                        margin: 0;
                        border-radius: 0.5rem;
                    }
                    
                    .detailed-results {
                        padding: 0.75rem;
                    }
                    
                    .question-result-item {
                        padding: 0.75rem;
                    }
                    
                    .question-result-header {
                        flex-wrap: wrap;
                        gap: 0.25rem;
                    }
                }
            </style>
        `);
    }
}); 