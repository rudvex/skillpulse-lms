import React from 'react';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import { SplmsIcon } from '../../components/SplmsIcon';

/**
 * Reusable Auto-Save Status Indicator Component
 */
const AutoSaveIndicator = ({ saveStatus, pendingCount, success }) => {
    switch (saveStatus) {
        case 'pending':
            return (
                <div className="auto-save-indicator pending">
                    <SplmsIcon mode="wp" name="clock" size={16} className="pending-icon" />
                    <span>
                        {pendingCount > 1 
                            ? __(`${pendingCount} changes pending...`, 'skillpulse-lms')
                            : __('Change pending...', 'skillpulse-lms')
                        }
                    </span>
                </div>
            );
            
        case 'saving':
            return (
                <div className="auto-save-indicator saving">
                    <Spinner style={{ width: '16px', height: '16px' }} />
                    <span>{__('Saving...', 'skillpulse-lms')}</span>
                </div>
            );
            
        case 'success':
            return (
                <div className="auto-save-indicator success">
                    <SplmsIcon mode="wp" name="saved" size={16} className="success-icon" />
                    <span>{success || __('Settings saved', 'skillpulse-lms')}</span>
                </div>
            );
            
        case 'error':
            return (
                <div className="auto-save-indicator error">
                    <SplmsIcon mode="wp" name="warning" size={16} className="error-icon" />
                    <span>{__('Save failed', 'skillpulse-lms')}</span>
                </div>
            );
            
        default:
            return null;
    }
};

export default AutoSaveIndicator;
