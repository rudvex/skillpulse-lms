/**
 * Admin App Component
 *
 * Main application wrapper for SkillPulse LMS admin interface.
 *
 * @since [SPLMS_VERSION]
 */

import React from 'react';

/**
 * Admin App Component.
 *
 * @param {Object} props - Component props.
 * @param {React.ReactNode} props.children - Child components.
 * @return {JSX.Element} Admin app component.
 */
const AdminApp = ({ children }) => {
	return (
		<div className="splms-admin-app">
			{children}
		</div>
	);
};

export default AdminApp;
