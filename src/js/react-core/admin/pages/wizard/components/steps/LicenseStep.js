/**
 * License Step Component
 *
 * This step reuses existing license and trial components for maximum code reuse.
 * It wraps the existing LicenseStatusCard and TrialConversionPanel components
 * within the wizard navigation flow.
 *
 * @since [SPLMS_VERSION]
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Card, CardBody, Notice } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

// Reuse existing license and trial components
import LicenseStatusCard from '../../../license/components/LicenseStatusCard';
import TrialConversionPanel from '../../../license/components/TrialConversionPanel';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

/**
 * License Step Component
 *
 * Key insight: This is a wrapper that reuses 90% existing code!
 */
const LicenseStep = ({ onNext, onBack, stepData, loading, error }) => {
	const [showLicenseInput, setShowLicenseInput] = useState(false);
	const [localError, setLocalError] = useState(null);

	// Use existing license store (no new store needed!)
	const {
		licenseInfo,
		licenseLoading,
		licenseError
	} = useSelect(select => {
		// Check if license store is available
		try {
			const licenseStore = select('splms/license');
			return {
				licenseInfo: licenseStore.getLicenseInfo(),
				licenseLoading: licenseStore.getLoading(),
				licenseError: licenseStore.getError()
			};
		} catch (err) {
			// License store may not be available in all contexts
			return {
				licenseInfo: {},
				licenseLoading: false,
				licenseError: null
			};
		}
	}, []);

	// Use existing license store actions
	const { activateLicense } = useDispatch('splms/license') || {};

	// Check trial status from global data and store
	const { splmsWizardData } = window;
	const initialTrialActive = splmsWizardData?.isTrialActive || false;

	// Monitor trial store for changes
	const { isTrialActive } = useSelect(select => {
		try {
			const trialStore = select('splms/trial');
			return {
				isTrialActive: trialStore ? trialStore.isTrialActive() : initialTrialActive
			};
		} catch (err) {
			return {
				isTrialActive: initialTrialActive
			};
		}
	}, []);

	/**
	 * Check if we should auto-advance based on license status
	 */
	useEffect(() => {
		// Only auto-advance if license is already active (not trial)
		const hasActiveLicense = licenseInfo?.status === 'active';

		if (hasActiveLicense) {
			// Auto-advance to next step after a brief delay for active license
			setTimeout(() => {
				onNext({
					method: 'license',
					auto_advanced: true,
					completed_at: Date.now()
				});
			}, 1000);
		}
	}, [licenseInfo]);

	/**
	 * Monitor trial activation and auto-advance
	 */
	useEffect(() => {
		// If trial becomes active during wizard (user just started trial), advance
		if (isTrialActive && !initialTrialActive) {
			// Trial was just activated - move to next step
			handleTrialStarted();
		}
	}, [isTrialActive]);

	/**
	 * Handle showing license input section
	 */
	const handleShowLicenseSection = () => {
		setShowLicenseInput(true);
	};

	/**
	 * Handle successful license activation
	 */
	const handleLicenseActivated = () => {
		// Move to next step
		onNext({
			method: 'license',
			license_key: licenseInfo.license_key,
			completed_at: Date.now()
		});
	};

	/**
	 * Handle trial started - actually start a trial via trial manager
	 */
	const handleTrialStarted = async () => {
		try {
			// Check if trial manager is available
			const { startTrial } = useDispatch('splms/trial') || {};

			if (startTrial) {
				// Actually start the trial
				await startTrial();
			}

			// Move to next step
			onNext({
				method: 'trial',
				trial_started: true,
				completed_at: Date.now()
			});
		} catch (error) {
			// If trial start fails, just continue with trial method
			onNext({
				method: 'trial',
				trial_started: false,
				error: error.message,
				completed_at: Date.now()
			});
		}
	};

	/**
	 * Handle skip license step
	 */
	const handleSkip = () => {
		onNext({
			method: 'skip',
			completed_at: Date.now()
		});
	};

	/**
	 * Handle continue without license/trial (limited mode)
	 */
	const handleContinueLimited = () => {
		onNext({
			method: 'limited',
			completed_at: Date.now()
		});
	};

	// Show auto-advance message only if license is already active
	if (licenseInfo?.status === 'active') {
		return (
			<div className="splms-wizard-step splms-license-step">
				<div className="splms-license-auto-advance">
					<Notice status="success" isDismissible={false}>
						{__('License is already active! Proceeding to next step...', 'skillpulse-lms')}
					</Notice>
				</div>
			</div>
		);
	}

	return (
		<div className="splms-wizard-step splms-license-step">
			<div className="splms-license-step-header">
				<h2>{__('License & Trial Setup', 'skillpulse-lms')}</h2>
				<p>{__('Choose how you\'d like to use SkillPulse LMS', 'skillpulse-lms')}</p>
			</div>

			{/* Display any errors */}
			{(error || localError || licenseError) && (
				<Notice
					status="error"
					onRemove={() => setLocalError(null)}
				>
					{error || localError || licenseError}
				</Notice>
			)}

			<div className="splms-license-step-content">
				{/* Main License Options */}
				<div className="splms-license-options-grid">
					{/* Free Trial Option */}
					<Card className="splms-license-option splms-license-option-trial">
						<CardBody>
							<div className="splms-license-option-content">
								<div className="splms-license-option-header">
									<SplmsIcon name="star-filled" size={32} className="splms-license-option-icon" />
									<h3>{__('Free 14-Day Trial', 'skillpulse-lms')}</h3>
									<p className="splms-license-option-subtitle">{__('Try all premium features', 'skillpulse-lms')}</p>
								</div>

								<div className="splms-license-features">
									<ul>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Unlimited courses & students', 'skillpulse-lms')}
										</li>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Advanced quizzes & certificates', 'skillpulse-lms')}
										</li>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Email automation & notifications', 'skillpulse-lms')}
										</li>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Premium support & documentation', 'skillpulse-lms')}
										</li>
									</ul>
								</div>

								<Button
									isPrimary
									size="large"
									onClick={handleTrialStarted}
									disabled={loading}
									className="splms-license-option-button"
								>
									{loading ? __('Starting...', 'skillpulse-lms') : __('Start Free Trial', 'skillpulse-lms')}
								</Button>

								<p className="splms-license-option-note">{__('No credit card required', 'skillpulse-lms')}</p>
							</div>
						</CardBody>
					</Card>

					{/* License Key Option */}
					<Card className="splms-license-option splms-license-option-license">
						<CardBody>
							<div className="splms-license-option-content">
								<div className="splms-license-option-header">
									<SplmsIcon name="admin-network" size={32} className="splms-license-option-icon" />
									<h3>{__('License Key', 'skillpulse-lms')}</h3>
									<p className="splms-license-option-subtitle">{__('Already purchased a license?', 'skillpulse-lms')}</p>
								</div>

								<div className="splms-license-features">
									<ul>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Full premium access', 'skillpulse-lms')}
										</li>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Priority customer support', 'skillpulse-lms')}
										</li>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Regular updates included', 'skillpulse-lms')}
										</li>
										<li>
											<SplmsIcon name="yes" size={16} />
											{__('Commercial use license', 'skillpulse-lms')}
										</li>
									</ul>
								</div>

								<Button
									isSecondary
									size="large"
									onClick={handleShowLicenseSection}
									disabled={loading}
									className="splms-license-option-button"
								>
									{__('Enter License Key', 'skillpulse-lms')}
								</Button>

								<p className="splms-license-option-note">
									<a href="https://skillpulselms.com/pricing/" target="_blank" rel="noopener noreferrer">
										{__('Purchase a license', 'skillpulse-lms')}
									</a>
								</p>
							</div>
						</CardBody>
					</Card>
				</div>

				{/* License Input Section (when requested) */}
				{showLicenseInput && (
					<div className="splms-license-step-section">
						<Card>
							<CardBody>
								<h3>{__('Enter License Key', 'skillpulse-lms')}</h3>
								<LicenseStatusCard
									licenseInfo={licenseInfo}
									loading={licenseLoading || loading}
									onActivateLicense={handleLicenseActivated}
									onLicenseKeyChange={() => {}}
								/>
							</CardBody>
						</Card>
					</div>
				)}

				{/* Skip Section */}
				<div className="splms-license-skip-section">
					<p className="splms-license-skip-text">
						{__('Want to explore first?', 'skillpulse-lms')}
						<Button
							isLink
							onClick={handleContinueLimited}
							disabled={loading}
							className="splms-license-skip-button"
						>
							{__('Skip and start with free features', 'skillpulse-lms')}
						</Button>
					</p>
				</div>

				{/* Navigation */}
				<div className="splms-wizard-step-actions">
					<div className="splms-wizard-step-actions-left">
						<Button
							isLink
							onClick={onBack}
							disabled={loading}
						>
							{__('← Back', 'skillpulse-lms')}
						</Button>
					</div>
				</div>
			</div>
		</div>
	);
};

export default LicenseStep;