// Notification Management Entry Point
// Single entry point for all notification management (Email + In-App)
// EmailPage component handles both email and in-app tabs

import { renderBlock } from '../../../utility/renderBlock';
import EmailPage from './email/components/EmailPage';

// Import email stores
import './email/email-templates/store';

// Main Render - Single container for all notification management
renderBlock('splms-notifications', <EmailPage />); 