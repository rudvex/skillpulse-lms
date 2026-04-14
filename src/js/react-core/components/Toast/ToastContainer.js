import React, { useState, useCallback } from 'react';
import Toast from './Toast';

/**
 * Toast Container Component
 * Manages multiple toast notifications
 */
const ToastContainer = () => {
    const [toasts, setToasts] = useState([]);

    const addToast = useCallback(({ message, type = 'info', duration = 5000, position = 'top-right' }) => {
        const id = Date.now() + Math.random();
        const newToast = { id, message, type, duration, position };
        
        setToasts(prev => [...prev, newToast]);
        
        return id;
    }, []);

    const removeToast = useCallback((id) => {
        setToasts(prev => prev.filter(toast => toast.id !== id));
    }, []);

    // Expose addToast method globally
    React.useEffect(() => {
        window.skillpulseToast = {
            show: addToast,
            success: (message, duration) => addToast({ message, type: 'success', duration }),
            error: (message, duration) => addToast({ message, type: 'error', duration }),
            warning: (message, duration) => addToast({ message, type: 'warning', duration }),
            info: (message, duration) => addToast({ message, type: 'info', duration })
        };
    }, [addToast]);

    return (
        <>
            {toasts.map(toast => (
                <Toast
                    key={toast.id}
                    message={toast.message}
                    type={toast.type}
                    duration={toast.duration}
                    position={toast.position}
                    onClose={() => removeToast(toast.id)}
                />
            ))}
        </>
    );
};

export default ToastContainer; 