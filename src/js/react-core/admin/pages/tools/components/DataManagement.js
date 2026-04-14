import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { SplmsIcon } from "../../../../components/SplmsIcon";
import ToolCard from './ToolCard';

const DataManagement = ({ isLoading, onExportCourses, onExportUserProgress, onImportCourses }) => {
    return (
        <div className="tools-section">
            <div className="tools-grid">
                <ToolCard
                    title={__('Export Courses', 'skillpulse-lms')}
                    description={__('Export all course data as JSON for backup purposes.', 'skillpulse-lms')}
                    icon="download"
                >
                    <div className="tool-actions">
                        <Button isPrimary onClick={onExportCourses} disabled={isLoading}>
                            <SplmsIcon mode="wp" icon="download" />
                            {__('Export Courses', 'skillpulse-lms')}
                        </Button>
                        <p className="tool-note">
                            {__('Exports all courses with metadata, lessons, and settings.', 'skillpulse-lms')}
                        </p>
                    </div>
                </ToolCard>
                
                <ToolCard
                    title={__('Export User Progress', 'skillpulse-lms')}
                    description={__('Export user enrollment and progress data for backup purposes.', 'skillpulse-lms')}
                    icon="groups"
                >
                    <div className="tool-actions">
                        <Button isPrimary onClick={onExportUserProgress} disabled={isLoading}>
                            <SplmsIcon mode="wp" icon="download" />
                            {__('Export User Progress', 'skillpulse-lms')}
                        </Button>
                        <p className="tool-note">
                            {__('Exports all user enrollments, progress, and completion data.', 'skillpulse-lms')}
                        </p>
                    </div>
                </ToolCard>
                
                <ToolCard
                    title={__('Import Courses', 'skillpulse-lms')}
                    description={__('Import course data from JSON or CSV files.', 'skillpulse-lms')}
                    icon="upload"
                >
                    <div className="tool-actions">
                        <input
                            type="file"
                            accept=".json,.csv"
                            onChange={(e) => onImportCourses(e.target.files[0])}
                            disabled={isLoading}
                        />
                        <p className="tool-note">
                            {__('Supports JSON and CSV formats. Existing courses will be updated.', 'skillpulse-lms')}
                        </p>
                    </div>
                </ToolCard>
            </div>
        </div>
    );
};

export default DataManagement; 