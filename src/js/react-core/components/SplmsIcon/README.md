# SkillPulse LMS Icon System ✨

A flexible and organized icon system for the SkillPulse LMS plugin. This system uses **React components** with embedded SVG for optimal performance and **conflict-free WordPress integration**.

## ✅ Why This Approach?
- **Fast**: No HTTP requests, icons bundled with JavaScript
- **Reliable**: No loading states or network failures  
- **Simple**: Just `<SplmsIcon name="iconName" />`
- **Conflict-Free**: Won't conflict with WordPress Dashicons or @wordpress/components
- **Optimized**: Webpack can tree-shake unused icons
- **Industry Standard**: Same approach as Feather Icons, Heroicons, etc.

## Basic Usage

```jsx
import { SplmsIcon } from './components';

// Custom SkillPulse icons (default)
<SplmsIcon name="plus" />
<SplmsIcon name="edit" size={20} />
<SplmsIcon name="delete" color="#ff4444" />

// WordPress Dashicons
<SplmsIcon mode="wp" name="admin-post" />
<SplmsIcon mode="wp" name="yes" size={16} />
<SplmsIcon mode="wp" name="editor-help" color="#0073aa" />

// With CSS classes and styles
<SplmsIcon name="arrow-up" className="text-blue-500" />
<SplmsIcon mode="wp" name="dashicons-format-status" style={{ marginRight: '8px' }} />
```

## Icon Modes

### Custom Mode (Default)
Uses SkillPulse LMS custom SVG icons:
```jsx
<SplmsIcon name="plus" />           // Custom plus icon
<SplmsIcon name="edit" size={20} /> // Custom edit icon
```

### WordPress Mode
Uses WordPress Dashicons:
```jsx
<SplmsIcon mode="wp" name="admin-post" />     // WordPress admin-post dashicon
<SplmsIcon mode="wp" name="yes" size={16} />  // WordPress checkmark dashicon
```

## 🛡️ WordPress Conflict Prevention

This icon system is designed to **avoid conflicts** with:
- **WordPress Dashicons** (`dashicons-*`)
- **@wordpress/components Icon** component
- **Other plugin icon systems**

### CSS Classes
Icons get prefixed CSS classes: `splms-icon splms-icon-{name}`

```html
<!-- Your icon -->
<svg class="splms-icon splms-icon-plus">...</svg>

<!-- WordPress Dashicon (no conflict) -->
<span class="dashicons dashicons-plus-alt">...</span>
```

## Available Icons

### Action Icons
- `view` / `visibility` / `eye` - Eye icon for viewing
- `edit` / `pencil` - Pencil icon for editing  
- `close` / `x` - X circle icon for closing
- `delete` / `trash` / `remove` - Trash icon for deleting
- `plus` / `add` - Plus icon for adding
- `plusCircle` - Plus in circle
- `update` / `refresh` - Refresh icon
- `filter` - Filter icon
- `sort` / `menu` - Menu/sort icon

### Arrow Icons
- `arrowDown` / `arrow-down` - Down arrow
- `arrowUp` / `arrow-up` - Up arrow
- `arrowRight` - Right arrow
- `arrowLeft` - Left arrow
- `arrowNext` - Next arrow
- `arrowPrev` - Previous arrow
- `arrowBack` - Back arrow

### Content Icons
- `quiz` - Puzzle piece for quizzes
- `lesson` - Document icon for lessons
- `book` - Book icon
- `category` / `folder` - Folder icon for categories
- `tag` - Tag icon
- `list-view` / `listView` - List view icon

### UI Icons
- `draggable` - Grip dots for dragging
- `draggableSmall` - Small grip dots
- `link` - Link icon
- `settings` - Settings/gear icon
- `help` - Help/question icon
- `info` - Information icon
- `cart` - Shopping cart icon
- `groups` / `users` - Users/group icon
- `yes-alt` / `yesAlt` / `check` - Check circle
- `clock` - Clock/time icon
- `email` / `mail` - Mail icon
- `adminTools` - Tools icon

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string` | required | The name of the icon to render |
| `mode` | `string` | `'custom'` | Icon mode: `'custom'` for SkillPulse icons, `'wp'` for WordPress Dashicons |
| `size` | `number\|string` | `24` | Size of the icon in pixels |
| `color` | `string` | `'currentColor'` | Color of the icon |
| `className` | `string` | `''` | Additional CSS classes |
| `style` | `object` | `{}` | Inline styles |
| `...props` | `object` | | Any other props passed to the SVG element |

### Mode Parameter

- **`mode="custom"`** (default): Uses SkillPulse LMS custom SVG icons
- **`mode="wp"`**: Uses WordPress Dashicons via @wordpress/components

## Popular WordPress Dashicons

When using `mode="wp"`, you can access any WordPress Dashicon:

```jsx
// Common WordPress icons
<SplmsIcon mode="wp" name="admin-post" />      // Post icon
<SplmsIcon mode="wp" name="admin-users" />     // Users icon  
<SplmsIcon mode="wp" name="admin-settings" />  // Settings icon
<SplmsIcon mode="wp" name="yes" />             // Checkmark
<SplmsIcon mode="wp" name="no-alt" />          // X close icon
<SplmsIcon mode="wp" name="editor-help" />     // Help icon
<SplmsIcon mode="wp" name="format-status" />   // Status/quiz icon
<SplmsIcon mode="wp" name="welcome-learn-more" /> // Learn more icon
<SplmsIcon mode="wp" name="saved" />           // Saved icon
<SplmsIcon mode="wp" name="clock" />           // Clock icon
<SplmsIcon mode="wp" name="forms" />           // Forms icon
```

[See all WordPress Dashicons →](https://developer.wordpress.org/resource/dashicons/)

## Adding New Icons

### Method 1: Add to Existing Category

1. Open the appropriate category file in `icons/` folder (e.g., `ActionIcons.js`)
2. Add your new SVG as an exported constant:

```jsx
export const myNewIcon = (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
  >
    {/* Your SVG paths here */}
  </svg>
);
```

3. Export it in `icons/index.js`:

```jsx
export {
  // ... existing exports
  myNewIcon
} from './ActionIcons';
```

### Method 2: Create New Category

1. Create a new file like `MyNewIcons.js` in the `icons/` folder
2. Export your icons from that file
3. Import and export them in `icons/index.js`

### Method 3: Quick Single Icon

Add directly to `icons/index.js`:

```jsx
const iconAliases = {
  // ... existing aliases
  'my-icon': (
    <svg>
      {/* Your SVG content */}
    </svg>
  )
};
```

## Best Practices

1. **Icon Names**: Use descriptive names in camelCase or kebab-case
2. **SVG Optimization**: Remove unnecessary attributes and optimize SVGs before adding
3. **Consistent Styling**: Use `currentColor` for stroke/fill to inherit text color
4. **Viewport**: Ensure SVGs have proper `viewBox` attributes
5. **Accessibility**: Consider adding `aria-label` or `title` when needed

## Examples

### Basic Button with Icon
```jsx
const DeleteButton = () => (
  <button className="btn btn-danger">
    <SplmsIcon name="delete" size={16} />
    Delete
  </button>
);
```

### Using WordPress Icons
```jsx
const AdminButton = () => (
  <button className="btn btn-primary">
    <SplmsIcon mode="wp" name="admin-post" size={16} />
    Manage Posts
  </button>
);
```

### Icon with Tooltip
```jsx
const InfoIcon = () => (
  <SplmsIcon 
    name="info" 
    size={20} 
    className="cursor-pointer" 
    title="More information"
  />
);
```

### Animated Icon
```jsx
const LoadingIcon = () => (
  <SplmsIcon 
    name="update" 
    className="animate-spin" 
    color="#3b82f6"
  />
);
```

### Mixed Icon Usage
```jsx
const Toolbar = () => (
  <div className="toolbar">
    <SplmsIcon name="plus" size={16} />            {/* Custom icon */}
    <SplmsIcon mode="wp" name="edit" size={16} />  {/* WordPress icon */}
    <SplmsIcon name="delete" color="#d63638" />    {/* Custom colored */}
  </div>
);
```

## Migration from Old System

If you're migrating from the old `icons.js` system:

**Old way:**
```jsx
import icons from './utility/icons';
<span>{icons.edit}</span>
```

**New way:**
```jsx
import Icon from './components/Icon';
<Icon name="edit" />
```

The new system provides better organization, easier maintenance, and more flexibility for styling and usage.
