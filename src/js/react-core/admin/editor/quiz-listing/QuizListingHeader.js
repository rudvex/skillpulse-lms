import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import AdminHeader from "../../../components/AdminHeader";
import { getAdminUrl } from '../../../utility/url';

class QuizListingHeader extends Component {
    getPrimaryAction = () => ({
        text: __('Add New Quiz', 'skillpulse-lms'),
        href: `${getAdminUrl()}/post-new.php?post_type=sp-quiz`,
        icon: 'plus',
        className: 'splms-add-quiz-button'
    });

    render() {
        return (
            <AdminHeader
                title={__("Quizzes", "skillpulse-lms")}
                helpUrl="https://skillpulselms.com/docs/quizzes"
                helpText={__("Quizzes Help", "skillpulse-lms")}
                primaryAction={this.getPrimaryAction()}
            />
        );
    }
}

export default QuizListingHeader; 