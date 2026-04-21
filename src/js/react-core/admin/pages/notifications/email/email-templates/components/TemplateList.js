import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { SplmsIcon } from '../../../../../../components/SplmsIcon';
class TemplateList extends Component {
    constructor(props) {
        super(props);
        this.state = {
            // Default icon and description for templates without specific config
            defaultConfig: {
                icon: 'email',
                description: __('Email template', 'skillpulse-lms')
            }
        };
    }

    handleTemplateClick = (templateKey) => {
        this.props.onTemplateSelect(templateKey);
    }

    handleToggleActive = (templateKey, event) => {
        event.stopPropagation();
        
        // Call the toggle function
        this.props.onToggleActive(templateKey);
    }

    getTemplateIcon = (templateKey) => {
        // Map template keys to appropriate icons
        const iconMap = {
            activation_email: 'email',
            welcome_email: 'email',
            course_enrollment: 'book',
            course_completion: 'yes-alt',
            certificate_email: 'yes-alt',
            password_reset: 'lock',
            // Add more mappings as needed
        };

        const iconName = iconMap[templateKey] || 'email';
        return iconName;
    }

    render() {
        const { templates, currentTemplate, togglingTemplate } = this.props;

        // If no templates loaded, show loading state
        if (!templates || Object.keys(templates).length === 0) {
            return (
                <div className="splms-template-list">
                    <div className="splms-template-loading">
                        <p>{__('Loading templates...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        // Convert templates object to array for rendering
        const templateList = Object.keys(templates).map(key => ({
            key: key,
            ...templates[key]
        }));

        return (
            <div className="splms-template-list">
                <ul className="splms-template-items">
                    {templateList.map(template => {
                        const isActive = template.is_active;
                        const isCurrent = currentTemplate === template.key;
                        const isProcessing = togglingTemplate === template.key;
                        const icon = this.getTemplateIcon(template.key);

                        return (
                            <li 
                                key={template.key} 
                                className={`splms-template-item ${isCurrent ? 'is-active' : ''}`}
                                onClick={() => this.handleTemplateClick(template.key)}
                            >
                                <div className="splms-template-item-content">
                                    <div className="splms-template-item-header">
                                        <div className="splms-template-item-icon">
                                            <SplmsIcon name={icon} size={24} />
                                        </div>
                                        <div className="splms-template-item-info">
                                            <h4 className="splms-template-item-title">
                                                {template.name}
                                            </h4>
                                        </div>
                                        <div className="splms-template-item-actions">
                                            <Button
                                                className={`splms-toggle-button ${isActive ? 'active' : ''} ${isProcessing ? 'processing' : ''}`}
                                                onClick={(e) => this.handleToggleActive(template.key, e)}
                                                isSmall
                                                disabled={isProcessing}
                                            >
                                                {isProcessing ? (
                                                    <>
                                                        <Spinner />
                                                        <span>{__('Updating...', 'skillpulse-lms')}</span>
                                                    </>
                                                ) : (
                                                    <>
                                                        {isActive ? __('Active', 'skillpulse-lms') : __('Inactive', 'skillpulse-lms')}
                                                    </>
                                                )}
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        );
                    })}
                </ul>
            </div>
        );
    }
}

export default TemplateList; 