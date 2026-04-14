// Central export file for all icons
// This file imports all icon categories and re-exports them as a single object

import * as ActionIcons from './ActionIcons';
import * as ArrowIcons from './ArrowIcons';
import * as ContentIcons from './ContentIcons';
import * as UIIcons from './UIIcons';

// Export all icons from different categories
export {
  // Action Icons
  view,
  edit,
  close,
  deleteIcon as delete,
  plus,
  plusCircle,
  update,
  filter,
  sort,
  trash,
  visibility,
  lock
} from './ActionIcons';

export {
  // Arrow Icons
  arrowDown,
  arrowRight,
  arrowNext,
  arrowPrev,
  arrowBack,
  arrowUp
} from './ArrowIcons';

export {
  // Content Icons
  quiz,
  lesson,
  book,
  category,
  tag,
  listView
} from './ContentIcons';

export {
  // UI Icons
  draggable,
  draggableSmall,
  link,
  settings,
  help,
  info,
  cart,
  groups,
  yesAlt,
  clock,
  email,
  adminTools,
  membership,
  calendar,
  sidebarSettings,
  sidebarListView
} from './UIIcons';

// Create aliases for icons with dash names and alternative names
const iconAliases = {
  'arrow-up': ArrowIcons.arrowUp,
  'arrow-down': ArrowIcons.arrowDown,
  'list-view': ContentIcons.listView,
  'yes-alt': UIIcons.yesAlt,
  // Additional aliases for common usage
  'remove': ActionIcons.deleteIcon,
  'add': ActionIcons.plus,
  'check': UIIcons.yesAlt,
  'mail': UIIcons.email,
  'users': UIIcons.groups,
  'folder': ContentIcons.category,
  'refresh': ActionIcons.update,
  'eye': ActionIcons.view,
  'eye-off': ActionIcons.eyeOff,
  'pencil': ActionIcons.edit,
  'x': ActionIcons.close,
  'menu': ActionIcons.sort,
  'sidebar-settings': UIIcons.sidebarSettings,
  'settings-sidebar': UIIcons.sidebarSettings,
  'sidebar-list-view': UIIcons.sidebarListView,
  'list-view-sidebar': UIIcons.sidebarListView,
  // Membership aliases
  'premium': UIIcons.membership,
  'vip': UIIcons.membership,
  'award': UIIcons.membership,
};

// Create a complete icons object for easy access
const allIcons = {
  // Action Icons
  ...ActionIcons,
  // Arrow Icons  
  ...ArrowIcons,
  // Content Icons
  ...ContentIcons,
  // UI Icons
  ...UIIcons,
  // Aliases for flexibility
  ...iconAliases
};

// Export available icon names for development/debugging
export const availableIcons = Object.keys(allIcons);

// Default export for the Icon component
export default allIcons;
