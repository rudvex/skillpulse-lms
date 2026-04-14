import { __, sprintf } from '@wordpress/i18n';
import { Button, Modal, CheckboxControl, Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { SplmsIcon } from "../../../../components/SplmsIcon";

const Modals = ({
    // Reset Modal
    showResetModal,
    resetOptions,
    onCloseResetModal,
    onResetOptionChange,
    onResetLMSData,

    // System Info Modal
    showSystemInfoModal,
    systemInfo,
    systemInfoLoading,
    downloadingReport,
    onCloseSystemInfoModal,
    onDownloadSystemReport,
    onRefreshSystemInfo
}) => {
    return (
        <>
            {/* Reset Modal */}
            {showResetModal && (
                <Modal
                    title={__('Reset LMS Data', 'skillpulse-lms')}
                    onRequestClose={onCloseResetModal}
                    className="splms-tools-modal"
                >
                    <p>{__('Select the data types you want to reset. This action cannot be undone.', 'skillpulse-lms')}</p>
                    <div className="reset-options">
                        <div className="reset-section">
                            <h4>{__('Individual Reset Options', 'skillpulse-lms')}</h4>
                        <CheckboxControl
                            label={__('Enrollments', 'skillpulse-lms')}
                            checked={resetOptions.enrollments}
                            onChange={(value) => onResetOptionChange('enrollments', value)}
                                disabled={resetOptions.complete_reset}
                        />
                        <CheckboxControl
                            label={__('Course Progress', 'skillpulse-lms')}
                            checked={resetOptions.progress}
                            onChange={(value) => onResetOptionChange('progress', value)}
                                disabled={resetOptions.complete_reset}
                        />
                        <CheckboxControl
                            label={__('Quiz Attempts', 'skillpulse-lms')}
                            checked={resetOptions.quizAttempts}
                            onChange={(value) => onResetOptionChange('quizAttempts', value)}
                                disabled={resetOptions.complete_reset}
                        />
                        <CheckboxControl
                            label={__('Certificates', 'skillpulse-lms')}
                            checked={resetOptions.certificates}
                            onChange={(value) => onResetOptionChange('certificates', value)}
                                disabled={resetOptions.complete_reset}
                        />
                    </div>

                        <div className="reset-section complete-reset-section">
                            <h4 className="complete-reset-title">{__('Complete Reset (Nuclear Option)', 'skillpulse-lms')}</h4>
                            <div className="complete-reset-warning">
                                <SplmsIcon mode="wp" icon="warning" className="warning-icon" />
                                <p className="warning-text">
                                    {__('This will completely remove ALL SkillPulse LMS data and return the system to a fresh install state. This includes:', 'skillpulse-lms')}
                                </p>
                                <ul className="warning-list">
                                    <li>{__('All database tables and data', 'skillpulse-lms')}</li>
                                    <li>{__('All courses, lessons, quizzes, and certificates', 'skillpulse-lms')}</li>
                                    <li>{__('All user enrollments and progress', 'skillpulse-lms')}</li>
                                    <li>{__('All plugin settings and configurations', 'skillpulse-lms')}</li>
                                    <li>{__('All custom user roles and capabilities', 'skillpulse-lms')}</li>
                                    <li>{__('License information and activation status', 'skillpulse-lms')}</li>
                                </ul>
                            </div>
                            <CheckboxControl
                                label={__('Complete Reset - Remove ALL SkillPulse LMS Data', 'skillpulse-lms')}
                                checked={resetOptions.complete_reset}
                                onChange={(value) => onResetOptionChange('complete_reset', value)}
                                className="complete-reset-checkbox"
                            />
                            {resetOptions.complete_reset && (
                                <div className="confirmation-section">
                                    <p className="confirmation-text">
                                        {__('⚠️ Are you absolutely sure? This action is irreversible and will completely wipe all SkillPulse LMS data!', 'skillpulse-lms')}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                    <div className="modal-actions">
                        <Button isDestructive onClick={onResetLMSData}>
                            {resetOptions.complete_reset
                                ? __('🚨 COMPLETE RESET - WIPE ALL DATA', 'skillpulse-lms')
                                : __('Reset Selected Data', 'skillpulse-lms')
                            }
                        </Button>
                        <Button isSecondary onClick={onCloseResetModal}>
                            {__('Cancel', 'skillpulse-lms')}
                        </Button>
                    </div>
                </Modal>
            )}

            {/* System Info Modal */}
            {showSystemInfoModal && (
                <SystemInfoModal
                    systemInfo={systemInfo}
                    systemInfoLoading={systemInfoLoading}
                    downloadingReport={downloadingReport}
                    onCloseSystemInfoModal={onCloseSystemInfoModal}
                    onDownloadSystemReport={onDownloadSystemReport}
                    onRefreshSystemInfo={onRefreshSystemInfo}
                />
            )}
        </>
    );
};

const SystemInfoModal = ({ systemInfo, systemInfoLoading, downloadingReport, onCloseSystemInfoModal, onDownloadSystemReport, onRefreshSystemInfo }) => {
    const [copied, setCopied] = useState(false);
    
    const copyReportToClipboard = async () => {
        try {
            // Generate report text
            const reportText = generateReportText(systemInfo);
            await navigator.clipboard.writeText(reportText);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (error) {
            console.error('Failed to copy report:', error);
        }
    };
    
    const generateReportText = (info) => {
        const currentDate = new Date().toLocaleString();
        const currentUser = wp.data?.select('core')?.getCurrentUser?.() || {};
        
        let report = `SkillPulse LMS System Report\n`;
        report += `Generated: ${currentDate}\n`;
        report += `${'='.repeat(50)}\n\n`;
        
        // WordPress Environment
        report += `=== WordPress Environment ===\n`;
        report += `WordPress Version: ${info.wp_version || 'N/A'}\n`;
        report += `Multisite: ${info.wp_multisite ? 'Yes' : 'No'}\n`;
        report += `Site URL: ${info.site_url || 'N/A'}\n`;
        report += `Home URL: ${info.home_url || 'N/A'}\n`;
        report += `WordPress Memory Limit: ${info.wp_memory_limit || 'N/A'}\n`;
        report += `WordPress Max Memory Limit: ${info.wp_max_memory_limit || 'N/A'}\n`;
        report += `WP Cache: ${info.wp_cache ? 'Enabled' : 'Disabled'}\n`;
        report += `WP Cron: ${info.wp_cron_disabled ? 'Disabled' : 'Enabled'}\n`;
        report += `WordPress Language: ${info.wp_language || 'N/A'}\n`;
        report += `WordPress Timezone: ${info.wp_timezone || 'N/A'}\n`;
        report += `Admin Email: ${info.admin_email || 'N/A'}\n`;
        report += `User Signup: ${info.users_can_register || 'N/A'}\n`;
        report += `Default Role: ${info.default_role || 'N/A'}\n`;
        if (info.auto_updates_core) report += `Auto Updates (Core): ${info.auto_updates_core}\n`;
        if (info.auto_updates_plugins) report += `Auto Updates (Plugins): ${info.auto_updates_plugins}\n`;
        if (info.auto_updates_themes) report += `Auto Updates (Themes): ${info.auto_updates_themes}\n`;
        report += `\n`;
        
        // Server Environment
        report += `=== Server Environment ===\n`;
        report += `Server Software: ${info.server_software || 'N/A'}\n`;
        report += `Server OS: ${info.server_os || 'N/A'}\n`;
        report += `Server Architecture: ${info.server_architecture || 'N/A'}\n`;
        report += `PHP Version: ${info.php_version || 'N/A'}\n`;
        report += `PHP SAPI: ${info.php_sapi || 'N/A'}\n`;
        report += `PHP Memory Limit: ${info.php_memory_limit || 'N/A'}\n`;
        report += `PHP Max Execution Time: ${info.php_max_execution_time || 'N/A'}s\n`;
        report += `PHP Max Input Vars: ${info.php_max_input_vars || 'N/A'}\n`;
        report += `PHP Post Max Size: ${info.php_post_max_size || 'N/A'}\n`;
        report += `PHP Upload Max Filesize: ${info.php_upload_max_filesize || 'N/A'}\n`;
        report += `PHP Max File Uploads: ${info.php_max_file_uploads || 'N/A'}\n`;
        report += `PHP Allow URL fopen: ${info.php_allow_url_fopen || 'N/A'}\n`;
        report += `PHP Display Errors: ${info.php_display_errors || 'N/A'}\n`;
        report += `PHP Session Save Path: ${info.php_session_save_path || 'N/A'}\n`;
        report += `MySQL Version: ${info.mysql_version || 'N/A'}\n`;
        report += `MySQL Host: ${info.mysql_host || 'N/A'}\n`;
        report += `MySQL Database: ${info.mysql_database || 'N/A'}\n`;
        report += `MySQL Charset: ${info.mysql_charset || 'N/A'}\n`;
        report += `MySQL Collate: ${info.mysql_collate || 'N/A'}\n\n`;
        
        // PHP Extensions
        if (info.php_extensions && info.php_extensions.length > 0) {
            report += `=== PHP Extensions ===\n`;
            info.php_extensions.forEach(ext => {
                report += `${ext.name}: ${ext.status}\n`;
            });
            report += `\n`;
        }
        
        // Theme Information
        report += `=== Theme Information ===\n`;
        report += `Active Theme: ${info.theme_name || 'N/A'}\n`;
        report += `Theme Version: ${info.theme_version || 'N/A'}\n`;
        report += `Theme Author: ${info.theme_author || 'N/A'}\n`;
        report += `Theme URI: ${info.theme_uri || 'N/A'}\n`;
        report += `Parent Theme: ${info.parent_theme || 'None'}\n`;
        report += `Child Theme: ${info.child_theme || 'N/A'}\n\n`;
        
        // SkillPulse LMS
        report += `=== SkillPulse LMS ===\n`;
        report += `LMS Version: ${info.lms_version || 'N/A'}\n`;
        report += `Database Version: ${info.lms_db_version || 'N/A'}\n`;
        report += `Total Courses: ${info.total_courses || '0'}\n`;
        report += `Total Students: ${info.total_students || '0'}\n\n`;
        
        // Active Plugins
        if (info.active_plugins && info.active_plugins.length > 0) {
            report += `=== Active Plugins ===\n`;
            if (info.total_plugins) {
                report += `Total Plugins Installed: ${info.total_plugins}\n`;
                report += `Active Plugins: ${info.active_plugins.length}\n\n`;
            }
            info.active_plugins.forEach(plugin => {
                report += `${plugin.name} (v${plugin.version}) by ${plugin.author || 'Unknown'}\n`;
            });
            report += `\n`;
        }
        
        // Must-Use Plugins
        if (info.mu_plugins && info.mu_plugins.length > 0) {
            report += `=== Must-Use Plugins ===\n`;
            info.mu_plugins.forEach(plugin => {
                report += `${plugin.name} (v${plugin.version})\n`;
            });
            report += `\n`;
        }
        
        // Additional Information
        report += `=== Additional Information ===\n`;
        report += `Report Generated By: ${currentUser.name || 'admin'} (ID: ${currentUser.id || '1'})\n`;
        report += `Server Time: ${currentDate}\n`;
        report += `Timezone: ${Intl.DateTimeFormat().resolvedOptions().timeZone}\n`;
        report += `User Agent: ${navigator.userAgent}\n`;
        
        return report;
    };
    // State for managing which sections are expanded - all collapsed by default
    const [expandedSections, setExpandedSections] = useState({
        'wordpress-environment': false,
        'server-environment': false,
        'php-extensions': false,
        'directories-sizes': false,
        'filesystem-permissions': false,
        'theme-information': false,
        'skillpulse-lms': false,
        'active-plugins': false,
        'mu-plugins': false
    });

    const toggleSection = (sectionKey) => {
        setExpandedSections(prev => ({
            ...prev,
            [sectionKey]: !prev[sectionKey]
        }));
    };




    const AccordionSection = ({ sectionKey, title, children, count = null }) => {
        const isExpanded = expandedSections[sectionKey];
        
        return (
            <div className="info-section accordion-section">
                <h3 
                    className="accordion-header"
                    onClick={() => toggleSection(sectionKey)}
                    aria-expanded={isExpanded}
                    role="button"
                    tabIndex={0}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            toggleSection(sectionKey);
                        }
                    }}
                >
                    <span className="section-title">
                        {title}
                        {count !== null && <span className="section-count">({count})</span>}
                    </span>
                    <SplmsIcon mode="wp"
                        icon={isExpanded ? 'arrow-up-alt2' : 'arrow-down-alt2'} 
                        className="accordion-icon"
                    />
                </h3>
                <div className={`accordion-content ${isExpanded ? 'expanded' : 'collapsed'}`}>
                    <div className="accordion-inner">
                        {children}
                    </div>
                </div>
            </div>
        );
    };

    return (
        <Modal
            title={__('System Information Report', 'skillpulse-lms')}
            onRequestClose={onCloseSystemInfoModal}
            className="splms-tools-modal system-info-modal"
            size="large"
        >
            <div className="system-info-wrapper">
                <div className="system-info-description">
                    <p>{__('Complete system information for troubleshooting and support. Share this report with support when reporting issues.', 'skillpulse-lms')}</p>
                </div>
                
                <div className="modal-body">
                {systemInfoLoading && (
                    <div className="system-info-loading">
                        <Spinner />
                        <p>{__('Loading system information...', 'skillpulse-lms')}</p>
                    </div>
                )}
                
                <div 
                    className={`system-info-sections ${systemInfoLoading ? 'loading' : ''}`}
                >
                    {/* WordPress Environment */}
                    <AccordionSection 
                        sectionKey="wordpress-environment"
                        title={__('WordPress Environment', 'skillpulse-lms')}
                    >
                        <div className="info-grid">
                            <div className="info-item">
                                <strong>{__('WordPress Version:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_version || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Multisite:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_multisite ? __('Yes', 'skillpulse-lms') : __('No', 'skillpulse-lms')}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Site URL:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.site_url || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Home URL:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.home_url || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('WP Memory Limit:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_memory_limit || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('WP Max Memory Limit:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_max_memory_limit || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('WP Cache:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_cache ? __('Enabled', 'skillpulse-lms') : __('Disabled', 'skillpulse-lms')}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('WP Cron:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_cron_disabled ? __('Disabled', 'skillpulse-lms') : __('Enabled', 'skillpulse-lms')}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Language:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_language || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Timezone:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_timezone || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Admin Email:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.admin_email || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('User Signup:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.users_can_register || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Default Role:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.default_role || 'N/A'}</span>
                            </div>
                            {systemInfo.auto_updates_core && (
                                <div className="info-item">
                                    <strong>{__('Auto Updates (Core):', 'skillpulse-lms')}</strong>
                                    <span>{systemInfo.auto_updates_core || 'N/A'}</span>
                                </div>
                            )}
                            {systemInfo.auto_updates_plugins && (
                                <div className="info-item">
                                    <strong>{__('Auto Updates (Plugins):', 'skillpulse-lms')}</strong>
                                    <span>{systemInfo.auto_updates_plugins || 'N/A'}</span>
                                </div>
                            )}
                            {systemInfo.auto_updates_themes && (
                                <div className="info-item">
                                    <strong>{__('Auto Updates (Themes):', 'skillpulse-lms')}</strong>
                                    <span>{systemInfo.auto_updates_themes || 'N/A'}</span>
                                </div>
                            )}
                        </div>
                    </AccordionSection>

                    {/* Server Environment */}
                    <AccordionSection 
                        sectionKey="server-environment"
                        title={__('Server Environment', 'skillpulse-lms')}
                    >
                        <div className="info-grid">
                            <div className="info-item">
                                <strong>{__('Server Software:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.server_software || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Server OS:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.server_os || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Architecture:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.server_architecture || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('PHP Version:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_version || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('PHP SAPI:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_sapi || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('PHP Memory Limit:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_memory_limit || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Max Execution Time:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_max_execution_time || 'N/A'}s</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Max Input Vars:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_max_input_vars || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Post Max Size:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_post_max_size || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Upload Max Filesize:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_upload_max_filesize || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Max File Uploads:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_max_file_uploads || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Allow URL fopen:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_allow_url_fopen || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Display Errors:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_display_errors || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Session Save Path:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.php_session_save_path || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('MySQL Version:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.mysql_version || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('MySQL Host:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.mysql_host || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('MySQL Database:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.mysql_database || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('MySQL Charset:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.mysql_charset || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('MySQL Collate:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.mysql_collate || 'N/A'}</span>
                            </div>
                        </div>
                    </AccordionSection>

                    {/* PHP Extensions */}
                    {systemInfo.php_extensions && systemInfo.php_extensions.length > 0 && (
                        <AccordionSection 
                            sectionKey="php-extensions"
                            title={__('PHP Extensions', 'skillpulse-lms')}
                        >
                            <div className="plugins-list">
                                {systemInfo.php_extensions.map((extension, index) => (
                                    <div key={index} className={`plugin-item ${extension.loaded ? 'enabled' : 'disabled'}`}>
                                        <strong>{extension.name}</strong>
                                        <span className={extension.loaded ? 'status-enabled' : 'status-disabled'}>
                                            {extension.status}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </AccordionSection>
                    )}

                    {/* Directories and Sizes */}
                    <AccordionSection 
                        sectionKey="directories-sizes"
                        title={__('Directories and Sizes', 'skillpulse-lms')}
                    >
                        <div className="info-grid">
                            <div className="info-item">
                                <strong>{__('WordPress Path:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_path || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Content Directory:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_content_dir || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Plugin Directory:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_plugin_dir || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Upload Directory:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_upload_dir || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Upload URL:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.wp_upload_url || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Temp Directory:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.temp_dir || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Disk Free Space:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.disk_free_space || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Disk Total Space:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.disk_total_space || 'N/A'}</span>
                            </div>
                        </div>
                        
                        {systemInfo.directory_sizes && systemInfo.directory_sizes.length > 0 && (
                            <div className="plugins-list" style={{marginTop: '20px'}}>
                                {systemInfo.directory_sizes.map((dir, index) => (
                                    <div key={index} className="plugin-item">
                                        <strong>{dir.name}</strong>
                                        <span>{dir.size_formatted}</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </AccordionSection>

                    {/* Filesystem Permissions */}
                    {systemInfo.filesystem_permissions && systemInfo.filesystem_permissions.length > 0 && (
                        <AccordionSection 
                            sectionKey="filesystem-permissions"
                            title={__('Filesystem Permissions', 'skillpulse-lms')}
                        >
                            <div className="plugins-list">
                                {systemInfo.filesystem_permissions.map((perm, index) => (
                                    <div key={index} className="plugin-item">
                                        <div className="permission-details">
                                            <strong>{perm.name}</strong>
                                            <div className="permission-status">
                                                <span className={`permission-badge ${perm.readable ? 'readable' : 'not-readable'}`}>
                                                    R: {perm.readable ? 'Yes' : 'No'}
                                                </span>
                                                <span className={`permission-badge ${perm.writable ? 'writable' : 'not-writable'}`}>
                                                    W: {perm.writable ? 'Yes' : 'No'}
                                                </span>
                                                <span className="permission-badge">
                                                    {perm.permissions}
                                                </span>
                                                <span className="permission-badge">
                                                    {perm.owner}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </AccordionSection>
                    )}

                    {/* Theme Information */}
                    <AccordionSection 
                        sectionKey="theme-information"
                        title={__('Theme Information', 'skillpulse-lms')}
                    >
                        <div className="info-grid">
                            <div className="info-item">
                                <strong>{__('Active Theme:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.theme_name || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Theme Version:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.theme_version || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Theme Author:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.theme_author || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Theme URI:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.theme_uri || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Parent Theme:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.parent_theme || __('None', 'skillpulse-lms')}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Child Theme:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.child_theme || 'N/A'}</span>
                            </div>
                        </div>
                    </AccordionSection>

                    {/* SkillPulse LMS */}
                    <AccordionSection 
                        sectionKey="skillpulse-lms"
                        title={__('SkillPulse LMS', 'skillpulse-lms')}
                    >
                        <div className="info-grid">
                            <div className="info-item">
                                <strong>{__('LMS Version:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.lms_version || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Database Version:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.lms_db_version || 'N/A'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Total Courses:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.total_courses || '0'}</span>
                            </div>
                            <div className="info-item">
                                <strong>{__('Total Students:', 'skillpulse-lms')}</strong>
                                <span>{systemInfo.total_students || '0'}</span>
                            </div>
                        </div>
                    </AccordionSection>

                    {/* Active Plugins */}
                    {systemInfo.active_plugins && systemInfo.active_plugins.length > 0 && (
                        <AccordionSection 
                            sectionKey="active-plugins"
                            title={__('Active Plugins', 'skillpulse-lms')}
                            count={systemInfo.active_plugins.length}
                        >
                            {systemInfo.total_plugins && (
                                <div className="info-grid" style={{marginBottom: '16px'}}>
                                    <div className="info-item">
                                        <strong>{__('Total Plugins Installed:', 'skillpulse-lms')}</strong>
                                        <span>{systemInfo.total_plugins}</span>
                                    </div>
                                </div>
                            )}
                            <div className="plugins-list">
                                {systemInfo.active_plugins.map((plugin, index) => (
                                    <div key={index} className="plugin-item">
                                        <strong>{plugin.name}</strong>
                                        <span>v{plugin.version}</span>
                                    </div>
                                ))}
                            </div>
                        </AccordionSection>
                    )}

                    {/* Must-Use Plugins */}
                    {systemInfo.mu_plugins && systemInfo.mu_plugins.length > 0 && (
                        <AccordionSection 
                            sectionKey="mu-plugins"
                            title={__('Must-Use Plugins', 'skillpulse-lms')}
                            count={systemInfo.mu_plugins.length}
                        >
                            <div className="plugins-list">
                                {systemInfo.mu_plugins.map((plugin, index) => (
                                    <div key={index} className="plugin-item">
                                        <strong>{plugin.name}</strong>
                                        <span>v{plugin.version}</span>
                                    </div>
                                ))}
                            </div>
                        </AccordionSection>
                    )}
                </div>
                </div>
                <footer className="splms-footer">
                    <div className="splms-footer-actions">
                        {!systemInfoLoading && (
                            <Button 
                                isSecondary
                                onClick={onRefreshSystemInfo}
                                className="refresh-data-btn"
                            >
                                <SplmsIcon mode="wp" icon="update" />
                                {__('Refresh Data', 'skillpulse-lms')}
                            </Button>
                        )}
                        <Button 
                            isSecondary
                            onClick={copyReportToClipboard}
                            disabled={systemInfoLoading}
                        >
                            <SplmsIcon mode="wp" icon={copied ? "yes" : "admin-page"} />
                            {copied ? __('Copied!', 'skillpulse-lms') : __('Copy to Clipboard', 'skillpulse-lms')}
                        </Button>
                        <Button 
                            isPrimary 
                            onClick={onDownloadSystemReport}
                            isBusy={downloadingReport}
                            disabled={downloadingReport || systemInfoLoading}
                            className={downloadingReport ? 'downloading' : ''}
                            aria-describedby="download-button-description"
                        >
                            <SplmsIcon mode="wp" icon={downloadingReport ? "update" : "download"} />
                            {downloadingReport ? __('Downloading...', 'skillpulse-lms') : __('Download Report', 'skillpulse-lms')}
                        </Button>
                        <div id="download-button-description" className="screen-reader-text">
                            {downloadingReport ? 
                                __('Downloading report...', 'skillpulse-lms') :
                                __('Report downloaded', 'skillpulse-lms')
                            }
                        </div>
                    </div>
                </footer>
            </div>
        </Modal>
    );
};

export default Modals; 