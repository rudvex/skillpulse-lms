/**
 * Quiz Utilities
 * Shared helper functions for quiz functionality
 * @module quiz-utils
 */

/**
 * Format seconds into human-readable time string
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time string (e.g., "1h 30m 15s")
 */
export function formatTime(seconds) {
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    
    if (hrs > 0) {
        return `${hrs}h ${mins}m ${secs}s`;
    } else if (mins > 0) {
        return `${mins}m ${secs}s`;
    } else {
        return `${secs}s`;
    }
}

/**
 * Format time for timer display (MM:SS)
 * @param {number} seconds - Time in seconds
 * @returns {string} Formatted time string (e.g., "05:30")
 */
export function formatTimerTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

/**
 * Debounce function to limit how often a function can be called
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Get namespaced storage key for quiz state
 * @param {number|string} quizId - Quiz ID
 * @returns {string} Storage key
 */
export function getStorageKey(quizId) {
    return `splms_quiz_${quizId}`;
}

/**
 * Save quiz state to localStorage
 * Uses SPLMSCore.helper.storage for consistent storage handling
 * @param {number|string} quizId - Quiz ID
 * @param {Object} state - State object to save
 * @returns {boolean} Success status
 */
export function saveToStorage(quizId, state) {
    try {
        const key = getStorageKey(quizId);
        // Use global SPLMSCore.helper storage with quiz-specific timestamp
        if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
            window.SPLMSCore.helper.storage.set(key, {
                ...state,
                savedAt: Date.now()
            });
            return true;
        }
        // Fallback to direct localStorage if SPLMSCore not available
        localStorage.setItem(key, JSON.stringify({
            ...state,
            savedAt: Date.now()
        }));
        return true;
    } catch (e) {
        console.warn('Failed to save quiz state to localStorage:', e);
        return false;
    }
}

/**
 * Load quiz state from localStorage
 * Uses SPLMSCore.helper.storage for consistent storage handling
 * @param {number|string} quizId - Quiz ID
 * @returns {Object|null} Saved state or null
 */
export function loadFromStorage(quizId) {
    try {
        const key = getStorageKey(quizId);
        let state = null;
        
        // Use global SPLMSCore.helper storage
        if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
            state = window.SPLMSCore.helper.storage.get(key, null);
        } else {
            // Fallback to direct localStorage if SPLMSCore not available
            const data = localStorage.getItem(key);
            state = data ? JSON.parse(data) : null;
        }
        
        if (!state) return null;
        
        // Check if state is expired (older than 7 days)
        const maxAge = 7 * 24 * 60 * 60 * 1000; // 7 days
        if (state.savedAt && (Date.now() - state.savedAt) > maxAge) {
            if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
                window.SPLMSCore.helper.storage.remove(key);
            } else {
                localStorage.removeItem(key);
            }
            return null;
        }
        return state;
    } catch (e) {
        console.warn('Failed to load quiz state from localStorage:', e);
        return null;
    }
}

/**
 * Clear quiz state from localStorage
 * Uses SPLMSCore.helper.storage for consistent storage handling
 * @param {number|string} quizId - Quiz ID
 */
export function clearStorage(quizId) {
    try {
        const key = getStorageKey(quizId);
        // Use global SPLMSCore.helper storage
        if (window.SPLMSCore && window.SPLMSCore.helper && window.SPLMSCore.helper.storage) {
            window.SPLMSCore.helper.storage.remove(key);
        } else {
            // Fallback to direct localStorage if SPLMSCore not available
            localStorage.removeItem(key);
        }
    } catch (e) {
        console.warn('Failed to clear quiz state from localStorage:', e);
    }
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML
 */
export function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Dispatch custom event
 * @param {string} eventName - Event name
 * @param {Object} detail - Event detail data
 */
export function dispatchEvent(eventName, detail = {}) {
    const event = new CustomEvent(eventName, {
        detail,
        bubbles: true,
        cancelable: true
    });
    document.dispatchEvent(event);
}

/**
 * Validate answer based on question type
 * @param {*} answer - User answer
 * @param {string} questionType - Question type
 * @returns {boolean} Whether answer is valid
 */
export function validateAnswer(answer, questionType) {
    if (!answer) return false;
    
    switch (questionType) {
        case 'multiple_select':
            return Array.isArray(answer) && answer.length > 0;
        case 'short_answer':
        case 'fill_blank':
        case 'essay':
        case 'long_answer':
            return typeof answer === 'string' && answer.trim().length > 0;
        case 'file_upload':
            return typeof answer === 'string' && answer.length > 0;
        case 'matching':
            return typeof answer === 'object' && answer !== null;
        case 'ordering':
            return Array.isArray(answer) && answer.length > 0;
        default:
            return answer !== '' && answer !== null && answer !== undefined;
    }
}

