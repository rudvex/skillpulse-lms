/**
 * Contextual Upgrade Prompt Modal Component
 *
 * Reusable modal component for displaying feature limitations with contextual upgrade prompts.
 * Shows usage statistics, feature-specific benefits, and upgrade paths when users hit limits.
 *
 * @since [SPLMS_VERSION]
 */

import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import {
	Modal,
	Button,
	ProgressBar,
	Notice
} from '@wordpress/components';
import { SplmsIcon } from '../../../components/SplmsIcon';

/**
 * Contextual Upgrade Prompt Component.
 *
 * @param {Object} props - Component props.
 * @param {string} props.feature - Feature name (courses, enrollments, certificates, email, api).
 * @param {number} props.currentUsage - Current usage count for the feature.
 * @param {number} props.limit - Trial limit for the feature.
 * @param {boolean} props.isOpen - Whether the modal is open.
 * @param {Function} props.onClose - Function to close the modal.
 * @param {Function} props.onUpgrade - Function to handle upgrade action.
 * @param {string} props.className - Additional CSS classes.
 * @return {JSX.Element|null} Upgrade prompt modal.
 */
const ContextualUpgradePrompt = ({
	feature,
	currentUsage = 0,
	limit = 0,
	isOpen = false,
	onClose = () => {},
	onUpgrade = () => {},
	className = ''
}) => {
	const {
		isTrialActive,
		daysRemaining,
		isExpiringSoon
	} = useSelect((select) => {
		const trialStore = select('splms/trial');
		return {
			isTrialActive: trialStore.isTrialActive(),
			daysRemaining: trialStore.getTrialDaysRemaining(),
			isExpiringSoon: trialStore.isTrialExpiringSoon()
		};
	}, []);

	// Don't render if trial is not active.
	if (!isTrialActive || !isOpen) {
		return null;
	}

	/**
	 * Get feature-specific configuration.
	 *
	 * @param {string} featureName - Feature name.
	 * @return {Object} Feature configuration.
	 */
	const getFeatureConfig = (featureName) => {
		const configs = {
			courses: {
				icon: 'book-alt',
				title: __('Course Creation Limit Reached', 'skillpulse-lms'),
				description: __('You\'ve reached your trial limit for creating courses.', 'skillpulse-lms'),
				benefits: [
					__('Unlimited course creation', 'skillpulse-lms'),
					__('Advanced course builder tools', 'skillpulse-lms'),
					__('Course templates and cloning', 'skillpulse-lms'),
					__('Advanced prerequisite management', 'skillpulse-lms'),
					__('Course completion certificates', 'skillpulse-lms')
				],
				urgency: __('Upgrade now to continue building your learning platform', 'skillpulse-lms')
			},
			enrollments: {
				icon: 'groups',
				title: __('Student Enrollment Limit Reached', 'skillpulse-lms'),
				description: __('You\'ve reached your trial limit for student enrollments.', 'skillpulse-lms'),
				benefits: [
					__('Unlimited student enrollments', 'skillpulse-lms'),
					__('Bulk enrollment tools', 'skillpulse-lms'),
					__('Advanced student management', 'skillpulse-lms'),
					__('Group enrollment features', 'skillpulse-lms'),
					__('Student progress tracking', 'skillpulse-lms')
				],
				urgency: __('Upgrade now to accept more students', 'skillpulse-lms')
			},
			certificates: {
				icon: 'awards',
				title: __('Certificate Template Limit Reached', 'skillpulse-lms'),
				description: __('You\'ve reached your trial limit for certificate templates.', 'skillpulse-lms'),
				benefits: [
					__('Unlimited certificate templates', 'skillpulse-lms'),
					__('Advanced customization options', 'skillpulse-lms'),
					__('Custom certificate branding', 'skillpulse-lms'),
					__('Automated certificate delivery', 'skillpulse-lms'),
					__('Certificate verification system', 'skillpulse-lms')
				],
				urgency: __('Upgrade now to create more certificate designs', 'skillpulse-lms')
			},
			email: {
				icon: 'email-alt',
				title: __('Email Notification Limit Reached', 'skillpulse-lms'),
				description: __('You\'ve reached your trial limit for email notifications.', 'skillpulse-lms'),
				benefits: [
					__('Unlimited email notifications', 'skillpulse-lms'),
					__('Advanced email sequences', 'skillpulse-lms'),
					__('Custom email templates', 'skillpulse-lms'),
					__('Email performance analytics', 'skillpulse-lms'),
					__('Automated drip campaigns', 'skillpulse-lms')
				],
				urgency: __('Upgrade now to continue automated communications', 'skillpulse-lms')
			},
			api: {
				icon: 'admin-tools',
				title: __('API Request Limit Reached', 'skillpulse-lms'),
				description: __('You\'ve reached your trial limit for API requests.', 'skillpulse-lms'),
				benefits: [
					__('Unlimited API requests', 'skillpulse-lms'),
					__('Advanced integrations', 'skillpulse-lms'),
					__('Webhook support', 'skillpulse-lms'),
					__('Third-party platform connections', 'skillpulse-lms'),
					__('Real-time data synchronization', 'skillpulse-lms')
				],
				urgency: __('Upgrade now to maintain your integrations', 'skillpulse-lms')
			}
		};

		return configs[featureName] || configs.courses;
	};

	/**
	 * Get upgrade urgency message based on trial status.
	 *
	 * @return {Object|null} Urgency configuration.
	 */
	const getUrgencyConfig = () => {
		if (daysRemaining <= 0) {
			return {
				type: 'error',
				message: __('Your trial has expired. Upgrade immediately to regain access.', 'skillpulse-lms')
			};
		}
		if (daysRemaining <= 1) {
			return {
				type: 'warning',
				message: __('Your trial expires today! Upgrade now to avoid losing access.', 'skillpulse-lms')
			};
		}
		if (isExpiringSoon) {
			return {
				type: 'warning',
				message: sprintf(
					/* translators: %d: days remaining */
					__('Only %d days left in your trial. Upgrade now to secure unlimited access.', 'skillpulse-lms'),
					daysRemaining
				)
			};
		}
		return null;
	};

	/**
	 * Handle upgrade button click.
	 */
	const handleUpgradeClick = () => {
		const upgradeUrl = `https://skillpulselms.com/pricing/?utm_source=feature-limit&utm_medium=admin&utm_campaign=${feature}-limit&utm_content=contextual-prompt`;
		window.open(upgradeUrl, '_blank', 'noopener,noreferrer');
		onUpgrade();
	};

	/**
	 * Handle "Go to License Page" button click.
	 */
	const handleLicensePageClick = () => {
		const licenseUrl = SPLMSCore_Data.adminUrl + '/admin.php?page=splms-license';
		window.location.href = licenseUrl;
	};

	const featureConfig = getFeatureConfig(feature);
	const urgencyConfig = getUrgencyConfig();
	const usagePercentage = limit > 0 ? Math.min(100, Math.round((currentUsage / limit) * 100)) : 100;
	const componentClasses = `splms-contextual-upgrade-prompt ${className}`.trim();

	return (
		<Modal
			title={featureConfig.title}
			onRequestClose={onClose}
			className={componentClasses}
			shouldCloseOnClickOutside={true}
			isDismissible={true}
			size="medium"
		>
			<div className="splms-upgrade-prompt-content">

				{/* Urgency Notice */}
				{urgencyConfig && (
					<div className="splms-upgrade-prompt__urgency">
						<Notice
							status={urgencyConfig.type}
							isDismissible={false}
							className="splms-upgrade-urgency-notice"
						>
							{urgencyConfig.message}
						</Notice>
					</div>
				)}

				{/* Feature Usage Display */}
				<div className="splms-upgrade-prompt__usage">
					<div className="splms-upgrade-prompt__usage-header">
						<div className="splms-upgrade-prompt__icon">
							<SplmsIcon
								mode="wp"
								name={featureConfig.icon}
								size={32}
								className="splms-feature-icon"
								aria-label={featureConfig.title}
							/>
						</div>
						<div className="splms-upgrade-prompt__usage-text">
							<h3 className="splms-upgrade-prompt__usage-title">
								{featureConfig.description}
							</h3>
							<p className="splms-upgrade-prompt__usage-stats">
								{sprintf(
									/* translators: 1: current usage, 2: limit */
									__('Usage: %1$d of %2$d (%3$d%% used)', 'skillpulse-lms'),
									currentUsage,
									limit,
									usagePercentage
								)}
							</p>
						</div>
					</div>

					{/* Progress Bar */}
					<div className="splms-upgrade-prompt__progress">
						<ProgressBar
							value={usagePercentage}
							className={`splms-usage-progress ${usagePercentage >= 100 ? 'splms-usage-progress--full' : usagePercentage >= 80 ? 'splms-usage-progress--warning' : 'splms-usage-progress--normal'}`}
							aria-label={sprintf(
								/* translators: 1: current usage, 2: limit */
								__('%1$d of %2$d used', 'skillpulse-lms'),
								currentUsage,
								limit
							)}
						/>
					</div>
				</div>

				{/* Upgrade Benefits */}
				<div className="splms-upgrade-prompt__benefits">
					<h4 className="splms-upgrade-prompt__benefits-title">
						{__('Upgrade to Pro and get:', 'skillpulse-lms')}
					</h4>
					<ul className="splms-upgrade-prompt__benefits-list">
						{featureConfig.benefits.map((benefit, index) => (
							<li key={index} className="splms-upgrade-prompt__benefit-item">
								<SplmsIcon
									mode="wp"
									name="yes-alt"
									size={16}
									className="splms-benefit-check-icon"
								/>
								<span className="splms-upgrade-prompt__benefit-text">
									{benefit}
								</span>
							</li>
						))}
					</ul>
				</div>

				{/* Value Proposition */}
				<div className="splms-upgrade-prompt__value">
					<div className="splms-upgrade-prompt__value-highlight">
						<SplmsIcon
							mode="wp"
							name="star-filled"
							size={20}
							className="splms-value-star-icon"
						/>
						<span className="splms-upgrade-prompt__value-text">
							{featureConfig.urgency}
						</span>
					</div>
				</div>

				{/* Action Buttons */}
				<div className="splms-upgrade-prompt__actions">
					<div className="splms-upgrade-prompt__primary-actions">
						<Button
							variant="primary"
							size="large"
							onClick={handleUpgradeClick}
							className="splms-upgrade-prompt__upgrade-btn"
						>
							<SplmsIcon
								mode="wp"
								name="external"
								size={16}
								className="splms-upgrade-btn-icon"
							/>
							{__('Upgrade Now', 'skillpulse-lms')}
						</Button>
						<Button
							variant="secondary"
							size="large"
							onClick={handleLicensePageClick}
							className="splms-upgrade-prompt__license-btn"
						>
							{__('I Have a License', 'skillpulse-lms')}
						</Button>
					</div>
					<div className="splms-upgrade-prompt__secondary-actions">
						<Button
							variant="link"
							size="small"
							onClick={onClose}
							className="splms-upgrade-prompt__dismiss-btn"
						>
							{__('Continue with Trial', 'skillpulse-lms')}
						</Button>
					</div>
				</div>

				{/* Data Assurance */}
				<div className="splms-upgrade-prompt__assurance">
					<div className="splms-upgrade-prompt__assurance-icon">
						<SplmsIcon
							mode="wp"
							name="shield-alt"
							size={18}
							className="splms-assurance-shield-icon"
						/>
					</div>
					<p className="splms-upgrade-prompt__assurance-text">
						{__('All your trial data will be preserved during upgrade. No courses or student progress will be lost.', 'skillpulse-lms')}
					</p>
				</div>
			</div>
		</Modal>
	);
};

export default ContextualUpgradePrompt;