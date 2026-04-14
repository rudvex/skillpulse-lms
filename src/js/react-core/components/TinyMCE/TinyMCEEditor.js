import { __ } from '@wordpress/i18n';
import { Component, createRef } from '@wordpress/element';
import { Editor } from '@tinymce/tinymce-react';
import { TextareaControl, Button } from '@wordpress/components';

// Import TinyMCE
import tinymce from 'tinymce/tinymce';
// Import themes
import 'tinymce/themes/silver';
// Import essential plugins only
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/table';
import 'tinymce/plugins/wordcount';

// Import icons
import 'tinymce/icons/default';
// Import models
import 'tinymce/models/dom';

// Import skins
import 'tinymce/skins/ui/oxide/skin.min.css';
//import 'tinymce/skins/content/default/content.min.css';

// Import component styles
import './styles.scss';

/**
 * Reusable TinyMCE Editor Component
 * 
 * USAGE EXAMPLES:
 * 
 * // Basic Editor
 * <TinyMCEEditor
 *   value={content}
 *   onChange={handleChange}
 *   label="Content"
 *   placeholder="Enter content here..."
 * />
 * 
 * // Advanced Editor with WordPress integration
 * <TinyMCEEditor
 *   value={content}
 *   onChange={handleChange}
 *   label="Article Content"
 *   height={500}
 *   enableWordPress={true}
 *   enableImageUpload={true}
 *   customButtons={{
 *     mybutton: {
 *       type: 'button',
 *       config: {
 *         text: 'My Button',
 *         onAction: () => console.log('Button clicked')
 *       }
 *     }
 *   }}
 * />
 * 
 * @param {Object} props
 * @param {string} props.value - Editor content
 * @param {function} props.onChange - Callback when content changes
 * @param {string} props.label - Field label
 * @param {string} props.help - Help text
 * @param {string} props.placeholder - Placeholder text
 * @param {number} props.height - Editor height (default: 350)
 * @param {boolean} props.disabled - Disable editor
 * @param {Array} props.plugins - Custom plugins array
 * @param {Array} props.toolbar - Custom toolbar configuration
 * @param {Object} props.customButtons - Custom buttons to add { buttonKey: { type: 'button|menuButton', config: {...} } }
 * @param {boolean} props.enableWordPress - Enable WordPress media library integration
 * @param {boolean} props.enableImageUpload - Enable image upload functionality
 * @param {string} props.contentStyle - Custom content styling CSS
 * @param {string} props.className - Additional CSS class name
 * @param {function} props.onInit - Callback when editor initializes
 */
class TinyMCEEditor extends Component {
    constructor(props) {
        super(props);
        this.state = {
            content: props.value || '',
            isEditorReady: false,
            editorMode: 'visual' // 'visual' or 'text'
        };
        this.editorRef = createRef();
    }

    componentDidMount() {
        // Deprecation warning
        if (process.env.NODE_ENV === 'development') {
            console.warn(
                'TinyMCEEditor is deprecated! Please use WordPressEditor instead. ' +
                'TinyMCE requires a paid license key and will be removed in a future version. ' +
                'WordPressEditor provides the same functionality using WordPress native components.'
            );
        }

        // Configure TinyMCE base settings to prevent external resource loading
        if (typeof window !== 'undefined' && window.tinymce) {
            window.tinymce.baseURL = false;
        }
    }

    componentDidUpdate(prevProps) {
        if (prevProps.value !== this.props.value && this.props.value !== this.state.content) {
            this.setState({ content: this.props.value || '' });
        }
    }

    handleEditorChange = (content, editor) => {
        const { placeholder } = this.props;

        // Check if content is just the placeholder
        const isPlaceholder = placeholder && content === `<p style="color: var(--splms-text-muted, #999); font-style: italic;">${placeholder}</p>`;

        // If it's placeholder content, treat as empty
        const actualContent = isPlaceholder ? '' : content;

        // Update local state immediately for UI responsiveness
        this.setState({ content: actualContent });

        // Defer parent callback to next tick to avoid cascading state updates
        // This prevents React error #185: "Cannot update state during an existing state transition"
        setTimeout(() => {
            if (this.props.onChange) {
                this.props.onChange(actualContent);
            }
        }, 0);
    }

    handleEditorInit = (evt, editor) => {
        this.setState({ isEditorReady: true });
        
        // Ensure content is set properly after editor initialization
        if (this.state.content && this.state.content.trim() !== '') {
            // Set content after a brief delay to ensure editor is fully ready
            setTimeout(() => {
                if (editor.getContent() !== this.state.content) {
                    editor.setContent(this.state.content);
                }
            }, 100);
        }
        
        if (this.props.onInit) {
            this.props.onInit(evt, editor);
        }
    }

    // Default TinyMCE configuration
    getDefaultConfig = () => {
        const { 
            height = 350, 
            disabled = false, 
            placeholder,
            plugins,
            toolbar,
            enableWordPress = false,
            enableImageUpload = false,
            contentStyle
        } = this.props;

        // Default plugins
        const defaultPlugins = plugins || [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'searchreplace', 'visualblocks', 'table', 'wordcount'
        ];

        // Default toolbar
        const defaultToolbar = toolbar || [
            'undo redo | blocks | bold italic underline strikethrough | ' +
            'alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | removeformat',
            'link unlink | image | table'
        ];

        // Default content style
        const defaultContentStyle = contentStyle || `
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
                font-size: 14px; 
                line-height: 1.5;
                color: var(--splms-text-color, #333);
                background-color: var(--splms-background, #fff);
                margin: 10px;
                padding: 0;
            }
            p { margin: 0 0 10px 0; }
            a { color: var(--splms-primary, #0073aa); text-decoration: underline; }
            h1, h2, h3, h4, h5, h6 { margin: 0 0 10px 0; font-weight: 600; }
            ul, ol { margin: 0 0 10px 0; padding-left: 20px; }
            table { border-collapse: collapse; width: 100%; margin: 0 0 10px 0; }
            table td, table th { border: 1px solid var(--splms-border-color-light, #ddd); padding: 8px; }
            table th { background-color: var(--splms-background-secondary, #f5f5f5); font-weight: 600; }
            blockquote { border-left: 4px solid var(--splms-primary, #0073aa); margin: 0 0 10px 0; padding-left: 16px; font-style: italic; color: var(--splms-text-muted, #666); }
        `;

        const config = {
            height: height,
            menubar: false,
            // Prevent TinyMCE from trying to load external resources
            skin: false,
            content_css: false,
            content_style: defaultContentStyle,
            plugins: defaultPlugins,
            toolbar: defaultToolbar,
            branding: false,
            elementpath: false,
            resize: true,
            statusbar: true,
            setup: (editor) => {
                this.setupEditor(editor);
            },
            readonly: disabled
        };

        // Add WordPress integration if enabled
        if (enableWordPress) {
            config.file_picker_types = 'image';
            config.file_picker_callback = this.handleWordPressMediaPicker;
            // Disable TinyMCE's built-in upload tab since we're using WordPress media library
            config.images_upload_url = false;
            config.automatic_uploads = false;
        }

        // Add image upload if enabled (only if WordPress is not enabled)
        if (enableImageUpload && !enableWordPress) {
            config.images_upload_handler = this.handleImageUpload;
        }

        return config;
    }

    // WordPress Media Library integration
    handleWordPressMediaPicker = (cb, value, meta) => {
        if (typeof wp !== 'undefined' && wp.media) {
            const frame = wp.media({
                title: __('Select or Upload Media', 'skillpulse-lms'),
                button: {
                    text: __('Use this media', 'skillpulse-lms')
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            frame.on('select', () => {
                const attachment = frame.state().get('selection').first().toJSON();
                cb(attachment.url, {
                    title: attachment.title,
                    alt: attachment.alt || attachment.title
                });
            });

            frame.open();
        } else {
            // Prompt for URL if WordPress media library is not available
            const url = prompt(__('Enter the URL of the image:', 'skillpulse-lms'));
            if (url) {
                cb(url, { title: '', alt: '' });
            }
        }
    }

    // Image upload handler
    handleImageUpload = (blobInfo, success, failure, progress) => {
        if (typeof wp !== 'undefined' && wp.media) {
            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('action', 'upload-attachment');
            formData.append('_wpnonce', wp.media.view.settings.post.nonce);

            const xhr = new XMLHttpRequest();
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progress(percentComplete);
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            success(response.data.url);
                        } else {
                            failure(__('Upload failed: ', 'skillpulse-lms') + (response.data.message || __('Unknown error', 'skillpulse-lms')));
                        }
                    } catch (e) {
                        failure(__('Invalid response from server', 'skillpulse-lms'));
                    }
                } else {
                    failure(__('HTTP Error: ', 'skillpulse-lms') + xhr.status);
                }
            });

            xhr.addEventListener('error', () => {
                failure(__('Image upload failed', 'skillpulse-lms'));
            });

            xhr.open('POST', wp.media.view.settings.ajaxurl);
            xhr.send(formData);
        } else {
            failure(__('WordPress media library is not available', 'skillpulse-lms'));
        }
    }

    // Setup editor with custom buttons and features
    setupEditor = (editor) => {
        const { placeholder, customButtons } = this.props;

        // Add custom placeholder support
        if (placeholder) {
            editor.on('init', () => {
                // Only set placeholder if there's no content in props/state
                const hasContent = this.state.content && this.state.content.trim() !== '';
                if (!hasContent && editor.getContent() === '') {
                    editor.setContent(`<p style="color: var(--splms-text-muted, #999); font-style: italic;">${placeholder}</p>`);
                }
            });
            
            editor.on('focus', () => {
                const content = editor.getContent();
                if (content === `<p style="color: var(--splms-text-muted, #999); font-style: italic;">${placeholder}</p>`) {
                    editor.setContent('');
                }
            });
            
            editor.on('blur', () => {
                if (editor.getContent() === '' || editor.getContent() === '<p></p>') {
                    // Set placeholder content without triggering change events
                    editor.setContent(`<p style="color: var(--splms-text-muted, #999); font-style: italic;">${placeholder}</p>`, { format: 'html', set: true, no_events: true });
                }
            });
        }

        // Add custom buttons if provided
        if (customButtons && typeof customButtons === 'object') {
            Object.keys(customButtons).forEach(buttonKey => {
                const button = customButtons[buttonKey];
                if (button.type === 'button') {
                    editor.ui.registry.addButton(buttonKey, button.config);
                } else if (button.type === 'menuButton') {
                    editor.ui.registry.addMenuButton(buttonKey, button.config);
                }
            });
        }
    }

    // Get current content based on active editor mode
    getCurrentContent = () => {
        const { editorMode } = this.state;

        if (editorMode === 'visual' && this.editorRef.current) {
            const editor = window.tinymce.get(this.editorRef.current.id);
            if (editor) {
                return editor.getContent();
            }
        }

        return this.state.content;
    }

    // Handle mode switching between visual and text
    handleModeSwitch = (newMode) => {
        if (newMode === this.state.editorMode) return;

        // Get current content before switching modes
        const currentContent = this.getCurrentContent();

        this.setState({
            editorMode: newMode,
            content: currentContent
        });

        // Notify parent of content change to keep state in sync
        if (this.props.onChange) {
            setTimeout(() => {
                this.props.onChange(currentContent);
            }, 0);
        }
    }

    // Handle text mode textarea changes
    handleTextModeChange = (value) => {
        this.setState({ content: value });

        if (this.props.onChange) {
            setTimeout(() => {
                this.props.onChange(value);
            }, 0);
        }
    }

    render() {
        const { label, help, disabled = false, className = '', enableModeToggle = true } = this.props;
        const { content, editorMode } = this.state;

        return (
            <div className={`splms-tinymce-editor ${className}`}>
                {label && (
                    <label className="splms-field-label">
                        {label}
                    </label>
                )}

                {enableModeToggle && (
                    <div className="splms-editor-tabs">
                        <button
                            type="button"
                            className={`splms-editor-tab ${editorMode === 'visual' ? 'active' : ''}`}
                            onClick={() => this.handleModeSwitch('visual')}
                            disabled={disabled}
                        >
                            {__('Visual', 'skillpulse-lms')}
                        </button>
                        <button
                            type="button"
                            className={`splms-editor-tab ${editorMode === 'text' ? 'active' : ''}`}
                            onClick={() => this.handleModeSwitch('text')}
                            disabled={disabled}
                        >
                            {__('Text', 'skillpulse-lms')}
                        </button>
                    </div>
                )}

                <div className="splms-editor-container">
                    {editorMode === 'visual' ? (
                        <Editor
                            ref={this.editorRef}
                            tinymceScriptSrc={false}
                            value={content}
                            onEditorChange={this.handleEditorChange}
                            onInit={this.handleEditorInit}
                            init={this.getDefaultConfig()}
                            disabled={disabled}
                        />
                    ) : (
                        <TextareaControl
                            value={content}
                            onChange={this.handleTextModeChange}
                            rows={10}
                            disabled={disabled}
                            className="splms-text-editor"
                            placeholder={this.props.placeholder}
                        />
                    )}
                </div>

                {help && (
                    <p className="splms-field-description">
                        {help}
                    </p>
                )}
            </div>
        );
    }
}

export default TinyMCEEditor;