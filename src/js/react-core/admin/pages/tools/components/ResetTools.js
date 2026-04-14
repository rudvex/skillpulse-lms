import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { SplmsIcon } from "../../../../components/SplmsIcon";
import ToolCard from './ToolCard';

const ResetTools = ({ isLoading, onShowResetModal }) => {
    return (
        <div className="tools-section">
            <div className="tools-grid">
                <ToolCard
                    title={__('Reset LMS Data', 'skillpulse-lms')}
                    description={__('Selectively reset different types of LMS data with confirmation.', 'skillpulse-lms')}
                    icon="trash"
                >
                    <div className="tool-actions">
                        <Button 
                            isDestructive 
                            onClick={onShowResetModal}
                            disabled={isLoading}
                        >
                            <SplmsIcon mode="wp" icon="trash" />
                            {__('Reset LMS Data', 'skillpulse-lms')}
                        </Button>
                        <p className="tool-note">
                            {__('Choose specific data types to reset. This action cannot be undone.', 'skillpulse-lms')}
                        </p>
                    </div>
                </ToolCard>
            </div>
        </div>
    );
};

export default ResetTools; 