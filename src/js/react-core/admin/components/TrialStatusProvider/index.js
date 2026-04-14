/**
 * Trial Status Provider Component
 *
 * Provides real-time trial status management and updates across all admin components.
 * Uses React Context API to share trial state and includes auto-refresh capabilities.
 *
 * @since [SPLMS_VERSION]
 */

import React, { createContext, useContext, useEffect, useReducer, useRef } from 'react';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Trial Status Context for sharing state across components.
 */
const TrialStatusContext = createContext();

/**
 * Initial state for trial status.
 */
const initialState = {
	isTrialActive: false,
	isTrialExpired: false,
	daysRemaining: 0,
	isExpiringSoon: false,
	isExpiring: false,
	systemEnabled: false,
	usage: {},
	limits: {},
	restrictions: {
		restrictedPages: [],
		blockedActions: {},
		messages: {}
	},
	isLoading: false,
	error: null,
	lastUpdated: null,
	isOnline: true
};

/**
 * Action types for trial status reducer.
 */
const ActionTypes = {
	FETCH_START: 'FETCH_START',
	FETCH_SUCCESS: 'FETCH_SUCCESS',
	FETCH_ERROR: 'FETCH_ERROR',
	UPDATE_STATUS: 'UPDATE_STATUS',
	SET_ONLINE_STATUS: 'SET_ONLINE_STATUS',
	CLEAR_ERROR: 'CLEAR_ERROR'
};

/**
 * Reducer for managing trial status state.
 *
 * @param {Object} state - Current state.
 * @param {Object} action - Action object with type and payload.
 * @return {Object} New state.
 */
const trialStatusReducer = (state, action) => {
	switch (action.type) {
		case ActionTypes.FETCH_START:
			return {
				...state,
				isLoading: true,
				error: null
			};

		case ActionTypes.FETCH_SUCCESS:
			return {
				...state,
				...action.payload,
				isLoading: false,
				error: null,
				lastUpdated: new Date().toISOString()
			};

		case ActionTypes.FETCH_ERROR:
			return {
				...state,
				isLoading: false,
				error: action.payload
			};

		case ActionTypes.UPDATE_STATUS:
			return {
				...state,
				...action.payload,
				lastUpdated: new Date().toISOString()
			};

		case ActionTypes.SET_ONLINE_STATUS:
			return {
				...state,
				isOnline: action.payload
			};

		case ActionTypes.CLEAR_ERROR:
			return {
				...state,
				error: null
			};

		default:
			return state;
	}
};

/**
 * Trial Status Provider Component.
 *
 * @param {Object} props - Component props.
 * @param {React.ReactNode} props.children - Child components.
 * @param {number} props.refreshInterval - Auto-refresh interval in milliseconds (default: 60000).
 * @param {boolean} props.enableAutoRefresh - Whether to enable auto-refresh (default: true).
 * @return {JSX.Element} Provider component.
 */
const TrialStatusProvider = ({
	children,
	refreshInterval = 60000, // 1 minute
	enableAutoRefresh = true
}) => {
	const [state, dispatch] = useReducer(trialStatusReducer, initialState);
	const intervalRef = useRef(null);
	const retryTimeoutRef = useRef(null);
	const retryCount = useRef(0);
	const maxRetries = 3;

	/**
	 * Fetch trial status from REST API.
	 *
	 * @param {boolean} isRetry - Whether this is a retry attempt.
	 */
	const fetchTrialStatus = async (isRetry = false) => {
		try {
			if (!isRetry) {
				dispatch({ type: ActionTypes.FETCH_START });
			}

			const [statusResponse, restrictionsResponse] = await Promise.all([
				apiFetch({
					path: '/skillpulse-lms/v1/trial/status',
					method: 'GET'
				}),
				apiFetch({
					path: '/skillpulse-lms/v1/trial/restrictions',
					method: 'GET'
				})
			]);

			// Combine status and restrictions data.
			const combinedData = {
				...statusResponse,
				restrictions: restrictionsResponse
			};

			dispatch({
				type: ActionTypes.FETCH_SUCCESS,
				payload: combinedData
			});

			// Reset retry count on success.
			retryCount.current = 0;

			// Update online status.
			if (!state.isOnline) {
				dispatch({ type: ActionTypes.SET_ONLINE_STATUS, payload: true });
			}

		} catch (error) {
			console.error('Failed to fetch trial status:', error);

			// Handle retry logic.
			if (retryCount.current < maxRetries) {
				retryCount.current++;
				const retryDelay = Math.pow(2, retryCount.current) * 1000; // Exponential backoff.

				retryTimeoutRef.current = setTimeout(() => {
					fetchTrialStatus(true);
				}, retryDelay);

				// Don't show error immediately on first retry.
				if (retryCount.current === 1) {
					return;
				}
			}

			dispatch({
				type: ActionTypes.FETCH_ERROR,
				payload: error.message || __('Failed to fetch trial status', 'skillpulse-lms')
			});

			// Update online status.
			dispatch({ type: ActionTypes.SET_ONLINE_STATUS, payload: false });
		}
	};

	/**
	 * Update specific trial status properties.
	 *
	 * @param {Object} updates - Object with properties to update.
	 */
	const updateTrialStatus = (updates) => {
		dispatch({ type: ActionTypes.UPDATE_STATUS, payload: updates });
	};

	/**
	 * Clear any error state.
	 */
	const clearError = () => {
		dispatch({ type: ActionTypes.CLEAR_ERROR });
	};

	/**
	 * Force refresh trial status.
	 */
	const forceRefresh = () => {
		clearTimeout(retryTimeoutRef.current);
		retryCount.current = 0;
		fetchTrialStatus();
	};

	/**
	 * Check if a specific page is restricted.
	 *
	 * @param {string} pageId - Page identifier to check.
	 * @return {boolean} True if page is restricted.
	 */
	const isPageRestricted = (pageId) => {
		return state.restrictions?.restrictedPages?.includes(pageId) && state.isTrialExpired;
	};

	/**
	 * Check if a specific action is blocked.
	 *
	 * @param {string} action - Action to check (form_submissions, new_content, bulk_operations).
	 * @return {boolean} True if action is blocked.
	 */
	const isActionBlocked = (action) => {
		return !!state.restrictions?.blockedActions?.[action];
	};

	/**
	 * Get appropriate message for current trial state.
	 *
	 * @param {string} context - Message context (expired, expiring_soon, feature_blocked).
	 * @return {string} Appropriate message.
	 */
	const getMessage = (context) => {
		return state.restrictions?.messages?.[context] || '';
	};

	/**
	 * Get usage percentage for a specific feature.
	 *
	 * @param {string} feature - Feature name.
	 * @return {number} Usage percentage (0-100).
	 */
	const getUsagePercentage = (feature) => {
		const usage = state.usage[feature] || 0;
		const limit = state.limits[feature] || 1;
		return Math.min(100, Math.round((usage / limit) * 100));
	};

	/**
	 * Check if a feature is at its limit.
	 *
	 * @param {string} feature - Feature name.
	 * @return {boolean} True if feature is at limit.
	 */
	const isFeatureAtLimit = (feature) => {
		return getUsagePercentage(feature) >= 100;
	};

	/**
	 * Check if a feature is near its limit (>= 80%).
	 *
	 * @param {string} feature - Feature name.
	 * @return {boolean} True if feature is near limit.
	 */
	const isFeatureNearLimit = (feature) => {
		return getUsagePercentage(feature) >= 80;
	};

	// Set up auto-refresh on component mount.
	useEffect(() => {
		// Initial fetch.
		fetchTrialStatus();

		// Set up auto-refresh interval.
		if (enableAutoRefresh && refreshInterval > 0) {
			intervalRef.current = setInterval(() => {
				// Only auto-refresh if window is visible to avoid unnecessary API calls.
				if (!document.hidden) {
					fetchTrialStatus();
				}
			}, refreshInterval);
		}

		// Listen for browser online/offline events.
		const handleOnline = () => {
			dispatch({ type: ActionTypes.SET_ONLINE_STATUS, payload: true });
			// Fetch fresh data when coming back online.
			fetchTrialStatus();
		};

		const handleOffline = () => {
			dispatch({ type: ActionTypes.SET_ONLINE_STATUS, payload: false });
		};

		window.addEventListener('online', handleOnline);
		window.addEventListener('offline', handleOffline);

		// Listen for page visibility changes.
		const handleVisibilityChange = () => {
			if (!document.hidden && enableAutoRefresh) {
				// Refresh when page becomes visible again.
				fetchTrialStatus();
			}
		};

		document.addEventListener('visibilitychange', handleVisibilityChange);

		// Cleanup on unmount.
		return () => {
			clearInterval(intervalRef.current);
			clearTimeout(retryTimeoutRef.current);
			window.removeEventListener('online', handleOnline);
			window.removeEventListener('offline', handleOffline);
			document.removeEventListener('visibilitychange', handleVisibilityChange);
		};
	}, [refreshInterval, enableAutoRefresh]);

	// Context value with state and actions.
	const contextValue = {
		// State.
		...state,

		// Actions.
		fetchTrialStatus: () => fetchTrialStatus(),
		updateTrialStatus,
		clearError,
		forceRefresh,

		// Utility functions.
		isPageRestricted,
		isActionBlocked,
		getMessage,
		getUsagePercentage,
		isFeatureAtLimit,
		isFeatureNearLimit,

		// Status helpers.
		isTrialHealthy: state.isTrialActive && !state.isTrialExpired && !state.error,
		needsAttention: state.isExpiringSoon || state.isExpiring || state.isTrialExpired,
		shouldShowBanner: state.isTrialActive && (state.isExpiringSoon || state.isExpiring),
		canCreateContent: state.isTrialActive && !state.isTrialExpired,

		// Network status.
		isConnected: state.isOnline && !state.error
	};

	return (
		<TrialStatusContext.Provider value={contextValue}>
			{children}
		</TrialStatusContext.Provider>
	);
};

/**
 * Hook to use trial status context.
 *
 * @return {Object} Trial status context value.
 * @throws {Error} If used outside of TrialStatusProvider.
 */
export const useTrialStatus = () => {
	const context = useContext(TrialStatusContext);

	if (context === undefined) {
		throw new Error('useTrialStatus must be used within a TrialStatusProvider');
	}

	return context;
};

/**
 * Higher-order component to wrap components with trial status.
 *
 * @param {React.Component} WrappedComponent - Component to wrap.
 * @return {React.Component} Wrapped component with trial status.
 */
export const withTrialStatus = (WrappedComponent) => {
	const WithTrialStatusComponent = (props) => {
		const trialStatus = useTrialStatus();

		return (
			<WrappedComponent
				{...props}
				trialStatus={trialStatus}
			/>
		);
	};

	WithTrialStatusComponent.displayName = `withTrialStatus(${WrappedComponent.displayName || WrappedComponent.name})`;

	return WithTrialStatusComponent;
};

export default TrialStatusProvider;