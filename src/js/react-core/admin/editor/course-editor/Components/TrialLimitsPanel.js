/**
 * Trial Limits Panel Component
 *
 * Displays course creation usage and limits within the course editor.
 * Shows progress tracking and upgrade prompts when approaching or at limits.
 *
 * @since [SPLMS_VERSION]
 */

import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import TrialUsageIndicator from '../../../components/TrialUsageIndicator';

/**
 * Trial Limits Panel Component.
 *
 * @return {JSX.Element|null} Trial limits panel or null if trial not active.
 */
const TrialLimitsPanel = () => {
	const {
		isTrialActive,
		isLicenseActive
	} = useSelect((select) => {
		const trialStore = select('splms/trial');
		// Check if license is active by checking if trial is inactive due to license
		const licenseStore = select('splms/license');
		const licenseInfo = licenseStore?.isLicenseValid?.() || false;

		return {
			isTrialActive: trialStore?.isTrialActive?.() || false,
			isLicenseActive: licenseInfo
		};
	}, []);

	// Don't render if trial is not active or if full license is active.
	if (!isTrialActive || isLicenseActive) {
		return null;
	}

	const courseHelpText = sprintf(
		/* translators: %d: course limit */
		__('Trial accounts can create up to %d courses. Upgrade for unlimited course creation.', 'skillpulse-lms'),
		3
	);

	return (
		<Card className="splms-trial-limits-panel" size="small">
			<CardHeader>
				<h3 className="splms-trial-limits-panel__title">
					{__('Trial Usage', 'skillpulse-lms')}
				</h3>
			</CardHeader>
			<CardBody>
				<div className="splms-trial-limits-panel__content">
					<p className="splms-trial-limits-panel__description">
						{__('Monitor your trial usage below. Upgrade to unlock unlimited features.', 'skillpulse-lms')}
					</p>

					<TrialUsageIndicator
						feature="courses"
						label={__('Courses Created', 'skillpulse-lms')}
						helpText={courseHelpText}
						showUpgradeLink={true}
						className="splms-trial-limits-panel__indicator"
					/>
				</div>
			</CardBody>
		</Card>
	);
};

export default TrialLimitsPanel;