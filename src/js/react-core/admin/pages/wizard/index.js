/**
 * Wizard Entry Point
 *
 * Main entry point for the SkillPulse LMS setup wizard.
 * Follows the same pattern as existing admin pages (e.g., license page).
 *
 * @since [SPLMS_VERSION]
 */

import { renderBlock } from '../../../utility/renderBlock';
import WizardPage from './WizardPage';

// Render wizard page in the designated container
renderBlock('splms-setup-wizard-root', <WizardPage />);