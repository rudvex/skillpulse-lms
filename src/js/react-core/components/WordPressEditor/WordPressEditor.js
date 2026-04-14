import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import SimpleEditor from './SimpleEditor';

// Import component styles
import './styles.scss';

/**
 * WordPress Native Rich Text Editor Component
 *
 * A replacement for TinyMCE using WordPress-style contentEditable editor
 * Maintains the same API as TinyMCEEditor for compatibility
 */
class WordPressEditor extends Component {
    render() {
        // Pass all props to SimpleEditor which handles the actual editing
        return <SimpleEditor {...this.props} />;
    }
}

export default WordPressEditor;