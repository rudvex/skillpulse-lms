import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { Component } from '@wordpress/element';
import { withSelect, withDispatch ,dispatch} from '@wordpress/data';
import { Icon, TextControl, Button } from '@wordpress/components';

import { DndProvider, useDrag, useDrop } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';

import '../../styles/curriculum.scss';
import Section from "../Components/Curriculum/Section";

class Curriculum extends Component {

    constructor(props) {
        super(props);
        this.state = {
            isAddingSection: false,
            sectionTitle: '',
            isLoading: false,
        }
    }

    componentDidMount() {
        if (this.props.postId && this.props.postId > 0) {
            this.props.fetchCurriculumData(this.props.postId);
        }
        // For new courses without postId, skip API call and use initial state
    }

    componentDidUpdate(prevProps, prevState) {
        const { postId, isSavingPost, fetchCurriculumData, saveCurriculumData, sections, activeTab } = this.props;

        // Fetch curriculum if the post ID changes and is valid
        if (postId !== prevProps.postId && postId && postId > 0) {
            fetchCurriculumData(postId);
        }

        // Trigger curriculum update when the post is saved
        if (isSavingPost && !prevProps.isSavingPost && activeTab === 'curriculum' && postId && postId > 0) {
            saveCurriculumData(postId, sections);
        }
    }

    handleAddSection = () => {
        this.setState({ isAddingSection: true });
        setTimeout(() => {
            // Focus the input field after state update
            const input = document.querySelector('.section-title-input input');
            if (input) {
                input.focus();
            }
        }, 100);
    };

    handleSaveSection = () => {
        const { sectionTitle } = this.state;
        const { saveSection } = this.props;
        
        if (sectionTitle.trim()) {
            this.setState({ isLoading: true });
            saveSection(sectionTitle.trim());
            
            // Reset state after a brief delay for UX feedback
            setTimeout(() => {
                this.setState({ 
                    isAddingSection: false, 
                    sectionTitle: '', 
                    isLoading: false 
                });
            }, 200);
        } else {
            this.setState({ isAddingSection: false, sectionTitle: '' });
        }
    };

    handleCancelSection = () => {
        this.setState({ isAddingSection: false, sectionTitle: '' });
    };

    handleKeyPress = (event) => {
        if (event.key === 'Enter') {
            this.handleSaveSection();
        } else if (event.key === 'Escape') {
            this.handleCancelSection();
        }
    };

    render() {
        const { sections, isFetching } = this.props;
        const { isAddingSection, sectionTitle, isLoading } = this.state;
        if (isFetching) {
            return (
                <div className="splms-course-curriculum loading-state">
                    <div className="loading-message">
                        <Icon icon="update" />
                        <p>{__('Loading curriculum...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        return (
            <DndProvider backend={HTML5Backend}>
                <div className="splms-course-curriculum">
                    <div className="splms-course-curriculum__content">
                        <div className="splms-course-curriculum__sections">
                            {sections.length === 0 && !isAddingSection && (
                                <div className="empty-curriculum-message">
                                    <Icon icon="book" size={48} />
                                    <h3>{__('Start Building Your Course Curriculum', 'skillpulse-lms')}</h3>
                                    <p>{__('Create sections to organize your lessons and quizzes.', 'skillpulse-lms')}</p>
                                </div>
                            )}

                            {sections.map((section, index) => (
                                <Section key={section.id} section={section} index={index} />
                            ))}

                            {isAddingSection && (
                                <div className="section new-section-form">
                                    <div className="section-header adding-section">
                                        <TextControl
                                            className="section-title-input"
                                            value={sectionTitle}
                                            placeholder={__('Enter section title...', 'skillpulse-lms')}
                                            onChange={(value) => this.setState({ sectionTitle: value })}
                                            onKeyDown={this.handleKeyPress}
                                            disabled={isLoading}
                                            autoFocus
                                        />
                                        <div className="section-actions">
                                            <Button 
                                                isPrimary 
                                                onClick={this.handleSaveSection}
                                                disabled={!sectionTitle.trim() || isLoading}
                                                size="small"
                                            >
                                                {isLoading ? __('Adding...', 'skillpulse-lms') : __('Add', 'skillpulse-lms')}
                                            </Button>
                                            <Button 
                                                isSecondary 
                                                onClick={this.handleCancelSection}
                                                disabled={isLoading}
                                                size="small"
                                            >
                                                {__('Cancel', 'skillpulse-lms')}
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        <Button
                            className="add-section"
                            onClick={this.handleAddSection}
                            disabled={isAddingSection || isLoading}
                        >
                            <Icon icon="plus" />
                            {isAddingSection ? __('Adding Section...', 'skillpulse-lms') : __('Add Section', 'skillpulse-lms')}
                        </Button>
                    </div>
                </div>
            </DndProvider>
        );
    }
}

export default compose([
    withDispatch((dispatch, ownProps) => {
        const { fetchCurriculumData, saveCurriculumData, saveSection } = dispatch('splms/course-curriculum');
        return {
            fetchCurriculumData,
            saveCurriculumData,
            saveSection,
        };
    }),
    withSelect((select) => {
        const {
            getEditedPostAttribute,
            isSavingPost,
        } = select('core/editor');

        const postId = getEditedPostAttribute ? getEditedPostAttribute( "id" ) : null;
        const curriculumStore = select('splms/course-curriculum');
        const { getActiveTab } = select('splms/course-tabs');
        
        // For new courses without postId, don't show loading state
        const shouldFetch = postId && postId > 0;
        return {
            postId,
            sections: curriculumStore.getCurriculumData(postId) || [],
            isFetching: shouldFetch ? curriculumStore.isFetching() : false,
            isSavingPost: isSavingPost(),
            activeTab: getActiveTab(),
        };
    }),
])(Curriculum);
