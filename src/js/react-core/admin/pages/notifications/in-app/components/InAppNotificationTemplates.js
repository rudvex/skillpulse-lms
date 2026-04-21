import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { 
    Card, 
    CardHeader, 
    CardBody, 
    Spinner,
    Notice
} from '@wordpress/components';
import { fetchNotificationTemplates, saveNotificationTemplate } from '../api';
import InAppTemplateList from './InAppTemplateList';
import InAppTemplateEditor from './InAppTemplateEditor';
import '../styles/templates.scss';

class InAppNotificationTemplates extends Component {
    constructor(props) {
        super(props);
        this.state = {
            templates: {},
            isLoading: true,
            error: null,
            successMessage: null,
            currentTemplate: null,
            isSubmitting: false,
            isInitialLoad: true,
            optimisticTemplates: null,
            togglingTemplate: null,
        };
    }

    componentDidMount() {
        // Start fetching templates (fetchTemplates will handle loading states)
        this.fetchTemplates();
    }

    fetchTemplates = async () => {
        this.setState({ isLoading: true, error: null, isInitialLoad: true });

        try {
            const response = await fetchNotificationTemplates();

            if (response.success) {
                const templates = response.data || {};
                const templateKeys = Object.keys(templates);
                
                this.setState({
                    templates: templates,
                    isLoading: false,
                    isInitialLoad: false,
                    currentTemplate: templateKeys.length > 0 ? templateKeys[0] : null
                });
            } else {
                throw new Error(response.message || __('Failed to fetch templates', 'skillpulse-lms'));
            }
        } catch (error) {
            console.error('Error fetching notification templates:', error);
            this.setState({
                error: error.message || __('Failed to load templates', 'skillpulse-lms'),
                isLoading: false,
                isInitialLoad: false
            });
        }
    }

    handleTemplateSelect = (templateKey) => {
        this.setState({ currentTemplate: templateKey });
    }

    handleToggleActive = async (templateKey) => {
        const templates = this.state.templates;
        if (!templates[templateKey]) return;

        const updatedTemplate = {
            ...templates[templateKey],
            is_enabled: !templates[templateKey].is_enabled
        };
        
        // Optimistic update
        this.setState({
            optimisticTemplates: {
                ...templates,
                [templateKey]: updatedTemplate
            },
            togglingTemplate: templateKey
        });
        
        try {
            const response = await saveNotificationTemplate({
                event_key: templateKey,
                ...updatedTemplate
            });

            if (response.success) {
                this.setState({
                    templates: {
                        ...templates,
                        [templateKey]: response.data
                    },
                    optimisticTemplates: null,
                    togglingTemplate: null
                });
            } else {
                throw new Error(response.message || __('Failed to update template', 'skillpulse-lms'));
            }
        } catch (error) {
            console.error('Error toggling template:', error);
            this.setState({
                optimisticTemplates: null,
                togglingTemplate: null,
                error: error.message || __('Failed to update template', 'skillpulse-lms')
            });
        }
    }

    handleSaveTemplate = async (templateData) => {
        this.setState({ isSubmitting: true, error: null, successMessage: null });

        try {
            const response = await saveNotificationTemplate(templateData);

            if (response.success) {
                this.setState({
                    templates: {
                        ...this.state.templates,
                        [response.data.event_key]: response.data
                    },
                    successMessage: __('Template saved successfully', 'skillpulse-lms'),
                    isSubmitting: false
                });

                // Show success toast if available
                if (window.skillpulseToast) {
                    window.skillpulseToast.success(__('Template saved successfully!', 'skillpulse-lms'));
                }
            } else {
                throw new Error(response.message || __('Failed to save template', 'skillpulse-lms'));
            }
        } catch (error) {
            console.error('Error saving template:', error);
            this.setState({
                error: error.message || __('Failed to save template', 'skillpulse-lms'),
                isSubmitting: false
            });
        }
    }

    render() {
        const { 
            templates, 
            isLoading, 
            error, 
            successMessage, 
            currentTemplate,
            isSubmitting,
            isInitialLoad,
            optimisticTemplates,
            togglingTemplate
        } = this.state;

        // Use optimistic templates if available and not toggling, otherwise use state templates
        const displayTemplates = (optimisticTemplates && !togglingTemplate) ? optimisticTemplates : templates;
        const currentTemplateData = currentTemplate && displayTemplates[currentTemplate] 
            ? displayTemplates[currentTemplate] 
            : null;

        // Show initial loading state
        if (isInitialLoad) {
            return (
                <div className="splms-in-app-templates-container">
                    <div className="splms-loading-container">
                        <Spinner />
                        <p>{__('Loading in-app notification templates...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        return (
            <div className="splms-in-app-templates-container">
                {isLoading && Object.keys(templates).length === 0 ? (
                    <div className="splms-loading-container">
                        <Spinner />
                        <p>{__('Loading templates...', 'skillpulse-lms')}</p>
                    </div>
                ) : Object.keys(templates).length === 0 ? (
                    <div className="splms-empty-state" style={{ textAlign: 'center', padding: '40px' }}>
                        <p>{__('No templates available.', 'skillpulse-lms')}</p>
                    </div>
                ) : (
                    <div className="splms-in-app-templates-layout">
                        {/* Template List Sidebar */}
                        <div className="splms-template-sidebar">
                            <Card className="splms-card">
                                <CardHeader>
                                    <h3>{__('Available Templates', 'skillpulse-lms')}</h3>
                                </CardHeader>
                                <CardBody>
                                    <InAppTemplateList 
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
                            <InAppTemplateEditor 
                                template={currentTemplateData}
                                templateKey={currentTemplate}
                                onSave={this.handleSaveTemplate}
                                isSubmitting={isSubmitting}
                            />
                        </div>
                    </div>
                )}
            </div>
        );
    }
}

export default InAppNotificationTemplates;
