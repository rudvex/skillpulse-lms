import { __ } from '@wordpress/i18n';
import { apiFetchBlob, apiFetchSafe } from '../../../utility/apiFetchSafe';
import { Button, Spinner, SelectControl, TextControl, Modal, Flex, FlexItem, RangeControl, TextareaControl } from '@wordpress/components';
import { withDispatch, withSelect } from "@wordpress/data";
import { compose } from '@wordpress/compose';
import { Component, Fragment } from '@wordpress/element';
import AdminHeader from "../../../components/AdminHeader";
import './styles/index.scss';
import { SplmsIcon } from "../../../components/SplmsIcon";
import { formatDate } from '../../../utility/helper';

// Import new components
import List from './List';
import { StatusBadge, MethodBadge, ProgressBar } from './components/EnrollmentBadges';
import Detail from './Detail';
import Edit from './Edit';

class EnrollmentsPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            searchTerm: '',
            selectedCourse: '',
            selectedStatus: '',
            selectedUser: '',
            selectedEnrollmentMethod: '',
            dateRangeStart: '',
            dateRangeEnd: '',
            selectedEnrollments: [],
            isModalOpen: false,
            modalType: '',
            currentEnrollment: null,
            bulkAction: '',
            perPage: 20,
            currentPage: 1,
            sortBy: 'enrolled_at',
            sortOrder: 'desc',
            isExporting: false,
            isSendingEmails: false,
            hasInitialLoad: false,
            // Certificate generation state
            certificateTemplates: [],
            selectedCertificateTemplate: '',
            isGeneratingCertificate: false,
            hasCertificate: false,
            certificateInfo: null,
            // Detail/Edit view state
            view: 'list',
            selectedEnrollmentId: null,
            selectedEnrollment: null,
            isLoadingEnrollment: false
        };
        this.handleViewEnrollment = this.handleViewEnrollment.bind(this);
        this.handleBackToList = this.handleBackToList.bind(this);
        this.handlePopState = this.handlePopState.bind(this);
    }

    componentDidMount() {
        // Clear any existing errors
        this.props.clearError();
        
        // Check for enrollment_id and mode in URL query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const enrollmentIdFromUrl = urlParams.get('enrollment_id');
        const modeFromUrl = urlParams.get('mode'); // 'view' or 'edit'
        
        if (enrollmentIdFromUrl) {
            // Auto-load enrollment from URL
            const enrollmentId = parseInt(enrollmentIdFromUrl, 10);
            if (enrollmentId && !isNaN(enrollmentId)) {
                const mode = modeFromUrl === 'edit' ? 'edit' : 'detail';
                this.handleViewEnrollment(enrollmentId, true, mode); // true = from URL, don't push state
            }
        } else {
            // Fetch initial data
            this.props.fetchEnrollments();
            this.props.fetchCourses();
            this.props.fetchUsers();
        }

        // Listen for browser back/forward navigation
        window.addEventListener('popstate', this.handlePopState);
    }

    componentDidUpdate(prevProps) {
        // Track when we've successfully loaded data
        if (prevProps.isLoading && !this.props.isLoading) {
            this.setState({ hasInitialLoad: true });
        }

        // Show error toast if error prop changes
        if (this.props.error && this.props.error !== prevProps.error && window.skillpulseToast) {
            window.skillpulseToast.error(this.props.error);
        }
    }

    componentWillUnmount() {
        window.removeEventListener('popstate', this.handlePopState);
    }

    handlePopState = (event) => {
        // Handle browser back/forward navigation
        const urlParams = new URLSearchParams(window.location.search);
        const enrollmentIdFromUrl = urlParams.get('enrollment_id');
        const modeFromUrl = urlParams.get('mode'); // 'view' or 'edit'
        
        if (enrollmentIdFromUrl) {
            const enrollmentId = parseInt(enrollmentIdFromUrl, 10);
            if (enrollmentId && !isNaN(enrollmentId)) {
                const mode = modeFromUrl === 'edit' ? 'edit' : 'detail';
                this.handleViewEnrollment(enrollmentId, true, mode);
            }
        } else {
            // No enrollment_id in URL, show list
            this.setState({ 
                view: 'list', 
                selectedEnrollmentId: null,
                selectedEnrollment: null
            });
            this.props.fetchEnrollments(this.getFilters());
        }
    }

    async handleViewEnrollment(enrollmentId, fromUrl = false, mode = 'detail') {
        // Find enrollment in current list
        const enrollment = this.props.enrollments.find(e => e.id === enrollmentId);
        
        if (enrollment) {
            // Enrollment is in the list, use it
            this.setState({
                view: mode,
                selectedEnrollmentId: enrollmentId,
                selectedEnrollment: enrollment
            });
            
            if (!fromUrl) {
                // Update URL without page reload
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('enrollment_id', enrollmentId.toString());
                if (mode === 'edit') {
                    currentUrl.searchParams.set('mode', 'edit');
                } else {
                    currentUrl.searchParams.delete('mode');
                }
                window.history.pushState({}, '', currentUrl.toString());
            }
        } else {
            // Enrollment not in list, fetch it
            this.setState({ isLoadingEnrollment: true });
            try {
                const response = await apiFetchSafe({
                    path: `/splms/v1/enrollments/${enrollmentId}`,
                    method: 'GET'
                });
                
                if (response && response.data) {
                    this.setState({
                        view: mode,
                        selectedEnrollmentId: enrollmentId,
                        selectedEnrollment: response.data,
                        isLoadingEnrollment: false
                    });
                    
                    if (!fromUrl) {
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('enrollment_id', enrollmentId.toString());
                        if (mode === 'edit') {
                            currentUrl.searchParams.set('mode', 'edit');
                        } else {
                            currentUrl.searchParams.delete('mode');
                        }
                        window.history.pushState({}, '', currentUrl.toString());
                    }
                } else {
                    throw new Error(__('Enrollment not found', 'skillpulse-lms'));
                }
            } catch (error) {
                console.error('Error loading enrollment:', error);
                if (window.skillpulseToast) {
                    window.skillpulseToast.error(error.message || __('Failed to load enrollment', 'skillpulse-lms'));
                }
                this.setState({ isLoadingEnrollment: false });
                this.handleBackToList();
            }
        }
    }

    handleBackToList() {
        this.setState({ 
            view: 'list', 
            selectedEnrollmentId: null,
            selectedEnrollment: null
        });
        
        // Update URL
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.delete('enrollment_id');
        currentUrl.searchParams.delete('mode');
        window.history.pushState({}, '', currentUrl.toString());
        
        // Refresh enrollments list
        this.props.fetchEnrollments(this.getFilters());
    }

    handleBackToDetail = () => {
        const { selectedEnrollmentId } = this.state;
        if (selectedEnrollmentId) {
            // Navigate back to detail view
            this.handleViewEnrollment(selectedEnrollmentId, false, 'detail');
        } else {
            // Fallback to list if no enrollment ID
            this.handleBackToList();
        }
    }

    handleEditEnrollment = (enrollment) => {
        if (enrollment && enrollment.id) {
            this.handleViewEnrollment(enrollment.id, false, 'edit');
        }
    }

    handleSaveEnrollment = async (enrollmentId, updateData) => {
        try {
            await this.props.updateEnrollmentData(enrollmentId, updateData);
            
            // Refresh the enrollments list
            await this.props.fetchEnrollments(this.getFilters());
            
            // Refresh the current enrollment data
            const response = await apiFetchSafe({
                path: `/splms/v1/enrollments/${enrollmentId}`,
                method: 'GET'
            });
            
            if (response && response.data) {
                this.setState({ 
                    selectedEnrollment: response.data
                });
            }
            
            if (window.skillpulseToast) {
                window.skillpulseToast.success(__('Enrollment updated successfully', 'skillpulse-lms'));
            }
            
            // Navigate back to detail view after save
            this.handleViewEnrollment(enrollmentId, false, 'detail');
        } catch (error) {
            console.error('Error updating enrollment:', error);
            throw error; // Let Edit component handle the error display
        }
    }

    getFilters() {
        const { searchTerm, selectedCourse, selectedStatus, selectedUser, selectedEnrollmentMethod, dateRangeStart, dateRangeEnd, sortBy, sortOrder, perPage, currentPage } = this.state;
        return {
            search: searchTerm,
            course_id: selectedCourse,
            status: selectedStatus,
            user_id: selectedUser,
            enrollment_method: selectedEnrollmentMethod,
            date_start: dateRangeStart,
            date_end: dateRangeEnd,
            sort_by: sortBy,
            sort_order: sortOrder,
            per_page: perPage,
            page: currentPage,
        };
    }

    handlePageChange(newPage) {
        this.setState({ currentPage: newPage }, () => {
            this.props.fetchEnrollments(this.getFilters());
        });
    }

    handleBulkAction() {
        const { bulkAction, selectedEnrollments } = this.state;
        if (!bulkAction || selectedEnrollments.length === 0) return;

        const actions = {
            'activate': () => this.props.bulkUpdateEnrollments(selectedEnrollments, { status: 'active' }),
            'complete': () => this.props.bulkUpdateEnrollments(selectedEnrollments, { status: 'completed' }),
            'suspend': () => this.props.bulkUpdateEnrollments(selectedEnrollments, { status: 'suspended' }),
            'cancel': () => this.props.bulkUpdateEnrollments(selectedEnrollments, { status: 'cancelled' }),
            'delete': () => this.props.bulkDeleteEnrollments(selectedEnrollments),
            'export': () => this.handleExportSelected(),
            'send_reminder': () => this.handleSendReminderEmails(),
        };

        if (actions[bulkAction]) {
            actions[bulkAction]();
            if (bulkAction !== 'export' && bulkAction !== 'send_reminder') {
                this.setState({ selectedEnrollments: [], bulkAction: '' });
            }
        }
    }

    async handleExportSelected() {
        const { selectedEnrollments } = this.state;
        if (selectedEnrollments.length === 0) return;

        this.setState({ isExporting: true });
        
        try {
            // Call export API using apiFetchBlob for blob response
            const blob = await apiFetchBlob('splms/v1/enrollments/export', {
                method: 'POST',
                data: { enrollment_ids: selectedEnrollments }
            });

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `enrollments_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            this.setState({ selectedEnrollments: [], bulkAction: '' });
            this.props.setError(''); // Clear any errors
        } catch (error) {
            console.error('Export error:', error);
            this.props.setError(error.message || __('Failed to export enrollments', 'skillpulse-lms'));
        } finally {
            this.setState({ isExporting: false });
        }
    }

    async handleSendReminderEmails() {
        const { selectedEnrollments } = this.state;
        if (selectedEnrollments.length === 0) return;

        this.setState({ isSendingEmails: true });
        
        try {
            // Call send reminder API using apiFetchSafe
            const result = await apiFetchSafe({
                path: '/splms/v1/enrollments/send-reminder',
                method: 'POST',
                data: { enrollment_ids: selectedEnrollments }
            });

            this.setState({ selectedEnrollments: [], bulkAction: '' });
            // Show success message
            this.props.setError(''); // Clear any errors
            if (window.skillpulseToast) {
                window.skillpulseToast.success(__('Reminder emails sent successfully', 'skillpulse-lms'));
            }
        } catch (error) {
            console.error('Send reminder error:', error);
            this.props.setError(error.message || __('Failed to send reminder emails', 'skillpulse-lms'));
        } finally {
            this.setState({ isSendingEmails: false });
        }
    }

    openModal(type, enrollment = null) {
        // Initialize edit form when opening edit modal
        if (type === 'edit' && enrollment) {
            this.setState({
                isModalOpen: true,
                modalType: type,
                currentEnrollment: enrollment,
                editForm: {
                    status: enrollment.status || '',
                    progress: enrollment.progress || 0,
                    completed_at: enrollment.completed_at || '',
                    notes: enrollment.notes || ''
                },
                formErrors: {}
            });
        } else if (type === 'generate_certificate' && enrollment) {
            // Initialize certificate generation modal
            this.setState({
                isModalOpen: true,
                modalType: type,
                currentEnrollment: enrollment,
                selectedCertificateTemplate: '',
                hasCertificate: false,
                certificateInfo: null
            });
            this.fetchCertificateTemplates(enrollment);
            this.checkExistingCertificate(enrollment);
        } else {
            this.setState({
                isModalOpen: true,
                modalType: type,
                currentEnrollment: enrollment,
            });
        }
    }

    closeModal() {
        this.setState({
            isModalOpen: false,
            modalType: '',
            currentEnrollment: null,
            editForm: {
                status: '',
                progress: 0,
                completed_at: '',
                notes: ''
            },
            formErrors: {},
            isSaving: false,
            certificateTemplates: [],
            selectedCertificateTemplate: '',
            isGeneratingCertificate: false,
            hasCertificate: false,
            certificateInfo: null
        });
    }

    handleFormChange(field, value) {
        this.setState(prevState => ({
            editForm: {
                ...prevState.editForm,
                [field]: value
            },
            formErrors: {
                ...prevState.formErrors,
                [field]: '' // Clear error for this field
            }
        }));
    }

    async fetchCertificateTemplates(enrollment) {
        try {
            // Fetch certificate templates
            const templatesResponse = await apiFetchSafe({
                path: '/splms/v1/certificate/templates?per_page=100',
                method: 'GET'
            });

            // API returns array directly
            const templates = Array.isArray(templatesResponse) ? templatesResponse : [];

            // Fetch course settings to get certificate template ID
            let courseTemplateId = '';
            try {
                const courseResponse = await apiFetchSafe({
                    path: `/splms/v1/courses/${enrollment.course_id}`,
                    method: 'GET'
                });
                if (courseResponse && courseResponse.meta) {
                    const completionSettings = courseResponse.meta.course_completion_settings || {};
                    courseTemplateId = completionSettings.certificate_template_id || '';
                }
            } catch (error) {
                console.error('Failed to fetch course settings:', error);
            }

            if (templates.length > 0) {
                // Use course certificate template if set, otherwise use first template
                this.setState({
                    certificateTemplates: templates,
                    selectedCertificateTemplate: courseTemplateId || templates[0].id.toString()
                });
            } else {
                this.setState({ certificateTemplates: [] });
            }
        } catch (error) {
            console.error('Failed to fetch certificate templates:', error);
            this.setState({ certificateTemplates: [] });
        }
    }

    async checkExistingCertificate(enrollment) {
        try {
            // Check if user has certificate for this course
            const response = await apiFetchSafe({
                path: `/splms/v1/certificate/user/${enrollment.user_id}?course_id=${enrollment.course_id}`,
                method: 'GET'
            });

            // API returns { user_id, certificates: [...] }
            const certificates = (response && response.certificates && Array.isArray(response.certificates)) 
                ? response.certificates 
                : [];

            if (certificates.length > 0) {
                this.setState({
                    hasCertificate: true,
                    certificateInfo: certificates[0]
                });
            }
        } catch (error) {
            console.error('Failed to check existing certificate:', error);
            // If error, assume no certificate exists
            this.setState({ hasCertificate: false });
        }
    }

    async handleGenerateCertificate() {
        const { currentEnrollment, selectedCertificateTemplate } = this.state;

        if (!currentEnrollment || !selectedCertificateTemplate) {
            if (window.skillpulseToast) {
                window.skillpulseToast.error(__('Please select a certificate template', 'skillpulse-lms'));
            }
            return;
        }

        this.setState({ isGeneratingCertificate: true });

        try {
            const response = await apiFetchSafe({
                path: `/splms/v1/certificate/${selectedCertificateTemplate}/generate`,
                method: 'POST',
                data: {
                    user_id: currentEnrollment.user_id,
                    course_id: currentEnrollment.course_id,
                    completion_date: currentEnrollment.completed_at || new Date().toISOString()
                }
            });

            if (response && response.success) {
                if (window.skillpulseToast) {
                    window.skillpulseToast.success(__('Certificate generated successfully', 'skillpulse-lms'));
                }
                this.closeModal();
                // Refresh enrollments to show updated data
                this.props.fetchEnrollments(this.getFilters());
            } else {
                throw new Error(response?.message || __('Failed to generate certificate', 'skillpulse-lms'));
            }
        } catch (error) {
            console.error('Certificate generation error:', error);
            const errorMessage = error.message || __('Failed to generate certificate', 'skillpulse-lms');
            if (window.skillpulseToast) {
                window.skillpulseToast.error(errorMessage);
            }
            this.props.setError(errorMessage);
        } finally {
            this.setState({ isGeneratingCertificate: false });
        }
    }

    renderModal() {
        const { isModalOpen, modalType, currentEnrollment } = this.state;

        if (!isModalOpen) return null;

        return (
            <Modal
                title={
                    modalType === 'view' ? __('Enrollment Details', 'skillpulse-lms') :
                    modalType === 'edit' ? __('Edit Enrollment', 'skillpulse-lms') :
                    modalType === 'generate_certificate' ? __('Generate Certificate', 'skillpulse-lms') :
                    __('Delete Enrollment', 'skillpulse-lms')
                }
                onRequestClose={() => this.closeModal()}
                className="enrollment-modal"
            >
                {modalType === 'delete' && this.renderDeleteModal(currentEnrollment)}
                {modalType === 'generate_certificate' && this.renderGenerateCertificateModal(currentEnrollment)}
            </Modal>
        );
    }

    renderDeleteModal(enrollment) {
        const { editForm, formErrors, isSaving } = this.state;
        
        if (!enrollment) return null;

        return (
            <div className="enrollment-edit">
                {/* Student Info (Read-only) */}
                <div className="edit-section">
                    <h4>{__('Student Information', 'skillpulse-lms')}</h4>
                    <div className="student-summary">
                        <img 
                            src={enrollment.user_avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(enrollment.user_name || 'Student')}&background=7e75ff&color=fff&size=40`} 
                            alt={enrollment.user_name || 'Student'}
                            onError={(e) => {
                                e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(enrollment.user_name || 'Student')}&background=7e75ff&color=fff&size=40`;
                            }}
                        />
                        <div>
                            <strong>{enrollment.user_name || __('Unknown Student', 'skillpulse-lms')}</strong>
                            <br />
                            <small>{enrollment.course_title || __('Unknown Course', 'skillpulse-lms')}</small>
                        </div>
                    </div>
                </div>

                {/* Learning Progress Details (Read-only) */}
                <div className="edit-section">
                    <h4>{__('Learning Progress', 'skillpulse-lms')}</h4>
                    
                    {/* Overall Progress Display */}
                    <div className="progress-overview">
                        <div className="progress-main">
                            <div className="progress-label">
                                <strong>{__('Overall Progress', 'skillpulse-lms')}</strong>
                                <span className="progress-percentage">{Math.round(enrollment.progress || 0)}%</span>
                            </div>
                            <ProgressBar progress={enrollment.progress} />
                        </div>
                        <div className="progress-note">
                            <SplmsIcon mode="wp" icon="info" size={16} />
                            <span>{__('Progress is automatically calculated based on completed course activities', 'skillpulse-lms')}</span>
                        </div>
                    </div>

                    {/* Progress Breakdown */}
                    <div className="progress-breakdown">
                        <h5>{__('Progress Breakdown', 'skillpulse-lms')}</h5>
                        <div className="progress-items">
                            <div className="progress-item">
                                <div className="progress-item-info">
                                    <SplmsIcon mode="wp" icon="editor-ol" size={16} />
                                    <span>{__('Lessons Completed', 'skillpulse-lms')}</span>
                                </div>
                                <div className="progress-item-stats">
                                    <span className="completed-count">{enrollment.lessons_completed || 0}</span>
                                    <span className="total-count">/ {enrollment.total_lessons || 0}</span>
                                    <span className="item-percentage">
                                        ({enrollment.total_lessons ? Math.round((enrollment.lessons_completed / enrollment.total_lessons) * 100) : 0}%)
                                    </span>
                                </div>
                            </div>
                            
                            <div className="progress-item">
                                <div className="progress-item-info">
                                    <SplmsIcon mode="wp" icon="forms" size={16} />
                                    <span>{__('Assignments Submitted', 'skillpulse-lms')}</span>
                                </div>
                                <div className="progress-item-stats">
                                    <span className="completed-count">{enrollment.assignments_completed || 0}</span>
                                    <span className="total-count">/ {enrollment.total_assignments || 0}</span>
                                    <span className="item-percentage">
                                        ({enrollment.total_assignments ? Math.round((enrollment.assignments_completed / enrollment.total_assignments) * 100) : 0}%)
                                    </span>
                                </div>
                            </div>
                            
                            <div className="progress-item">
                                <div className="progress-item-info">
                                    <SplmsIcon mode="wp" icon="yes" size={16} />
                                    <span>{__('Quizzes Passed', 'skillpulse-lms')}</span>
                                </div>
                                <div className="progress-item-stats">
                                    <span className="completed-count">{enrollment.quizzes_passed || 0}</span>
                                    <span className="total-count">/ {enrollment.total_quizzes || 0}</span>
                                    <span className="item-percentage">
                                        ({enrollment.total_quizzes ? Math.round((enrollment.quizzes_passed / enrollment.total_quizzes) * 100) : 0}%)
                                    </span>
                                </div>
                            </div>
                            
                            {enrollment.last_activity && (
                                <div className="progress-item">
                                    <div className="progress-item-info">
                                        <SplmsIcon mode="wp" icon="clock" size={16} />
                                        <span>{__('Last Activity', 'skillpulse-lms')}</span>
                                    </div>
                                    <div className="progress-item-stats">
                                        <span className="last-activity-date">{formatDate(enrollment.last_activity)}</span>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Editable Administrative Fields */}
                <div className="edit-section">
                    <h4>{__('Administrative Settings', 'skillpulse-lms')}</h4>
                    
                    {/* Status Field */}
                    <div className="form-field">
                        <SelectControl
                            label={__('Enrollment Status', 'skillpulse-lms')}
                            value={editForm.status}
                            onChange={(value) => this.handleFormChange('status', value)}
                            options={[
                                { label: __('Select Status', 'skillpulse-lms'), value: '' },
                                { label: __('Active', 'skillpulse-lms'), value: 'active' },
                                { label: __('Completed', 'skillpulse-lms'), value: 'completed' },
                                { label: __('Suspended', 'skillpulse-lms'), value: 'suspended' },
                                { label: __('Cancelled', 'skillpulse-lms'), value: 'cancelled' }
                            ]}
                            help={formErrors.status ? formErrors.status : __('Select the enrollment status', 'skillpulse-lms')}
                        />
                        {formErrors.status && (
                            <div className="form-error">{formErrors.status}</div>
                        )}
                    </div>

                    {/* Manual Progress Override (Advanced) */}
                    <div className="form-field advanced-field">
                        <details>
                            <summary className="progress-override-toggle">
                                <SplmsIcon mode="wp" icon="warning" size={16} />
                                {__('Advanced: Manual Progress Override', 'skillpulse-lms')}
                            </summary>
                            <div className="progress-override-content">
                                <div className="override-warning">
                                    <div className="splms-notice-warning" style={{ padding: '10px', background: '#fff3cd', border: '1px solid #ffc107', borderRadius: '4px', marginBottom: '15px' }}>
                                        <strong>{__('Warning:', 'skillpulse-lms')}</strong> {__('Manual progress override should only be used in special circumstances (e.g., granting credit for prior learning, resolving technical issues). This will override the automatically calculated progress.', 'skillpulse-lms')}
                                    </div>
                                </div>
                                
                                <Flex>
                                    <FlexItem>
                                        <RangeControl
                                            label={__('Override Progress (%)', 'skillpulse-lms')}
                                            value={editForm.progress}
                                            onChange={(value) => this.handleFormChange('progress', value)}
                                            min={0}
                                            max={100}
                                            step={1}
                                            help={formErrors.progress ? formErrors.progress : __('Override the automatically calculated progress', 'skillpulse-lms')}
                                        />
                                    </FlexItem>
                                    <FlexItem>
                                        <TextControl
                                            label={__('Exact Progress', 'skillpulse-lms')}
                                            value={editForm.progress}
                                            onChange={(value) => this.handleFormChange('progress', Math.max(0, Math.min(100, parseFloat(value) || 0)))}
                                            type="number"
                                            min={0}
                                            max={100}
                                            step={0.1}
                                        />
                                    </FlexItem>
                                </Flex>
                                {formErrors.progress && (
                                    <div className="form-error">{formErrors.progress}</div>
                                )}
                            </div>
                        </details>
                    </div>

                    {/* Completion Date Field (only show when status is completed) */}
                    {editForm.status === 'completed' && (
                        <div className="form-field">
                            <TextControl
                                label={__('Completion Date', 'skillpulse-lms')}
                                value={editForm.completed_at}
                                onChange={(value) => this.handleFormChange('completed_at', value)}
                                type="date"
                                help={formErrors.completed_at ? formErrors.completed_at : __('Date when the course was completed', 'skillpulse-lms')}
                            />
                            {formErrors.completed_at && (
                                <div className="form-error">{formErrors.completed_at}</div>
                            )}
                        </div>
                    )}

                    {/* Notes Field */}
                    <div className="form-field">
                        <TextareaControl
                            label={__('Administrative Notes', 'skillpulse-lms')}
                            value={editForm.notes}
                            onChange={(value) => this.handleFormChange('notes', value)}
                            rows={3}
                            help={__('Optional notes about this enrollment (visible to administrators only)', 'skillpulse-lms')}
                        />
                    </div>
                </div>

                {/* Action Buttons */}
                <div className="edit-actions">
                    <Flex justify="flex-end">
                        <FlexItem>
                            <Button 
                                isSecondary 
                                onClick={() => this.closeModal()}
                                disabled={isSaving}
                            >
                                {__('Cancel', 'skillpulse-lms')}
                            </Button>
                        </FlexItem>
                        <FlexItem>
                            <Button 
                                isPrimary 
                                onClick={() => this.handleSaveEnrollment()}
                                disabled={isSaving}
                                isBusy={isSaving}
                            >
                                {isSaving ? __('Saving...', 'skillpulse-lms') : __('Save Changes', 'skillpulse-lms')}
                            </Button>
                        </FlexItem>
                    </Flex>
                </div>
            </div>
        );
    }

    renderDeleteModal(enrollment) {
        return (
            <div className="enrollment-delete">
                <p>
                    {__('Are you sure you want to delete this enrollment?', 'skillpulse-lms')}
                </p>
                <p>
                    <strong>{enrollment.user_name}</strong> - {enrollment.course_title}
                </p>
                <p>
                    <em>{__('This action cannot be undone.', 'skillpulse-lms')}</em>
                </p>
                <Flex justify="flex-end">
                    <FlexItem>
                        <Button onClick={() => this.closeModal()}>
                            {__('Cancel', 'skillpulse-lms')}
                        </Button>
                    </FlexItem>
                    <FlexItem>
                        <Button
                            isPrimary
                            isDestructive
                            onClick={() => {
                                this.props.deleteEnrollmentData(enrollment.id);
                                this.closeModal();
                            }}
                        >
                            {__('Delete', 'skillpulse-lms')}
                        </Button>
                    </FlexItem>
                </Flex>
            </div>
        );
    }

    renderGenerateCertificateModal(enrollment) {
        const { certificateTemplates, selectedCertificateTemplate, isGeneratingCertificate, hasCertificate, certificateInfo } = this.state;

        if (!enrollment) return null;

        return (
            <div className="certificate-generation-modal">
                {hasCertificate && certificateInfo ? (
                    <div className="certificate-exists-notice" style={{ 
                        padding: '12px', 
                        backgroundColor: '#fff3cd', 
                        border: '1px solid #ffc107', 
                        borderRadius: '4px',
                        marginBottom: '16px'
                    }}>
                        <p>
                            <strong>{__('Certificate Already Exists', 'skillpulse-lms')}</strong>
                        </p>
                        <p>
                            {__('This user already has a certificate for this course.', 'skillpulse-lms')}
                        </p>
                        {certificateInfo.certificate_url && (
                            <p>
                                <a 
                                    href={certificateInfo.certificate_url} 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                >
                                    {__('View Certificate', 'skillpulse-lms')}
                                </a>
                            </p>
                        )}
                    </div>
                ) : (
                    <>
                        <div className="certificate-info" style={{ marginBottom: '20px' }}>
                            <p>
                                <strong>{__('Student:', 'skillpulse-lms')}</strong> {enrollment.user_name}
                            </p>
                            <p>
                                <strong>{__('Course:', 'skillpulse-lms')}</strong> {enrollment.course_title}
                            </p>
                        </div>

                        {certificateTemplates.length > 0 ? (
                            <SelectControl
                                label={__('Certificate Template', 'skillpulse-lms')}
                                value={selectedCertificateTemplate}
                                options={[
                                    { label: __('Select a template...', 'skillpulse-lms'), value: '' },
                                    ...certificateTemplates.map(template => ({
                                        label: template.title?.rendered || template.title || `Template #${template.id}`,
                                        value: template.id.toString()
                                    }))
                                ]}
                                onChange={(value) => this.setState({ selectedCertificateTemplate: value })}
                                disabled={isGeneratingCertificate}
                            />
                        ) : (
                            <div style={{ padding: '12px', backgroundColor: '#f0f0f1', borderRadius: '4px', marginBottom: '16px' }}>
                                <p>{__('Loading certificate templates...', 'skillpulse-lms')}</p>
                            </div>
                        )}

                        <Flex justify="flex-end" style={{ marginTop: '20px' }}>
                            <FlexItem>
                                <Button 
                                    onClick={() => this.closeModal()}
                                    disabled={isGeneratingCertificate}
                                >
                                    {__('Cancel', 'skillpulse-lms')}
                                </Button>
                            </FlexItem>
                            <FlexItem>
                                <Button
                                    isPrimary
                                    onClick={() => this.handleGenerateCertificate()}
                                    disabled={isGeneratingCertificate || !selectedCertificateTemplate || hasCertificate}
                                    isBusy={isGeneratingCertificate}
                                >
                                    {isGeneratingCertificate 
                                        ? __('Generating...', 'skillpulse-lms') 
                                        : __('Generate Certificate', 'skillpulse-lms')
                                    }
                                </Button>
                            </FlexItem>
                        </Flex>
                    </>
                )}
            </div>
        );
    }

    render() {
        const { enrollments, isLoading, error } = this.props;
        const { view, selectedEnrollment, isLoadingEnrollment } = this.state;

        // If showing detail view, render Detail component
        if (view === 'detail' && selectedEnrollment) {
            return (
                <Detail
                    enrollment={selectedEnrollment}
                    onBack={this.handleBackToList}
                    onEdit={this.handleEditEnrollment}
                    onDelete={(enrollment) => this.openModal('delete', enrollment)}
                    onGenerateCertificate={(enrollment) => this.openModal('generate_certificate', enrollment)}
                />
            );
        }

        // If showing edit view, render Edit component
        if (view === 'edit' && selectedEnrollment) {
            return (
                <Edit
                    enrollment={selectedEnrollment}
                    onBack={this.handleBackToDetail}
                    onSave={this.handleSaveEnrollment}
                />
            );
        }

        // Show loading if fetching enrollment from API
        if (isLoadingEnrollment) {
            return (
                <div className="splms-container">
                    <div style={{ textAlign: 'center', padding: '40px' }}>
                        <Spinner />
                        <p>{__('Loading enrollment details...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        // Don't show error notices during initial load
        const shouldShowError = error && (hasInitialLoad || !isLoading);

        return (
            <div className="splms-container" role="main">

                <Fragment>
                    <AdminHeader
                        title={__("Course Enrollments", "skillpulse-lms")}
                        helpType={null}
                    />

                    <div className="splms-content">
                        <List
                            enrollments={enrollments}
                            courses={this.props.courses}
                            users={this.props.users}
                            isLoading={isLoading}
                            pagination={{
                                currentPage: this.state.currentPage,
                                perPage: this.state.perPage,
                                total: this.props.totalEnrollments,
                            }}
                            onView={(enrollment) => this.handleViewEnrollment(enrollment.id)}
                            onDelete={(enrollment) => this.openModal('delete', enrollment)}
                            onGenerateCertificate={(enrollment) => this.openModal('generate_certificate', enrollment)}
                            onBulkAction={(action, selectedIds) => {
                                this.setState({
                                    bulkAction: action,
                                    selectedEnrollments: selectedIds
                                }, () => {
                                    this.handleBulkAction();
                                });
                            }}
                            onFilterChange={(filters) => {
                                this.setState({
                                    searchTerm: filters.search || '',
                                    selectedCourse: filters.course || '',
                                    selectedStatus: filters.status || '',
                                    selectedUser: filters.user || '',
                                    selectedEnrollmentMethod: filters.method || '',
                                    currentPage: 1,
                                }, () => {
                                    this.props.fetchEnrollments(this.getFilters());
                                });
                            }}
                            onSortChange={(sortBy, sortOrder) => {
                                this.setState({ sortBy, sortOrder, currentPage: 1 }, () => {
                                    this.props.fetchEnrollments(this.getFilters());
                                });
                            }}
                            onPageChange={(page) => this.handlePageChange(page)}
                            onPerPageChange={(perPage) => {
                                this.setState({ perPage, currentPage: 1 }, () => {
                                    this.props.fetchEnrollments(this.getFilters());
                                });
                            }}
                        />
                    </div>
                </Fragment>

                {this.renderModal()}
            </div>
        );
    }
}

export default compose([
    withDispatch((dispatch) => {
        const { 
            fetchEnrollments, 
            fetchCourses, 
            fetchUsers,
            createEnrollment,
            updateEnrollmentData,
            deleteEnrollmentData,
            bulkUpdateEnrollments,
            bulkDeleteEnrollments,
            setError,
            clearError 
        } = dispatch('splms/enrollments');
        
        return {
            fetchEnrollments,
            fetchCourses,
            fetchUsers,
            createEnrollment,
            updateEnrollmentData,
            deleteEnrollmentData,
            bulkUpdateEnrollments,
            bulkDeleteEnrollments,
            setError,
            clearError
        };
    }),
    withSelect((select) => {
        const { 
            getEnrollments, 
            getCourses, 
            getUsers,
            getTotalEnrollments,
            getLoading, 
            getError 
        } = select("splms/enrollments");
        
        return {
            enrollments: getEnrollments(),
            courses: getCourses(),
            users: getUsers(),
            totalEnrollments: getTotalEnrollments(),
            isLoading: getLoading(),
            error: getError(),
        };
    }),
])(EnrollmentsPage); 