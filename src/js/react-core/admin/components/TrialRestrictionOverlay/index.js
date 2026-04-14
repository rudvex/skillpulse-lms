/**
 * Trial Restriction Overlay Component
 *
 * Enhanced overlay component that restricts admin actions when trial expires.
 * Provides contextual messaging, upgrade prompts, and graceful degradation.
 *
 * @since [SPLMS_VERSION]
 */

import React, { useEffect, useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { Modal, Button, Notice } from '@wordpress/components';
import { SplmsIcon } from '../../../components/SplmsIcon';
import { useTrialStatus } from '../TrialStatusProvider';

/**
 * Trial Restriction Overlay Component.
 *
 * @param {Object} props - Component props.
 * @param {boolean} props.isVisible - Whether overlay should be visible.
 * @param {string} props.restrictionType - Type of restriction (expired, limit_reached, feature_blocked).
 * @param {string} props.feature - Specific feature being restricted.
 * @param {string} props.customMessage - Custom restriction message.
 * @param {boolean} props.allowDismiss - Whether overlay can be dismissed.
 * @param {Function} props.onDismiss - Callback when overlay is dismissed.
 * @param {Function} props.onUpgrade - Callback when upgrade is initiated.
 * @param {string} props.className - Additional CSS classes.
 * @return {JSX.Element|null} Restriction overlay component.
 */
const TrialRestrictionOverlay = ({
	isVisible = false,
	restrictionType = 'expired',
	feature = '',
	customMessage = '',
	allowDismiss = true,
	onDismiss = () => {},
	onUpgrade = () => {},
	className = ''
}) => {
	const {
		isTrialExpired,
		isTrialActive,
		daysRemaining,
		getMessage,
		isActionBlocked,
		isConnected
	} = useTrialStatus();

	const [showOverlay, setShowOverlay] = useState(false);
	const [overlayContent, setOverlayContent] = useState(null);

	/**
	 * Get restriction configuration based on type and context.
	 *
	 * @param {string} type - Restriction type.
	 * @param {string} featureName - Feature name.
	 * @return {Object} Restriction configuration.
	 */
	const getRestrictionConfig = (type, featureName = '') => {
		const configs = {
			expired: {
				icon: 'warning',
				iconColor: '#d63638',
				title: __('Trial Expired', 'skillpulse-lms'),
				message: customMessage || getMessage('expired') || __('Your SkillPulse LMS trial has expired. Please upgrade to continue using this feature.', 'skillpulse-lms'),
				urgency: 'high',
				showDataAssurance: true,
				allowDismiss: false,
				primaryAction: {
					label: __('Upgrade Now', 'skillpulse-lms'),
					style: 'primary'
				},
				secondaryAction: {
					label: __('Go to License Page', 'skillpulse-lms'),
					style: 'secondary'
				}
			},
			limit_reached: {
				icon: 'info',
				iconColor: '#f56e28',
				title: sprintf(
					/* translators: %s: feature name */
					__('%s Limit Reached', 'skillpulse-lms'),
					featureName || __('Feature', 'skillpulse-lms')
				),
				message: customMessage || sprintf(
					/* translators: %s: feature name */
					__('You\'ve reached your trial limit for %s. Upgrade to continue using this feature.', 'skillpulse-lms'),
					featureName.toLowerCase() || __('this feature', 'skillpulse-lms')
				),
				urgency: 'medium',
				showDataAssurance: true,
				allowDismiss: true,
				primaryAction: {
					label: __('Upgrade for Unlimited', 'skillpulse-lms'),
					style: 'primary'
				},
				secondaryAction: {
					label: __('Continue with Trial', 'skillpulse-lms'),
					style: 'link'
				}
			},
			feature_blocked: {
				icon: 'lock',
				iconColor: '#f56e28',
				title: __('Feature Not Available', 'skillpulse-lms'),
				message: customMessage || getMessage('feature_blocked') || sprintf(
					/* translators: %s: feature name */
					__('The %s feature is not available in trial mode. Upgrade to unlock all features.', 'skillpulse-lms'),
					featureName || __('requested', 'skillpulse-lms')
				),
				urgency: 'low',
				showDataAssurance: false,
				allowDismiss: true,
				primaryAction: {
					label: __('Learn About Pro Features', 'skillpulse-lms'),
					style: 'primary'
				},
				secondaryAction: {
					label: __('Continue with Trial', 'skillpulse-lms'),
					style: 'link'
				}
			},
			offline: {
				icon: 'cloud',
				iconColor: '#8c8f94',
				title: __('Connection Lost', 'skillpulse-lms'),
				message: __('Unable to verify trial status. Please check your internet connection and try again.', 'skillpulse-lms'),
				urgency: 'low',
				showDataAssurance: false,
				allowDismiss: true,
				primaryAction: {
					label: __('Retry Connection', 'skillpulse-lms'),
					style: 'primary'
				},
				secondaryAction: {
					label: __('Work Offline', 'skillpulse-lms'),
					style: 'secondary'
				}
			}
		};

		return configs[type] || configs.expired;
	};

	/**
	 * Get urgency notice configuration.
	 *
	 * @return {Object|null} Urgency notice config.
	 */
	const getUrgencyNotice = () => {
		if (daysRemaining <= 0) {
			return {
				status: 'error',
				message: __('Your trial has expired today. Upgrade immediately to restore access.', 'skillpulse-lms')
			};
		}
		if (daysRemaining <= 1) {
			return {
				status: 'warning',
				message: __('Your trial expires in 1 day. Upgrade now to avoid service interruption.', 'skillpulse-lms')
			};
		}
		if (daysRemaining <= 3) {
			return {
				status: 'warning',
				message: sprintf(
					/* translators: %d: days remaining */
					__('Your trial expires in %d days. Upgrade soon to secure uninterrupted access.', 'skillpulse-lms'),
					daysRemaining
				)
			};
		}
		return null;
	};

	/**
	 * Handle primary action (usually upgrade).
	 */
	const handlePrimaryAction = () => {
		if (restrictionType === 'offline') {
			// Retry connection.
			window.location.reload();
			return;
		}

		// Navigate to upgrade or license page.
		const upgradeUrl = restrictionType === 'feature_blocked'
			? 'https://skillpulselms.com/features/?utm_source=admin&utm_medium=restriction-overlay&utm_campaign=feature-blocked'
			: `https://skillpulselms.com/pricing/?utm_source=admin&utm_medium=restriction-overlay&utm_campaign=${restrictionType}`;

		window.open(upgradeUrl, '_blank', 'noopener,noreferrer');
		onUpgrade();
	};

	/**
	 * Handle secondary action.
	 */
	const handleSecondaryAction = () => {
		if (restrictionType === 'offline') {
			// Continue offline.
			setShowOverlay(false);
			onDismiss();
			return;
		}

		if (restrictionType === 'expired') {
			// Go to license page.
			const licenseUrl = SPLMSCore_Data?.adminUrl + '/admin.php?page=splms-license' || '#';
			window.location.href = licenseUrl;
			return;
		}

		// Dismiss overlay for other types.
		setShowOverlay(false);
		onDismiss();
	};

	/**
	 * Handle overlay dismiss.
	 */
	const handleDismiss = () => {
		if (!allowDismiss) {
			return;
		}

		setShowOverlay(false);
		onDismiss();
	};

	// Determine if overlay should be shown based on trial status and props.
	useEffect(() => {
		let shouldShow = isVisible;

		// Auto-determine visibility based on trial status if not explicitly set.
		if (!isVisible) {
			if (!isConnected && restrictionType === 'offline') {
				shouldShow = true;
			} else if (isTrialExpired && restrictionType === 'expired') {
				shouldShow = true;
			} else if (feature && isActionBlocked('form_submissions') && restrictionType === 'limit_reached') {
				shouldShow = true;
			}
		}

		setShowOverlay(shouldShow);

		// Update overlay content based on current restriction type.
		const config = getRestrictionConfig(restrictionType, feature);
		setOverlayContent(config);
	}, [isVisible, isTrialExpired, isConnected, restrictionType, feature, isActionBlocked]);

	// Don't render if overlay shouldn't be shown or if content isn't ready.
	if (!showOverlay || !overlayContent) {
		return null;
	}

	const urgencyNotice = getUrgencyNotice();
	const componentClasses = `splms-trial-restriction-overlay ${className}`.trim();

	return (
		<Modal
			title={overlayContent.title}
			onRequestClose={overlayContent.allowDismiss ? handleDismiss : undefined}
			className={componentClasses}
			isDismissible={overlayContent.allowDismiss && allowDismiss}
			shouldCloseOnClickOutside={false}
			size="medium"
			headerActions={
				overlayContent.allowDismiss && allowDismiss ? (
					<Button
						icon="no-alt"
						onClick={handleDismiss}
						label={__('Close', 'skillpulse-lms')}
						showTooltip={true}
					/>
				) : null
			}
		>
			<div className="splms-restriction-overlay-content">

				{/* Urgency Notice */}
				{urgencyNotice && (
					<div className="splms-restriction-overlay__urgency">
						<Notice
							status={urgencyNotice.status}
							isDismissible={false}
							className="splms-urgency-notice"
						>
							{urgencyNotice.message}
						</Notice>
					</div>
				)}

				{/* Main Content */}
				<div className="splms-restriction-overlay__main">
					<div className="splms-restriction-overlay__icon">
						<SplmsIcon
							mode="wp"
							name={overlayContent.icon}
							size={48}
							style={{ color: overlayContent.iconColor }}
							aria-label={overlayContent.title}
						/>
					</div>

					<div className="splms-restriction-overlay__message">
						<p className="splms-restriction-message">
							{overlayContent.message}
						</p>

						{/* Trial status info */}
						{isTrialActive && (
							<div className="splms-restriction-overlay__trial-info">
								<p className="splms-trial-info">
									{daysRemaining > 0 ? (
										sprintf(
											/* translators: %d: days remaining */
											__('Trial expires in %d days.', 'skillpulse-lms'),
											daysRemaining
										)
									) : (
										__('Trial expired.', 'skillpulse-lms')
									)}
								</p>
							</div>
						)}
					</div>
				</div>

				{/* Data Assurance */}
				{overlayContent.showDataAssurance && (
					<div className="splms-restriction-overlay__assurance">
						<div className="splms-assurance-content">
							<SplmsIcon
								mode="wp"
								name="shield-alt"
								size={20}
								className="splms-assurance-icon"
							/>
							<p className="splms-assurance-text">
								{__('All your trial data is safe and will be preserved when you upgrade. No content or progress will be lost.', 'skillpulse-lms')}
							</p>
						</div>
					</div>
				)}

				{/* Action Buttons */}
				<div className="splms-restriction-overlay__actions">
					<div className="splms-overlay-actions">
						<Button
							variant={overlayContent.primaryAction.style}
							size="large"
							onClick={handlePrimaryAction}
							className="splms-overlay-primary-btn"
						>
							{restrictionType === 'offline' ? (
								<SplmsIcon
									mode="wp"
									name="update"
									size={16}
									className="splms-action-icon"
								/>
							) : (
								<SplmsIcon
									mode="wp"
									name="external"
									size={16}
									className="splms-action-icon"
								/>
							)}
							{overlayContent.primaryAction.label}
						</Button>

						<Button
							variant={overlayContent.secondaryAction.style}
							size="large"
							onClick={handleSecondaryAction}
							className="splms-overlay-secondary-btn"
						>
							{overlayContent.secondaryAction.label}
						</Button>
					</div>
				</div>

				{/* Additional Help Links */}
				{restrictionType !== 'offline' && (
					<div className="splms-restriction-overlay__help">
						<p className="splms-help-links">
							<Button
								variant="link"
								size="small"
								onClick={() => {
									window.open('https://skillpulselms.com/help/?utm_source=admin&utm_medium=restriction-overlay', '_blank', 'noopener,noreferrer');
								}}
							>
								{__('Need Help?', 'skillpulse-lms')}
							</Button>
							{' | '}
							<Button
								variant="link"
								size="small"
								onClick={() => {
									window.open('https://skillpulselms.com/contact/?utm_source=admin&utm_medium=restriction-overlay', '_blank', 'noopener,noreferrer');
								}}
							>
								{__('Contact Support', 'skillpulse-lms')}
							</Button>
						</p>
					</div>
				)}
			</div>

			<style jsx>{`
				.splms-restriction-overlay-content {
					padding: 0;
				}

				.splms-restriction-overlay__urgency {
					margin-bottom: 20px;
				}

				.splms-restriction-overlay__main {
					display: flex;
					align-items: flex-start;
					gap: 20px;
					margin-bottom: 24px;
				}

				.splms-restriction-overlay__icon {
					flex-shrink: 0;
				}

				.splms-restriction-overlay__message {
					flex: 1;
				}

				.splms-restriction-message {
					font-size: 16px;
					line-height: 1.5;
					margin-bottom: 12px;
				}

				.splms-restriction-overlay__trial-info {
					background: #f6f7f7;
					border-radius: 4px;
					padding: 12px;
				}

				.splms-trial-info {
					margin: 0;
					font-size: 14px;
					color: #50575e;
				}

				.splms-restriction-overlay__assurance {
					background: #e7f5e7;
					border: 1px solid #00a32a;
					border-radius: 6px;
					padding: 16px;
					margin-bottom: 24px;
				}

				.splms-assurance-content {
					display: flex;
					align-items: center;
					gap: 12px;
				}

				.splms-assurance-icon {
					color: #00a32a;
					flex-shrink: 0;
				}

				.splms-assurance-text {
					margin: 0;
					font-size: 14px;
					line-height: 1.4;
				}

				.splms-restriction-overlay__actions {
					margin-bottom: 16px;
				}

				.splms-overlay-actions {
					display: flex;
					gap: 12px;
					justify-content: center;
				}

				.splms-overlay-primary-btn {
					display: flex;
					align-items: center;
					gap: 8px;
				}

				.splms-restriction-overlay__help {
					text-align: center;
					border-top: 1px solid #ddd;
					padding-top: 16px;
				}

				.splms-help-links {
					margin: 0;
					font-size: 13px;
				}

				@media (max-width: 768px) {
					.splms-restriction-overlay__main {
						flex-direction: column;
						align-items: center;
						text-align: center;
					}

					.splms-overlay-actions {
						flex-direction: column;
					}
				}
			`}</style>
		</Modal>
	);
};

export default TrialRestrictionOverlay;