import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

class InAppTemplateList extends Component {
    constructor(props) {
        super(props);
        this.state = {
            defaultConfig: {
                icon: 'bell',
                description: __('In-app notification template', 'skillpulse-lms')
            }
        };
    }

    handleTemplateClick = (templateKey) => {
        this.props.onTemplateSelect(templateKey);
    }

    handleToggleActive = (templateKey, event) => {
        event.stopPropagation();
        this.props.onToggleActive(templateKey);
    }

    getTemplateIcon = (templateKey) => {
        const iconMap = {
            course_enrollment: 'book-alt',
            course_completion: 'yes-alt',
            lesson_completion: 'book-alt',
            quiz_completion: 'editor-help',
            quiz_passed: 'yes-alt',
            quiz_failed: 'warning',
            certificate_awarded: 'awards',
            signup_created: 'admin-users',
            signup_activated: 'yes-alt',
            review_reply: 'email-alt',
            review_new: 'star-filled',
            review_moderation: 'editor-help',
            review_moderation_result: 'yes-alt',
        };

        return iconMap[templateKey] || 'bell';
    }

    getEventDisplayName = (eventKey, template) => {
        if (template && template.event_name) {
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

        return eventNames[eventKey] || eventKey;
    }

    render() {
        const { templates, currentTemplate, togglingTemplate } = this.props;

        if (!templates || Object.keys(templates).length === 0) {
            return (
                <div className="splms-template-list">
                    <div className="splms-template-loading">
                        <p>{__('Loading templates...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        const templateList = Object.keys(templates).map(key => ({
            key: key,
            ...templates[key]
        }));

        return (
            <div className="splms-template-list">
                <ul className="splms-template-items">
                    {templateList.map(template => {
                        const isActive = template.is_enabled !== false;
                        const isCurrent = currentTemplate === template.key;
                        const isProcessing = togglingTemplate === template.key;
                        const icon = this.getTemplateIcon(template.key);
                        const displayName = this.getEventDisplayName(template.key, template);

                        return (
                            <li 
                                key={template.key} 
                                className={`splms-template-item ${isCurrent ? 'is-active' : ''}`}
                                onClick={() => this.handleTemplateClick(template.key)}
                            >
                                <div className="splms-template-item-content">
                                    <div className="splms-template-item-header">
                                        <div className="splms-template-item-icon">
                                            <SplmsIcon mode="wp" name={icon} size={24} />
                                        </div>
                                        <div className="splms-template-item-info">
                                            <h4 className="splms-template-item-title">
                                                {displayName}
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

export default InAppTemplateList;

