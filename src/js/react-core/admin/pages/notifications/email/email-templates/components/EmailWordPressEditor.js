import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { shortcode } from '@wordpress/icons';
import WordPressEditor from '../../../../../../components/WordPressEditor';

/**
 * Email Template WordPress Rich Text Editor
 * Enhanced version of WordPressEditor with email-specific features
 */
class EmailWordPressEditor extends Component {

    // Get email-specific custom buttons for toolbar
    getEmailCustomButtons = () => ({
        wpshortcode: {
            type: 'button',
            config: {
                icon: shortcode,
                text: __('Shortcode', 'skillpulse-lms'),
                tooltip: __('Insert WordPress shortcode', 'skillpulse-lms'),
                onAction: () => this.insertShortcode()
            }
        }
    });


    // Helper method to insert shortcode
    insertShortcode = () => {
        const shortcode = prompt(__('Enter shortcode (without brackets):', 'skillpulse-lms'));
        if (shortcode) {
            const currentValue = this.props.value || '';
            // Insert shortcode with some spacing for better visibility
            const newValue = currentValue + (currentValue ? ' ' : '') + `[${shortcode}]`;

            if (this.props.onChange) {
                this.props.onChange(newValue);
            }
        }
    }

    // Handle editor initialization
    handleEditorInit = (evt, editor) => {
        this.editorInstance = editor;
        if (this.props.onInit) {
            this.props.onInit(evt, editor);
        }
    }


    render() {
        // Pass through all props to WordPressEditor with email-specific enhancements
        const editorProps = {
            ...this.props,
            className: `splms-email-wordpress-editor ${this.props.className || ''}`,
            enableWordPress: true,
            onInit: this.handleEditorInit,
            // Add email-specific custom buttons to the main toolbar
            customButtons: {
                ...this.getEmailCustomButtons(),
                ...(this.props.customButtons || {})
            }
        };

        return (
            <WordPressEditor {...editorProps} />
        );
    }
}

export default EmailWordPressEditor;