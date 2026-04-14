/**
 * Welcome Step Component
 *
 * First step of the setup wizard. Provides introduction and overview
 * of what the wizard will accomplish.
 *
 * @since [SPLMS_VERSION]
 */

import { __ } from '@wordpress/i18n';
import { Button, Card, CardBody } from '@wordpress/components';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

/**
 * Welcome Step Component
 */
const WelcomeStep = ({ onNext, loading }) => {
	/**
	 * Handle getting started
	 */
	const handleGetStarted = () => {
		// Save that welcome step was viewed
		onNext({
			viewed: true,
			viewed_at: Date.now()
		});
	};

	return (
		<div className="splms-wizard-step splms-welcome-step">
			{/* Hero Section */}
			<div className="splms-welcome-hero">
				<div className="splms-welcome-icon">
					<SplmsIcon name="welcome" size={64} />
				</div>
				<h1 className="splms-welcome-title">
					{__('Welcome to SkillPulse LMS!', 'skillpulse-lms')}
				</h1>
				<p className="splms-welcome-subtitle">
					{__('Let\'s get your learning management system up and running in just a few quick steps.', 'skillpulse-lms')}
				</p>
			</div>

			{/* Features Overview */}
			<div className="splms-welcome-features">
				<h2 className="splms-welcome-features-title">
					{__('This wizard will help you:', 'skillpulse-lms')}
				</h2>

				<div className="splms-welcome-features-grid">
					<div className="splms-welcome-feature">
						<div className="splms-welcome-feature-icon">
							<SplmsIcon name="admin-network" size={24} />
						</div>
						<div className="splms-welcome-feature-content">
							<h3 className="splms-welcome-feature-title">
								{__('Create your first course', 'skillpulse-lms')}
							</h3>
							<p className="splms-welcome-feature-description">
								{__('Start building courses with lessons, quizzes, and certificates', 'skillpulse-lms')}
							</p>
						</div>
					</div>

					<div className="splms-welcome-feature">
						<div className="splms-welcome-feature-icon">
							<SplmsIcon name="admin-settings" size={24} />
						</div>
						<div className="splms-welcome-feature-content">
							<h3 className="splms-welcome-feature-title">
								{__('Configure basic settings', 'skillpulse-lms')}
							</h3>
							<p className="splms-welcome-feature-description">
								{__('Set up your site name, currency, timezone, and other essential options', 'skillpulse-lms')}
							</p>
						</div>
					</div>

					<div className="splms-welcome-feature">
						<div className="splms-welcome-feature-icon">
							<SplmsIcon name="admin-tools" size={24} />
						</div>
						<div className="splms-welcome-feature-content">
							<h3 className="splms-welcome-feature-title">
								{__('Review key features', 'skillpulse-lms')}
							</h3>
							<p className="splms-welcome-feature-description">
								{__('Learn about the powerful features available in your LMS', 'skillpulse-lms')}
							</p>
						</div>
					</div>
				</div>
			</div>

			{/* Time Estimate */}
			<div className="splms-welcome-time">
				<Card className="splms-welcome-time-card">
					<CardBody>
						<div className="splms-welcome-time-content">
							<div className="splms-welcome-time-icon">
								<SplmsIcon name="clock" size={20} />
							</div>
							<div className="splms-welcome-time-text">
								<strong>{__('Takes about 2-3 minutes', 'skillpulse-lms')}</strong>
							</div>
						</div>
					</CardBody>
				</Card>
			</div>

			{/* CTA Section */}
			<div className="splms-welcome-cta">
				<Button
					isPrimary
					size="large"
					onClick={handleGetStarted}
					disabled={loading}
					className="splms-welcome-get-started-btn"
				>
					<SplmsIcon name="arrow-right-alt" size={16} />
					{__('Get Started', 'skillpulse-lms')}
				</Button>

				<p className="splms-welcome-skip-note">
					{__('You can skip this setup and configure these settings later in the admin panel.', 'skillpulse-lms')}
				</p>
			</div>
		</div>
	);
};

export default WelcomeStep;