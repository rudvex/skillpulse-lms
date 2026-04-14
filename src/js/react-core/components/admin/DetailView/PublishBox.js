/**
 * PublishBox component for WordPress-style sidebar publish meta box
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { SplmsIcon } from '../../SplmsIcon';

const PublishBox = ( {
	status = null,
	actions = [],
	metadata = [],
} ) => {
	return (
		<div className="splms-publish-box">
			{/* Status Badge */}
			{status && (
				<div className="publish-status">
					{status.type === 'success' && <span className="status-badge status-success">{status.label}</span>}
					{status.type === 'warning' && <span className="status-badge status-warning">{status.label}</span>}
					{status.type === 'error' && <span className="status-badge status-error">{status.label}</span>}
					{status.type === 'info' && <span className="status-badge status-info">{status.label}</span>}
					{! status.type && <span className="status-badge">{status.label}</span>}
				</div>
			)}

			{/* Actions */}
			{actions.length > 0 && (
				<div className="publish-actions">
					{actions.map( ( action, index ) => (
						<Button
							key={index}
							isPrimary={action.isPrimary}
							isSecondary={action.isSecondary}
							isDestructive={action.isDestructive}
							onClick={action.onClick}
							className="publish-action-button"
							disabled={action.disabled}
						>
							{action.icon && <SplmsIcon mode="wp" icon={action.icon} />}
							{action.label}
						</Button>
					) )}
				</div>
			)}

			{/* Metadata */}
			{metadata.length > 0 && (
				<div className="publish-metadata">
					{metadata.map( ( item, index ) => (
						<div key={index} className="metadata-item">
							<span className="metadata-label">{item.label}:</span>
							<span className="metadata-value">{item.value}</span>
						</div>
					) )}
				</div>
			)}
		</div>
	);
};

export default PublishBox;
