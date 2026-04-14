import { __ } from '@wordpress/i18n';
import { Button, TextControl, SelectControl, TextareaControl } from '@wordpress/components';
import { SplmsIcon } from "../../../components/SplmsIcon";
import ToolCard from './ToolCard';

const ApiTools = ({ 
    apiKeys, 
    testEndpoint, 
    testMethod, 
    testData, 
    testResult,
    onGenerateApiKey,
    onTestEndpointChange,
    onTestMethodChange,
    onTestDataChange,
    onTestApiEndpoint
}) => {
    return (
        <div className="tools-section">
            <div className="tools-grid">
                <ToolCard
                    title={__('API Key Management', 'skillpulse-lms')}
                    description={__('Generate and manage API keys for REST API access.', 'skillpulse-lms')}
                    icon="admin-network"
                >
                    <div className="tool-actions">
                        <div className="api-keys-list">
                            {apiKeys.map((key, index) => (
                                <div key={index} className="api-key-item">
                                    <span>{key.name}</span>
                                    <span className="api-key-value">{key.key}</span>
                                </div>
                            ))}
                        </div>
                        <Button isPrimary onClick={onGenerateApiKey}>
                            <SplmsIcon mode="wp" icon="admin-network" />
                            {__('Generate API Key', 'skillpulse-lms')}
                        </Button>
                    </div>
                </ToolCard>
                
                <ToolCard
                    title={__('API Endpoint Tester', 'skillpulse-lms')}
                    description={__('Test REST API endpoints directly from the admin interface.', 'skillpulse-lms')}
                    icon="rest-api"
                >
                    <div className="tool-actions">
                        <TextControl
                            label={__('Endpoint URL', 'skillpulse-lms')}
                            value={testEndpoint}
                            onChange={onTestEndpointChange}
                            placeholder="/wp-json/splms/v1/courses"
                        />
                        <SelectControl
                            label={__('Method', 'skillpulse-lms')}
                            value={testMethod}
                            options={[
                                { value: 'GET', label: 'GET' },
                                { value: 'POST', label: 'POST' },
                                { value: 'PUT', label: 'PUT' },
                                { value: 'DELETE', label: 'DELETE' }
                            ]}
                            onChange={onTestMethodChange}
                        />
                        <TextareaControl
                            label={__('Request Data (JSON)', 'skillpulse-lms')}
                            value={testData}
                            onChange={onTestDataChange}
                            placeholder='{"key": "value"}'
                        />
                        <Button isPrimary onClick={onTestApiEndpoint}>
                            <SplmsIcon mode="wp" icon="rest-api" />
                            {__('Test Endpoint', 'skillpulse-lms')}
                        </Button>
                        {testResult && (
                            <div className="api-test-result">
                                <h4>{__('Response:', 'skillpulse-lms')}</h4>
                                <pre>{testResult}</pre>
                            </div>
                        )}
                    </div>
                </ToolCard>
            </div>
        </div>
    );
};

export default ApiTools; 