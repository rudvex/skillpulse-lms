/**
 * Trial Security Dashboard Component
 *
 * Provides comprehensive trial status, security monitoring, and usage analytics
 * for administrators to monitor trial restrictions and security.
 */

import React, { useState, useEffect, useCallback } from 'react';
import './styles.scss';

const TrialSecurityDashboard = () => {
    const [trialData, setTrialData] = useState(null);
    const [securityStatus, setSecurityStatus] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [refreshing, setRefreshing] = useState(false);

    // Fetch trial status from REST API
    const fetchTrialStatus = useCallback(async () => {
        try {
            setRefreshing(true);
            const response = await fetch('/wp-json/skillpulse-lms/v1/trial/status', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wpApiSettings?.nonce || ''
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                setTrialData(data.data);
                setError(null);
            } else {
                throw new Error(data.message || 'Failed to fetch trial status');
            }
        } catch (err) {
            console.error('Trial status fetch error:', err);
            setError(err.message);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    // Fetch security status
    const fetchSecurityStatus = useCallback(async () => {
        try {
            const response = await jQuery.ajax({
                url: window.ajaxurl,
                method: 'POST',
                data: {
                    action: 'splms_validate_trial_integrity',
                    nonce: window.splmsTrialRestrictions?.nonce || '',
                    detailed: true
                }
            });

            if (response.success) {
                setSecurityStatus(response.data);
            }
        } catch (err) {
            console.warn('Security status fetch error:', err);
        }
    }, []);

    // Initial data fetch
    useEffect(() => {
        fetchTrialStatus();
        fetchSecurityStatus();

        // Refresh every 5 minutes
        const interval = setInterval(() => {
            fetchTrialStatus();
            fetchSecurityStatus();
        }, 5 * 60 * 1000);

        return () => clearInterval(interval);
    }, [fetchTrialStatus, fetchSecurityStatus]);

    // Manual refresh
    const handleRefresh = () => {
        fetchTrialStatus();
        fetchSecurityStatus();
    };

    if (loading) {
        return (
            <div className="splms-trial-dashboard loading">
                <div className="loading-spinner"></div>
                <p>Loading trial status...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="splms-trial-dashboard error">
                <div className="error-message">
                    <h3>Error Loading Trial Data</h3>
                    <p>{error}</p>
                    <button onClick={handleRefresh} className="button button-primary">
                        Retry
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="splms-trial-dashboard">
            <div className="dashboard-header">
                <h2>Trial Security Dashboard</h2>
                <div className="dashboard-actions">
                    <button
                        onClick={handleRefresh}
                        className={`button ${refreshing ? 'refreshing' : ''}`}
                        disabled={refreshing}
                    >
                        {refreshing ? 'Refreshing...' : 'Refresh'}
                    </button>
                </div>
            </div>

            <div className="dashboard-content">
                <div className="dashboard-grid">
                    <TrialStatusCard trialData={trialData} />
                    <SecurityStatusCard securityStatus={securityStatus} />
                    <UsageAnalyticsCard trialData={trialData} />
                    <RestrictionsCard trialData={trialData} />
                </div>

                <div className="dashboard-details">
                    <TrialTimelineCard trialData={trialData} />
                    <SecurityLogsCard securityStatus={securityStatus} />
                </div>
            </div>
        </div>
    );
};

// Trial Status Card
const TrialStatusCard = ({ trialData }) => {
    if (!trialData) return null;

    const { isActive, isExpired, daysRemaining, startDate, expirationDate, hasValidLicense } = trialData;

    const getStatusClass = () => {
        if (hasValidLicense) return 'licensed';
        if (isExpired) return 'expired';
        if (daysRemaining <= 3) return 'expiring';
        return 'active';
    };

    const getStatusText = () => {
        if (hasValidLicense) return 'Licensed';
        if (isExpired) return 'Trial Expired';
        if (daysRemaining <= 3) return 'Expiring Soon';
        return 'Trial Active';
    };

    return (
        <div className={`dashboard-card trial-status ${getStatusClass()}`}>
            <div className="card-header">
                <h3>Trial Status</h3>
                <span className={`status-badge ${getStatusClass()}`}>
                    {getStatusText()}
                </span>
            </div>
            <div className="card-content">
                {!hasValidLicense && (
                    <>
                        <div className="status-metric">
                            <label>Days Remaining:</label>
                            <span className="metric-value">{daysRemaining}</span>
                        </div>
                        <div className="status-metric">
                            <label>Started:</label>
                            <span className="metric-value">
                                {startDate ? new Date(startDate).toLocaleDateString() : 'N/A'}
                            </span>
                        </div>
                        <div className="status-metric">
                            <label>Expires:</label>
                            <span className="metric-value">
                                {expirationDate ? new Date(expirationDate).toLocaleDateString() : 'N/A'}
                            </span>
                        </div>
                    </>
                )}
                {hasValidLicense && (
                    <div className="license-info">
                        <p>✓ Full license active - All features unlocked</p>
                    </div>
                )}
            </div>
            {!hasValidLicense && (isExpired || daysRemaining <= 3) && (
                <div className="card-actions">
                    <a href={window.splmsTrialRestrictions?.licenseUrl || '#'} className="button button-primary">
                        Activate License
                    </a>
                </div>
            )}
        </div>
    );
};

// Security Status Card
const SecurityStatusCard = ({ securityStatus }) => {
    if (!securityStatus) {
        return (
            <div className="dashboard-card security-status">
                <div className="card-header">
                    <h3>Security Status</h3>
                </div>
                <div className="card-content">
                    <p>Security data not available</p>
                </div>
            </div>
        );
    }

    const { is_valid, validation_timestamp, usage_stats } = securityStatus;

    return (
        <div className={`dashboard-card security-status ${is_valid ? 'secure' : 'warning'}`}>
            <div className="card-header">
                <h3>Security Status</h3>
                <span className={`status-badge ${is_valid ? 'secure' : 'warning'}`}>
                    {is_valid ? 'Secure' : 'Warning'}
                </span>
            </div>
            <div className="card-content">
                <div className="security-metrics">
                    <div className="metric">
                        <label>Validation Status:</label>
                        <span className={is_valid ? 'valid' : 'invalid'}>
                            {is_valid ? '✓ Valid' : '✗ Invalid'}
                        </span>
                    </div>
                    <div className="metric">
                        <label>Last Check:</label>
                        <span>
                            {validation_timestamp ?
                                new Date(validation_timestamp * 1000).toLocaleString() :
                                'Never'
                            }
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
};

// Usage Analytics Card
const UsageAnalyticsCard = ({ trialData }) => {
    if (!trialData || !trialData.usage || !trialData.limits) return null;

    const { usage, limits } = trialData;

    const getUsagePercentage = (used, limit) => {
        return limit > 0 ? Math.min((used / limit) * 100, 100) : 0;
    };

    const getUsageClass = (percentage) => {
        if (percentage >= 100) return 'exceeded';
        if (percentage >= 80) return 'warning';
        return 'normal';
    };

    return (
        <div className="dashboard-card usage-analytics">
            <div className="card-header">
                <h3>Usage Analytics</h3>
            </div>
            <div className="card-content">
                <div className="usage-metrics">
                    {Object.entries(usage).map(([key, used]) => {
                        const limit = limits[key] || 0;
                        const percentage = getUsagePercentage(used, limit);

                        return (
                            <div key={key} className="usage-item">
                                <div className="usage-header">
                                    <label>{key.charAt(0).toUpperCase() + key.slice(1)}:</label>
                                    <span className="usage-numbers">
                                        {used} / {limit}
                                    </span>
                                </div>
                                <div className="usage-bar">
                                    <div
                                        className={`usage-fill ${getUsageClass(percentage)}`}
                                        style={{ width: `${percentage}%` }}
                                    ></div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
};

// Restrictions Card
const RestrictionsCard = ({ trialData }) => {
    if (!trialData) return null;

    const { hasValidLicense, isExpired } = trialData;

    const activeRestrictions = [];

    if (!hasValidLicense) {
        activeRestrictions.push(
            'Limited to trial usage limits',
            'Bulk operations disabled',
            'Advanced analytics unavailable',
            'Premium integrations locked'
        );
    }

    if (isExpired) {
        activeRestrictions.push(
            'All features disabled',
            'Trial period ended'
        );
    }

    return (
        <div className="dashboard-card restrictions">
            <div className="card-header">
                <h3>Active Restrictions</h3>
            </div>
            <div className="card-content">
                {activeRestrictions.length === 0 ? (
                    <div className="no-restrictions">
                        <p>✓ No restrictions - Full license active</p>
                    </div>
                ) : (
                    <ul className="restrictions-list">
                        {activeRestrictions.map((restriction, index) => (
                            <li key={index}>{restriction}</li>
                        ))}
                    </ul>
                )}
            </div>
        </div>
    );
};

// Trial Timeline Card
const TrialTimelineCard = ({ trialData }) => {
    if (!trialData || trialData.hasValidLicense) return null;

    const { startDate, expirationDate, daysRemaining, isExpired } = trialData;

    const totalDays = 14; // Trial duration
    const daysUsed = totalDays - daysRemaining;
    const progressPercentage = (daysUsed / totalDays) * 100;

    return (
        <div className="dashboard-card trial-timeline">
            <div className="card-header">
                <h3>Trial Timeline</h3>
            </div>
            <div className="card-content">
                <div className="timeline-progress">
                    <div className="progress-bar">
                        <div
                            className={`progress-fill ${isExpired ? 'expired' : ''}`}
                            style={{ width: `${Math.min(progressPercentage, 100)}%` }}
                        ></div>
                    </div>
                    <div className="timeline-labels">
                        <span>Started: {startDate ? new Date(startDate).toLocaleDateString() : 'N/A'}</span>
                        <span>Expires: {expirationDate ? new Date(expirationDate).toLocaleDateString() : 'N/A'}</span>
                    </div>
                </div>
                <div className="timeline-stats">
                    <div className="stat">
                        <label>Days Used:</label>
                        <span>{daysUsed}</span>
                    </div>
                    <div className="stat">
                        <label>Days Remaining:</label>
                        <span>{Math.max(daysRemaining, 0)}</span>
                    </div>
                </div>
            </div>
        </div>
    );
};

// Security Logs Card
const SecurityLogsCard = ({ securityStatus }) => {
    if (!securityStatus) return null;

    // Mock security events for demonstration
    const securityEvents = [
        { time: Date.now() - 3600000, event: 'Trial validation successful', type: 'info' },
        { time: Date.now() - 7200000, event: 'Restriction enforced: Bulk import blocked', type: 'warning' },
        { time: Date.now() - 10800000, event: 'Trial integrity check passed', type: 'success' },
    ];

    return (
        <div className="dashboard-card security-logs">
            <div className="card-header">
                <h3>Recent Security Events</h3>
            </div>
            <div className="card-content">
                <div className="logs-list">
                    {securityEvents.map((event, index) => (
                        <div key={index} className={`log-entry ${event.type}`}>
                            <div className="log-time">
                                {new Date(event.time).toLocaleString()}
                            </div>
                            <div className="log-event">
                                {event.event}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default TrialSecurityDashboard;