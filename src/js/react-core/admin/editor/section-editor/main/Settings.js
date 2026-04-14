import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { Component } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { Card, CardBody, CardHeader, Spinner, Flex, FlexBlock } from '@wordpress/components';
import Field from "../../../../components/Field";
import { SplmsIcon } from "../../../../components/SplmsIcon";
import { translateLabel, getConfig, isVisibleField } from "../../../../utility/helper";
import { fetchApiData, formatOptions } from '../../../../utility/apiHelper';
import { AutoSaveManager, AutoSaveIndicator } from '../../../../utility/debounce';

class Settings extends Component {
    constructor(props) {
        super(props);
        this.state = {
            config: null,
            configLoading: true,
            configError: null,
            dynamicOptions: {},
            selectedOptions: {},
            loading: {},
            activeSection: null,
            saveStatus: 'idle'
        };

        this.getAllFields = this.getAllFields.bind(this);
        this.getFieldValueFromSettings = this.getFieldValueFromSettings.bind(this);

        // Initialize auto-save manager.
        this.autoSaveManager = new AutoSaveManager(
            this.executeBatchSave.bind(this),
            {
                delay: 2000,
                onSaveStart: this.handleSaveStart.bind(this),
                onSaveSuccess: this.handleSaveSuccess.bind(this),
                onSaveError: this.handleSaveError.bind(this)
            }
        );
    }

    async componentDidMount() {
        // Load configuration with embedded values - no separate settings fetch needed
        await this.loadConfiguration();
    }

    /**
     * Load configuration from the new Config Loader API with embedded values
     */
    async loadConfiguration() {
        try {
            this.setState({ configLoading: true, configError: null });
            const { postId } = this.props;

            // Detect if this is a new post
            const isNewPost = !postId || postId === 'auto-draft' || postId === 0;

            // Load config with item_id parameter for existing posts
            const configParams = isNewPost ? {} : { item_id: postId };
            const config = await getConfig('section_settings_config', 'admin', configParams);
console.log("config",config);
            if (!config || !config.sections) {
                throw new Error('Invalid configuration structure received');
            }

            // Extract embedded settings values from config
            const extractedSettings = this.extractSettingsFromConfig(config);

            // Populate Redux store with extracted settings
            this.props.setInitialSettings(extractedSettings, postId);

            this.setState({
                config,
                configLoading: false
            });

        } catch (error) {
            console.error('Failed to load section settings configuration:', error);
            this.setState({
                configError: error.message || 'Failed to load configuration',
                configLoading: false
            });
        }
    }

    /**
     * Extract settings values from config fields that have embedded 'value' properties
     */
    extractSettingsFromConfig(config) {
        const settings = {};

        if (!config || !config.sections) return settings;

        config.sections.forEach(section => {
            if (section.fields) {
                section.fields.forEach(field => {
                    if (field.value !== undefined) {
                        if (field.group) {
                            // Handle grouped fields
                            if (!settings[field.group]) {
                                settings[field.group] = {};
                            }
                            settings[field.group][field.id] = field.value;
                        } else {
                            // Handle individual fields
                            settings[field.id] = field.value;
                        }
                    }
                });
            }
        });

        return settings;
    }

    componentDidUpdate(prevProps) {
        const {
            postId,
            isSaving,
            sectionSettings,
            updateAllSectionSetting,
            activeTab
        } = this.props;

        // Reload configuration if the post ID changes
        if (postId !== prevProps.postId && postId) {
            this.loadConfiguration();
        }

        // Load dynamic options when settings change.
        if (sectionSettings && sectionSettings !== prevProps.sectionSettings) {
            this.loadDynamicOptions(sectionSettings);
        }

        // Trigger settings update when the post is saved OR force save pending changes.
        if (isSaving && !prevProps.isSaving && activeTab === 'settings') {
            // Force save any pending auto-save changes first.
            this.forceSave().then(() => {
                updateAllSectionSetting(postId, sectionSettings);
            });
        }
    }

    getAllFields() {
        const { config } = this.state;
        if (!config || !config.sections) {
            return [];
        }

        let allFields = [];

        config.sections.forEach(section => {
            section.fields.forEach(field => {
                if (field.group) {
                    allFields.push({
                        ...field,
                        groupKey: field.group,
                        isGrouped: true
                    });
                } else {
                    allFields.push({
                        ...field,
                        groupKey: null,
                        isGrouped: false
                    });
                }
            });
        });

        return allFields;
    }

    loadDynamicOptions(sectionSettings) {
        const allFields = this.getAllFields();

        allFields.forEach(field => {
            if (field.api) {
                this.fetchFieldOptions(field);
            }
        });
    }

    async fetchFieldOptions(field) {
        this.setState(prevState => ({
            loading: { ...prevState.loading, [field.id]: true }
        }));
        try {
            const params = {
                ...field.api.params,
            };

            const data = await fetchApiData(field.api.endpoint, params);
            const dataToFormat = field.api.dataPath ? (data[field.api.dataPath] || data) : data;
            const formattedOptions = formatOptions(dataToFormat, field.api.useProperties);
            this.setState(prevState => ({
                dynamicOptions: { ...prevState.dynamicOptions, [field.id]: formattedOptions },
                loading: { ...prevState.loading, [field.id]: false }
            }));
        } catch (error) {
            console.error(`Error fetching options for field ${field.id}:`, error);
            this.setState(prevState => ({
                loading: { ...prevState.loading, [field.id]: false }
            }));
        }
    }

    handleFieldChangeWithAutoSave = (postId, fieldId, value) => {
        const allFields = this.getAllFields();
        const field = allFields.find(f => f.id === fieldId);

        // Update state immediately via Redux with group information.
        if (field && field.isGrouped && field.groupKey) {
            this.props.updateSetting(postId, fieldId, value, field.groupKey);
        } else {
            this.props.updateSetting(postId, fieldId, value);
        }

        // Queue for debounced auto-save.
        this.autoSaveManager.queueSave(fieldId, value, {
            timestamp: Date.now(),
            postId: postId,
            field: field
        });
    }

    executeBatchSave = async (pendingChanges) => {
        const { postId, sectionSettings } = this.props;
        if (!postId) return;

        const updatedSettings = JSON.parse(JSON.stringify(sectionSettings || {}));
        const allFields = this.getAllFields();

        const fieldMap = new Map();
        allFields.forEach(field => {
            fieldMap.set(field.id, field);
        });

        for (const [fieldId, changeData] of pendingChanges) {
            const field = fieldMap.get(fieldId);

            if (field && field.isGrouped && field.groupKey) {
                if (!updatedSettings[field.groupKey]) {
                    updatedSettings[field.groupKey] = {};
                }
                updatedSettings[field.groupKey][fieldId] = changeData.value;
            } else {
                updatedSettings[fieldId] = changeData.value;
            }
        }

        return await this.props.updateAllSectionSetting(postId, updatedSettings);
    }

    handleSaveStart = (saveInfo) => {
        if (saveInfo.isPending) {
            this.setState({ saveStatus: 'pending' });
        } else if (saveInfo.isSaving) {
            this.setState({ saveStatus: 'saving' });
        }
    }

    handleSaveSuccess = (saveInfo) => {
        this.setState({ saveStatus: 'success' });
        setTimeout(() => {
            this.setState({ saveStatus: 'idle' });
        }, 3000);
    }

    handleSaveError = (errorInfo) => {
        this.setState({ saveStatus: 'error' });
        setTimeout(() => {
            this.setState({ saveStatus: 'idle' });
        }, 5000);
    }

    forceSave = async () => {
        if (this.autoSaveManager.hasPendingChanges()) {
            return await this.autoSaveManager.forceSave();
        }
    }

    handleInputChange = async (field, searchValue) => {
        if (searchValue.length < 2) return;

        this.setState(prevState => ({
            loading: { ...prevState.loading, [field.id]: true }
        }));

        try {
            const params = {
                ...field.api.params,
                search: searchValue
            };
            const data = await fetchApiData(field.api.endpoint, params);
            const dataToFormat = field.api.dataPath ? (data[field.api.dataPath] || data) : data;
            const formattedOptions = formatOptions(dataToFormat, field.api.useProperties);
            this.setState(prevState => ({
                dynamicOptions: { ...prevState.dynamicOptions, [field.id]: formattedOptions },
                loading: { ...prevState.loading, [field.id]: false }
            }));
        } catch (error) {
            console.error(`Error fetching selected options for field ${field.id}:`, error);
            this.setState(prevState => ({
                loading: { ...prevState.loading, [field.id]: false }
            }));
        }
    }

    renderSaveStatus = () => {
        const { saveStatus } = this.state;
        const pendingCount = this.autoSaveManager.getPendingCount();

        return (
            <AutoSaveIndicator
                saveStatus={saveStatus}
                pendingCount={pendingCount}
                success={__('Settings auto-saved', 'skillpulse-lms')}
            />
        );
    }

    shouldDisplayField(field, sectionSettings) {
        if (!field.conditional) {
            return true;
        }

        return isVisibleField(field, sectionSettings, this.getAllFields, this.getFieldValueFromSettings);
    }

    getFieldValueFromSettings(sectionSettings, field) {
        if (!sectionSettings) return undefined;

        if (field.group) {
            return sectionSettings[field.group]?.[field.id];
        } else {
            return sectionSettings[field.id];
        }
    }

    getFieldValue(field, sectionSettings) {
        const rawValue = this.getFieldValueFromSettings(sectionSettings, field);
        const finalValue = rawValue !== undefined ? rawValue : field.default;

        if (field.type === 'toggle') {
            return Boolean(finalValue);
        }

        return finalValue;
    }

    getFieldOptions(field, dynamicOptions) {
        let finalOptions = [];

        if (field.options && Array.isArray(field.options)) {
            finalOptions = field.options.map(opt => ({
                label: translateLabel(opt.label),
                value: opt.value
            }));
        }

        if (dynamicOptions[field.id] && Array.isArray(dynamicOptions[field.id])) {
            const dynamicOpts = dynamicOptions[field.id];
            const existingValues = new Set(finalOptions.map(opt => opt.value));
            const uniqueDynamicOpts = dynamicOpts.filter(opt => !existingValues.has(opt.value));
            finalOptions = [...finalOptions, ...uniqueDynamicOpts];
        }

        return finalOptions;
    }

    getFieldClasses(field) {
        const classes = ['enhanced-field'];
        classes.push('splms-field');
        classes.push('splms-field-type-' + field.type);

        if (field.column === 'half') {
            classes.push('half-width');
        } else {
            classes.push('full-width');
        }

        return classes.join(' ');
    }

    renderStandardField(field, sectionSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId) {
        const fieldValue = this.getFieldValue(field, sectionSettings);
        const fieldOptions = this.getFieldOptions(field, dynamicOptions);
        const fieldClasses = this.getFieldClasses(field);

        return (
            <div key={field.id} className={fieldClasses}>
                <Field
                    {...field}
                    value={fieldValue}
                    options={fieldOptions}
                    onChange={(value) => this.handleFieldChangeWithAutoSave(postId, field.id, value)}
                    onInputChange={(searchValue) => this.handleInputChange(field, searchValue)}
                    loading={loading[field.id]}
                    selectedOptions={selectedOptions[field.id] || []}
                />
            </div>
        );
    }

    renderField(field, sectionSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId) {
        return this.renderStandardField(
            field,
            sectionSettings,
            dynamicOptions,
            selectedOptions,
            loading,
            updateSetting,
            postId
        );
    }

    renderSection(section, sectionSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId) {
        const sectionFields = section.fields.filter(field =>
            this.shouldDisplayField(field, sectionSettings)
        );

        if (sectionFields.length === 0) return null;
        return (
            <Card key={section.id} className="settings-section">
                <CardHeader>
                    <Flex align="center" gap={3}>
                        <SplmsIcon mode={section.mode || 'wp'} name={section.icon || 'admin-generic'} size={20} />
                        <FlexBlock>
                            <h3>{section.title}</h3>
                        </FlexBlock>
                    </Flex>
                </CardHeader>
                <CardBody>
                    <div className="section-fields">
                        {sectionFields.map((field) =>
                            this.renderField(
                                field,
                                sectionSettings,
                                dynamicOptions,
                                selectedOptions,
                                loading,
                                updateSetting,
                                postId
                            )
                        )}
                    </div>
                </CardBody>
            </Card>
        );
    }

    render() {
        const { sectionSettings, isLoading, error, updateSetting, postId } = this.props;
        const { config, configLoading, configError, dynamicOptions, selectedOptions, loading, activeSection } = this.state;

        // Show loading if configuration is still loading
        if (configLoading) {
            return (
                <div className="settings-loading">
                    <Spinner />
                    <p>{__('Loading configuration...', 'skillpulse-lms')}</p>
                </div>
            );
        }

        // Show error if configuration failed to load
        if (configError) {
            return (
                <div className="splms-error-message" style={{ padding: '15px', background: 'var(--splms-danger-bg, #f8d7da)', border: '1px solid var(--splms-danger-border, #f5c6cb)', borderRadius: 'var(--splms-border-radius, 4px)', color: 'var(--splms-danger-text, #721c24)', margin: '20px' }}>
                    {__('Configuration Error:', 'skillpulse-lms')} {configError}
                </div>
            );
        }

        // Show error if config is invalid
        if (!config || !config.sections) {
            return (
                <div className="splms-error-message" style={{ padding: '15px', background: 'var(--splms-danger-bg, #f8d7da)', border: '1px solid var(--splms-danger-border, #f5c6cb)', borderRadius: 'var(--splms-border-radius, 4px)', color: 'var(--splms-danger-text, #721c24)', margin: '20px' }}>
                    {__('Invalid configuration: No sections found', 'skillpulse-lms')}
                </div>
            );
        }

        if (isLoading || typeof sectionSettings !== 'object' || Object.keys(sectionSettings).length === 0) {
            return (
                <div className="settings-loading">
                    <Spinner />
                    <p>{__('Loading section settings...', 'skillpulse-lms')}</p>
                </div>
            );
        }

        if (error) {
            if (window.skillpulseToast) {
                window.skillpulseToast.error(error.message);
            }
            return (
                <div className="splms-error-message" style={{ padding: '15px', background: 'var(--splms-danger-bg, #f8d7da)', border: '1px solid var(--splms-danger-border, #f5c6cb)', borderRadius: 'var(--splms-border-radius, 4px)', color: 'var(--splms-danger-text, #721c24)', margin: '20px' }}>
                    {error.message}
                </div>
            );
        }

        return (
            <div className="enhanced-section-settings enhanced-settings-wrapper">
                <div className="settings-header">
                    <div className="header-content">
                        <SplmsIcon mode="wp" name="welcome-learn-more" />
                        <div>
                            <h2>{__('Section Settings', 'skillpulse-lms')}</h2>
                            <p>{__('Configure your section content, pricing, and access settings', 'skillpulse-lms')}</p>
                        </div>
                    </div>
                    {this.renderSaveStatus()}
                </div>

                <div className="settings-sections">
                    {config.sections.map((section) =>
                        this.renderSection(
                            section,
                            sectionSettings,
                            dynamicOptions,
                            selectedOptions,
                            loading,
                            updateSetting,
                            postId
                        )
                    )}
                </div>

                <div className="settings-footer">
                    <Card className="auto-save-info">
                        <CardBody>
                            <Flex align="center" gap={2}>
                                <SplmsIcon mode="wp" name="saved" size={16} />
                                <span>{__('Settings are automatically saved as you make changes. No manual save required.', 'skillpulse-lms')}</span>
                            </Flex>
                        </CardBody>
                    </Card>
                </div>
            </div>
        );
    }
}

export default compose([
    withDispatch((dispatch, props) => {
        const { fetchSettings, updateSetting, updateAllSectionSetting, fetchSettingsSuccess } = dispatch('splms/section-settings');
        return {
            fetchSettings,
            updateSetting,
            updateAllSectionSetting,
            setInitialSettings: fetchSettingsSuccess,
        };
    }),
    withSelect((select) => {
        const { getEditedPostAttribute, isSavingPost } = select("core/editor");
        const { getSectionSettings, isLoading, getError } = select("splms/section-settings");
        const postId = getEditedPostAttribute ? getEditedPostAttribute("id") : null;
        const { getActiveTab } = select('splms/section-tabs');
        return {
            postId,
            sectionSettings: getSectionSettings(),
            isLoading: isLoading(),
            isSaving: isSavingPost(),
            error: getError(),
            activeTab: getActiveTab(),
        };
    }),
])(Settings);
