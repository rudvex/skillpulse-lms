import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import AdminHeader from "../../../components/AdminHeader";
import { getAdminUrl } from '../../../utility/url';

class SectionListingHeader extends Component {
    getPrimaryAction = () => ({
        text: __('Add New Section', 'skillpulse-lms'),
        href: `${getAdminUrl()}/post-new.php?post_type=sp-section`,
        icon: 'plus',
        className: 'splms-add-section-button'
    });

    render() {
        return (
            <AdminHeader
                title={__("Sections", "skillpulse-lms")}
                helpUrl="https://skillpulselms.com/docs/sections"
                helpText={__("Sections Help", "skillpulse-lms")}
                primaryAction={this.getPrimaryAction()}
            />
        );
    }
}

export default SectionListingHeader;
