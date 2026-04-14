import { useState, useCallback, useEffect } from '@wordpress/element';
import { TextControl, Button, BaseControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { translateLabel } from '../../utility/helper';
import './styles.scss';

/**
 * MediaUrlField Component
 * 
 * Combines a text input for manual URL entry with a WordPress Media Library button.
 * Supports both external URLs and WordPress media library selections.
 * 
 * @param {Object} props Component props
 * @param {string} props.value Current URL value
 * @param {Function} props.onChange Callback when value changes
 * @param {string} props.label Field label
 * @param {string} props.help Help text
 * @param {string} props.placeholder Placeholder text
 * @param {string} props.id Field ID
 * @param {string} props.mediaType Type of media to filter ('video', 'audio', 'document')
 */
const MediaUrlField = (props) => {
    const {
        value = '',
        onChange,
        label,
        help,
        placeholder,
        id,
        mediaType = '',
    } = props;

    const [mediaFrame, setMediaFrame] = useState(null);

    // Determine MIME type filter based on mediaType
    const getMimeTypeFilter = useCallback(() => {
        switch (mediaType) {
            case 'video':
                return 'video';
            case 'audio':
                return 'audio';
            case 'document':
                // WordPress Media Library doesn't have a built-in 'document' filter
                // So we'll allow all file types but can filter on the frontend
                // Alternative: use specific MIME types for PDFs and documents
                return ''; // Allow all types, user can select any file
            default:
                return '';
        }
    }, [mediaType]);

    // Initialize WordPress Media Library frame
    useEffect(() => {
        if (typeof wp === 'undefined' || !wp.media) {
            return;
        }

        const mimeType = getMimeTypeFilter();
        const frame = wp.media({
            title: __('Select or Upload File', 'skillpulse-lms'),
            button: {
                text: __('Use this file', 'skillpulse-lms')
            },
            multiple: false,
            library: {
                type: mimeType ? mimeType : '' // Filter by MIME type if specified
            }
        });

        frame.on('select', () => {
            const attachment = frame.state().get('selection').first().toJSON();
            if (attachment && attachment.url && onChange) {
                onChange(attachment.url);
            }
        });

        setMediaFrame(frame);

        // Cleanup on unmount
        return () => {
            if (frame) {
                frame.detach();
            }
        };
    }, [getMimeTypeFilter, onChange]);

    const openMediaLibrary = useCallback(() => {
        if (mediaFrame) {
            mediaFrame.open();
        } else if (typeof wp !== 'undefined' && wp.media) {
            // Create frame on demand
            const mimeType = getMimeTypeFilter();
            const frame = wp.media({
                title: __('Select or Upload File', 'skillpulse-lms'),
                button: {
                    text: __('Use this file', 'skillpulse-lms')
                },
                multiple: false,
                library: {
                    type: mimeType ? mimeType : ''
                }
            });

            frame.on('select', () => {
                const attachment = frame.state().get('selection').first().toJSON();
                if (attachment && attachment.url && onChange) {
                    onChange(attachment.url);
                }
            });

            frame.open();
        } else {
            // Prompt for URL if wp.media is not available
            const url = prompt(__('Enter the file URL:', 'skillpulse-lms'));
            if (url && onChange) {
                onChange(url);
            }
        }
    }, [mediaFrame, getMimeTypeFilter, onChange]);

    return (
        <BaseControl
            label={translateLabel(label)}
            help={translateLabel(help)}
            className="splms-media-url-field"
        >
            <div className="splms-media-url-field__wrapper">
                <div className="splms-media-url-field__input-wrapper">
                    <TextControl
                        value={value || ''}
                        onChange={onChange}
                        placeholder={translateLabel(placeholder)}
                        className="splms-media-url-field__input"
                    />
                    <Button
                        onClick={openMediaLibrary}
                        variant="secondary"
                        className="splms-media-url-field__button"
                        icon="admin-media"
                    >
                        {__('Select File', 'skillpulse-lms')}
                    </Button>
                </div>
                <p className="splms-media-url-field__help-text">
                    {__('You can paste a URL or select from Media Library.', 'skillpulse-lms')}
                </p>
            </div>
        </BaseControl>
    );
};

export default MediaUrlField;

