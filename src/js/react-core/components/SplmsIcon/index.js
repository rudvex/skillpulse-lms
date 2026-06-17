import React from 'react';
import { Icon } from '@wordpress/components';
import allIcons from './icons';

/**
 * SkillPulse LMS Icon Component
 * 
 * A flexible icon component that can render custom SVG icons or WordPress Dashicons.
 * Supports both custom SkillPulse icons and WordPress icon library.
 * 
 * @param {string} name - The name of the icon to render
 * @param {string} mode - Icon mode: 'custom' (default) or 'wp' for WordPress icons
 * @param {number|string} size - The size of the icon (default: 24)
 * @param {string} color - The color of the icon (default: 'currentColor')
 * @param {string} className - Additional CSS classes
 * @param {object} style - Inline styles
 * @param {object} ...props - Other props to pass to the SVG element
 * 
 * Usage:
 * <SplmsIcon name="plus" />                              // Custom SkillPulse icon
 * <SplmsIcon mode="wp" name="admin-post" size={20} />   // WordPress Dashicon
 * <SplmsIcon name="edit" size={20} />                   // Custom icon with size
 * <SplmsIcon mode="wp" name="yes" color="#333" />       // WordPress icon with color
 */
const SplmsIcon = React.forwardRef(({
  name,
  mode = 'custom',
  size = 24,
  color = '',
  className = '',
  style = {},
  ...props
}, ref) => {
  // Handle WordPress icons mode
  if (mode === 'wp') {
    const wpIconStyle = {
      width: size,
      height: size,
      color: color,
      ...style
    };

    return (
      <Icon 
        icon={name}
        size={size}
        style={wpIconStyle}
        className={`wp-icon wp-icon-${name} ${className}`.trim()}
        ref={ref}
        {...props}
      />
    );
  }

  // Handle custom SkillPulse icons (default mode)
  const IconComponent = allIcons[name];

  if (!IconComponent) {
    console.warn(`Custom icon "${name}" not found. Available icons:`, Object.keys(allIcons));
    console.info('💡 Tip: Use mode="wp" for WordPress Dashicons, e.g., <SplmsIcon mode="wp" name="admin-post" />');
    return null;
  }

  // Default style with size and color for custom icons
  const iconStyle = {
    width: size,
    height: size,
    fill: color,
    stroke: color,
    ...style
  };

  // Clone the custom icon component and add our props
  return React.cloneElement(IconComponent, {
    style: iconStyle,
    className: `splms-icon splms-icon-${name} ${className}`.trim(),
    ref: ref,
    ...props
  });
});

SplmsIcon.displayName = 'SplmsIcon';

// Main export for Icon component (conflict-safe)
export default SplmsIcon;
export { SplmsIcon };

// Export individual icon categories for advanced usage
export * as ActionIcons from './icons/ActionIcons';
export * as ArrowIcons from './icons/ArrowIcons';
export * as ContentIcons from './icons/ContentIcons';
export * as UIIcons from './icons/UIIcons';

// Export all icons object for reference
export { default as allIcons } from './icons';
