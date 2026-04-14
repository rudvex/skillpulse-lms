/**
 * DetailView header component with back navigation, title, and actions
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { SplmsIcon } from '../../SplmsIcon';

const DetailHeader = ({
	title = '',
	subtitle = '',
	icon,
	onBack,
	backLabel = __( 'Back', 'skillpulse-lms' ),
	actions = [],
	status,
}) => {
	const getStatusClass = ( type ) => {
		const statusClasses = {
			success: 'splms-status-success',
			warning: 'splms-status-warning',
			error: 'splms-status-error',
			info: 'splms-status-info',
		};
		return statusClasses[type] || 'splms-status-info';
	};

	return (
		<div className="splms-detail-header">
			{/* Back Navigation */}
			{onBack && (
				<div className="splms-detail-back">
					<Button
						onClick={onBack}
						className="splms-back-button"
					>
						<SplmsIcon mode="wp" icon="arrow-left-alt2" />
						{backLabel}
					</Button>
				</div>
			)}

			{/* Title and Status */}
			<div className="splms-detail-title-row">
				<div className="splms-detail-title">
					{icon && <SplmsIcon mode="wp" icon={icon} size={24} />}
					<div>
						<h1>{title}</h1>
						{subtitle && <p className="splms-detail-subtitle">{subtitle}</p>}
					</div>
				</div>

				<div className="splms-detail-meta">
					{status && (
						<span className={`splms-detail-status ${getStatusClass( status.type )}`}>
							{status.label}
						</span>
					)}

					{actions.length > 0 && (
						<div className="splms-detail-actions">
							{actions.map( ( action, index ) => (
								<Button
									key={index}
									onClick={action.onClick}
									isPrimary={action.isPrimary}
									isSecondary={action.isSecondary}
									isDestructive={action.isDestructive}
									disabled={action.disabled}
									title={action.title}
								>
									{action.icon && <SplmsIcon mode="wp" icon={action.icon} />}
									{action.label}
								</Button>
							) )}
						</div>
					)}
				</div>
			</div>
		</div>
	);
};

export default DetailHeader;
