/**
 * Trial Usage Indicator Component
 *
 * Reusable component for displaying trial feature usage with progress bars and warnings.
 * Shows current usage vs limits with visual progress tracking and upgrade prompts.
 *
 * @since [SPLMS_VERSION]
 */

import React, { useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Button, Spinner } from '@wordpress/components';
import { SplmsIcon } from '../../../components/SplmsIcon';
import { useTrialStatus } from '../TrialStatusProvider';
import ContextualUpgradePrompt from '../ContextualUpgradePrompt';

/**
 * Trial Usage Indicator Component.
 *
 * @param {Object} props - Component props.
 * @param {string} props.feature - Feature name (courses, enrollments, certificates, etc).
 * @param {string} props.label - Display label for the feature.
 * @param {string} props.helpText - Help text explaining the feature limit.
 * @param {boolean} props.showUpgradeLink - Whether to show upgrade link when at limit.
 * @param {string} props.className - Additional CSS classes.
 * @return {JSX.Element|null} Usage indicator component or null if trial not active.
 */
const TrialUsageIndicator = ({
	feature,
	label,
	helpText,
	showUpgradeLink = true,
	className = ''
}) => {
	// Use enhanced trial status provider for real-time updates.
	const {
		isTrialActive,
		usage,
		limits,
		isLoading,
		error,
		getUsagePercentage,
		isFeatureAtLimit,
		isFeatureNearLimit
	} = useTrialStatus();

	const [showUpgradeModal, setShowUpgradeModal] = useState(false);

	// Fallback to legacy data store if provider not available.
	const legacyData = useSelect((select) => {
		// Only use if trial status provider data isn't available.
		if (isTrialActive !== undefined) {
			return {};
		}

		const trialStore = select('splms/trial');
		if (!trialStore) {
			return {};
		}

		return {
			isTrialActive: trialStore.isTrialActive(),
			usage: trialStore.getTrialUsage(),
			limits: trialStore.getTrialLimits(),
			isLoading: trialStore.getLoading(),
			error: trialStore.getError()
		};
	}, []);

	// Use provider data if available, otherwise fallback to legacy.
	const currentData = isTrialActive !== undefined ? {
		isTrialActive,
		usage,
		limits,
		isLoading,
		error
	} : legacyData;

	// Don't render if trial is not active.
	if (!currentData.isTrialActive || currentData.error) {
		return null;
	}

	// Show loading state.
	if (currentData.isLoading) {
		return (
			<div className="splms-usage-indicator splms-usage-indicator--loading">
				<Spinner />
				<span>{__('Loading usage data...', 'skillpulse-lms')}</span>
			</div>
		);
	}

	const currentUsage = currentData.usage[feature] || 0;
	const limit = currentData.limits[feature] || 1;

	// Use enhanced calculation methods if available.
	const percentage = getUsagePercentage ? getUsagePercentage(feature) : Math.min(100, Math.round((currentUsage / limit) * 100));
	const isAtLimit = isFeatureAtLimit ? isFeatureAtLimit(feature) : currentUsage >= limit;
	const isNearLimit = isFeatureNearLimit ? isFeatureNearLimit(feature) : percentage >= 80;
	const remaining = Math.max(0, limit - currentUsage);

	/**
	 * Get progress bar color based on usage percentage.
	 *
	 * @return {string} CSS class for progress bar color.
	 */
	const getProgressColor = () => {
		if (percentage >= 100) {
			return 'splms-progress--error';
		}
		if (percentage >= 80) {
			return 'splms-progress--warning';
		}
		if (percentage >= 60) {
			return 'splms-progress--caution';
		}
		return 'splms-progress--success';
	};

	/**
	 * Get warning icon based on usage level.
	 *
	 * @return {string|null} Icon name or null.
	 */
	const getWarningIcon = () => {
		if (isAtLimit) {
			return 'warning';
		}
		if (isNearLimit) {
			return 'info';
		}
		return null;
	};

	/**
	 * Get status message based on usage.
	 *
	 * @return {string} Status message.
	 */
	const getStatusMessage = () => {
		if (isAtLimit) {
			return sprintf(
				/* translators: %s: feature label */
				__('You\'ve reached your %s limit.', 'skillpulse-lms'),
				label.toLowerCase()
			);
		}
		if (isNearLimit) {
			return sprintf(
				/* translators: 1: remaining count, 2: feature label */
				__('Only %1$d %2$s remaining.', 'skillpulse-lms'),
				remaining,
				label.toLowerCase()
			);
		}
		return '';
	};

	/**
	 * Handle upgrade button click with enhanced modal.
	 */
	const handleUpgradeClick = () => {
		// Show contextual upgrade prompt modal instead of direct redirect.
		if (isAtLimit) {
			setShowUpgradeModal(true);
		} else {
			// For non-limit situations, go directly to license page.
			const licenseUrl = SPLMSCore_Data?.adminUrl + '/admin.php?page=splms-license' || '#';
			window.location.href = licenseUrl;
		}

		// Track interaction.
		if (window.gtag) {
			window.gtag('event', 'trial_upgrade_click', {
				source: 'usage_indicator',
				feature,
				current_usage: currentUsage,
				limit,
				percentage,
				is_at_limit: isAtLimit
			});
		}
	};

	/**
	 * Handle direct license page navigation.
	 */
	const handleLicenseClick = () => {
		const licenseUrl = SPLMSCore_Data?.adminUrl + '/admin.php?page=splms-license' || '#';
		window.location.href = licenseUrl;
	};

	const progressColor = getProgressColor();
	const warningIcon = getWarningIcon();
	const statusMessage = getStatusMessage();
	const componentClasses = `splms-usage-indicator ${className}`.trim();

	return (
		<>
			<div className={componentClasses}>
				<div className="splms-usage-indicator__header">
					<div className="splms-usage-indicator__title">
						{warningIcon && (
							<span className="splms-usage-indicator__icon">
								<SplmsIcon
									mode="wp"
									name={warningIcon}
									size={16}
									aria-label={__('Warning', 'skillpulse-lms')}
								/>
							</span>
						)}
						<strong>{label}</strong>
					</div>
					<div className="splms-usage-indicator__stats">
						<span className="splms-usage-indicator__count">
							{sprintf(
								/* translators: 1: current usage, 2: limit */
								__('%1$d of %2$d used', 'skillpulse-lms'),
								currentUsage,
								limit
							)}
						</span>
						<span className="splms-usage-indicator__percentage">
							({percentage}%)
						</span>
					</div>
				</div>

				<div className="splms-usage-indicator__progress">
					<div
						className={`splms-progress-bar ${progressColor}`}
						role="progressbar"
						aria-valuenow={currentUsage}
						aria-valuemin={0}
						aria-valuemax={limit}
						aria-label={sprintf(
							/* translators: 1: current usage, 2: limit, 3: feature label */
							__('%1$d of %2$d %3$s used', 'skillpulse-lms'),
							currentUsage,
							limit,
							label.toLowerCase()
						)}
					>
						<div
							className="splms-progress-bar__fill"
							style={{ width: `${percentage}%` }}
						/>
					</div>
				</div>

				{statusMessage && (
					<div className="splms-usage-indicator__status">
						<p className={`splms-usage-indicator__message ${isAtLimit ? 'splms-message--error' : 'splms-message--warning'}`}>
							{statusMessage}
						</p>
					</div>
				)}

				{helpText && (
					<div className="splms-usage-indicator__help">
						<p className="splms-usage-indicator__help-text">
							{helpText}
						</p>
					</div>
				)}

				{/* Enhanced action buttons */}
				{(isAtLimit || isNearLimit) && showUpgradeLink && (
					<div className="splms-usage-indicator__actions">
						<Button
							variant={isAtLimit ? "primary" : "secondary"}
							size="small"
							onClick={handleUpgradeClick}
							className="splms-usage-indicator__upgrade-btn"
						>
							{isAtLimit
								? __('Upgrade for Unlimited', 'skillpulse-lms')
								: __('Learn About Upgrade', 'skillpulse-lms')
							}
						</Button>
						{isNearLimit && !isAtLimit && (
							<Button
								variant="link"
								size="small"
								onClick={handleLicenseClick}
								className="splms-usage-indicator__license-btn"
							>
								{__('Enter License', 'skillpulse-lms')}
							</Button>
						)}
					</div>
				)}
			</div>

			{/* Enhanced contextual upgrade modal */}
			{showUpgradeModal && (
				<ContextualUpgradePrompt
					feature={feature}
					currentUsage={currentUsage}
					limit={limit}
					isOpen={showUpgradeModal}
					onClose={() => setShowUpgradeModal(false)}
					onUpgrade={() => {
						setShowUpgradeModal(false);
						// Track modal upgrade interaction.
						if (window.gtag) {
							window.gtag('event', 'contextual_upgrade_modal', {
								source: 'usage_indicator',
								feature,
								action: 'upgrade'
							});
						}
					}}
				/>
			)}

			<style jsx>{`
				.splms-usage-indicator__actions {
					display: flex;
					gap: 8px;
					align-items: center;
					margin-top: 12px;
				}

				.splms-usage-indicator__progress {
					margin: 8px 0;
				}

				.splms-progress-bar {
					height: 8px;
					background-color: #ddd;
					border-radius: 4px;
					overflow: hidden;
				}

				.splms-progress-bar__fill {
					height: 100%;
					transition: width 0.3s ease;
				}

				.splms-progress--success .splms-progress-bar__fill {
					background-color: #00a32a;
				}

				.splms-progress--caution .splms-progress-bar__fill {
					background-color: #f56e28;
				}

				.splms-progress--warning .splms-progress-bar__fill {
					background-color: #dba617;
				}

				.splms-progress--error .splms-progress-bar__fill {
					background-color: #d63638;
				}

				.splms-usage-indicator__header {
					display: flex;
					justify-content: space-between;
					align-items: center;
					margin-bottom: 8px;
				}

				.splms-usage-indicator__title {
					display: flex;
					align-items: center;
					gap: 8px;
				}

				.splms-usage-indicator__stats {
					display: flex;
					align-items: center;
					gap: 8px;
					font-size: 13px;
					color: #50575e;
				}

				.splms-message--error {
					color: #d63638;
				}

				.splms-message--warning {
					color: #dba617;
				}

				@media (max-width: 480px) {
					.splms-usage-indicator__header {
						flex-direction: column;
						align-items: flex-start;
						gap: 4px;
					}

					.splms-usage-indicator__actions {
						flex-direction: column;
						align-items: stretch;
					}
				}
			`}</style>
		</>
	);
};

export default TrialUsageIndicator;