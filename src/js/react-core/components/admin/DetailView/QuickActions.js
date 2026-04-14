/**
 * QuickActions component for sidebar quick links
 */

import React from 'react';
import { SplmsIcon } from '../../SplmsIcon';

const QuickActions = ( { actions = [] } ) => {
	if ( 0 === actions.length ) {
		return null;
	}

	return (
		<div className="splms-quick-actions">
			<ul className="quick-actions-list">
				{actions.map( ( action, index ) => (
					<li key={index} className="quick-action-item">
						{action.url ? (
							<a
								href={action.url}
								target={action.external ? '_blank' : '_self'}
								rel={action.external ? 'noopener noreferrer' : ''}
								onClick={action.onClick}
								className="quick-action-link"
							>
								<span className="quick-action-icon">
									{action.icon && <SplmsIcon mode="wp" icon={action.icon} size={18} />}
								</span>
								<span className="quick-action-label">{action.label}</span>
								{action.external && <span className="external-icon">→</span>}
							</a>
						) : (
							<button
								type="button"
								onClick={action.onClick}
								className="quick-action-button"
							>
								<span className="quick-action-icon">
									{action.icon && <SplmsIcon mode="wp" icon={action.icon} size={18} />}
								</span>
								<span className="quick-action-label">{action.label}</span>
							</button>
						)}
					</li>
				) )}
			</ul>
		</div>
	);
};

export default QuickActions;
