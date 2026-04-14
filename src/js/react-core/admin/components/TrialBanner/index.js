/**
 * Trial Banner Component
 *
s * Enhanced trial countdown and upgrade prompts with real-time restrictions.
 * Displays trial countdown and upgrade prompts across admin interface.
 *
 * @since [SPLMS_VERSION]
 */

import React, { useEffect, useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { Notice, Button, Spinner } from '@wordpress/components';
import { SplmsIcon } from '../../../components/SplmsIcon';
import { useTrialStatus } from '../TrialStatusProvider';
import TrialRestrictionOverlay from '../TrialRestrictionOverlay';

/**
 * Trial Banner Component.
 *
 * @return {JSX.Element|null} Enhanced trial banner component or null if trial not active.
 */
const TrialBanner = () => {
	// Use new trial status provider for real-time updates.
	const {
		isTrialActive,
		isTrialExpired,
		daysRemaining,
		isExpiringSoon,
		isExpiring,
		isLoading,
		error,
		shouldShowBanner,
		isConnected,
		forceRefresh
	} = useTrialStatus();

	const [showRestrictionModal, setShowRestrictionModal] = useState(false);
	const [lastRefresh, setLastRefresh] = useState(Date.now());

	// Fallback to legacy data store if trial status provider not available.
	const legacyData = useSelect((select) => {
		// Only use if trial status provider data isn't available.
		if (isTrialActive !== undefined) {
			return {};
		}

		const trialStore = select('splms/trial');
		if (!trialStore) {
			return {};
		}

		const daysLeft = trialStore.getTrialDaysRemaining();

		return {
			isTrialActive: trialStore.isTrialActive(),
			daysRemaining: daysLeft,
			isExpiringSoon: trialStore.isTrialExpiringSoon(),
			isExpiring: trialStore.isTrialExpiring(),
			isLoading: trialStore.getLoading(),
			error: trialStore.getError()
		};
	}, []);

	const { fetchTrialStatus } = useDispatch('splms/trial');

	// Use provider data if available, otherwise fallback to legacy.
	const trialData = isTrialActive !== undefined ? {
		isTrialActive,
		isTrialExpired,
		daysRemaining,
		isExpiringSoon,
		isExpiring,
		isLoading,
		error
	} : legacyData;

	// Auto-refresh every minute for real-time updates.
	useEffect(() => {
		const interval = setInterval(() => {
			if (isConnected) {
				forceRefresh();
				setLastRefresh(Date.now());
			}
		}, 60000);

		return () => clearInterval(interval);
	}, [forceRefresh, isConnected]);

	// Use appropriate data source.
	const currentData = trialData;

	// Don't render if trial is not active or if there's an error.
	if (!currentData.isTrialActive || currentData.error) {
		return null;
	}

	// Show loading state.
	if (currentData.isLoading) {
		return (
			<div className="splms-trial-banner splms-trial-banner--loading">
				<Spinner />
				<span>{__('Loading trial status...', 'skillpulse-lms')}</span>
			</div>
		);
	}

	// Don't show banner if trial expired (show overlay instead).
	if (currentData.isTrialExpired) {
		return (
			<TrialRestrictionOverlay
				isVisible={true}
				restrictionType="expired"
				allowDismiss={false}
				onUpgrade={() => {
					// Track upgrade interaction.
					if (window.gtag) {
						window.gtag('event', 'trial_upgrade_click', {
							source: 'expired_banner',
							days_remaining: currentData.daysRemaining
						});
					}
				}}
			/>
		);
	}

	/**
	 * Determine notice status based on urgency level.
	 *
	 * @return {string} Notice status.
	 */
	const getNoticeStatus = () => {
		if (currentData.isExpiring) {
			return 'error'; // <= 1 day
		}
		if (currentData.isExpiringSoon) {
			return 'warning'; // <= 3 days
		}
		return 'info'; // > 3 days
	};

	/**
	 * Get countdown message based on days remaining.
	 *
	 * @return {string} Countdown message.
	 */
	const getCountdownMessage = () => {
		if (currentData.daysRemaining === 0) {
			return __('Your trial expires today!', 'skillpulse-lms');
		}
		if (currentData.daysRemaining === 1) {
			return __('Your trial expires tomorrow!', 'skillpulse-lms');
		}
		return sprintf(
			/* translators: %d: number of days remaining */
			__('Your trial expires in %d days.', 'skillpulse-lms'),
			currentData.daysRemaining
		);
	};

	/**
	 * Get urgency message based on days remaining.
	 *
	 * @return {string} Urgency message.
	 */
	const getUrgencyMessage = () => {
		if (currentData.isExpiring) {
			return __('Upgrade now to keep your courses and data!', 'skillpulse-lms');
		}
		if (currentData.isExpiringSoon) {
			return __('Don\'t lose access to your content - upgrade today!', 'skillpulse-lms');
		}
		return __('Unlock unlimited features with a license!', 'skillpulse-lms');
	};

	/**
	 * Handle upgrade button click with analytics tracking.
	 */
	const handleUpgradeClick = () => {
		// Track upgrade interaction.
		if (window.gtag) {
			window.gtag('event', 'trial_upgrade_click', {
				source: 'banner',
				days_remaining: currentData.daysRemaining,
				is_expiring_soon: currentData.isExpiringSoon
			});
		}

		const licenseUrl = SPLMSCore_Data?.adminUrl + '/admin.php?page=splms-license' || '#';
		window.location.href = licenseUrl;
	};

	/**
	 * Handle refresh button click.
	 */
	const handleRefreshClick = () => {
		if (forceRefresh) {
			forceRefresh();
			setLastRefresh(Date.now());
		}
	};

	const noticeStatus = getNoticeStatus();
	const countdownMessage = getCountdownMessage();
	const urgencyMessage = getUrgencyMessage();

	return (
		<>
			<Notice
				status={noticeStatus}
				className="splms-trial-banner"
				isDismissible={false}
			>
				<div className="splms-trial-banner__content">
					<div className="splms-trial-banner__icon">
						<SplmsIcon
							mode="wp"
							name={currentData.isExpiring ? 'warning' : 'clock'}
							size={20}
							aria-label={__('Trial status', 'skillpulse-lms')}
						/>
					</div>
					<div className="splms-trial-banner__text">
						<strong className="splms-trial-banner__countdown">
							{countdownMessage}
						</strong>
						<span className="splms-trial-banner__message">
							{urgencyMessage}
						</span>
						{!isConnected && (
							<small className="splms-trial-banner__offline">
								{__('(Last updated offline)', 'skillpulse-lms')}
							</small>
						)}
					</div>
					<div className="splms-trial-banner__actions">
						<Button
							variant="primary"
							onClick={handleUpgradeClick}
							className="splms-trial-banner__upgrade-btn"
							aria-describedby="splms-trial-upgrade-help"
						>
							{__('Upgrade Now', 'skillpulse-lms')}
						</Button>
						{!isConnected && forceRefresh && (
							<Button
								variant="secondary"
								size="small"
								onClick={handleRefreshClick}
								className="splms-trial-banner__refresh-btn"
								title={__('Retry connection', 'skillpulse-lms')}
							>
								<SplmsIcon
									mode="wp"
									name="update"
									size={14}
								/>
							</Button>
						)}
					</div>
				</div>
				<div id="splms-trial-upgrade-help" className="screen-reader-text">
					{__('Navigate to license page to upgrade your trial account', 'skillpulse-lms')}
				</div>
			</Notice>

			{/* Show restriction modal if requested */}
			{showRestrictionModal && (
				<TrialRestrictionOverlay
					isVisible={showRestrictionModal}
					restrictionType="limit_reached"
					allowDismiss={true}
					onDismiss={() => setShowRestrictionModal(false)}
					onUpgrade={() => {
						setShowRestrictionModal(false);
						handleUpgradeClick();
					}}
				/>
			)}

			<style jsx>{`
				.splms-trial-banner__content {
					display: flex;
					align-items: center;
					gap: 16px;
				}

				.splms-trial-banner__text {
					flex: 1;
				}

				.splms-trial-banner__offline {
					display: block;
					color: #8c8f94;
					font-style: italic;
				}

				.splms-trial-banner__actions {
					display: flex;
					align-items: center;
					gap: 8px;
				}

				@media (max-width: 768px) {
					.splms-trial-banner__content {
						flex-direction: column;
						align-items: flex-start;
						gap: 12px;
					}

					.splms-trial-banner__actions {
						align-self: stretch;
						justify-content: center;
					}
				}
			`}</style>
		</>
	);
};

export default TrialBanner;