import React, { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { SplmsIcon } from '../SplmsIcon';

/**
 * Toast Notification Component
 * Displays notifications in the top-right corner
 */
const Toast = ({ 
    message, 
    type = 'info', 
    duration = 5000, 
    onClose, 
    position = 'top-right' 
}) => {
    const [isVisible, setIsVisible] = useState(true);
    const [isExiting, setIsExiting] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            handleClose();
        }, duration);

        return () => clearTimeout(timer);
    }, [duration]);

    const handleClose = () => {
        setIsExiting(true);
        setTimeout(() => {
            setIsVisible(false);
            onClose && onClose();
        }, 300);
    };

    const getIcon = () => {
        switch (type) {
            case 'success':
                return 'yes-alt';
            case 'error':
                return 'dismiss';
            case 'warning':
                return 'warning';
            default:
                return 'info';
        }
    };

    const getToastClasses = () => {
        const baseClasses = 'splms-toast';
        const typeClasses = {
            success: 'splms-toast--success',
            error: 'splms-toast--error',
            warning: 'splms-toast--warning',
            info: 'splms-toast--info'
        };
        const positionClasses = {
            'top-right': 'splms-toast--top-right',
            'top-left': 'splms-toast--top-left',
            'bottom-right': 'splms-toast--bottom-right',
            'bottom-left': 'splms-toast--bottom-left'
        };

        return `${baseClasses} ${typeClasses[type] || typeClasses.info} ${positionClasses[position] || positionClasses['top-right']} ${isExiting ? 'splms-toast--exiting' : ''}`;
    };

    if (!isVisible) return null;

    return createPortal(
        <div className={getToastClasses()}>
            <div className="splms-toast__content">
                <div className="splms-toast__icon">
                    <SplmsIcon mode="wp" icon={getIcon()} />
                </div>
                <div className="splms-toast__message">
                    {message}
                </div>
                <button 
                    className="splms-toast__close" 
                    onClick={handleClose}
                    aria-label="Close notification"
                >
                    <SplmsIcon mode="wp" icon="no-alt" />
                </button>
            </div>
            <div className="splms-toast__progress">
                <div 
                    className="splms-toast__progress-bar"
                    style={{ 
                        animationDuration: `${duration}ms`,
                        animationPlayState: isExiting ? 'paused' : 'running'
                    }}
                />
            </div>
        </div>,
        document.body
    );
};

export default Toast; 