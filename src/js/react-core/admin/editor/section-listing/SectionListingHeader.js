import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import AdminHeader from "../../../components/AdminHeader";

class SectionListingHeader extends Component {
    getPrimaryAction = () => ({
        text: __('Add New Section', 'skillpulse-lms'),
        href: `${window.location.origin}/wp-admin/post-new.php?post_type=sp-section`,
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
