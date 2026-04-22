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
import { useSelect } from '@wordpress/data';

// Reuse existing license components.
import LicenseStatusCard from '../../../license/components/LicenseStatusCard';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

/**
 * License Step Component
 */
const LicenseStep = ({ onNext, onBack, stepData, loading, error }) => {
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

	/**
	 * Auto-advance when license becomes active.
	 */
	useEffect(() => {
		if ( 'active' === licenseInfo?.status && licenseInfo?.domain_activated ) {
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
	 * Handle continue without license (free mode).
	 */
	const handleContinueFree = () => {
		onNext({
			method: 'skip',
			completed_at: Date.now()
		});
	};

	// Show auto-advance message if license is already active.
	if ( 'active' === licenseInfo?.status && licenseInfo?.domain_activated ) {
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
				{/* License activation card - shown directly */}
				<div className="splms-license-step-section">
					<Card className="splms-license-option splms-license-option-license">
						<CardBody>
							<div className="splms-license-option-header">
								<SplmsIcon name="admin-network" size={32} className="splms-license-option-icon" />
								<h3>{__('Activate Your License', 'skillpulse-lms')}</h3>
								<p className="splms-license-option-subtitle">{__('Enter your license key to unlock all pro features', 'skillpulse-lms')}</p>
							</div>

							<LicenseStatusCard
								licenseInfo={licenseInfo}
								loading={licenseLoading || loading}
								onActivateLicense={handleLicenseActivated}
								onLicenseKeyChange={() => {}}
							/>

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

							<p className="splms-license-option-note">
								<a href="https://skillpulselms.com/pricing/" target="_blank" rel="noopener noreferrer">
									{__('Purchase a license', 'skillpulse-lms')}
								</a>
							</p>
						</CardBody>
					</Card>
				</div>

				{/* Skip Section */}
				<div className="splms-license-skip-section">
					<p className="splms-license-skip-text">
						{__('Want to explore first?', 'skillpulse-lms')}
						<Button
							isLink
							onClick={handleContinueFree}
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
