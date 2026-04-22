/**
 * Admin Form Restriction Component
 *
 * Wraps admin forms. With a valid Pro license, all forms are unrestricted.
 *
 * @since [SPLMS_VERSION]
 */

import React from 'react';

/**
 * Admin Form Restriction Component.
 *
 * @param {Object} props - Component props.
 * @param {React.ReactNode} props.children - Form content to wrap.
 * @param {string} props.className - Additional CSS classes.
 * @return {JSX.Element} Form restriction wrapper.
 */
const AdminFormRestriction = ({ children, className = '' }) => {
	return (
		<div className={`splms-admin-form-restriction ${className}`.trim()}>
			<div className="splms-form-content">
				{children}
			</div>
		</div>
	);
};

export default AdminFormRestriction;
