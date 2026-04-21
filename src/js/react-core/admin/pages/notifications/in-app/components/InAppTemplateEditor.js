import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { 
    Card, 
    CardHeader, 
    CardBody, 
    Button
} from '@wordpress/components';
import { SplmsIcon } from '../../../../../components/SplmsIcon';
import Field from '../../../../../components/Field';

class InAppTemplateEditor extends Component {
    constructor(props) {
        super(props);
        this.state = {
            formData: {
                title: '',
                message: '',
                type: 'info',
                is_enabled: true
            },
            hasChanges: false,
            copiedPlaceholder: null
        };
    }

    componentDidMount() {
        this.loadTemplateData();
    }

    componentDidUpdate(prevProps) {
        if (prevProps.template !== this.props.template) {
            this.loadTemplateData();
        }
    }

    loadTemplateData = () => {
        const { template } = this.props;
        if (template) {
            this.setState({
                formData: {
                    title: template.title || '',
                    message: template.message || '',
                    type: template.type || 'info',
                    is_enabled: template.is_enabled !== false
                },
                hasChanges: false
            });
        }
    }

    handleFieldChange = (field, value) => {
        this.setState(prevState => ({
            formData: {
                ...prevState.formData,
                [field]: value
            },
            hasChanges: true
        }));
    }

    handleSave = () => {
        const { formData } = this.state;
        const { templateKey, onSave } = this.props;
        
        onSave({
            event_key: templateKey,
            ...formData
        });
        
        this.setState({ hasChanges: false });
    }

    handlePlaceholderClick = (placeholder) => {
        const placeholderText = `{${placeholder}}`;
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(placeholderText).then(() => {
                this.showPlaceholderCopied(placeholder);
            }).catch(() => {
                this.fallbackCopyToClipboard(placeholderText);
            });
        } else {
            this.fallbackCopyToClipboard(placeholderText);
        }
    }

    fallbackCopyToClipboard = (text) => {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            this.showPlaceholderCopied(text.replace(/[{}]/g, ''));
        } catch (err) {
            console.error('Failed to copy placeholder:', err);
        }
        
        document.body.removeChild(textArea);
    }

    showPlaceholderCopied = (placeholder) => {
        this.setState({ copiedPlaceholder: placeholder });
        setTimeout(() => {
            this.setState({ copiedPlaceholder: null });
        }, 2000);
    }

    getPlaceholders = () => {
        const { template } = this.props;
        if (!template || !template.event_key) {
            return {};
        }

        // Common placeholders available for all events
        const commonPlaceholders = {
            'site_name': __('Site name', 'skillpulse-lms'),
            'user_name': __('User name', 'skillpulse-lms'),
        };

        // Event-specific placeholders
        const eventPlaceholders = {
            'course_enrollment': {
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'course_completion': {
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'lesson_completion': {
                'lesson_title': __('Lesson title', 'skillpulse-lms'),
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'quiz_completion': {
                'quiz_title': __('Quiz title', 'skillpulse-lms'),
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'quiz_passed': {
                'quiz_title': __('Quiz title', 'skillpulse-lms'),
                'score': __('Score percentage', 'skillpulse-lms'),
            },
            'quiz_failed': {
                'quiz_title': __('Quiz title', 'skillpulse-lms'),
                'score': __('Score percentage', 'skillpulse-lms'),
                'passing_score': __('Passing score percentage', 'skillpulse-lms'),
            },
            'certificate_awarded': {
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'review_reply': {
                'author_name': __('Author name', 'skillpulse-lms'),
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'review_new': {
                'reviewer_name': __('Reviewer name', 'skillpulse-lms'),
                'rating': __('Rating (stars)', 'skillpulse-lms'),
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'review_moderation': {
                'course_title': __('Course title', 'skillpulse-lms'),
            },
            'review_moderation_result': {
                'course_title': __('Course title', 'skillpulse-lms'),
                'moderation_status': __('Moderation status (approved/rejected)', 'skillpulse-lms'),
            },
        };

        const eventKey = template.event_key;
        const specificPlaceholders = eventPlaceholders[eventKey] || {};

        return { ...commonPlaceholders, ...specificPlaceholders };
    }

    getEventDisplayName = () => {
        const { template } = this.props;
        if (!template) return '';

        if (template.event_name) {
            return template.event_name;
        }

        const eventNames = {
            'course_enrollment': __('Course Enrollment', 'skillpulse-lms'),
            'course_completion': __('Course Completion', 'skillpulse-lms'),
            'lesson_completion': __('Lesson Completion', 'skillpulse-lms'),
            'quiz_completion': __('Quiz Completion', 'skillpulse-lms'),
            'quiz_passed': __('Quiz Passed', 'skillpulse-lms'),
            'quiz_failed': __('Quiz Failed', 'skillpulse-lms'),
            'certificate_awarded': __('Certificate Awarded', 'skillpulse-lms'),
            'signup_created': __('Signup Created', 'skillpulse-lms'),
            'signup_activated': __('Signup Activated', 'skillpulse-lms'),
            'review_reply': __('Review Reply', 'skillpulse-lms'),
            'review_new': __('New Review', 'skillpulse-lms'),
            'review_moderation': __('Review Moderation', 'skillpulse-lms'),
            'review_moderation_result': __('Review Moderation Result', 'skillpulse-lms'),
        };

        return eventNames[template.event_key] || template.event_key;
    }

    render() {
        const { template, isSubmitting } = this.props;
        const { formData, hasChanges } = this.state;
        
        if (!template) {
            return (
                <Card className="splms-card">
                    <CardBody>
                        <p>{__('No template selected', 'skillpulse-lms')}</p>
                    </CardBody>
                </Card>
            );
        }

        const placeholders = this.getPlaceholders();
        const eventDisplayName = this.getEventDisplayName();

        return (
            <div className="splms-template-editor">
                <Card className="splms-card">
                    <CardHeader>
                        <div className="splms-template-editor-header">
                            <div className="splms-template-editor-title">
                                <h3>{eventDisplayName}</h3>
                                <p>{__('Edit in-app notification template content and settings', 'skillpulse-lms')}</p>
                            </div>
                            <div className="splms-template-editor-actions">
                                <Button
                                    isPrimary
                                    onClick={this.handleSave}
                                    isBusy={isSubmitting}
                                    disabled={isSubmitting || !hasChanges}
                                >
                                    {isSubmitting ? __('Saving...', 'skillpulse-lms') : __('Save Template', 'skillpulse-lms')}
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardBody>
                        <div className="splms-template-form">
                            {/* Template Status */}
                            <div className="splms-form-section">
                                <div className="enhanced-field splms-field splms-field-type-toggle full-width">
                                    <Field
                                        id="is_enabled"
                                        type="toggle"
                                        label={__('Enable this template', 'skillpulse-lms')}
                                        value={formData.is_enabled}
                                        onChange={(value) => this.handleFieldChange('is_enabled', value)}
                                        help={__('When enabled, this template will be used for in-app notifications', 'skillpulse-lms')}
                                    />
                                </div>
                            </div>

                            {/* Notification Type */}
                            <div className="splms-form-section">
                                <div className="enhanced-field splms-field splms-field-type-select full-width">
                                    <Field
                                        id="type"
                                        type="select"
                                        label={__('Notification Type', 'skillpulse-lms')}
                                        value={formData.type}
                                        onChange={(value) => this.handleFieldChange('type', value)}
                                        options={[
                                            { label: __('Info', 'skillpulse-lms'), value: 'info' },
                                            { label: __('Success', 'skillpulse-lms'), value: 'success' },
                                            { label: __('Warning', 'skillpulse-lms'), value: 'warning' },
                                            { label: __('Error', 'skillpulse-lms'), value: 'error' },
                                        ]}
                                        help={__('The visual style of the notification (info, success, warning, error)', 'skillpulse-lms')}
                                    />
                                </div>
                            </div>

                            {/* Notification Title */}
                            <div className="splms-form-section">
                                <div className="enhanced-field splms-field splms-field-type-text full-width">
                                    <Field
                                        id="title"
                                        type="text"
                                        label={__('Notification Title', 'skillpulse-lms')}
                                        value={formData.title}
                                        onChange={(value) => this.handleFieldChange('title', value)}
                                        placeholder={__('Enter notification title...', 'skillpulse-lms')}
                                        help={__('The title that will be displayed in the notification. You can use placeholders like {course_title}, {site_name}, etc.', 'skillpulse-lms')}
                                    />
                                </div>
                            </div>

                            {/* Notification Message */}
                            <div className="splms-form-section">
                                <div className="enhanced-field splms-field splms-field-type-textarea full-width">
                                    <Field
                                        id="message"
                                        type="textarea"
                                        label={__('Notification Message', 'skillpulse-lms')}
                                        value={formData.message}
                                        onChange={(value) => this.handleFieldChange('message', value)}
                                        placeholder={__('Enter notification message...', 'skillpulse-lms')}
                                        rows={8}
                                        help={__('The message content that will be displayed in the notification. You can use HTML formatting and placeholders.', 'skillpulse-lms')}
                                    />
                                </div>
                            </div>

                            {/* Available Placeholders */}
                            {Object.keys(placeholders).length > 0 && (
                                <div className="splms-form-section">
                                    <div className="splms-placeholders-help">
                                        <h4>{__('Available Placeholders', 'skillpulse-lms')}</h4>
                                        <p>
                                            {__('You can use these placeholders in your notification title and message. They will be automatically replaced with actual values when the notification is sent.', 'skillpulse-lms')}
                                        </p>
                                        <ul className="splms-placeholders-list">
                                            {Object.entries(placeholders).map(([key, description]) => (
                                                <li 
                                                    key={key} 
                                                    onClick={() => this.handlePlaceholderClick(key)}
                                                    title={__('Click to copy to clipboard', 'skillpulse-lms')}
                                                >
                                                    <code>{`{${key}}`}</code>
                                                    {description}
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                </div>
                            )}
                        </div>
                    </CardBody>
                </Card>
            </div>
        );
    }
}

export default InAppTemplateEditor;

