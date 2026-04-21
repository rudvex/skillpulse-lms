import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import EmailWordPressEditor from './EmailWordPressEditor';

/**
 * Email Template Rich Text Editor
 * Uses the WordPress native editor with email-specific features
 *
 * This replaces TinyMCE with WordPress's native RichText editor
 * to avoid licensing costs while maintaining all functionality.
 */
class RichTextEditor extends Component {

    render() {
        return (
            <EmailWordPressEditor
                {...this.props}
                className="splms-rich-text-editor"
                enableWordPress={true}
                placeholder={this.props.placeholder || __('Enter your email content...', 'skillpulse-lms')}
            />
        );
    }
}

export default RichTextEditor; 