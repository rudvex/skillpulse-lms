/**
 * Finish Step Component
 *
 * Final step of the setup wizard. Shows completion message,
 * summary of what was accomplished, and clear next action.
 * Streamlined for better user experience and business focus.
 *
 * @since [SPLMS_VERSION]
 */

import { __ } from '@wordpress/i18n';
import { getSiteUrl, getAdminUrl, getPostTypeCreateUrl } from '../../../../../utility/url';
import { Button, Card, CardBody } from '@wordpress/components';
import { SplmsIcon } from '../../../../../components/SplmsIcon';

/**
 * Finish Step Component
 */
const FinishStep = ({ onNext, stepData, loading, wizardData }) => {
	const { splmsWizardData } = window;

	/**
	 * Get setup status and personalized messaging
	 */
	const getSetupStatus = () => {
		return {
			type: 'free',
			icon: 'yes-alt',
			color: 'primary',
			title: __('Setup Complete!', 'skillpulse-lms'),
			message: __('Your LMS is ready. Start creating courses and enrolling students.', 'skillpulse-lms'),
			nextStep: __('Create your first course', 'skillpulse-lms')
		};
	};

	/**
	 * Handle going to dashboard
	 */
	const handleGoToDashboard = async () => {
		try {
			// Complete wizard via AJAX
			const response = await wp.ajax.post('splms_wizard_complete', {
				nonce: splmsWizardData.nonce,
			});

			// Redirect to dashboard
			if (response.redirect_url) {
				window.location.href = response.redirect_url;
			} else {
				// Fallback redirect to dashboard
				const { siteUrl } = splmsWizardData || {};
				const mainUrl = splmsWizardData?.mainUrl;
				window.location.href = mainUrl;
			}
		} catch (err) {
			console.error('Failed to complete wizard:', err);
			// Fallback redirect even if completion fails
			const { siteUrl } = splmsWizardData || {};
			const mainUrl = splmsWizardData?.mainUrl;
			window.location.href = mainUrl;
		}
	};

	/**
	 * Quick actions for next steps
	 */
	const getQuickActions = () => {
		const { siteUrl } = splmsWizardData || {};
		const baseUrl = siteUrl || getSiteUrl();

		return [
			{
				title: __('Manage Students', 'skillpulse-lms'),
				description: __('Add students and manage enrollments', 'skillpulse-lms'),
				icon: 'groups',
				url: splmsWizardData?.dashboardUrl || `${baseUrl}/wp-admin/admin.php?page=skillpulse-lms`,
				primary: false
			},
			{
				title: __('View Documentation', 'skillpulse-lms'),
				description: __('Learn more about SkillPulse LMS features', 'skillpulse-lms'),
				icon: 'book',
				url: 'https://skillpulselms.com/docs',
				external: true,
				primary: false
			}
		];
	};

	const setupStatus = getSetupStatus();
	const quickActions = getQuickActions();

	return (
		<div className="splms-wizard-step splms-finish-step">
			{/* Success Hero */}
			<div className="splms-finish-hero">
				<div className="splms-finish-icon">
					<SplmsIcon name="yes-alt" size={64} className="splms-finish-success-icon" />
				</div>
				<h1 className="splms-finish-title">
					{'\ud83c\udf89 '}{__('Setup Complete!', 'skillpulse-lms')}
				</h1>
				<p className="splms-finish-subtitle">
					{__('Your SkillPulse LMS is ready to go. Let\'s start building amazing learning experiences!', 'skillpulse-lms')}
				</p>
			</div>

			{/* Status Summary */}
			<div className="splms-finish-status">
				<Card className={`splms-status-card splms-status-${setupStatus.type}`}>
					<CardBody>
						<div className="splms-finish-status-content">
							<div className="splms-finish-status-icon">
								<SplmsIcon name={setupStatus.icon} size={32} />
							</div>
							<div className="splms-finish-status-details">
								<h3 className="splms-finish-status-title">
									{setupStatus.title}
								</h3>
								<p className="splms-finish-status-message">
									{setupStatus.message}
								</p>
								<p className="splms-finish-status-next">
									{setupStatus.nextStep}
								</p>
							</div>
						</div>
					</CardBody>
				</Card>
			</div>

			{/* Primary Action */}
			<div className="splms-finish-primary-action">
				<Button
					isPrimary
					size="large"
					href={getPostTypeCreateUrl('sp-course')}
					className="splms-finish-primary-button"
				>
					<SplmsIcon name="book" size={16} />
					{__('Create Your First Course', 'skillpulse-lms')}
				</Button>
			</div>

			{/* Quick Actions */}
			<div className="splms-finish-actions">
				<h2 className="splms-finish-actions-title">
					{__('Quick Actions', 'skillpulse-lms')}
				</h2>
				<div className="splms-finish-actions-grid">
					{quickActions.map((action, index) => (
						<div key={index} className="splms-finish-action-item">
							<div className="splms-finish-action-icon">
								<SplmsIcon name={action.icon} size={20} />
							</div>
							<div className="splms-finish-action-content">
								<h4 className="splms-finish-action-title">
									{action.title}
								</h4>
								<p className="splms-finish-action-description">
									{action.description}
								</p>
							</div>
							<div className="splms-finish-action-button">
								<Button
									isLink
									href={action.url}
									target={action.external ? '_blank' : undefined}
									rel={action.external ? 'noopener noreferrer' : undefined}
								>
									{action.external ? __('Learn More', 'skillpulse-lms') : __('Go', 'skillpulse-lms')}
									{action.external && <SplmsIcon name="link" size={16} />}
								</Button>
							</div>
						</div>
					))}
				</div>
			</div>

			{/* Secondary Actions */}
			<div className="splms-finish-secondary">
				<Button
					isSecondary
					size="large"
					onClick={handleGoToDashboard}
					disabled={loading}
					className="splms-finish-dashboard-button"
				>
					<SplmsIcon mode="wp" name="dashboard" size={16} />
					{__('Go to Dashboard', 'skillpulse-lms')}
				</Button>

				<p className="splms-finish-help-text">
					{__('Need help getting started?', 'skillpulse-lms')}{' '}
					<a
						href="https://skillpulselms.com/docs/getting-started"
						target="_blank"
						rel="noopener noreferrer"
					>
						{__('Check out our getting started guide', 'skillpulse-lms')}
					</a>
				</p>
			</div>
		</div>
	);
};

export default FinishStep;