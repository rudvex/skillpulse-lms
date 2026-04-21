import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { 
    Card, 
    CardHeader, 
    CardBody, 
    Button,
    DropdownMenu,
    MenuGroup,
    MenuItem,
    Modal
} from '@wordpress/components';
import { SplmsIcon } from '../../../../../../components/SplmsIcon';
import RichTextEditor from './RichTextEditor';
import Field from '../../../../../../components/Field';

class TemplateEditor extends Component {
    constructor(props) {
        super(props);
        this.state = {
            formData: {
                subject: '',
                content: '',
                is_active: true
            },
            isEditing: false,
            hasChanges: false,
            copiedPlaceholder: null,
            showPreviewModal: false
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
                    subject: template.subject || '',
                    content: template.content || '',
                    is_active: template.is_active !== false
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
            template_key: templateKey,
            ...formData
        });
        
        this.setState({ hasChanges: false, isSubmitting: true });
    }

    handleTest = () => {
        const { formData } = this.state;
        const { templateKey, onTest } = this.props;
        
        onTest({
            template_key: templateKey,
            ...formData
        });
    }

    handleReset = () => {
        const { templateKey, onReset } = this.props;
        
        if (window.confirm(__('Are you sure you want to reset this template to default? This action cannot be undone.', 'skillpulse-lms'))) {
            onReset(templateKey);
        }
    }

    handlePlaceholderClick = (placeholder) => {
        const placeholderText = `{${placeholder}}`;
        
        // Copy to clipboard
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(placeholderText).then(() => {
                // Show temporary success message
                this.showPlaceholderCopied(placeholder);
            }).catch(() => {
                // For older browsers
                this.fallbackCopyToClipboard(placeholderText);
            });
        } else {
            // For older browsers
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
        // Set a temporary state to show "copied" message
        this.setState({ copiedPlaceholder: placeholder });
        
        // Clear the message after 2 seconds
        setTimeout(() => {
            this.setState({ copiedPlaceholder: null });
        }, 2000);
    }

    handleShowPreview = () => {
        this.setState({ showPreviewModal: true });
    }

    handleClosePreview = () => {
        this.setState({ showPreviewModal: false });
    }

    render() {
        const { template, isSubmitting, isTesting } = this.props;
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

        return (
            <div className="splms-template-editor">
                <Card className="splms-card">
                    <CardHeader>
                        <div className="splms-template-editor-header">
                            <div className="splms-template-editor-title">
                                <h3>{template.name}</h3>
                                <p>{__('Edit email template content and settings', 'skillpulse-lms')}</p>
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
                                
                                <DropdownMenu
                                    icon="ellipsis"
                                    label={__('Template Actions', 'skillpulse-lms')}
                                    className="splms-template-actions-dropdown"
                                >
                                    {({ onClose }) => (
                                        <MenuGroup>
                                            <MenuItem
                                                icon="visibility"
                                                onClick={() => {
                                                    this.handleShowPreview();
                                                    onClose();
                                                }}
                                            >
                                                {__('Show Preview', 'skillpulse-lms')}
                                            </MenuItem>
                                            <MenuItem
                                                icon="email"
                                                onClick={() => {
                                                    this.handleTest();
                                                    onClose();
                                                }}
                                                disabled={isTesting}
                                            >
                                                {isTesting ? __('Sending...', 'skillpulse-lms') : __('Send Test', 'skillpulse-lms')}
                                            </MenuItem>
                                            <MenuItem
                                                icon="update"
                                                onClick={() => {
                                                    this.handleReset();
                                                    onClose();
                                                }}
                                                className="splms-destructive-action"
                                            >
                                                {__('Reset to Default', 'skillpulse-lms')}
                                            </MenuItem>
                                        </MenuGroup>
                                    )}
                                </DropdownMenu>
                            </div>
                        </div>
                    </CardHeader>
                    <CardBody>
                        <div className="splms-template-form">
                            {/* Template Status */}
                            <div className="splms-form-section">
                                <div className="enhanced-field splms-field splms-field-type-toggle full-width">
                                    <Field
                                        id="is_active"
                                        type="toggle"
                                        label={__('Enable this template', 'skillpulse-lms')}
                                        value={formData.is_active}
                                        onChange={(value) => this.handleFieldChange('is_active', value)}
                                        help={__('When enabled, this template will be used for sending emails', 'skillpulse-lms')}
                                    />
                                </div>
                            </div>

                            {/* Email Subject */}
                            <div className="splms-form-section">
                                <div className="enhanced-field splms-field splms-field-type-text full-width">
                                    <Field
                                        id="subject"
                                        type="text"
                                        label={__('Email Subject', 'skillpulse-lms')}
                                        value={formData.subject}
                                        onChange={(value) => this.handleFieldChange('subject', value)}
                                        placeholder={__('Enter email subject...', 'skillpulse-lms')}
                                        help={template.placeholders && Object.keys(template.placeholders).length > 0 
                                            ? __('The subject line of the email. You can use placeholders like {user_name}, {site_name}, etc.', 'skillpulse-lms')
                                            : __('The subject line of the email', 'skillpulse-lms')
                                        }
                                    />
                                </div>
                            </div>

                            {/* Email Content */}
                            <div className="splms-form-section">
                                <RichTextEditor
                                    label={__('Email Content', 'skillpulse-lms')}
                                    help={template.placeholders && Object.keys(template.placeholders).length > 0 
                                        ? __('The complete email content including header, body, and footer. Use the placeholders shown above by copying and pasting them into your content.', 'skillpulse-lms')
                                        : __('The complete email content including header, body, and footer. Use placeholders like {user_name}, {site_name}, etc.', 'skillpulse-lms')
                                    }
                                    value={formData.content}
                                    onChange={(value) => this.handleFieldChange('content', value)}
                                    placeholder={__('Enter complete email content including header, body, and footer...', 'skillpulse-lms')}
                                    height={500}
                                />
                            </div>

                            {/* Available Placeholders */}
                            {template.placeholders && Object.keys(template.placeholders).length > 0 && (
                                <div className="splms-form-section">
                                    <div className="splms-placeholders-section">
                                        <h4>{__('Available Placeholders', 'skillpulse-lms')}</h4>
                                        <p className="splms-placeholders-help">
                                            {__('You can use these placeholders in your email subject and content. They will be automatically replaced with actual values when the email is sent.', 'skillpulse-lms')}
                                        </p>
                                        <div className="splms-placeholders-grid">
                                            {Object.entries(template.placeholders).map(([key, description]) => (
                                                <div 
                                                    key={key} 
                                                    className={`splms-placeholder-item ${this.state.copiedPlaceholder === key ? 'copied' : ''}`}
                                                    onClick={() => this.handlePlaceholderClick(key)}
                                                    title={__('Click to copy to clipboard', 'skillpulse-lms')}
                                                >
                                                    <code className="splms-placeholder-code">
                                                        {`{${key}}`}
                                                        {this.state.copiedPlaceholder === key && (
                                                            <span className="splms-copied-indicator">
                                                                <SplmsIcon mode="wp" name="yes" size={12} />
                                                                {__('Copied!', 'skillpulse-lms')}
                                                            </span>
                                                        )}
                                                    </code>
                                                    <span className="splms-placeholder-desc">{description}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </CardBody>
                </Card>

                {/* Preview Modal */}
                {this.state.showPreviewModal && (
                    <Modal
                        title={__('Email Preview', 'skillpulse-lms')}
                        onRequestClose={this.handleClosePreview}
                        className="splms-email-preview-modal"
                        isFullScreen={false}
                    >
                        <div className="splms-email-preview-modal-content">
                            {/* Email Header */}
                            <div className="splms-email-preview-header">
                                <div className="splms-email-preview-subject">
                                    <strong>{__('Subject:', 'skillpulse-lms')}</strong>
                                    <span className="splms-email-subject-text">
                                        {formData.subject || __('No subject', 'skillpulse-lms')}
                                    </span>
                                </div>
                                <div className="splms-email-preview-meta">
                                    <span className="splms-email-from">
                                        <strong>{__('From:', 'skillpulse-lms')}</strong> {template.site_name || 'noreply@example.com'}
                                    </span>
                                    <span className="splms-email-to">
                                        <strong>{__('To:', 'skillpulse-lms')}</strong> {template.user_name || 'user@example.com'}
                                    </span>
                                </div>
                            </div>

                            {/* Email Content */}
                            <div className="splms-email-preview-body">
                                <div 
                                    className="splms-email-content"
                                    dangerouslySetInnerHTML={{ 
                                        __html: formData.content || __('No content', 'skillpulse-lms')
                                    }} 
                                />
                            </div>

                            {/* Email Footer */}
                            <div className="splms-email-preview-footer">
                                <div className="splms-email-preview-info">
                                    <p className="splms-email-preview-note">
                                        {__('This is a preview of how your email will appear to recipients. The actual email may look slightly different depending on the email client.', 'skillpulse-lms')}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="splms-email-preview-modal-actions">
                            <Button
                                isPrimary
                                onClick={this.handleClosePreview}
                            >
                                {__('Close Preview', 'skillpulse-lms')}
                            </Button>
                        </div>
                    </Modal>
                )}
            </div>
        );
    }
}

export default TemplateEditor; 