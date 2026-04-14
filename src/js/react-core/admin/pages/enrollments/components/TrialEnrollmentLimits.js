/**
 * Trial Enrollment Limits Component
 *
 * Displays enrollment usage and limits for trial accounts.
 * Provides clear distinction between SkillPulse course enrollments and WordPress users.
 *
 * @since [SPLMS_VERSION]
 */

import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import TrialUsageIndicator from '../../../components/TrialUsageIndicator';

/**
 * Trial Enrollment Limits Component.
 *
 * @return {JSX.Element|null} Trial enrollment limits or null if trial not active.
 */
const TrialEnrollmentLimits = () => {
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

	const enrollmentHelpText = sprintf(
		/* translators: %d: enrollment limit */
		__('Trial accounts can have up to %d course enrollments. This tracks SkillPulse LMS course enrollments only - WordPress user registration is never limited.', 'skillpulse-lms'),
		25
	);

	return (
		<Card className="splms-trial-enrollment-limits" size="small">
			<CardHeader>
				<h3 className="splms-trial-enrollment-limits__title">
					{__('Trial Enrollment Usage', 'skillpulse-lms')}
				</h3>
			</CardHeader>
			<CardBody>
				<div className="splms-trial-enrollment-limits__content">
					<p className="splms-trial-enrollment-limits__description">
						{__('Track your trial enrollment usage below. Note: This only affects SkillPulse course enrollments, not WordPress user accounts.', 'skillpulse-lms')}
					</p>

					<TrialUsageIndicator
						feature="enrollments"
						label={__('Course Enrollments', 'skillpulse-lms')}
						helpText={enrollmentHelpText}
						showUpgradeLink={true}
						className="splms-trial-enrollment-limits__indicator"
					/>

					<div className="splms-trial-enrollment-limits__clarification">
						<p className="splms-trial-enrollment-limits__note">
							<strong>{__('Important:', 'skillpulse-lms')}</strong> {__('WordPress user registration and management is never restricted. This limit only applies to SkillPulse course enrollment features.', 'skillpulse-lms')}
						</p>
					</div>
				</div>
			</CardBody>
		</Card>
	);
};

export default TrialEnrollmentLimits;