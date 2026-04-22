/**
 * License Step Component
 *
 * This step reuses existing license components for maximum code reuse.
 * It wraps the existing LicenseStatusCard component
 * within the wizard navigation flow.
 *
 * @since [SPLMS_VERSION]
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Card, CardBody, Notice } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

// Reuse existing license components
import LicenseStatusCard from '../../../license/components/LicenseStatusCard';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

/**
 * License Step Component
 */
const LicenseStep = ({ onNext, onBack, stepData, loading, error }) => {
	const [showLicenseInput, setShowLicenseInput] = useState(false);
	const [localError, setLocalError] = useState(null);

	// Use existing license store.
	const {
		licenseInfo,
		licenseLoading,
		licenseError
	} = useSelect(select => {
		try {
			const licenseStore = select('splms/license');
			return {
				licenseInfo: licenseStore.getLicenseInfo(),
				licenseLoading: licenseStore.getLoading(),
				licenseError: licenseStore.getError()
			};
		} catch (err) {
			return {
				licenseInfo: {},
				licenseLoading: false,
				licenseError: null
			};
		}
	}, []);

	// Use existing license store actions.
	const { activateLicense } = useDispatch('splms/license') || {};

	/**
	 * Check if we should auto-advance based on license status.
	 */
	useEffect(() => {
		if ( 'active' === licenseInfo?.status ) {
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
	 * Handle showing license input section.
	 */
	const handleShowLicenseSection = () => {
		setShowLicenseInput(true);
	};

	/**
	 * Handle successful license activation.
	 */
	const handleLicenseActivated = () => {
		onNext({
			method: 'license',
			license_key: licenseInfo.license_key,
			completed_at: Date.now()
		});
	};

	/**
	 * Handle skip license step.
	 */
	const handleSkip = () => {
		onNext({
			method: 'skip',
			completed_at: Date.now()
		});
	};

	/**
	 * Handle continue without license (limited mode).
	 */
	const handleContinueLimited = () => {
		onNext({
			method: 'limited',
			completed_at: Date.now()
		});
	};

	// Show auto-advance message only if license is already active.
	if ( 'active' === licenseInfo?.status ) {
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
				<h2>{__('License Setup', 'skillpulse-lms')}</h2>
				<p>{__('Activate your license to unlock all features', 'skillpulse-lms')}</p>
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
				{/* License Key Option */}
				<div className="splms-license-options-grid">
					<Card className="splms-license-option splms-license-option-license">
						<CardBody>
							<div className="splms-license-option-content">
								<div className="splms-license-option-header">
									<SplmsIcon name="admin-network" size={32} className="splms-license-option-icon" />
									<h3>{__('License Key', 'skillpulse-lms')}</h3>
									<p className="splms-license-option-subtitle">{__('Activate your license to get started', 'skillpulse-lms')}</p>
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
									isPrimary
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
							{__('\u2190 Back', 'skillpulse-lms')}
						</Button>
					</div>
				</div>
			</div>
		</div>
	);
};

export default LicenseStep;
