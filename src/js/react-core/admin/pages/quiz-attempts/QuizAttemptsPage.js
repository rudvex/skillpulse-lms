/**
 * QuizAttemptsPage - Main quiz attempts management page
 */

import React, { Component, Fragment } from 'react';
import { __ } from '@wordpress/i18n';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { 
    Button, 
    Spinner, 
    Card,
    CardBody,
    CardHeader,
    Icon
} from '@wordpress/components';

import './styles/index.scss';
import BrandLogo from "../../../components/BrandLogo";
import { SplmsIcon } from "../../../components/SplmsIcon";

// Import components
import List from './List';
import Detail from './Detail';

class QuizAttemptsPage extends Component {
    constructor(props) {
        super(props);
        
        this.state = {
            // View mode: 'list' or 'detail'
            view: 'list',
            selectedAttemptId: null,
            
            // Filters
            filters: {
                search: '',
                course_id: '',
                quiz_id: '',
                passed: '',
            },
            
            // Pagination
            currentPage: 1,
            perPage: 20,
            
            // Sorting
            sortBy: 'attempt_time',
            sortOrder: 'desc',
            
        };
        
        // Bind methods
        this.handleViewAttempt = this.handleViewAttempt.bind(this);
        this.handleBackToList = this.handleBackToList.bind(this);
        this.handleFilterChange = this.handleFilterChange.bind(this);
        this.handleApplyFilters = this.handleApplyFilters.bind(this);
        this.handlePageChange = this.handlePageChange.bind(this);
        this.handleSortChange = this.handleSortChange.bind(this);
        this.handleVerifyAttempt = this.handleVerifyAttempt.bind(this);
        this.handleDeleteAttempt = this.handleDeleteAttempt.bind(this);
    }

    /**
     * Safely convert WordPress translation result to string
     */
    safeTranslate(translationResult) {
        if (typeof translationResult === 'string') {
            return translationResult;
        }
        if (translationResult && typeof translationResult === 'object' && translationResult.rendered) {
            return translationResult.rendered;
        }
        return String(translationResult || '');
    }

    componentDidMount() {
        // Check for attempt_id in URL query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const attemptIdFromUrl = urlParams.get('attempt_id');
        
        if (attemptIdFromUrl) {
            // Auto-load attempt from URL - don't load attempts list
            const attemptId = parseInt(attemptIdFromUrl, 10);
            if (attemptId && !isNaN(attemptId)) {
                this.handleViewAttempt(attemptId, true); // true = from URL, don't push state
            }
        } else {
            // Load initial data only if not viewing detail
            if (this.state.view === 'list') {
                this.loadAttempts();
            }
        }
        
        // Listen for browser back/forward navigation
        window.addEventListener('popstate', this.handlePopState);
    }

    componentDidUpdate(prevProps) {
        // Show error toast if storeError prop changes
        if (this.props.error && this.props.error !== prevProps.error && window.skillpulseToast) {
            window.skillpulseToast.error(this.props.error);
        }
    }
    
    componentWillUnmount() {
        // Clean up event listener
        window.removeEventListener('popstate', this.handlePopState);
    }
    
    handlePopState = (event) => {
        // Handle browser back/forward navigation
        const urlParams = new URLSearchParams(window.location.search);
        const attemptIdFromUrl = urlParams.get('attempt_id');
        
        if (attemptIdFromUrl) {
            const attemptId = parseInt(attemptIdFromUrl, 10);
            if (attemptId && !isNaN(attemptId)) {
                this.handleViewAttempt(attemptId, true);
            }
        } else {
            // No attempt_id in URL, show list
            this.setState({ 
                view: 'list', 
                selectedAttemptId: null 
            });
            this.loadAttempts();
        }
    }

    loadAttempts() {
        const { fetchAttempts } = this.props;
        const { filters, currentPage, perPage, sortBy, sortOrder } = this.state;
        
        const params = {
            ...filters,
            page: currentPage,
            per_page: perPage,
            order_by: sortBy,
            order: sortOrder.toUpperCase() // Ensure uppercase for REST API
        };
        
        // Remove empty filters
        Object.keys(params).forEach(key => {
            if (params[key] === '' || params[key] === null || params[key] === undefined) {
                delete params[key];
            }
        });
        
        fetchAttempts(params).catch(error => {
            const errorMessage = error.message || __('Failed to load quiz attempts', 'skillpulse-lms');
            if (window.skillpulseToast) {
                window.skillpulseToast.error(errorMessage);
            }
        });
    }

    handleViewAttempt(attemptId, fromUrl = false) {
        const { fetchAttempt } = this.props;
        this.setState({ 
            view: 'detail', 
            selectedAttemptId: attemptId 
        });
        
        // Update URL with query parameter (unless loading from URL)
        if (!fromUrl) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('attempt_id', attemptId);
            window.history.pushState({ attemptId }, '', currentUrl.toString());
        }
        
        fetchAttempt(attemptId).catch(error => {
            const errorMessage = error.message || __('Failed to load quiz attempt', 'skillpulse-lms');
            if (window.skillpulseToast) {
                window.skillpulseToast.error(errorMessage);
            }
            this.setState({ 
                view: 'list'
            });
            // Remove query param on error
            if (!fromUrl) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.delete('attempt_id');
                window.history.replaceState({}, '', currentUrl.toString());
            }
        });
    }

    handleBackToList() {
        this.setState({ 
            view: 'list', 
            selectedAttemptId: null 
        });
        
        // Remove query parameter from URL when going back
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete('attempt_id');
        window.history.pushState({}, '', currentUrl.toString());
        
        this.loadAttempts();
    }

    handleFeedbackUpdate = (feedback) => {
        // Update the attempt in the store if needed
        const { attempts } = this.props;
        const { selectedAttemptId } = this.state;
        if (selectedAttemptId) {
            const attempt = attempts.find(a => a.id === selectedAttemptId);
            if (attempt) {
                attempt.feedback = feedback;
            }
        }
    }

    handleAttemptUpdate = (updatedAttemptData) => {
        // Update the attempt in the store
        const { selectedAttemptId } = this.state;
        if (selectedAttemptId && this.props.dispatch) {
            const storeDispatch = this.props.dispatch('splms/quiz-attempts');
            storeDispatch.updateAttempt(selectedAttemptId, updatedAttemptData);
        }
    }

    handleFilterChange(key, value) {
        this.setState(prevState => ({
            filters: {
                ...prevState.filters,
                [key]: value
            }
        }));
    }

    handleApplyFilters() {
        this.setState({ 
            currentPage: 1
        }, () => {
            this.loadAttempts();
        });
    }

    handlePageChange(page) {
        this.setState({ currentPage: page }, () => {
            this.loadAttempts();
        });
    }

    handleSortChange(column, order) {
        this.setState({ 
            sortBy: column, 
            sortOrder: order,
            currentPage: 1
        }, () => {
            this.loadAttempts();
        });
    }

    handleVerifyAttempt(attemptId) {
        const { verifyAttempt } = this.props;
        verifyAttempt(attemptId).then(() => {
            if (window.skillpulseToast) {
                window.skillpulseToast.success(__('Attempt status updated successfully', 'skillpulse-lms'));
            }
            this.loadAttempts();
        }).catch(error => {
            const errorMessage = error.message || __('Failed to update attempt status', 'skillpulse-lms');
            if (window.skillpulseToast) {
                window.skillpulseToast.error(errorMessage);
            }
        });
    }

    handleDeleteAttempt(attemptId, skipConfirmation = false) {
        const { deleteAttemptAction } = this.props;
        const confirmed = skipConfirmation || window.confirm(
            __('Are you sure you want to delete this attempt? This action cannot be undone.', 'skillpulse-lms')
        );

        if (confirmed) {
            deleteAttemptAction(attemptId).then(() => {
                if (window.skillpulseToast) {
                    window.skillpulseToast.success(__('Attempt deleted successfully', 'skillpulse-lms'));
                }
                if (this.state.view === 'detail' && this.state.selectedAttemptId === attemptId) {
                    this.handleBackToList();
                } else {
                    this.loadAttempts();
                }
            }).catch(error => {
                const errorMessage = error.message || __('Failed to delete attempt', 'skillpulse-lms');
                if (window.skillpulseToast) {
                    window.skillpulseToast.error(errorMessage);
                }
            });
        }
    }


    render() {
        const {
            attempts = [],
            isLoading = false,
            error: storeError = null,
            pagination = null
        } = this.props;

        const {
            view,
            selectedAttemptId,
            filters
        } = this.state;

        // If showing detail view, render Detail component
        if (view === 'detail' && selectedAttemptId) {
            const attempt = attempts.find(a => a.id === selectedAttemptId);
            if (!attempt) {
                // Attempt not in store, try to get it
                return (
                    <div className="splms-container">
                        <Spinner />
                    </div>
                );
            }

            return (
                <Detail
                    attempt={attempt}
                    onBack={this.handleBackToList}
                    onVerify={this.handleVerifyAttempt}
                    onDelete={this.handleDeleteAttempt}
                    onFeedbackUpdate={this.handleFeedbackUpdate}
                    onAttemptUpdate={this.handleAttemptUpdate}
                />
            );
        }

        return (
            <div className="splms-container" role="main">
                <Fragment>
                    <header id="splms-admin-header" role="banner">
                        <div className="splms-header-logo">
                            <div className="logo inline">
                                <BrandLogo/>
                                <div className="splms-header-separator" />
                                <h1 className="splms-header-title inline">
                                    {this.safeTranslate(__("Quiz Attempts", "skillpulse-lms"))}
                                </h1>
                            </div>
                            <div className="splms-header-icon">
                                <div className="splms-header-icon-help">
                                    <Button
                                        className="splms-header-icon-help-button"
                                        onClick={() => {
                                            window.open("https://skillpulselms.com/docs", "_blank", "noopener,noreferrer");
                                        }}
                                        aria-label={this.safeTranslate(__("Get help with SkillPulse LMS", "skillpulse-lms"))}
                                    >
                                        <SplmsIcon name="help" size={20} />
                                        <span className="splms-header-icon-help-text">
                                            {this.safeTranslate(__("Help & Documentation", "skillpulse-lms"))}
                                        </span>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div className="splms-content">

                        {isLoading && attempts.length === 0 ? (
                            <div className="splms-loading-container">
                                <Spinner />
                                <p>{this.safeTranslate(__('Loading quiz attempts...', 'skillpulse-lms'))}</p>
                            </div>
                        ) : (
                            <List
                                attempts={attempts}
                                filters={filters}
                                isLoading={isLoading}
                                pagination={pagination}
                                onFilterChange={this.handleFilterChange}
                                onApplyFilters={this.handleApplyFilters}
                                onViewAttempt={this.handleViewAttempt}
                                onVerifyAttempt={this.handleVerifyAttempt}
                                onDeleteAttempt={this.handleDeleteAttempt}
                                onPageChange={this.handlePageChange}
                                sortBy={this.state.sortBy}
                                sortOrder={this.state.sortOrder}
                                onSortChange={this.handleSortChange}
                            />
                        )}
                    </div>
                </Fragment>
            </div>
        );
    }
}

const mapSelectToProps = (select) => ({
    attempts: select('splms/quiz-attempts').getAttempts(),
    isLoading: select('splms/quiz-attempts').isLoading(),
    error: select('splms/quiz-attempts').getError(),
    pagination: select('splms/quiz-attempts').getPagination()
});

const mapDispatchToProps = (dispatch) => ({
    fetchAttempts: (params) => dispatch('splms/quiz-attempts').fetchAttempts(params),
    fetchAttempt: (attemptId) => dispatch('splms/quiz-attempts').fetchAttempt(attemptId),
    verifyAttempt: (attemptId) => dispatch('splms/quiz-attempts').verifyAttempt(attemptId),
    deleteAttemptAction: (attemptId) => dispatch('splms/quiz-attempts').deleteAttemptAction(attemptId),
    dispatch: (storeKey) => dispatch(storeKey),
});

export default compose(
    withSelect(mapSelectToProps),
    withDispatch(mapDispatchToProps)
)(QuizAttemptsPage);

