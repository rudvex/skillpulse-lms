/**
 * License Step Component
 *
 * This step handles license activation within the wizard.
 * Uses PHP-provided license data from splmsWizardData instead of a Redux store
 * to avoid bundle conflicts with the main admin scripts.
 *
 * @since [SPLMS_VERSION]
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Card, CardBody, CardHeader, TextControl, Spinner, Notice } from '@wordpress/components';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

/**
 * License Step Component
 */
const LicenseStep = ({ onNext, onBack, stepData, loading, error }) => {
	const { splmsWizardData } = window;

	const [licenseKey, setLicenseKey] = useState('');
	const [licenseInfo, setLicenseInfo] = useState(splmsWizardData?.licenseInfo || {});
	const [activating, setActivating] = useState(false);
	const [localError, setLocalError] = useState(null);

	const hasLicense = licenseInfo.license_key && licenseInfo.license_key.length > 0;
	const isActive = 'active' === licenseInfo.status && licenseInfo.domain_activated;

	/**
	 * Auto-advance when license is already active on mount.
	 */
	useEffect(() => {
		if ( isActive ) {
			setTimeout(() => {
				onNext({
					method: 'license',
					auto_advanced: true,
					completed_at: Date.now()
				});
			}, 1500);
		}
	}, []);

	/**
	 * Activate license via direct AJAX call.
	 */
	const handleActivateLicense = async () => {
		if ( ! licenseKey.trim() ) {
			return;
		}

		setActivating(true);
		setLocalError(null);

		try {
			const response = await fetch(splmsWizardData.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'splms_activate_license',
					license_key: licenseKey,
					nonce: splmsWizardData.licenseNonce
				})
			});

			const data = await response.json();

			if (data.success) {
				setLicenseInfo(data.data);

				// Auto-advance after successful activation.
				setTimeout(() => {
					onNext({
						method: 'license',
						license_key: licenseKey,
						completed_at: Date.now()
					});
				}, 1000);
			} else {
				setLocalError(data.data?.message || __('License activation failed.', 'skillpulse-lms'));
			}
		} catch (err) {
			setLocalError(__('An error occurred. Please try again.', 'skillpulse-lms'));
		} finally {
			setActivating(false);
		}
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
	if ( isActive ) {
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

	const isLoading = activating || loading;

	return (
		<div className="splms-wizard-step splms-license-step">
			<div className="splms-license-step-header">
				<h2>{__('License Setup', 'skillpulse-lms')}</h2>
				<p>{__('Activate your license to unlock all features', 'skillpulse-lms')}</p>
			</div>

			{/* Display any errors */}
			{(error || localError) && (
				<Notice
					status="error"
					onRemove={() => setLocalError(null)}
				>
					{error || localError}
				</Notice>
			)}

			<div className="splms-license-step-content">
				<div className="splms-license-step-section">
					<Card className="splms-license-status-card">
						<CardHeader>
							<h2>{__('Subscription Status', 'skillpulse-lms')}</h2>
						</CardHeader>
						<CardBody>
							<div className="splms-license-key-section">
								<TextControl
									label={__('Subscription Key:', 'skillpulse-lms')}
									value={licenseKey}
									onChange={setLicenseKey}
									placeholder={__('Enter your subscription key', 'skillpulse-lms')}
									disabled={isLoading}
									__next40pxDefaultSize
									__nextHasNoMarginBottom
								/>
							</div>

							<div className="splms-license-actions">
								<Button
									isPrimary
									onClick={handleActivateLicense}
									disabled={!licenseKey.trim() || isLoading}
									className="splms-activate-license-btn"
								>
									{isLoading ? <Spinner /> : __('Activate Subscription', 'skillpulse-lms')}
								</Button>
							</div>
						</CardBody>
					</Card>

					{/* Features list */}
					<div className="splms-license-features">
						<ul>
							<li>
								<SplmsIcon name="check" size={16} />
								{__('Full premium access', 'skillpulse-lms')}
							</li>
							<li>
								<SplmsIcon name="check" size={16} />
								{__('Priority customer support', 'skillpulse-lms')}
							</li>
							<li>
								<SplmsIcon name="check" size={16} />
								{__('Regular updates included', 'skillpulse-lms')}
							</li>
							<li>
								<SplmsIcon name="check" size={16} />
								{__('Commercial use license', 'skillpulse-lms')}
							</li>
						</ul>
					</div>

					<p className="splms-license-option-note">
						<a href="https://skillpulselms.com/pricing/" target="_blank" rel="noopener noreferrer">
							{__('Purchase a license', 'skillpulse-lms')}
						</a>
					</p>
				</div>

				{/* Skip Section */}
				<div className="splms-license-skip-section">
					<p className="splms-license-skip-text">
						{__('Want to explore first?', 'skillpulse-lms')}
						<Button
							isLink
							onClick={handleContinueFree}
							disabled={isLoading}
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
							disabled={isLoading}
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
