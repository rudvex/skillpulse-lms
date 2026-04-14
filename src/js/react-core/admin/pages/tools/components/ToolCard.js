import { Card, CardBody, CardHeader } from '@wordpress/components';
import { SplmsIcon } from "../../../../components/SplmsIcon";

const ToolCard = ({ title, description, icon, children, status = null, className = '' }) => (
    <Card className={`tool-card ${className}`}>
        <CardHeader>
            <div className="tool-header">
                <div className="tool-icon">
                    <SplmsIcon mode="wp" icon={icon} size={24} />
                </div>
                <div className="tool-info">
                    <h3>{title}</h3>
                    <p>{description}</p>
                </div>
                {status && (
                    <div className={`tool-status ${status.type}`}>
                        {status.icon && <SplmsIcon mode="wp" icon={status.icon} size={16} />}
                        <span>{status.text}</span>
                    </div>
                )}
            </div>
        </CardHeader>
        <CardBody>
            {children}
        </CardBody>
    </Card>
);

export default ToolCard; 