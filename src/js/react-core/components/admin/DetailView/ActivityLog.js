/**
 * ActivityLog component for displaying timeline of events
 */

import React from 'react';
import { SplmsIcon } from '../../SplmsIcon';

const ActivityLog = ( { activities = [] } ) => {
	if ( 0 === activities.length ) {
		return null;
	}

	const getIcon = ( type ) => {
		switch ( type ) {
			case 'success':
				return 'yes-alt';
			case 'pending':
				return 'clock';
			case 'info':
				return 'info';
			case 'warning':
				return 'warning';
			case 'error':
				return 'dismiss';
			default:
				return 'marker';
		}
	};

	return (
		<div className="splms-activity-log">
			<div className="activity-timeline">
				{activities.map( ( activity, index ) => (
					<div
						key={index}
						className={`activity-item activity-${activity.type || 'default'}`}
					>
						<div className="activity-icon">
							<SplmsIcon mode="wp" icon={activity.icon || getIcon( activity.type )} size={16} />
						</div>
						<div className="activity-content">
							<div className="activity-title">{activity.title}</div>
							{activity.description && (
								<div className="activity-description">{activity.description}</div>
							)}
							{activity.timestamp && (
								<div className="activity-timestamp">{activity.timestamp}</div>
							)}
						</div>
					</div>
				) )}
			</div>
		</div>
	);
};

export default ActivityLog;
