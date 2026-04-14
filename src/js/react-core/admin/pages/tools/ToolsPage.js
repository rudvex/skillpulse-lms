import { __, sprintf } from '@wordpress/i18n';
import { Button, Spinner, ProgressBar } from '@wordpress/components';
import { Component, Fragment } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

import './styles/index.scss';
import BrandLogo from "../../../components/BrandLogo";
import { SplmsIcon } from "../../../components/SplmsIcon";

// Import tab components
import DataManagement from './components/DataManagement';
import ResetTools from './components/ResetTools';
import SystemInfo from './components/SystemInfo';
import Modals from './components/Modals';

class ToolsPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoading: false,
            activeTab: 'data-management',
            
            // Modal states
            showResetModal: false,
            showSystemInfoModal: false,

            // Reset options
            resetOptions: {
                enrollments: false,
                progress: false,
                quizAttempts: false,
                certificates: false,
                complete_reset: false
            },

            // System info
            systemInfo: {},
            systemInfoLoading: false,
            downloadingReport: false
        };
        
        this.tabs = [
            { id: 'data-management', title: __('Data Management', 'skillpulse-lms') },
            { id: 'reset-tools', title: __('Reset Tools', 'skillpulse-lms') },
            { id: 'system-info', title: __('System Info', 'skillpulse-lms') }
        ];
    }

    componentDidMount() {
        // Set initial tab from URL hash or default to first tab
        if (window.location.hash) {
            const tab = window.location.hash.substring(1);
            const validTab = this.tabs.find(t => t.id === tab);
            if (validTab) {
                this.setState({ activeTab: tab });
            }
        }

        this.loadInitialData();
    }

    loadInitialData = async () => {
        await this.loadSystemInfo();
    };

    loadSystemInfo = async (forceRefresh = false) => {
        this.setState({ systemInfoLoading: true });
        
        // Check for cached data first (client-side cache)
        const cacheKey = 'splms_system_info';
        const cacheTimeout = 15 * 60 * 1000; // 15 minutes in milliseconds
        
        if (!forceRefresh) {
            const cachedData = this.getCachedData(cacheKey, cacheTimeout);
            if (cachedData) {
                this.setState({ 
                    systemInfo: cachedData,
                    systemInfoLoading: false
                });
                return;
            }
        }
        
        try {
            const queryParams = forceRefresh ? '?force_refresh=1' : '';
            const response = await apiFetch({
                path: `/splms/v1/admin/system-info${queryParams}`,
                method: 'GET',
            });
            
            // Cache the response
            this.setCachedData(cacheKey, response || {});
            
            this.setState({ 
                systemInfo: response || {},
                systemInfoLoading: false
            });
        } catch (error) {
            console.error('Error loading system info:', error);
            this.setState({ systemInfoLoading: false });
            this.showNotice(__('Failed to load system information.', 'skillpulse-lms'), 'error');
        }
    };


    /**
     * Helper function to force download of a file from a URL
     * @param {string} url - The download URL
     * @param {string} filename - The filename for the download
     */
    forceDownload = (url, filename) => {
        // Create a temporary anchor element
        const a = document.createElement('a');
        a.href = url;
        a.download = filename || '';
        a.style.display = 'none';
        
        // Append to body, click, and remove
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };



    showNotice = (message, type = 'success') => {
        if (window.skillpulseToast) {
            if (type === 'error') {
                window.skillpulseToast.error(message);
            } else {
                window.skillpulseToast.success(message);
            }
        }
    };

    // Tab Management
    tabClassName = (tabName) => {
        return this.state.activeTab === tabName ? "active" : "";
    };

    handleTabChange = (tabId) => {
        this.setState({ activeTab: tabId });
        window.location.hash = tabId;
    };

    // Event Handlers
    exportCourses = async () => {
        try {
            this.setState({ isLoading: true });
            const response = await apiFetch({
                path: '/splms/v1/admin/export-courses',
                method: 'POST',
            });
            
            if (response.download_url) {
                this.forceDownload(response.download_url, response.filename || 'splms-courses-export.json');
                this.showNotice(__('Courses exported successfully!', 'skillpulse-lms'));
            }
        } catch (error) {
            this.showNotice(__('Error exporting courses.', 'skillpulse-lms'), 'error');
        } finally {
            this.setState({ isLoading: false });
        }
    };

    exportUserProgress = async () => {
        try {
            this.setState({ isLoading: true });
            const response = await apiFetch({
                path: '/splms/v1/admin/export-user-progress',
                method: 'POST',
            });
            
            if (response.download_url) {
                this.forceDownload(response.download_url, response.filename || 'splms-user-progress-export.csv');
                this.showNotice(__('User progress exported successfully!', 'skillpulse-lms'));
            }
        } catch (error) {
            this.showNotice(__('Error exporting user progress.', 'skillpulse-lms'), 'error');
        } finally {
            this.setState({ isLoading: false });
        }
    };

    importCourses = async (file) => {
        if (!file) return;
        
        try {
            this.setState({ isLoading: true });
            const formData = new FormData();
            formData.append('file', file);
            
            const response = await apiFetch({
                path: '/splms/v1/admin/import-courses',
                method: 'POST',
                body: formData,
                // Note: apiFetch will automatically handle FormData without Content-Type header
            });
            
            if (response.success) {
                this.showNotice(sprintf(__('Successfully imported %d courses!', 'skillpulse-lms'), response.imported));
            }
        } catch (error) {
            this.showNotice(__('Error importing courses.', 'skillpulse-lms'), 'error');
        } finally {
            this.setState({ isLoading: false });
        }
    };

    resetLMSData = async () => {
        try {
            this.setState({ isLoading: true });
            const response = await apiFetch({
                path: '/splms/v1/admin/reset-data',
                method: 'POST',
                data: { options: this.state.resetOptions }
            });
            
            if (response.success) {
                if (this.state.resetOptions.complete_reset) {
                    // For complete reset, show detailed message
                    this.showNotice(response.message);

                    // Show detailed reset items in console
                    if (response.reset_items && response.reset_items.length > 0) {
                        console.log('SkillPulse LMS Complete Reset Details:', response.reset_items);
                    }

                    // Reset the form state after complete reset
                    this.setState({
                        resetOptions: {
                            enrollments: false,
                            progress: false,
                            quizAttempts: false,
                            certificates: false,
                            complete_reset: false
                        }
                    });
                } else {
                    // For individual resets, show record count
                this.showNotice(sprintf(__('Successfully reset %d records!', 'skillpulse-lms'), response.reset_count));
            }
            }
        } catch (error) {
            console.error('Reset error:', error);
            this.showNotice(__('Error resetting data.', 'skillpulse-lms'), 'error');
        } finally {
            this.setState({ isLoading: false, showResetModal: false });
        }
    };



    downloadSystemReport = async () => {
        this.setState({ downloadingReport: true });
        
        try {
            // Get REST API base URL and nonce
            const restUrl = (window.wpApiSettings?.root || window.SPLMSCore_Data?.restUrl || '/wp-json/').replace(/\/$/, '');
            const nonce = window.SPLMSCore_Data?.restNonce || window.wpApiSettings?.nonce || '';
            const endpoint = `${restUrl}/splms/v1/admin/system-report?force_refresh=1`;
            
            // Fetch the file content directly with proper headers
            const response = await fetch(endpoint, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'X-WP-Nonce': nonce,
                },
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Get the filename from Content-Disposition header or use default
            let filename = 'splms-system-report.txt';
            const contentDisposition = response.headers.get('Content-Disposition');
            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (filenameMatch && filenameMatch[1]) {
                    filename = filenameMatch[1].replace(/['"]/g, '');
                }
            }
            
            // Get the file content as blob
            const blob = await response.blob();
            
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            
            // Clean up
            setTimeout(() => {
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }, 100);
            
            this.showNotice(__('System report downloaded successfully!', 'skillpulse-lms'));
        } catch (error) {
            console.error('Error downloading system report:', error);
            this.showNotice(__('Error downloading system report.', 'skillpulse-lms'), 'error');
        } finally {
            this.setState({ downloadingReport: false });
        }
    };

    handleShowSystemInfoModal = () => {
        this.setState({ showSystemInfoModal: true });
        // Use existing data - no need to fetch again since it's already loaded
    };

    refreshSystemInfo = async () => {
        await this.loadSystemInfo(true); // Force refresh
        this.showNotice(__('System information refreshed successfully!', 'skillpulse-lms'));
    };

    // Cache helper methods
    getCachedData = (key, timeout) => {
        try {
            const cached = localStorage.getItem(key);
            if (!cached) return null;
            
            const data = JSON.parse(cached);
            const now = new Date().getTime();
            
            // Check if cache is expired
            if (now - data.timestamp > timeout) {
                localStorage.removeItem(key);
                return null;
            }
            
            return data.value;
        } catch (error) {
            console.error('Error reading cache:', error);
            return null;
        }
    };

    setCachedData = (key, value) => {
        try {
            const data = {
                value: value,
                timestamp: new Date().getTime()
            };
            localStorage.setItem(key, JSON.stringify(data));
        } catch (error) {
            console.error('Error setting cache:', error);
        }
    };

    generateSystemReportText = () => {
        const { systemInfo } = this.state;
        const currentDate = new Date().toLocaleString();
        const currentUser = wp.data?.select('core')?.getCurrentUser?.() || {};
        
        return `SkillPulse LMS System Report
Generated: ${currentDate}
${Array(50).fill('=').join('')}

=== WordPress Environment ===
WordPress Version: ${systemInfo.wp_version || 'N/A'}
Multisite: ${systemInfo.wp_multisite ? 'Yes' : 'No'}
Site URL: ${systemInfo.site_url || 'N/A'}
Home URL: ${systemInfo.home_url || 'N/A'}
WordPress Memory Limit: ${systemInfo.wp_memory_limit || 'N/A'}
WordPress Max Memory Limit: ${systemInfo.wp_max_memory_limit || 'N/A'}
WP Cache: ${systemInfo.wp_cache ? 'Enabled' : 'Disabled'}
WP Cron: ${systemInfo.wp_cron_disabled ? 'Disabled' : 'Enabled'}
WordPress Language: ${systemInfo.wp_language || 'N/A'}
WordPress Timezone: ${systemInfo.wp_timezone || 'N/A'}
Admin Email: ${systemInfo.admin_email || 'N/A'}
User Signup: ${systemInfo.users_can_register || 'N/A'}
Default Role: ${systemInfo.default_role || 'N/A'}
${systemInfo.auto_updates_core ? `Auto Updates (Core): ${systemInfo.auto_updates_core}\n` : ''}${systemInfo.auto_updates_plugins ? `Auto Updates (Plugins): ${systemInfo.auto_updates_plugins}\n` : ''}${systemInfo.auto_updates_themes ? `Auto Updates (Themes): ${systemInfo.auto_updates_themes}\n` : ''}

=== Server Environment ===
Server Software: ${systemInfo.server_software || 'N/A'}
Server OS: ${systemInfo.server_os || 'N/A'}
Server Architecture: ${systemInfo.server_architecture || 'N/A'}
PHP Version: ${systemInfo.php_version || 'N/A'}
PHP SAPI: ${systemInfo.php_sapi || 'N/A'}
PHP Memory Limit: ${systemInfo.php_memory_limit || 'N/A'}
PHP Max Execution Time: ${systemInfo.php_max_execution_time || 'N/A'}s
PHP Max Input Vars: ${systemInfo.php_max_input_vars || 'N/A'}
PHP Post Max Size: ${systemInfo.php_post_max_size || 'N/A'}
PHP Upload Max Filesize: ${systemInfo.php_upload_max_filesize || 'N/A'}
PHP Max File Uploads: ${systemInfo.php_max_file_uploads || 'N/A'}
PHP Allow URL fopen: ${systemInfo.php_allow_url_fopen || 'N/A'}
PHP Display Errors: ${systemInfo.php_display_errors || 'N/A'}
PHP Session Save Path: ${systemInfo.php_session_save_path || 'N/A'}
MySQL Version: ${systemInfo.mysql_version || 'N/A'}
MySQL Host: ${systemInfo.mysql_host || 'N/A'}
MySQL Database: ${systemInfo.mysql_database || 'N/A'}
MySQL Charset: ${systemInfo.mysql_charset || 'N/A'}
MySQL Collate: ${systemInfo.mysql_collate || 'N/A'}

=== PHP Extensions ===
${systemInfo.php_extensions && systemInfo.php_extensions.length > 0 ? 
    systemInfo.php_extensions.map(ext => `${ext.name}: ${ext.status}`).join('\n') : 
    'No PHP extension information available'}

=== Directories and Sizes ===
WordPress Path: ${systemInfo.wp_path || 'N/A'}
Content Directory: ${systemInfo.wp_content_dir || 'N/A'}
Plugin Directory: ${systemInfo.wp_plugin_dir || 'N/A'}
Upload Directory: ${systemInfo.wp_upload_dir || 'N/A'}
Upload URL: ${systemInfo.wp_upload_url || 'N/A'}
Temp Directory: ${systemInfo.temp_dir || 'N/A'}
Disk Free Space: ${systemInfo.disk_free_space || 'N/A'}
Disk Total Space: ${systemInfo.disk_total_space || 'N/A'}

${systemInfo.directory_sizes && systemInfo.directory_sizes.length > 0 ? 
    '\nDirectory Sizes:\n' + systemInfo.directory_sizes.map(dir => `${dir.name}: ${dir.size_formatted}`).join('\n') : ''}

=== Filesystem Permissions ===
${systemInfo.filesystem_permissions && systemInfo.filesystem_permissions.length > 0 ? 
    systemInfo.filesystem_permissions.map(perm => `${perm.name}: R:${perm.readable ? 'Yes' : 'No'} W:${perm.writable ? 'Yes' : 'No'} (${perm.permissions}) ${perm.owner}`).join('\n') : 
    'No filesystem permission information available'}

=== Theme Information ===
Active Theme: ${systemInfo.theme_name || 'N/A'}
Theme Version: ${systemInfo.theme_version || 'N/A'}
Theme Author: ${systemInfo.theme_author || 'N/A'}
Theme URI: ${systemInfo.theme_uri || 'N/A'}
Parent Theme: ${systemInfo.parent_theme || 'None'}
Child Theme: ${systemInfo.child_theme || 'N/A'}

=== SkillPulse LMS ===
LMS Version: ${systemInfo.lms_version || 'N/A'}
Database Version: ${systemInfo.lms_db_version || 'N/A'}
Total Courses: ${systemInfo.total_courses || '0'}
Total Students: ${systemInfo.total_students || '0'}

=== Active Plugins ===
${systemInfo.total_plugins ? `Total Plugins Installed: ${systemInfo.total_plugins}\n` : ''}${systemInfo.active_plugins && systemInfo.active_plugins.length > 0 ? 
    `Active Plugins: ${systemInfo.active_plugins.length}\n\n` + systemInfo.active_plugins.map(plugin => `${plugin.name} (v${plugin.version}) by ${plugin.author || 'Unknown'}`).join('\n') : 
    'No plugin information available'}

=== Must-Use Plugins ===
${systemInfo.mu_plugins && systemInfo.mu_plugins.length > 0 ? 
    systemInfo.mu_plugins.map(plugin => `${plugin.name} (v${plugin.version})`).join('\n') : 
    'No must-use plugins'}

=== System Directories ===
WordPress Path: ${systemInfo.wp_path || 'N/A'}
Upload Directory: ${systemInfo.upload_dir || systemInfo.wp_upload_dir || 'N/A'}
Upload Directory Writable: ${systemInfo.upload_dir_writable ? 'Yes' : 'No'}

=== Additional Information ===
Report Generated By: ${currentUser.name || 'admin'} (ID: ${currentUser.id || '1'})
Server Time: ${currentDate}
Timezone: ${Intl.DateTimeFormat().resolvedOptions().timeZone}
User Agent: ${navigator.userAgent}
`;
    };


    renderTabContent = () => {
        const { activeTab } = this.state;
        
        switch (activeTab) {
            case 'data-management':
                return (
                    <DataManagement
                        isLoading={this.state.isLoading}
                        onExportCourses={this.exportCourses}
                        onExportUserProgress={this.exportUserProgress}
                        onImportCourses={this.importCourses}
                    />
                );
            case 'reset-tools':
                return (
                    <ResetTools
                        isLoading={this.state.isLoading}
                        onShowResetModal={() => this.setState({ showResetModal: true })}
                    />
                );
            case 'system-info':
                return (
                    <SystemInfo
                        systemInfo={this.state.systemInfo}
                        downloadingReport={this.state.downloadingReport}
                        onShowSystemInfoModal={this.handleShowSystemInfoModal}
                        onDownloadSystemReport={this.downloadSystemReport}
                    />
                );

            default:
                return null;
        }
    };

    render() {
        const { isLoading, activeTab } = this.state;

        return (
            <div className="splms-container" role="main">
                {/* Header similar to Settings page */}
                <header id="splms-admin-header" role="banner">
                    <div className="splms-header-logo">
                        <div className="logo inline">
                            <BrandLogo />
                            <div className="splms-header-separator" />
                            <h1 className="splms-header-title inline">
                                {__("Tools", "skillpulse-lms")}
                            </h1>
                        </div>
                        <div className="splms-header-icon">
                            <div className="splms-header-icon-help">
                                <Button
                                    className="splms-header-icon-help-button"
                                    onClick={() => {
                                        window.open("https://skillpulselms.com/docs/tools", "_blank", "noopener,noreferrer");
                                    }}
                                    aria-label={__("Get help with SkillPulse LMS Tools", "skillpulse-lms")}
                                >
                                    <SplmsIcon name="help" size={20} />
                                    <span className="splms-header-icon-help-text">
                                        {__("Tools Documentation", "skillpulse-lms")}
                                    </span>
                                </Button>
                            </div>
                        </div>
                    </div>
                    
                    {/* Tab Navigation */}
                    <nav className="splms-header-nav" role="navigation" aria-label={__("Tools navigation", "skillpulse-lms")}>
                        <ul role="tablist">
                            {this.tabs.map(tab => (
                                <li key={tab.id} className={this.tabClassName(tab.id)} role="presentation">
                                    <a 
                                        onClick={(e) => {
                                            e.preventDefault();
                                            this.handleTabChange(tab.id);
                                        }} 
                                        href={`#${tab.id}`}
                                        role="tab"
                                        aria-selected={activeTab === tab.id}
                                        aria-controls={`panel-${tab.id}`}
                                        tabIndex={activeTab === tab.id ? 0 : -1}
                                    >
                                        {tab.title}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </header>

                {/* Main Content */}
                <div className="splms-content">
                    {/* Loading Overlay */}
                    {isLoading && (
                        <div className="loading-overlay">
                            <div className="loading-content">
                                <Spinner />
                                <p>{__('Processing...', 'skillpulse-lms')}</p>
                            </div>
                        </div>
                    )}

                    <div 
                        id={`panel-${activeTab}`}
                        role="tabpanel" 
                        aria-labelledby={`tab-${activeTab}`}
                    >
                        {this.renderTabContent()}
                    </div>
                </div>

                {/* Modals */}
                <Modals
                    showResetModal={this.state.showResetModal}
                    resetOptions={this.state.resetOptions}
                    onCloseResetModal={() => this.setState({ showResetModal: false })}
                    onResetOptionChange={(key, value) => {
                        if (key === 'complete_reset' && value) {
                            // If complete reset is selected, clear all other options
                            this.setState({
                                resetOptions: {
                                    enrollments: false,
                                    progress: false,
                                    quizAttempts: false,
                                    certificates: false,
                                    complete_reset: true
                                }
                            });
                        } else if (key === 'complete_reset' && !value) {
                            // If complete reset is deselected, just update that option
                            this.setState({
                                resetOptions: { ...this.state.resetOptions, complete_reset: false }
                            });
                        } else {
                            // For other options, update normally but clear complete_reset if it was set
                            this.setState({
                                resetOptions: {
                                    ...this.state.resetOptions,
                                    [key]: value,
                                    complete_reset: false
                                }
                            });
                        }
                    }}
                    onResetLMSData={this.resetLMSData}
                    
                    showSystemInfoModal={this.state.showSystemInfoModal}
                    systemInfo={this.state.systemInfo}
                    systemInfoLoading={this.state.systemInfoLoading}
                    downloadingReport={this.state.downloadingReport}
                    onCloseSystemInfoModal={() => this.setState({ showSystemInfoModal: false })}
                    onDownloadSystemReport={this.downloadSystemReport}
                    onRefreshSystemInfo={this.refreshSystemInfo}
                />
            </div>
        );
    }
}

export default ToolsPage; 