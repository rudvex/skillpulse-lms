/**
 * Basic Setup Step Component
 *
 * Collects essential configuration settings for the LMS.
 * Simple form that saves basic site configuration.
 *
 * @since [SPLMS_VERSION]
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextControl, SelectControl, Card, CardBody, Notice } from '@wordpress/components';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

/**
 * Basic Setup Step Component
 */
const BasicSetupStep = ({ onNext, onBack, stepData, loading, error }) => {
	const [formData, setFormData] = useState({
		site_name: '',
		admin_email: '',
		timezone: 'UTC'
	});

	const [formErrors, setFormErrors] = useState({});
	const [isValid, setIsValid] = useState(false);

	// Get initial data from window object
	const { splmsWizardData } = window;

	/**
	 * Initialize form data
	 */
	useEffect(() => {
		const initialData = {
			site_name: stepData.site_name || splmsWizardData?.siteName || '',
			admin_email: stepData.admin_email || splmsWizardData?.adminEmail || '',
			timezone: stepData.timezone || splmsWizardData?.timezone || 'UTC'
		};
		setFormData(initialData);

		// Validate the initial data to enable/disable continue button
		const errors = validateForm(initialData);
		setFormErrors(errors);
		setIsValid(Object.keys(errors).length === 0);
	}, [stepData, splmsWizardData]);


	/**
	 * WordPress timezone options (from WordPress settings)
	 */
	const timezones = splmsWizardData?.timezones || [
		{ value: 'UTC', label: __('UTC', 'skillpulse-lms') },
		{ value: 'America/New_York', label: __('Eastern Time (US)', 'skillpulse-lms') },
		{ value: 'America/Chicago', label: __('Central Time (US)', 'skillpulse-lms') },
		{ value: 'America/Denver', label: __('Mountain Time (US)', 'skillpulse-lms') },
		{ value: 'America/Los_Angeles', label: __('Pacific Time (US)', 'skillpulse-lms') },
		{ value: 'Europe/London', label: __('London', 'skillpulse-lms') },
		{ value: 'Europe/Paris', label: __('Paris', 'skillpulse-lms') },
		{ value: 'Europe/Berlin', label: __('Berlin', 'skillpulse-lms') },
		{ value: 'Asia/Tokyo', label: __('Tokyo', 'skillpulse-lms') },
		{ value: 'Asia/Shanghai', label: __('Shanghai', 'skillpulse-lms') },
		{ value: 'Australia/Sydney', label: __('Sydney', 'skillpulse-lms') }
	];

	/**
	 * Validate form
	 */
	const validateForm = (data) => {
		const errors = {};

		if (!data.site_name.trim()) {
			errors.site_name = __('Site name is required.', 'skillpulse-lms');
		}

		if (!data.admin_email.trim()) {
			errors.admin_email = __('Admin email is required.', 'skillpulse-lms');
		} else if (!data.admin_email.includes('@')) {
			errors.admin_email = __('Please enter a valid email address.', 'skillpulse-lms');
		}

		return errors;
	};

	/**
	 * Handle form field changes
	 */
	const handleFieldChange = (field, value) => {
		const newFormData = {
			...formData,
			[field]: value
		};

		setFormData(newFormData);

		// Validate and update errors
		const errors = validateForm(newFormData);
		setFormErrors(errors);
		setIsValid(Object.keys(errors).length === 0);
	};

	/**
	 * Handle form submission
	 */
	const handleContinue = () => {
		const errors = validateForm(formData);
		setFormErrors(errors);

		if (Object.keys(errors).length === 0) {
			onNext({
				...formData,
				completed_at: Date.now()
			});
		}
	};

	/**
	 * Handle skip this step
	 */
	const handleSkip = () => {
		onNext({
			skipped: true,
			completed_at: Date.now()
		});
	};

	return (
		<div className="splms-wizard-step splms-basic-setup-step">
			<div className="splms-basic-setup-header">
				<h2>{__('Basic Configuration', 'skillpulse-lms')}</h2>
				<p>{__('Let\'s configure your LMS basics. You can change these settings anytime later.', 'skillpulse-lms')}</p>
			</div>

			{/* Display any errors */}
			{error && (
				<Notice status="error" isDismissible={false}>
					{error}
				</Notice>
			)}

			<div className="splms-basic-setup-content">
				<Card>
					<CardBody>
						<div className="splms-basic-setup-form">
							{/* Site Name */}
							<div className="splms-form-field">
								<TextControl
									label={__('LMS Name', 'skillpulse-lms')}
									help={__('This will be displayed throughout your learning management system.', 'skillpulse-lms')}
									value={formData.site_name}
									onChange={(value) => handleFieldChange('site_name', value)}
									placeholder={__('My Learning Academy', 'skillpulse-lms')}
									className={formErrors.site_name ? 'has-error' : ''}
									__next40pxDefaultSize
									__nextHasNoMarginBottom
								/>
								{formErrors.site_name && (
									<div className="splms-form-error">
										{formErrors.site_name}
									</div>
								)}
							</div>

							{/* Admin Email */}
							<div className="splms-form-field">
								<TextControl
									label={__('Admin Email', 'skillpulse-lms')}
									help={__('This email will receive important notifications about your LMS.', 'skillpulse-lms')}
									type="email"
									value={formData.admin_email}
									onChange={(value) => handleFieldChange('admin_email', value)}
									placeholder={__('admin@example.com', 'skillpulse-lms')}
									className={formErrors.admin_email ? 'has-error' : ''}
									__next40pxDefaultSize
									__nextHasNoMarginBottom
								/>
								{formErrors.admin_email && (
									<div className="splms-form-error">
										{formErrors.admin_email}
									</div>
								)}
							</div>

							{/* Timezone */}
							<div className="splms-form-field">
								<SelectControl
									label={__('Timezone', 'skillpulse-lms')}
									help={__('This affects course schedules and notification timing.', 'skillpulse-lms')}
									value={formData.timezone}
									options={timezones}
									onChange={(value) => handleFieldChange('timezone', value)}
									__next40pxDefaultSize
									__nextHasNoMarginBottom
								/>
							</div>
						</div>
					</CardBody>
				</Card>

				{/* Info Card */}
				<Card className="splms-basic-setup-info">
					<CardBody>
						<div className="splms-basic-setup-info-content">
							<SplmsIcon name="info" size={20} />
							<div className="splms-basic-setup-info-text">
								<strong>{__('Don\'t worry!', 'skillpulse-lms')}</strong>
								<p>{__('You can change all of these settings later in the admin panel under Settings > General.', 'skillpulse-lms')}</p>
							</div>
						</div>
					</CardBody>
				</Card>

				{/* Actions */}
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

					<div className="splms-wizard-step-actions-right">
						<Button
							isLink
							onClick={handleSkip}
							disabled={loading}
							className="splms-wizard-skip-button"
						>
							{__('Skip This Step', 'skillpulse-lms')}
						</Button>

						<Button
							isPrimary
							size="large"
							onClick={handleContinue}
							disabled={loading || !isValid}
							className="splms-wizard-continue-button"
						>
							{loading ? (
								__('Saving...', 'skillpulse-lms')
							) : (
								<>
									{__('Continue', 'skillpulse-lms')}
									<SplmsIcon name="arrowRight" size={16} />
								</>
							)}
						</Button>
					</div>
				</div>
			</div>
		</div>
	);
};

export default BasicSetupStep;