/**
 * Wizard Container Component
 *
 * Provides the main layout and navigation for the setup wizard.
 * Includes progress indicator, header, and step content area.
 *
 * @since [SPLMS_VERSION]
 */

import { __ } from '@wordpress/i18n';
import { Button, Card, CardHeader, CardBody } from '@wordpress/components';
import { SplmsIcon } from '../../../../components/SplmsIcon';
import BrandLogo from '../../../../components/BrandLogo';

/**
 * Wizard Container Component
 */
const WizardContainer = ({
	children,
	currentStep,
	steps,
	stepTitles,
	onSkip
}) => {
	/**
	 * Get current step index
	 */
	const getCurrentStepIndex = () => {
		return steps.indexOf(currentStep) + 1;
	};

	/**
	 * Get total steps count
	 */
	const getTotalSteps = () => {
		return steps.length;
	};

	/**
	 * Render progress indicator
	 */
	const renderProgressIndicator = () => {
		return (
			<div className="splms-wizard-progress">
				<div className="splms-wizard-progress-bar">
					{steps.map((step, index) => (
						<div
							key={step}
							className={`splms-wizard-progress-step ${
								index < getCurrentStepIndex() - 1 ? 'completed' :
								index === getCurrentStepIndex() - 1 ? 'current' : 'pending'
							}`}
						>
							<div className="splms-wizard-progress-circle">
								{index < getCurrentStepIndex() - 1 ? (
									<SplmsIcon mode="wp" name="yes" size={16} />
								) : (
									<span>{index + 1}</span>
								)}
							</div>
							<div className="splms-wizard-progress-label">
								{stepTitles[step]?.title || step}
							</div>
						</div>
					))}
				</div>
				<div className="splms-wizard-progress-text">
					{__('Step %d of %d', 'skillpulse-lms').replace('%d', getCurrentStepIndex()).replace('%d', getTotalSteps())}
				</div>
			</div>
		);
	};

	return (
		<div className="splms-wizard-container">
			{/* Header */}
			<header className="splms-wizard-header">
				<div className="splms-wizard-header-content">
					<div className="splms-wizard-header-logo">
						<BrandLogo />
						<div className="splms-wizard-header-separator" />
						<h1 className="splms-wizard-header-title">
							{__('Setup Wizard', 'skillpulse-lms')}
						</h1>
					</div>
					<div className="splms-wizard-header-actions">
						<Button
							isLink
							onClick={onSkip}
							className="splms-wizard-skip-button"
						>
							{__('Skip Setup', 'skillpulse-lms')}
						</Button>
						<Button
							isLink
							href="https://skillpulselms.com/docs/setup"
							target="_blank"
							rel="noopener noreferrer"
							className="splms-wizard-help-button"
						>
							<SplmsIcon name="help" size={16} />
							{__('Help', 'skillpulse-lms')}
						</Button>
					</div>
				</div>
			</header>

			{/* Progress Indicator */}
			{renderProgressIndicator()}

			{/* Main Content */}
			<main className="splms-content">
				<div className="splms-card">
					<div className="components-card__body">
						{children}
					</div>
				</div>
			</main>

			{/* Footer */}
			<footer className="splms-wizard-footer">
				<div className="splms-wizard-footer-content">
					<p className="splms-wizard-footer-text">
						{__('Need help? Visit our', 'skillpulse-lms')}{' '}
						<a
							href="https://skillpulselms.com/docs"
							target="_blank"
							rel="noopener noreferrer"
						>
							{__('documentation', 'skillpulse-lms')}
						</a>{' '}
						{__('or', 'skillpulse-lms')}{' '}
						<a
							href="https://skillpulselms.com/support"
							target="_blank"
							rel="noopener noreferrer"
						>
							{__('contact support', 'skillpulse-lms')}
						</a>
					</p>
				</div>
			</footer>
		</div>
	);
};

export default WizardContainer;