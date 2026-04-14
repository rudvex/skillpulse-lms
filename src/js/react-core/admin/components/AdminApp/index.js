/**
 * Admin App Component
 *
 * Main application wrapper for SkillPulse LMS admin interface with enhanced
 * trial status management and real-time restrictions.
 *
 * Example usage of the new trial system components.
 *
 * @since [SPLMS_VERSION]
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import TrialStatusProvider from '../TrialStatusProvider';
import TrialBanner from '../TrialBanner';
import AdminFormRestriction from '../AdminFormRestriction';
import TrialUsageIndicator from '../TrialUsageIndicator';

/**
 * Admin App Component.
 *
 * @param {Object} props - Component props.
 * @param {React.ReactNode} props.children - Child components.
 * @param {boolean} props.enableTrialSystem - Whether to enable trial system.
 * @param {number} props.refreshInterval - Trial status refresh interval in ms.
 * @return {JSX.Element} Admin app component.
 */
const AdminApp = ({
	children,
	enableTrialSystem = true,
	refreshInterval = 60000 // 1 minute
}) => {
	// Don't wrap with trial provider if disabled.
	if (!enableTrialSystem) {
		return (
			<div className="splms-admin-app">
				{children}
			</div>
		);
	}

	return (
		<TrialStatusProvider
			refreshInterval={refreshInterval}
			enableAutoRefresh={true}
		>
			<div className="splms-admin-app">
				{/* Trial banner should appear at top of admin */}
				<TrialBanner />

				{/* Main content area with form restrictions */}
				<AdminFormRestriction
					feature="courses" // Adjust based on current page
					action="create"
					className="splms-main-content-wrapper"
				>
					{children}
				</AdminFormRestriction>
			</div>
		</TrialStatusProvider>
	);
};

/**
 * Course Management Page Component
 *
 * Example of how to use trial components in a specific admin page.
 */
export const CourseManagementPage = () => {
	return (
		<AdminApp>
			<div className="splms-course-management">
				<div className="splms-page-header">
					<h1>{__('Course Management', 'skillpulse-lms')}</h1>

					{/* Usage indicator for courses */}
					<TrialUsageIndicator
						feature="courses"
						label={__('Courses', 'skillpulse-lms')}
						helpText={__('Number of courses you can create during your trial.', 'skillpulse-lms')}
						className="splms-course-usage"
					/>
				</div>

				{/* Course form with automatic restriction handling */}
				<AdminFormRestriction
					feature="courses"
					action="create"
					onRestricted={(data) => {
						console.log('Course creation restricted:', data);
					}}
				>
					<form id="course-creation-form">
						<div className="form-field">
							<label htmlFor="course-title">
								{__('Course Title', 'skillpulse-lms')}
							</label>
							<input
								type="text"
								id="course-title"
								name="course_title"
								required
							/>
						</div>

						<div className="form-field">
							<label htmlFor="course-description">
								{__('Course Description', 'skillpulse-lms')}
							</label>
							<textarea
								id="course-description"
								name="course_description"
								rows="4"
							/>
						</div>

						<div className="form-actions">
							<button type="submit" className="button button-primary">
								{__('Create Course', 'skillpulse-lms')}
							</button>
						</div>
					</form>
				</AdminFormRestriction>
			</div>
		</AdminApp>
	);
};

/**
 * Enrollment Management Page Component
 *
 * Another example showing multiple usage indicators.
 */
export const EnrollmentManagementPage = () => {
	return (
		<AdminApp>
			<div className="splms-enrollment-management">
				<div className="splms-page-header">
					<h1>{__('Enrollment Management', 'skillpulse-lms')}</h1>

					{/* Multiple usage indicators */}
					<div className="splms-usage-indicators">
						<TrialUsageIndicator
							feature="enrollments"
							label={__('Student Enrollments', 'skillpulse-lms')}
							helpText={__('Total number of student enrollments allowed.', 'skillpulse-lms')}
						/>

						<TrialUsageIndicator
							feature="courses"
							label={__('Available Courses', 'skillpulse-lms')}
							helpText={__('Courses available for enrollment.', 'skillpulse-lms')}
							showUpgradeLink={false}
						/>
					</div>
				</div>

				<AdminFormRestriction
					feature="enrollments"
					action="create"
					restrictionMessage={__('Enrollment management is limited during trial.', 'skillpulse-lms')}
				>
					<div className="splms-enrollment-tools">
						<p>{__('Enrollment management tools will appear here.', 'skillpulse-lms')}</p>
					</div>
				</AdminFormRestriction>
			</div>
		</AdminApp>
	);
};

/**
 * Dashboard Widget Component
 *
 * Example of trial status in dashboard widget.
 */
export const TrialDashboardWidget = () => {
	return (
		<TrialStatusProvider>
			<div className="splms-trial-dashboard-widget">
				<h3>{__('Trial Status', 'skillpulse-lms')}</h3>

				{/* Compact trial banner for dashboard */}
				<TrialBanner />

				{/* Quick usage overview */}
				<div className="splms-quick-usage">
					<TrialUsageIndicator
						feature="courses"
						label={__('Courses', 'skillpulse-lms')}
						className="splms-compact-usage"
						showUpgradeLink={false}
					/>

					<TrialUsageIndicator
						feature="enrollments"
						label={__('Students', 'skillpulse-lms')}
						className="splms-compact-usage"
						showUpgradeLink={false}
					/>
				</div>
			</div>
		</TrialStatusProvider>
	);
};

export default AdminApp;