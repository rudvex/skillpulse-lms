import { __ } from '@wordpress/i18n';

export const StatusBadge = ({ status }) => {
    const statusClasses = {
        'active': 'status-active',
        'inactive': 'status-inactive',
        'completed': 'status-completed',
        'cancelled': 'status-cancelled',
        'suspended': 'status-suspended',
    };

    return (
        <span className={`enrollment-status ${statusClasses[status] || 'status-unknown'}`}>
            {status.charAt(0).toUpperCase() + status.slice(1)}
        </span>
    );
};

export const MethodBadge = ({ method }) => {
    const methodClasses = {
        'manual': 'method-manual',
        'self': 'method-self',
        'purchase': 'method-purchase',
        'admin': 'method-admin',
        'bulk': 'method-bulk',
    };

    const methodLabels = {
        'manual': __('Manual', 'skillpulse-lms'),
        'self': __('Self Enrolled', 'skillpulse-lms'),
        'purchase': __('Purchase', 'skillpulse-lms'),
        'admin': __('Admin', 'skillpulse-lms'),
        'bulk': __('Bulk Import', 'skillpulse-lms'),
    };

    return (
        <span className={`enrollment-method ${methodClasses[method] || 'method-unknown'}`}>
            {methodLabels[method] || method}
        </span>
    );
};

export const ProgressBar = ({ progress }) => {
    const percentage = Math.round(progress || 0);
    return (
        <div className="progress-container">
            <div className="progress-bar">
                <div
                    className="progress-fill"
                    style={{ width: `${percentage}%` }}
                ></div>
            </div>
            <span className="progress-text">{percentage}%</span>
        </div>
    );
}; 