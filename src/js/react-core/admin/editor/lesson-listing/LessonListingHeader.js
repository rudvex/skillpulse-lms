import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import AdminHeader from "../../../components/AdminHeader";

class LessonListingHeader extends Component {
    getPrimaryAction = () => ({
        text: __('Add New Lesson', 'skillpulse-lms'),
        href: `${window.location.origin}/wp-admin/post-new.php?post_type=sp-lesson`,
        icon: 'plus',
        className: 'splms-add-lesson-button'
    });

    render() {
        return (
            <AdminHeader
                title={__("Lessons", "skillpulse-lms")}
                helpUrl="https://skillpulselms.com/docs/lessons"
                helpText={__("Lessons Help", "skillpulse-lms")}
                primaryAction={this.getPrimaryAction()}
            />
        );
    }
}

export default LessonListingHeader; 