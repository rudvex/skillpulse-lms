import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { 
    Card, 
    CardHeader, 
    CardBody, 
    Spinner
} from '@wordpress/components';
import { 
    withSelect, 
    withDispatch 
} from '@wordpress/data';
import { compose } from '@wordpress/compose';
import TemplateList from './components/TemplateList';
import TemplateEditor from './components/TemplateEditor';

// Import styles
import './styles/index.scss';

class EmailTemplatesPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            currentTemplate: 'activation_email',
            isSubmitting: false,
            testStatus: 'idle',
            isInitialLoad: true,
            // Add optimistic updates state
            optimisticTemplates: null,
            // Track which template is being toggled
            togglingTemplate: null,
        };
    }

    componentDidMount() {
        // Load email templates on mount
        this.props.fetchEmailTemplates();
        
        // Set initial load to false immediately (will be updated when templates load)
        this.setState({ isInitialLoad: false });
    }

    componentDidUpdate(prevProps) {
        // Auto-select first template when templates are loaded
        if (prevProps.isLoading && !this.props.isLoading && this.props.templates && Object.keys(this.props.templates).length > 0) {
            const templateKeys = Object.keys(this.props.templates);
            if (!templateKeys.includes(this.state.currentTemplate)) {
                this.setState({ currentTemplate: templateKeys[0] });
            }
        }

        // Show success message when template is saved
        if (prevProps.saveStatus === 'loading' && this.props.saveStatus === 'succeeded') {
            this.setState({ 
                isSubmitting: false,
                optimisticTemplates: null, // Clear optimistic updates
                togglingTemplate: null // Clear toggling state
            });
            // Show success toast
            if (window.skillpulseToast) {
                window.skillpulseToast.success(__('Template saved successfully!', 'skillpulse-lms'));
            }
        }

        // Show error message when save fails
        if (prevProps.saveStatus === 'loading' && this.props.saveStatus === 'failed') {
            this.setState({ 
                isSubmitting: false,
                optimisticTemplates: null, // Clear optimistic updates on error
                togglingTemplate: null // Clear toggling state
            });
            // Show error toast
            if (window.skillpulseToast) {
                window.skillpulseToast.error(this.props.error || __('Failed to save template.', 'skillpulse-lms'));
            }
        }

        // Handle test email status
        if (prevProps.testStatus === 'loading' && this.props.testStatus === 'succeeded') {
            this.setState({ 
                isSubmitting: false,
                testStatus: 'succeeded' 
            });
            // Show success toast
            if (window.skillpulseToast) {
                window.skillpulseToast.success(__('Test email sent successfully!', 'skillpulse-lms'));
            }
        }

        if (prevProps.testStatus === 'loading' && this.props.testStatus === 'failed') {
            this.setState({ 
                isSubmitting: false,
                testStatus: 'failed' 
            });
            // Show error toast
            if (window.skillpulseToast) {
                window.skillpulseToast.error(this.props.error || __('Failed to send test email.', 'skillpulse-lms'));
            }
        }
    }

    handleTemplateSelect = (templateKey) => {
        this.setState({ currentTemplate: templateKey });
    }

    handleToggleActive = (templateKey) => {
        // Toggle the active status of the template with optimistic update
        const templates = this.props.templates;
        if (templates[templateKey]) {
            const updatedTemplate = {
                ...templates[templateKey],
                is_active: !templates[templateKey].is_active
            };
            
            // Apply optimistic update immediately
            this.setState({
                optimisticTemplates: {
                    ...templates,
                    [templateKey]: updatedTemplate
                },
                togglingTemplate: templateKey
            });
            
            // Save the template
            this.props.saveEmailTemplate(updatedTemplate);
        }
    }

    handleSaveTemplate = async (templateData) => {
        this.setState({ isSubmitting: true });
        await this.props.saveEmailTemplate(templateData);
    }

    handleTestTemplate = async (templateData) => {
        this.setState({ testStatus: 'loading' });
        await this.props.testEmailTemplate(templateData);
    }

    handleResetTemplate = async (templateKey) => {
        if (window.confirm(__('Are you sure you want to reset this template to default? This action cannot be undone.', 'skillpulse-lms'))) {
            await this.props.resetEmailTemplate(templateKey);
        }
    }

    render() {
        const { 
            templates, 
            isLoading, 
            error, 
            saveStatus, 
            testStatus 
        } = this.props;

        const { 
            currentTemplate, 
            isInitialLoad,
            optimisticTemplates,
            togglingTemplate
        } = this.state;

        // Use optimistic templates if available and not toggling, otherwise use props templates
        // This ensures that when a toggle is being processed, we show the optimistic state
        // but when the server responds, we use the server's response
        const displayTemplates = (optimisticTemplates && !togglingTemplate) ? optimisticTemplates : templates;
        const currentTemplateData = displayTemplates[currentTemplate] || {};

        // Show initial loading state
        if (isInitialLoad) {
            return (
                <div className="splms-email-templates-container">
                    <div className="splms-loading-container">
                        <Spinner />
                        <p>{__('Loading email templates...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        return (
            <div className="splms-email-templates-container">



                {isLoading ? (
                    <div className="splms-loading-container">
                        <Spinner />
                        <p>{__('Loading email templates...', 'skillpulse-lms')}</p>
                    </div>
                ) : (
                    <div className="splms-email-templates-layout">
                        {/* Template List Sidebar */}
                        <div className="splms-template-sidebar">
                            <Card className="splms-card">
                                <CardHeader>
                                    <h3>{__('Available Templates', 'skillpulse-lms')}</h3>
                                </CardHeader>
                                <CardBody>
                                    <TemplateList 
                                        templates={displayTemplates}
                                        currentTemplate={currentTemplate}
                                        onTemplateSelect={this.handleTemplateSelect}
                                        onToggleActive={this.handleToggleActive}
                                        togglingTemplate={togglingTemplate}
                                    />
                                </CardBody>
                            </Card>
                        </div>

                        {/* Main Editor Area */}
                        <div className="splms-template-main">
                            <TemplateEditor 
                                template={currentTemplateData}
                                templateKey={currentTemplate}
                                onSave={this.handleSaveTemplate}
                                onTest={this.handleTestTemplate}
                                onReset={this.handleResetTemplate}
                                isSubmitting={saveStatus === 'loading'}
                                isTesting={testStatus === 'loading'}
                            />
                        </div>
                    </div>
                )}
            </div>
        );
    }
}

const mapSelectToProps = (select) => ({
    templates: select('splms/email-templates').getTemplates(),
    isLoading: select('splms/email-templates').isLoading(),
    error: select('splms/email-templates').getError(),
    saveStatus: select('splms/email-templates').getSaveStatus(),
    testStatus: select('splms/email-templates').getTestStatus(),
});

const mapDispatchToProps = (dispatch) => ({
    fetchEmailTemplates: () => dispatch('splms/email-templates').fetchEmailTemplates(),
    saveEmailTemplate: (templateData) => dispatch('splms/email-templates').saveEmailTemplate(templateData),
    testEmailTemplate: (templateData) => dispatch('splms/email-templates').testEmailTemplate(templateData),
    resetEmailTemplate: (templateKey) => dispatch('splms/email-templates').resetEmailTemplate(templateKey),
});

export default compose(
    withSelect(mapSelectToProps),
    withDispatch(mapDispatchToProps)
)(EmailTemplatesPage); 