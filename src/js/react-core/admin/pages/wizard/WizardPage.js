/**
 * Main Wizard Page Component
 *
 * Main container for the SkillPulse LMS setup wizard.
 * Manages step navigation and coordinates with the license system.
 *
 * @since [SPLMS_VERSION]
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner, Notice } from '@wordpress/components';
import WizardContainer from './components/WizardContainer';
import WelcomeStep from './components/steps/WelcomeStep';
import BasicSetupStep from './components/steps/BasicSetupStep';
import FinishStep from './components/steps/FinishStep';

//import './styles/index.scss';

/**
 * Main Wizard Page Component
 */
const WizardPage = () => {
	const [currentStep, setCurrentStep] = useState('welcome');
	const [loading, setLoading] = useState(false);
	const [error, setError] = useState(null);
	const [stepData, setStepData] = useState({});
	const [transitioning, setTransitioning] = useState(false);

	// Get wizard data from localized script
	const { splmsWizardData } = window;

	// Steps configuration
	const steps = {
		welcome: {
			title: __('Welcome', 'skillpulse-lms'),
			component: WelcomeStep,
		},
		'basic-setup': {
			title: __('Basic Setup', 'skillpulse-lms'),
			component: BasicSetupStep,
		},
		finish: {
			title: __('Complete', 'skillpulse-lms'),
			component: FinishStep,
		},
	};

	const stepOrder = ['welcome', 'basic-setup', 'finish'];

	/**
	 * Initialize wizard state
	 */
	useEffect(() => {
		if (splmsWizardData?.currentStep) {
			setCurrentStep(splmsWizardData.currentStep);
		}
	}, []);

	/**
	 * Navigate to next step
	 */
	const nextStep = async (stepDataToSave = {}) => {
		const currentIndex = stepOrder.indexOf(currentStep);
		const nextIndex = currentIndex + 1;

		// Save current step data if provided
		if (Object.keys(stepDataToSave).length > 0) {
			await saveStepData(currentStep, stepDataToSave);
		}

		// Move to next step
		if (nextIndex < stepOrder.length) {
			setCurrentStep(stepOrder[nextIndex]);
		} else {
			// Complete wizard
			await completeWizard();
		}
	};

	/**
	 * Navigate to previous step
	 */
	const prevStep = () => {
		const currentIndex = stepOrder.indexOf(currentStep);
		const prevIndex = currentIndex - 1;

		if (prevIndex >= 0) {
			setCurrentStep(stepOrder[prevIndex]);
		}
	};

	/**
	 * Save step data via AJAX
	 */
	const saveStepData = async (step, data) => {
		setLoading(true);
		setError(null);

		try {
			const response = await wp.ajax.post('splms_wizard_save_step', {
				step,
				data,
				nonce: splmsWizardData.nonce,
			});

			// Update local state
			setStepData(prev => ({
				...prev,
				[step]: data,
			}));

			return response;
		} catch (err) {
			setError(err.message || __('Failed to save step data.', 'skillpulse-lms'));
			throw err;
		} finally {
			setLoading(false);
		}
	};

	/**
	 * Complete wizard
	 */
	const completeWizard = async () => {
		setLoading(true);
		setError(null);

		try {
			const response = await wp.ajax.post('splms_wizard_complete', {
				nonce: splmsWizardData.nonce,
			});

			// Redirect to dashboard
			if (response.redirect_url) {
				window.location.href = response.redirect_url;
			}
		} catch (err) {
			setError(err.message || __('Failed to complete wizard.', 'skillpulse-lms'));
		} finally {
			setLoading(false);
		}
	};

	/**
	 * Skip wizard
	 */
	const skipWizard = async () => {
		if (confirm(__('Are you sure you want to skip the setup wizard? You can access these settings later.', 'skillpulse-lms'))) {
			setLoading(true);
			setError(null);

			try {
				const response = await wp.ajax.post('splms_wizard_skip', {
					nonce: splmsWizardData.nonce,
				});

				// Redirect to dashboard
				if (response.redirect_url) {
					window.location.href = response.redirect_url;
				}
			} catch (err) {
				setError(err.message || __('Failed to skip wizard.', 'skillpulse-lms'));
			} finally {
				setLoading(false);
			}
		}
	};

	/**
	 * Get current step component
	 */
	const getCurrentStepComponent = () => {
		const StepComponent = steps[currentStep]?.component;

		if (!StepComponent) {
			return (
				<div className="splms-wizard-error">
					<Notice status="error" isDismissible={false}>
						{__('Invalid wizard step.', 'skillpulse-lms')}
					</Notice>
				</div>
			);
		}

		return (
			<StepComponent
				onNext={nextStep}
				onBack={prevStep}
				onSkip={skipWizard}
				stepData={stepData[currentStep] || {}}
				saveStepData={(data) => saveStepData(currentStep, data)}
				loading={loading}
				error={error}
			/>
		);
	};

	return (
		<div className="skillpulse-lms-user-admin">
			<div className="splms-container">
				<div className="splms-wizard-page">
					{/* Global Error Display */}
					{error && (
						<div className="splms-wizard-error">
							<Notice
								status="error"
								onRemove={() => setError(null)}
							>
								{error}
							</Notice>
						</div>
					)}

					{/* Global Loading Overlay */}
					{loading && (
						<div className="splms-loading-container">
							<Spinner />
							<p>{__('Processing...', 'skillpulse-lms')}</p>
						</div>
					)}

					{/* Wizard Container */}
					<WizardContainer
						currentStep={currentStep}
						steps={stepOrder}
						stepTitles={steps}
						onSkip={skipWizard}
					>
						{getCurrentStepComponent()}
					</WizardContainer>
				</div>
			</div>
		</div>
	);
};

export default WizardPage;