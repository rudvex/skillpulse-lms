# SkillPulse LMS SCSS Variables Guide

## Overview

This directory contains a centralized CSS variable management system for SkillPulse LMS. All variables are now managed from a single source of truth, eliminating duplication and improving maintainability.

## Directory Structure

```
src/scss/
├── shared/                           # Centralized variable system
│   ├── _core-variables.scss         # Foundation variables (colors, spacing, typography)
│   ├── _admin-variables.scss        # Admin-specific variables
│   └── _frontend-variables.scss     # Frontend-specific variables
├── admin/
│   └── _variables.scss              # Admin entry point
├── frontend/
│   └── _variables.scss              # Frontend entry point
├── admin/common/_variables.scss     # Legacy compatibility (deprecated)
└── frontend/base/_variables.scss    # Legacy compatibility (deprecated)
```

## Usage

### For New SCSS Files

**Admin Files:**
```scss
// Single command import for all admin variables
@use '../admin/variables' as *;

.my-admin-component {
  background: var(--splms-primary);
  color: var(--splms-white);
  border-radius: var(--splms-border-radius-lg);
}
```

**Frontend Files:**
```scss
// Single command import for all frontend variables
@use '../frontend/variables' as *;

.my-frontend-component {
  background: var(--splms-surface);
  color: var(--splms-text-primary);
  box-shadow: var(--splms-shadow-md);
}
```

### For React Component Styles

React components can directly use CSS custom properties without imports:

```scss
.my-react-component {
  background: var(--splms-primary);
  color: var(--splms-white);
  padding: var(--splms-spacing-4);
  border-radius: var(--splms-border-radius-lg);
  transition: var(--splms-transition-base);
}
```

## Available Variables

### Core Variables (Available everywhere)

#### Colors
- **Brand Colors**: `--splms-primary`, `--splms-primary-hover`, `--splms-secondary`, etc.
- **Semantic Colors**: `--splms-success`, `--splms-warning`, `--splms-danger`, `--splms-info`
- **Neutral Colors**: `--splms-gray-50` through `--splms-gray-900`, `--splms-white`, `--splms-black`

#### Typography
- **Font Families**: `--splms-font-family-base`, `--splms-font-family-heading`, `--splms-font-family-mono`
- **Font Sizes**: `--splms-font-size-xs` through `--splms-font-size-6xl`
- **Line Heights**: `--splms-line-height-tight`, `--splms-line-height-normal`, etc.

#### Spacing
- **Standard Scale**: `--splms-spacing-1` (4px) through `--splms-spacing-32` (128px)

#### Border Radius
- **Scale**: `--splms-border-radius-sm` through `--splms-border-radius-3xl`, `--splms-border-radius-full`

#### Shadows
- **Elevation**: `--splms-shadow-sm` through `--splms-shadow-2xl`, `--splms-shadow-inner`

#### Transitions
- **Timing**: `--splms-transition-fast`, `--splms-transition-base`, `--splms-transition-slow`

### Admin-Specific Variables

- **WordPress Integration**: `--splms-wp-admin-blue`, `--splms-admin-bar-height`
- **Admin Colors**: `--splms-admin-bg`, `--splms-admin-content-bg`, `--splms-admin-border`
- **Component Styles**: `--splms-metabox-bg`, `--splms-admin-table-border`

### Frontend-Specific Variables

- **Layout**: `--splms-header-height`, `--splms-sidebar-width`, `--splms-course-sidebar-width`
- **Surface Colors**: `--splms-surface`, `--splms-bg`, `--splms-text-primary`
- **Interactive Elements**: `--splms-btn-primary-bg`, `--splms-link-color`
- **Learning Interface**: `--splms-progress-fill`, `--splms-difficulty-beginner`

## Legacy Compatibility

Existing SCSS files that use old variable names (like `$primary`, `$splms-gray-500`) will continue to work through legacy compatibility layers:

- `src/scss/admin/common/_variables.scss` (deprecated)
- `src/scss/frontend/base/_variables.scss` (deprecated)

These files automatically map old variable names to new centralized variables.

## Migration Guide

### Step 1: Update Imports (Recommended)
```scss
// Old way (deprecated)
@use './common/variables' as *;
@use '../base/variables' as *;

// New way
@use '../admin/variables' as *;     // For admin files
@use '../frontend/variables' as *; // For frontend files
```

### Step 2: Update Variable Usage (Optional)
```scss
// Old variables (still work via legacy mapping)
background: $primary;
color: $white;

// New variables (recommended)
background: var(--splms-primary);
color: var(--splms-white);
```

### Step 3: Use Modern Mixins (Optional)
```scss
// Admin components
.my-button {
  @include admin-button;
}

// Frontend components
.my-card {
  @include card;
}

.my-primary-button {
  @include button-primary;
}
```

## Benefits

1. **Single Source of Truth**: All variables managed centrally
2. **Consistency**: Unified naming and organization across admin and frontend
3. **Maintainability**: Easy to update design tokens globally
4. **Performance**: Reduced CSS output through deduplication
5. **Developer Experience**: Simple import system with context-aware variables
6. **Future-Proof**: CSS custom properties support dynamic theming
7. **Backward Compatibility**: Existing code continues to work without changes

## Best Practices

1. **Use the new entry points**: Import from `admin/_variables.scss` or `frontend/_variables.scss`
2. **Prefer CSS custom properties**: Use `var(--splms-*)` over SCSS variables for dynamic values
3. **Follow naming conventions**: Use descriptive, semantic names
4. **Avoid scattered `:root` declarations**: All CSS custom properties are centrally managed
5. **Use mixins for common patterns**: Leverage provided mixins for consistent styling

## File Organization

- **Core variables** (`_core-variables.scss`): Foundation variables shared everywhere
- **Context variables** (`_admin-variables.scss`, `_frontend-variables.scss`): Context-specific extensions
- **Entry points** (`admin/_variables.scss`, `frontend/_variables.scss`): Single imports for each context
- **Legacy files**: Maintain backward compatibility during transition