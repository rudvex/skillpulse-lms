# TinyMCE Editor Component

## ⚠️ DEPRECATED

**This component is deprecated and will be removed in a future version.**

Please use `WordPressEditor` instead, which provides the same functionality using WordPress native components without requiring a paid TinyMCE license.

### Migration Guide

Replace:
```javascript
import TinyMCEEditor from './TinyMCE';
```

With:
```javascript
import WordPressEditor from './WordPressEditor';
```

The API is the same, so no other changes are needed.

---

A reusable TinyMCE editor component for the SkillPulse LMS plugin with WordPress integration and customizable features.

## Installation

The component is already included in the plugin. Import it using:

```jsx
import TinyMCEEditor from '../../components/TinyMCE';
// OR
import { TinyMCEEditor } from '../../components/TinyMCE';
```

## Basic Usage

```jsx
import TinyMCEEditor from '../../components/TinyMCE';

function MyComponent() {
  const [content, setContent] = useState('');

  return (
    <TinyMCEEditor
      value={content}
      onChange={setContent}
      label="Content"
      placeholder="Enter your content here..."
    />
  );
}
```

## Advanced Usage

### WordPress Integration (Recommended)
```jsx
<TinyMCEEditor
  value={content}
  onChange={setContent}
  label="Article Content"
  height={500}
  enableWordPress={true}
  // Note: enableImageUpload is not needed when enableWordPress is true
/>
```

### Custom Buttons
```jsx
<TinyMCEEditor
  value={content}
  onChange={setContent}
  customButtons={{
    customAction: {
      type: 'button',
      config: {
        text: 'Custom',
        tooltip: 'Perform custom action',
        onAction: () => {
          console.log('Custom button clicked');
        }
      }
    },
    customMenu: {
      type: 'menuButton',
      config: {
        text: 'Options',
        fetch: (callback) => {
          callback([
            {
              type: 'menuitem',
              text: 'Option 1',
              onAction: () => console.log('Option 1')
            },
            {
              type: 'menuitem',
              text: 'Option 2',
              onAction: () => console.log('Option 2')
            }
          ]);
        }
      }
    }
  }}
/>
```

### Custom Toolbar
```jsx
<TinyMCEEditor
  value={content}
  onChange={setContent}
  toolbar={[
    'undo redo | bold italic underline | alignleft aligncenter alignright',
    'bullist numlist | link image | removeformat'
  ]}
  plugins={['lists', 'link', 'image']}
/>
```

## Props Reference

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | string | `''` | Editor content |
| `onChange` | function | - | Callback when content changes `(content) => void` |
| `label` | string | - | Field label |
| `help` | string | - | Help text below editor |
| `placeholder` | string | - | Placeholder text |
| `height` | number | `350` | Editor height in pixels |
| `disabled` | boolean | `false` | Disable the editor |
| `className` | string | `''` | Additional CSS class |
| `plugins` | array | `[default plugins]` | TinyMCE plugins to load |
| `toolbar` | array | `[default toolbar]` | Toolbar configuration |
| `customButtons` | object | `{}` | Custom buttons to add |
| `enableWordPress` | boolean | `false` | Enable WordPress media integration |
| `enableImageUpload` | boolean | `false` | Enable image upload |
| `contentStyle` | string | `[default styles]` | Custom content CSS |
| `onInit` | function | - | Callback when editor initializes `(event, editor) => void` |

### Custom Button Format

```jsx
customButtons: {
  buttonKey: {
    type: 'button' | 'menuButton',
    config: {
      text: 'Button Text',
      tooltip: 'Button tooltip',
      onAction: () => {}, // For button type
      fetch: (callback) => {} // For menuButton type
    }
  }
}
```

## Use Cases

### 1. Blog Post Editor
```jsx
<TinyMCEEditor
  value={post.content}
  onChange={(content) => setPost({...post, content})}
  label="Post Content"
  height={600}
  enableWordPress={true}
  enableImageUpload={true}
/>
```

### 2. Course Description
```jsx
<TinyMCEEditor
  value={course.description}
  onChange={(description) => setCourse({...course, description})}
  label="Course Description"
  height={400}
  toolbar={[
    'undo redo | bold italic underline | alignleft aligncenter alignright',
    'bullist numlist | link | removeformat'
  ]}
/>
```

### 3. Simple Comment Editor
```jsx
<TinyMCEEditor
  value={comment}
  onChange={setComment}
  placeholder="Write your comment..."
  height={150}
  toolbar={['bold italic | link']}
  plugins={['link']}
/>
```

### 4. Email Template Content
```jsx
<TinyMCEEditor
  value={emailContent}
  onChange={setEmailContent}
  label="Email Content"
  enableWordPress={true}
  customButtons={{
    placeholders: {
      type: 'menuButton',
      config: {
        text: 'Placeholders',
        fetch: (callback) => {
          callback([
            { type: 'menuitem', text: 'User Name', onAction: () => insertPlaceholder('{user_name}') },
            { type: 'menuitem', text: 'Site Name', onAction: () => insertPlaceholder('{site_name}') }
          ]);
        }
      }
    }
  }}
/>
```

## Features

- **WordPress Integration**: Media library and shortcode support
- **Customizable**: Flexible toolbar and plugin configuration
- **Responsive**: Works on desktop and mobile devices
- **Accessible**: Follows WordPress accessibility standards
- **Performance**: Optimized bundle size and loading
- **Extensible**: Easy to add custom buttons and functionality

## Styling

The component includes its own styles (`styles.scss`) that provide:
- Professional WordPress-compatible appearance
- Consistent theming with CSS variables
- Responsive design
- Dark mode support (optional)
- Custom button styling for WordPress features

## Dependencies

- `@tinymce/tinymce-react`
- `tinymce`
- `@wordpress/element`
- `@wordpress/i18n`

## Image Upload Options

### Option 1: WordPress Media Library (Recommended)
```jsx
<TinyMCEEditor
  enableWordPress={true}
  // This provides the small icon next to image button that opens WP Media Library
/>
```
**Benefits:**
- ✅ Uses WordPress Media Library interface
- ✅ Integrates with existing WordPress media
- ✅ Respects WordPress security and permissions
- ✅ Better user experience
- ✅ Automatic alt text and title attributes

### Option 2: Direct Upload (Not recommended for WordPress)
```jsx
<TinyMCEEditor
  enableImageUpload={true}
  enableWordPress={false} // Don't use both together
  // This provides the "Upload" tab in image dialog
/>
```
**Use only when:**
- You're not in a WordPress environment
- You have custom upload handling requirements

### WordPress Features

When `enableWordPress={true}`:
- Image button shows small icon that opens WordPress Media Library
- Supports media selection from existing library
- Automatic alt text and title attributes  
- WordPress shortcode support (when custom buttons are configured)
- Disables conflicting TinyMCE upload functionality