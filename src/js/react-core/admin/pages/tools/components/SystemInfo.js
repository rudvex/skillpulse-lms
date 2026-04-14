import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { SplmsIcon } from "../../../../components/SplmsIcon";
import ToolCard from './ToolCard';

const SystemInfo = ({ systemInfo, downloadingReport, onShowSystemInfoModal, onDownloadSystemReport }) => {
    return (
        <div className="tools-section">
            <div className="tools-grid">
                <ToolCard
                    title={__('System Information', 'skillpulse-lms')}
                    description={__('View complete system details and generate reports for support.', 'skillpulse-lms')}
                    icon="info"
                >
                    <div className="tool-actions">
                        <div className="system-info-summary">
                            <div className="info-row">
                                <span className="info-label">{__('PHP Version:', 'skillpulse-lms')}</span>
                                <span className="info-value">{systemInfo.php_version || 'N/A'}</span>
                            </div>
                            <div className="info-row">
                                <span className="info-label">{__('WordPress Version:', 'skillpulse-lms')}</span>
                                <span className="info-value">{systemInfo.wp_version || 'N/A'}</span>
                            </div>
                            <div className="info-row">
                                <span className="info-label">{__('Active Theme:', 'skillpulse-lms')}</span>
                                <span className="info-value">{systemInfo.theme_name || 'N/A'}</span>
                            </div>
                            <div className="info-row">
                                <span className="info-label">{__('LMS Version:', 'skillpulse-lms')}</span>
                                <span className="info-value">{systemInfo.lms_version || 'N/A'}</span>
                            </div>
                            <div className="info-row">
                                <span className="info-label">{__('Total Courses:', 'skillpulse-lms')}</span>
                                <span className="info-value">{systemInfo.total_courses || '0'}</span>
                            </div>
                            <div className="info-row">
                                <span className="info-label">{__('Total Students:', 'skillpulse-lms')}</span>
                                <span className="info-value">{systemInfo.total_students || '0'}</span>
                            </div>
                        </div>
                        <div className="system-info-buttons">
                            <Button 
                                isPrimary 
                                onClick={onShowSystemInfoModal}
                            >
                                <SplmsIcon mode="wp" icon="visibility" />
                                {__('View Full Report', 'skillpulse-lms')}
                            </Button>
                            <Button 
                                isSecondary 
                                onClick={onDownloadSystemReport}
                                isBusy={downloadingReport}
                                disabled={downloadingReport}
                                className={downloadingReport ? 'downloading' : ''}
                            >
                                <SplmsIcon mode="wp" icon={downloadingReport ? "update" : "download"} />
                                {downloadingReport ? __('Downloading...', 'skillpulse-lms') : __('Download Report', 'skillpulse-lms')}
                            </Button>
                        </div>
                    </div>
                </ToolCard>
            </div>
        </div>
    );
};

export default SystemInfo; 