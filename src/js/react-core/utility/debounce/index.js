/**
 * Debounce utility for auto-save functionality
 */

// Import auto-save styles when this module is loaded
import './auto-save.scss';

// Export the reusable component
export { default as AutoSaveIndicator } from './AutoSaveIndicator';

/**
 * Creates a debounced function that delays invoking func until after wait milliseconds
 * have elapsed since the last time the debounced function was invoked.
 *
 * @param {Function} func The function to debounce
 * @param {number} wait The number of milliseconds to delay
 * @param {boolean} immediate Whether to execute immediately on the leading edge
 * @returns {Function} The debounced function
 */
export function debounce(func, wait, immediate = false) {
    let timeout;
    
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        
        const callNow = immediate && !timeout;
        
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func(...args);
    };
}

/**
 * Auto-save manager class for handling debounced saves with visual feedback
 */
export class AutoSaveManager {
    constructor(saveFunction, options = {}) {
        this.saveFunction = saveFunction;
        this.options = {
            delay: options.delay || 1500, // Default 1.5 seconds
            showFeedback: options.showFeedback !== false, // Default true
            onSaveStart: options.onSaveStart || (() => {}),
            onSaveSuccess: options.onSaveSuccess || (() => {}),
            onSaveError: options.onSaveError || (() => {}),
            ...options
        };

        this.pendingChanges = new Map();
        this.isDebouncing = false;
        this.savePromise = null;
        this.isSavingInProgress = false;

        // Create debounced save function with cancel support
        this.debouncedSave = debounceWithCancel(
            this.executeSave.bind(this),
            this.options.delay
        );
    }
    
    /**
     * Queue a field for auto-save
     * @param {string} fieldId Field identifier
     * @param {any} value Field value
     * @param {Object} metadata Additional metadata for the save
     */
    queueSave(fieldId, value, metadata = {}) {
        // Store the pending change
        this.pendingChanges.set(fieldId, { value, metadata, timestamp: Date.now() });
        
        // Mark as debouncing
        this.isDebouncing = true;
        
        // Trigger debounced save
        this.debouncedSave();
        
        // Show immediate feedback that changes are pending
        if (this.options.showFeedback) {
            this.options.onSaveStart({
                fieldId,
                value,
                isPending: true,
                pendingCount: this.pendingChanges.size
            });
        }
    }
    
    /**
     * Execute the actual save operation
     */
    async executeSave() {
        if (this.pendingChanges.size === 0) {
            return;
        }

        // Prevent race condition: if a save is already in progress, queue changes for next batch.
        if (this.isSavingInProgress) {
            console.log('Save already in progress, changes will be saved in next batch');
            // Re-trigger debounced save to handle queued changes after current save completes.
            this.debouncedSave();
            return;
        }

        // Mark as saving.
        this.isSavingInProgress = true;

        // Get all pending changes.
        const changes = new Map(this.pendingChanges);

        // Clear pending changes.
        this.pendingChanges.clear();
        this.isDebouncing = false;

        try {
            // Show saving feedback.
            if (this.options.showFeedback) {
                this.options.onSaveStart({
                    isPending: false,
                    isSaving: true,
                    changeCount: changes.size
                });
            }

            // Execute the save function with all changes.
            this.savePromise = this.saveFunction(changes);
            const result = await this.savePromise;

            // Show success feedback.
            if (this.options.showFeedback) {
                this.options.onSaveSuccess({
                    result,
                    changeCount: changes.size,
                    savedFields: Array.from(changes.keys())
                });
            }

            return result;

        } catch (error) {
            console.error('Auto-save error:', error);

            // Show error feedback.
            if (this.options.showFeedback) {
                this.options.onSaveError({
                    error,
                    changeCount: changes.size,
                    failedFields: Array.from(changes.keys())
                });
            }

            throw error;
        } finally {
            this.savePromise = null;
            this.isSavingInProgress = false;
        }
    }
    
    /**
     * Force save all pending changes immediately
     */
    async forceSave() {
        // Cancel any pending debounced save
        if (this.debouncedSave.cancel) {
            this.debouncedSave.cancel();
        }
        
        // Execute save immediately
        return await this.executeSave();
    }
    
    /**
     * Check if there are pending changes
     */
    hasPendingChanges() {
        return this.pendingChanges.size > 0;
    }
    
    /**
     * Check if currently saving
     */
    isSaving() {
        return this.savePromise !== null;
    }
    
    /**
     * Get pending changes count
     */
    getPendingCount() {
        return this.pendingChanges.size;
    }
    
    /**
     * Clear all pending changes without saving
     */
    clearPendingChanges() {
        this.pendingChanges.clear();
        this.isDebouncing = false;
    }
}

/**
 * Enhanced debounce with cancel functionality
 */
export function debounceWithCancel(func, wait, immediate = false) {
    let timeout;
    
    const debounced = function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        
        const callNow = immediate && !timeout;
        
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func(...args);
    };
    
    debounced.cancel = function() {
        clearTimeout(timeout);
        timeout = null;
    };
    
    return debounced;
}
