import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import AdminHeader from "../../../components/AdminHeader";
import { getTaxonomyEditUrl, getPostTypeCreateUrl } from '../../../utility/url';

/**
 * CourseListingHeader Component
 * 
 * This component displays the header for the course listing page with dynamic dropdown actions
 * based on the course settings. The dropdown items are managed through the settings system.
 * 
 * How to add new dropdown items:
 * 1. Add a new setting in settings-config.json under course_settings
 * 2. Add the corresponding configuration in getAvailableDropdownItems()
 * 3. The dropdown will automatically show/hide based on the setting value
 * 
 * Example setting in settings-config.json:
 * {
 *   "id": "course_difficulty_enabled",
 *   "type": "toggle",
 *   "label": "Enable Course Difficulty Levels",
 *   "description": "Allow courses to have difficulty levels.",
 *   "default": false
 * }
 * 
 * Example dropdown config:
 * {
 *   key: 'course_difficulty_enabled',
 *   text: __('Course Difficulty Levels', 'skillpulse-lms'),
 *   icon: 'chart-line',
 *   taxonomy: 'sp-course-difficulty',
 *   defaultEnabled: false
 * }
 */
class CourseListingHeader extends Component {
    handleCreateTaxonomy = (taxonomyType) => {
        const taxonomySlug = taxonomyType === 'category' ? 'sp-course-category' : 'sp-course-tag';
        const createUrl = getTaxonomyEditUrl(taxonomySlug, 'sp-course');
        window.open(createUrl, '_blank');
    };

    /**
     * Get available dropdown items based on course settings
     * 
     * This function filters the dropdown items based on the enabled settings.
     * Each item in the dropdownConfig array represents a possible dropdown item.
     * 
     * @param {Object} courseSettings - The course settings from the store
     * @returns {Array} Array of dropdown items to display
     */
    getAvailableDropdownItems = (courseSettings) => {
        const dropdownConfig = [
            {
                key: 'course_categories_enabled',
                text: __('Course Categories', 'skillpulse-lms'),
                icon: 'category',
                taxonomy: 'sp-course-category',
                defaultEnabled: true
            },
            {
                key: 'course_tags_enabled',
                text: __('Course Tags', 'skillpulse-lms'),
                icon: 'tag',
                taxonomy: 'sp-course-tag',
                defaultEnabled: true
            }
        ];

        return dropdownConfig
            .filter(item => {
                const settingValue = courseSettings[item.key];
                return settingValue !== false; // Default to enabled if not explicitly disabled
            })
            .map(item => ({
                text: item.text,
                icon: item.icon,
                onClick: () => window.open(
                    getTaxonomyEditUrl(item.taxonomy, 'sp-course'),
                    '_blank'
                )
            }));
    };

    /**
     * Get the actions dropdown configuration
     * 
     * This method builds the dropdown configuration based on the current settings.
     * It will only show items for features that are enabled in the settings.
     * 
     * @returns {Object} Dropdown configuration object
     */
    getActionsDropdown = () => {
        const { settings } = this.props;
        
        // Get course settings from the settings store
        const courseSettings = settings?.courses || {};
        
        // Get available dropdown items based on settings
        const dropdownItems = this.getAvailableDropdownItems(courseSettings);
        
        // If no items are available, show a message
        if (dropdownItems.length === 0) {
            return [];
        }

        return {
            triggerText: __('Actions', 'skillpulse-lms'),
            triggerIcon: 'admin-generic',
            className: 'splms-actions-dropdown',
            items: dropdownItems
        };
    };

    getPrimaryAction = () => ({
        text: __('Add New Course', 'skillpulse-lms'),
        href: getPostTypeCreateUrl('sp-course'),
        icon: 'plus',
        className: 'splms-add-course-button'
    });

    render() {
        return (
            <AdminHeader
                title={__("Courses", "skillpulse-lms")}
                helpUrl="https://skillpulselms.com/docs/courses"
                helpText={__("Courses Help", "skillpulse-lms")}
                primaryAction={this.getPrimaryAction()}
                dropdownActions={this.getActionsDropdown()}
            />
        );
    }
}

// Connect to the settings store
export default compose([
    withSelect((select) => {
        const { getSettings } = select('splms/settings');
        return {
            settings: getSettings()
        };
    })
])(CourseListingHeader); 