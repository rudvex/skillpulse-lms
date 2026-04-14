# TinyMCE Component Changelog

## [1.0.0] - 2024-12-09

### Added
- Created separate, reusable TinyMCE component
- Self-contained styles in `styles.scss`
- WordPress media library integration
- Custom button support system
- Comprehensive documentation and examples
- Responsive design and accessibility features

### Changed
- Moved TinyMCE component from email templates folder to main components folder
- Separated concerns: generic TinyMCE component vs. email-specific implementation
- Optimized bundle size by removing duplicate styles
- Improved maintainability with centralized component

### Technical Details
- **Location**: `src/js/react-core/components/TinyMCE/`
- **Main File**: `TinyMCEEditor.js`
- **Styles**: `styles.scss` (self-contained)
- **Exports**: Available via `index.js`
- **Dependencies**: TinyMCE 6.8.3, @tinymce/tinymce-react 4.3.2

### Email Template Integration
- Updated `RichTextEditor.js` to use the new separate component
- Maintained all existing functionality (placeholders, shortcodes, WordPress integration)
- Removed duplicate styles from email template styles
- Maintained all functionality

### Benefits
- **Reusable**: Can be used across the entire application
- **Maintainable**: Single source of truth for TinyMCE functionality
- **Performant**: Reduced bundle duplication
- **Extensible**: Easy to add new features and custom buttons
- **Consistent**: Unified appearance and behavior

### Migration Guide
To use the new component in other parts of the application:

```jsx
// Import the component
import TinyMCEEditor from '../components/TinyMCE';

// Basic usage
<TinyMCEEditor
  value={content}
  onChange={setContent}
  label="Content"
/>

// Advanced usage with WordPress
<TinyMCEEditor
  value={content}
  onChange={setContent}
  enableWordPress={true}
  customButtons={{
    myButton: {
      type: 'button',
      config: {
        text: 'My Button',
        onAction: () => console.log('clicked')
      }
    }
  }}
/>
```