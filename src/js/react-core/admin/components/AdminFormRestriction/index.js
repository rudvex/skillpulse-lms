/**
 * Admin Form Restriction Component
 *
 * Wraps admin forms with trial restrictions and provides form submission blocking
 * when trial expires or limits are reached. Includes graceful degradation and
 * user-friendly restriction messaging.
 *
 * @since [SPLMS_VERSION]
 */

import React, { useEffect, useRef, useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Notice, Spinner } from '@wordpress/components';
import { SplmsIcon } from '../../../components/SplmsIcon';
import { useTrialStatus } from '../TrialStatusProvider';
import TrialRestrictionOverlay from '../TrialRestrictionOverlay';

/**
 * Admin Form Restriction Component.
 *
 * @param {Object} props - Component props.
 * @param {React.ReactNode} props.children - Form content to wrap.
 * @param {string} props.feature - Feature being restricted (courses, enrollments, etc).
 * @param {string} props.action - Specific action (create, edit, delete, bulk).
 * @param {boolean} props.blockOnExpired - Block form when trial expires (default: true).
 * @param {boolean} props.blockOnLimit - Block form when feature limit reached (default: true).
 * @param {boolean} props.showUsageWarning - Show usage warning when near limit (default: true).
 * @param {Function} props.onRestricted - Callback when form is restricted.
 * @param {Function} props.onSubmit - Custom submit handler.
 * @param {string} props.restrictionMessage - Custom restriction message.
 * @param {string} props.className - Additional CSS classes.
 * @return {JSX.Element} Form restriction wrapper.
 */
const AdminFormRestriction = ({
	children,
	feature = '',
	action = 'create',
	blockOnExpired = true,
	blockOnLimit = true,
	showUsageWarning = true,
	onRestricted = () => {},
	onSubmit = null,
	restrictionMessage = '',
	className = ''
}) => {
	const {
		isTrialActive,
		isTrialExpired,
		isFeatureAtLimit,
		isFeatureNearLimit,
		getUsagePercentage,
		usage,
		limits,
		getMessage,
		canCreateContent,
		isConnected
	} = useTrialStatus();

	const formRef = useRef(null);
	const [isSubmitting, setIsSubmitting] = useState(false);
	const [showRestrictionModal, setShowRestrictionModal] = useState(false);
	const [restrictionType, setRestrictionType] = useState('');
	const [hasInitialized, setHasInitialized] = useState(false);

	/**
	 * Determine if form should be restricted.
	 *
	 * @return {Object} Restriction status object.
	 */
	const getRestrictionStatus = () => {
		// No restriction if trial system not active.
		if (!isTrialActive) {
			return { restricted: false, reason: '' };
		}

		// Check for trial expiration.
		if (isTrialExpired && blockOnExpired) {
			return {
				restricted: true,
				reason: 'expired',
				message: restrictionMessage || getMessage('expired')
			};
		}

		// Check for feature limits.
		if (feature && blockOnLimit) {
			const atLimit = isFeatureAtLimit(feature);
			const nearLimit = isFeatureNearLimit(feature);

			if (atLimit) {
				return {
					restricted: true,
					reason: 'limit_reached',
					message: restrictionMessage || sprintf(
						/* translators: %s: feature name */
						__('You\'ve reached your trial limit for %s.', 'skillpulse-lms'),
						feature
					)
				};
			}

			if (nearLimit && showUsageWarning && action === 'create') {
				const percentage = getUsagePercentage(feature);
				return {
					restricted: false,
					reason: 'near_limit',
					warning: true,
					message: sprintf(
						/* translators: 1: percentage, 2: feature name */
						__('You\'ve used %1$d%% of your %2$s limit. Consider upgrading soon.', 'skillpulse-lms'),
						percentage,
						feature
					)
				};
			}
		}

		// Check general content creation ability.
		if (!canCreateContent && (action === 'create' || action === 'bulk')) {
			return {
				restricted: true,
				reason: 'general_restriction',
				message: restrictionMessage || __('Content creation is not available at this time.', 'skillpulse-lms')
			};
		}

		return { restricted: false, reason: '' };
	};

	/**
	 * Handle form submission with restriction checks.
	 *
	 * @param {Event} event - Form submission event.
	 */
	const handleFormSubmit = async (event) => {
		const restrictionStatus = getRestrictionStatus();

		// Block submission if restricted.
		if (restrictionStatus.restricted) {
			event.preventDefault();
			event.stopPropagation();

			// Show restriction modal.
			setRestrictionType(restrictionStatus.reason);
			setShowRestrictionModal(true);

			// Call restriction callback.
			onRestricted({
				reason: restrictionStatus.reason,
				message: restrictionStatus.message,
				feature,
				action
			});

			return false;
		}

		// If custom submit handler provided, use it.
		if (onSubmit && typeof onSubmit === 'function') {
			event.preventDefault();
			setIsSubmitting(true);

			try {
				await onSubmit(event);
			} catch (error) {
				console.error('Form submission error:', error);
			} finally {
				setIsSubmitting(false);
			}

			return false;
		}

		// Allow normal form submission.
		return true;
	};

	/**
	 * Attach form restriction handlers.
	 */
	const setupFormRestriction = () => {
		if (!formRef.current || hasInitialized) {
			return;
		}

		// Find all forms within the component.
		const forms = formRef.current.querySelectorAll('form');

		forms.forEach(form => {
			// Add restriction check to form submission.
			form.addEventListener('submit', handleFormSubmit, true);

			// Add visual indicators to submit buttons.
			const submitButtons = form.querySelectorAll('input[type="submit"], button[type="submit"], .button-primary');

			submitButtons.forEach(button => {
				// Store original state.
				const originalText = button.textContent || button.value || '';
				button.setAttribute('data-original-text', originalText);

				// Add restriction check on click.
				button.addEventListener('click', (event) => {
					const restrictionStatus = getRestrictionStatus();

					if (restrictionStatus.restricted) {
						event.preventDefault();
						event.stopPropagation();

						// Show visual feedback.
						button.style.opacity = '0.6';
						button.disabled = true;

						setTimeout(() => {
							button.style.opacity = '';
							button.disabled = false;
						}, 2000);

						// Show restriction modal.
						setRestrictionType(restrictionStatus.reason);
						setShowRestrictionModal(true);

						return false;
					}
				}, true);
			});
		});

		setHasInitialized(true);
	};

	/**
	 * Cleanup form restriction handlers.
	 */
	const cleanupFormRestriction = () => {
		if (!formRef.current) {
			return;
		}

		const forms = formRef.current.querySelectorAll('form');

		forms.forEach(form => {
			form.removeEventListener('submit', handleFormSubmit, true);
		});

		setHasInitialized(false);
	};

	// Setup form restriction when component mounts or content changes.
	useEffect(() => {
		// Small delay to ensure DOM is ready.
		const timer = setTimeout(() => {
			setupFormRestriction();
		}, 100);

		return () => {
			clearTimeout(timer);
			cleanupFormRestriction();
		};
	}, [children, isTrialExpired, isFeatureAtLimit]);

	/**
	 * Get usage indicator for the feature.
	 *
	 * @return {JSX.Element|null} Usage indicator component.
	 */
	const renderUsageIndicator = () => {
		if (!feature || !isTrialActive) {
			return null;
		}

		const currentUsage = usage[feature] || 0;
		const limit = limits[feature] || 1;
		const percentage = getUsagePercentage(feature);
		const atLimit = isFeatureAtLimit(feature);
		const nearLimit = isFeatureNearLimit(feature);

		// Only show if near or at limit.
		if (!nearLimit && !atLimit) {
			return null;
		}

		const getIndicatorStatus = () => {
			if (atLimit) return 'error';
			if (percentage >= 90) return 'warning';
			return 'info';
		};

		const getIndicatorMessage = () => {
			if (atLimit) {
				return sprintf(
					/* translators: %s: feature name */
					__('You\'ve reached your %s limit.', 'skillpulse-lms'),
					feature
				);
			}
			return sprintf(
				/* translators: 1: current usage, 2: limit, 3: feature name */
				__('%1$d of %2$d %3$s used (%4$d%%).', 'skillpulse-lms'),
				currentUsage,
				limit,
				feature,
				percentage
			);
		};

		return (
			<div className="splms-form-usage-indicator">
				<Notice
					status={getIndicatorStatus()}
					isDismissible={false}
					className="splms-usage-notice"
				>
					<div className="splms-usage-content">
						<div className="splms-usage-text">
							<strong>{getIndicatorMessage()}</strong>
							{nearLimit && !atLimit && (
								<p>{__('Consider upgrading to avoid hitting limits.', 'skillpulse-lms')}</p>
							)}
						</div>
						{atLimit && (
							<Button
								variant="primary"
								size="small"
								onClick={() => {
									setRestrictionType('limit_reached');
									setShowRestrictionModal(true);
								}}
								className="splms-usage-upgrade-btn"
							>
								{__('Upgrade Now', 'skillpulse-lms')}
							</Button>
						)}
					</div>
				</Notice>
			</div>
		);
	};

	/**
	 * Render loading overlay when submitting.
	 *
	 * @return {JSX.Element|null} Loading overlay.
	 */
	const renderLoadingOverlay = () => {
		if (!isSubmitting) {
			return null;
		}

		return (
			<div className="splms-form-loading-overlay">
				<div className="splms-form-loading-content">
					<Spinner />
					<span>{__('Processing...', 'skillpulse-lms')}</span>
				</div>
			</div>
		);
	};

	/**
	 * Render offline notice.
	 *
	 * @return {JSX.Element|null} Offline notice.
	 */
	const renderOfflineNotice = () => {
		if (isConnected) {
			return null;
		}

		return (
			<div className="splms-form-offline-notice">
				<Notice
					status="warning"
					isDismissible={false}
					className="splms-offline-notice"
				>
					<div className="splms-offline-content">
						<SplmsIcon
							mode="wp"
							name="cloud"
							size={16}
							className="splms-offline-icon"
						/>
						<span>
							{__('Connection lost. Changes may not be saved properly.', 'skillpulse-lms')}
						</span>
					</div>
				</Notice>
			</div>
		);
	};

	const restrictionStatus = getRestrictionStatus();
	const componentClasses = `splms-admin-form-restriction ${className} ${restrictionStatus.restricted ? 'splms-form-restricted' : ''}`.trim();

	return (
		<div ref={formRef} className={componentClasses}>

			{/* Offline Notice */}
			{renderOfflineNotice()}

			{/* Usage Indicator */}
			{renderUsageIndicator()}

			{/* Warning for near-limit situations */}
			{restrictionStatus.warning && (
				<div className="splms-form-warning">
					<Notice
						status="warning"
						isDismissible={true}
						className="splms-warning-notice"
					>
						{restrictionStatus.message}
					</Notice>
				</div>
			)}

			{/* Form Content */}
			<div className="splms-form-content">
				{children}
			</div>

			{/* Loading Overlay */}
			{renderLoadingOverlay()}

			{/* Restriction Modal */}
			<TrialRestrictionOverlay
				isVisible={showRestrictionModal}
				restrictionType={restrictionType}
				feature={feature}
				allowDismiss={true}
				onDismiss={() => setShowRestrictionModal(false)}
				onUpgrade={() => {
					setShowRestrictionModal(false);
					// Track upgrade interaction.
					if (window.gtag) {
						window.gtag('event', 'trial_upgrade_click', {
							source: 'form_restriction',
							feature,
							action,
							restriction_type: restrictionType
						});
					}
				}}
			/>

			<style jsx>{`
				.splms-admin-form-restriction {
					position: relative;
				}

				.splms-form-restricted .splms-form-content {
					position: relative;
				}

				.splms-form-restricted .splms-form-content::after {
					content: '';
					position: absolute;
					top: 0;
					left: 0;
					right: 0;
					bottom: 0;
					background: rgba(255, 255, 255, 0.8);
					z-index: 10;
					pointer-events: all;
				}

				.splms-form-usage-indicator {
					margin-bottom: 16px;
				}

				.splms-usage-content {
					display: flex;
					align-items: center;
					justify-content: space-between;
					gap: 16px;
				}

				.splms-usage-text {
					flex: 1;
				}

				.splms-usage-text p {
					margin: 4px 0 0 0;
					font-size: 13px;
					opacity: 0.8;
				}

				.splms-form-warning {
					margin-bottom: 16px;
				}

				.splms-form-loading-overlay {
					position: absolute;
					top: 0;
					left: 0;
					right: 0;
					bottom: 0;
					background: rgba(255, 255, 255, 0.9);
					display: flex;
					align-items: center;
					justify-content: center;
					z-index: 20;
				}

				.splms-form-loading-content {
					display: flex;
					align-items: center;
					gap: 12px;
					background: #fff;
					padding: 16px 24px;
					border-radius: 6px;
					box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
				}

				.splms-form-offline-notice {
					margin-bottom: 16px;
				}

				.splms-offline-content {
					display: flex;
					align-items: center;
					gap: 8px;
				}

				@media (max-width: 768px) {
					.splms-usage-content {
						flex-direction: column;
						align-items: flex-start;
					}

					.splms-usage-upgrade-btn {
						margin-top: 8px;
					}
				}
			`}</style>
		</div>
	);
};

export default AdminFormRestriction;