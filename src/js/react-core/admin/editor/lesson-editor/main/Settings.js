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

// import '../styles/settings.scss';

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
            saveStatus: 'idle' // idle, pending, saving, success, error
        };

        this.getAllFields = this.getAllFields.bind(this);
        this.getFieldValueFromSettings = this.getFieldValueFromSettings.bind(this);

        // Initialize auto-save manager
        this.autoSaveManager = new AutoSaveManager(
            this.executeBatchSave.bind(this),
            {
                delay: 2000, // 2 seconds for lesson settings (same as course)
                onSaveStart: this.handleSaveStart.bind(this),
                onSaveSuccess: this.handleSaveSuccess.bind(this),
                onSaveError: this.handleSaveError.bind(this)
            }
        );
    }

    // ============================================================================
    // GROUPED FIELDS SUPPORT
    // ============================================================================
    // This component now supports both grouped and non-grouped field structures:
    //
    // GROUPED STRUCTURE (with group property in field config):
    // {
    //   "completion_settings": {
    //     "completion_type": "manual",
    //     "completion_required": true
    //   },
    //   "content_settings": {
    //     "lesson_type": "video",
    //     "lesson_duration": 30
    //   }
    // }
    //
    // NON-GROUPED STRUCTURE (without group property):
    // {
    //   "lesson_title": "My Lesson",
    //   "lesson_description": "Description"
    // }
    //
    // Key methods modified for grouped support:
    // - getAllFields(): Adds groupKey and isGrouped flags to fields
    // - getFieldValueFromSettings(): Handles grouped vs non-grouped value retrieval
    // - handleFieldChangeWithAutoSave(): Updates Redux state with group info
    // - executeBatchSave(): Structures data correctly for grouped fields
    // - shouldDisplayField(): Handles conditional logic for grouped fields
    // ============================================================================

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
            const config = await getConfig('lesson_settings_config', 'admin', configParams);

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
            console.error('Failed to load lesson settings configuration:', error);
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
            lessonSettings,
            updateAllLessonSetting,
            activeTab
        } = this.props;

        // Reload configuration if the post ID changes
        if (postId !== prevProps.postId && postId) {
            this.loadConfiguration();
        }

        // Load dynamic options when settings change
        if (lessonSettings && lessonSettings !== prevProps.lessonSettings) {
            this.loadDynamicOptions(lessonSettings);
        }
        
        // Trigger settings update when the post is saved OR force save pending changes
        if (isSaving && !prevProps.isSaving && activeTab === 'settings') {
            // Force save any pending auto-save changes first
            this.forceSave().then(() => {
                updateAllLessonSetting(postId, lessonSettings);
            });
        }
    }

    /**
     * Get all fields from all sections (both grouped and non-grouped)
     */
    getAllFields() {
        const { config } = this.state;
        if (!config || !config.sections) {
            return [];
        }

        let allFields = [];

        config.sections.forEach(section => {
            section.fields.forEach(field => {
                if (field.group) {
                    // This is a grouped field - add group information
                    allFields.push({
                        ...field,
                        groupKey: field.group,
                        isGrouped: true
                    });
                } else {
                    // This is a non-grouped field
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

    /**
     * Load dynamic options for fields that require API data.
     */
    loadDynamicOptions(lessonSettings) {
        const allFields = this.getAllFields();

        allFields.forEach(field => {
            if (field.api) {
                this.fetchFieldOptions(field);
            }
        });
    }

    /**
     * Fetch options for a specific field.
     */
    async fetchFieldOptions(field) {
        const { postId } = this.props;
        this.setState(prevState => ({
            loading: { ...prevState.loading, [field.id]: true }
        }));
        try {
            const params = {
                ...field.api.params,
            };

            const data = await fetchApiData(field.api.endpoint, params);
            // Extract nested data if dataPath is specified (e.g., {lessons: [...]} -> lessons array)
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

    /**
     * Handle field changes with debounced auto-save
     */
    handleFieldChangeWithAutoSave = (postId, fieldId, value) => {
        const allFields = this.getAllFields();
        const field = allFields.find(f => f.id === fieldId);
        
        // Update state immediately via Redux with group information
        if (field && field.isGrouped && field.groupKey) {
            this.props.updateSetting(postId, fieldId, value, field.groupKey);
        } else {
            this.props.updateSetting(postId, fieldId, value);
        }
        
        // Queue for debounced auto-save
        this.autoSaveManager.queueSave(fieldId, value, {
            timestamp: Date.now(),
            postId: postId,
            field: field
        });
    }

    /**
     * Execute batch save for all pending changes
     */
    executeBatchSave = async (pendingChanges) => {
        const { postId, lessonSettings } = this.props;
        if (!postId) return;

        // Deep clone the current settings to avoid mutations
        const updatedSettings = JSON.parse(JSON.stringify(lessonSettings || {}));
        const allFields = this.getAllFields();
        
        // Create a map of field ID to field info for quick lookup
        const fieldMap = new Map();
        allFields.forEach(field => {
            fieldMap.set(field.id, field);
        });
        
        for (const [fieldId, changeData] of pendingChanges) {
            const field = fieldMap.get(fieldId);
            
            if (field && field.isGrouped && field.groupKey) {
                // For grouped fields, ensure the group object exists
                if (!updatedSettings[field.groupKey]) {
                    updatedSettings[field.groupKey] = {};
                }
                updatedSettings[field.groupKey][fieldId] = changeData.value;
            } else {
                // For non-grouped fields, set at root level
                updatedSettings[fieldId] = changeData.value;
            }
        }
        
        // Execute the save via Redux action
        return await this.props.updateAllLessonSetting(postId, updatedSettings);
    }

    /**
     * Handle save start feedback
     */
    handleSaveStart = (saveInfo) => {
        if (saveInfo.isPending) {
            this.setState({ saveStatus: 'pending' });
        } else if (saveInfo.isSaving) {
            this.setState({ saveStatus: 'saving' });
        }
    }

    /**
     * Handle save success feedback
     */
    handleSaveSuccess = (saveInfo) => {
        this.setState({ saveStatus: 'success' });

        // Clear success message after 3 seconds
        setTimeout(() => {
            this.setState({ saveStatus: 'idle' });
        }, 3000);
    }

    /**
     * Handle save error feedback
     */
    handleSaveError = (errorInfo) => {
        this.setState({ saveStatus: 'error' });

        // Clear error message after 5 seconds
        setTimeout(() => {
            this.setState({ saveStatus: 'idle' });
        }, 5000);
    }

    /**
     * Force save all pending changes
     */
    forceSave = async () => {
        if (this.autoSaveManager.hasPendingChanges()) {
            return await this.autoSaveManager.forceSave();
        }
    }

    /**
     * Handle input change for search fields.
     */
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
            // Extract nested data if dataPath is specified (e.g., {lessons: [...]} -> lessons array)
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

    /**
     * Render save status indicator
     */
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

    /**
     * Checks if a field should be displayed based on conditional logic.
     */
    shouldDisplayField(field, lessonSettings) {
        if (!field.conditional) {
            return true;
        }

        return isVisibleField(field, lessonSettings, this.getAllFields, this.getFieldValueFromSettings);
    }

    /**
     * Get field value from lessonSettings - simple and direct approach
     */
    getFieldValueFromSettings(lessonSettings, field) {
        if (!lessonSettings) return undefined;

        if (field.group) {
            // For grouped fields, get from the group
            return lessonSettings[field.group]?.[field.id];
        } else {
            // For non-grouped fields, get from root level
            return lessonSettings[field.id];
        }
    }

    /**
     * Get field value with proper type handling - simplified approach
     */
    getFieldValue(field, lessonSettings) {
        const rawValue = this.getFieldValueFromSettings(lessonSettings, field);

        // Use the actual value if it exists, otherwise use default
        const finalValue = rawValue !== undefined ? rawValue : field.default;
        
        // Handle boolean fields
        if (field.type === 'toggle') {
            return Boolean(finalValue);
        }
        
        return finalValue;
    }

    /**
     * Get field options with proper formatting
     */
    getFieldOptions(field, dynamicOptions) {
        let finalOptions = [];
        
        // Start with static field options if they exist
        if (field.options && Array.isArray(field.options)) {
            finalOptions = field.options.map(opt => ({
                label: translateLabel(opt.label),
                value: opt.value
            }));
        }
        
        // If we have dynamic options, merge them with static options
        if (dynamicOptions[field.id] && Array.isArray(dynamicOptions[field.id])) {
            const dynamicOpts = dynamicOptions[field.id];
            const existingValues = new Set(finalOptions.map(opt => opt.value));
            const uniqueDynamicOpts = dynamicOpts.filter(opt => !existingValues.has(opt.value));
            finalOptions = [...finalOptions, ...uniqueDynamicOpts];
        }
        
        return finalOptions;
    }

    /**
     * Get field CSS classes
     */
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

    /**
     * Render field label with icon
     */
    renderFieldLabel(field) {
        if (field.type === 'toggle') {
            return null;
        }

        return (
            <label className="field-label">
                <SplmsIcon mode={field.mode || 'wp'} name={field.icon || 'admin-generic'} size={16} />
                {translateLabel(field.label)}
            </label>
        );
    }

    /**
     * Render a standard field with Field component
     */
    renderStandardField(field, lessonSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId) {
        const fieldValue = this.getFieldValue(field, lessonSettings);
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

    /**
     * Render a field based on its type and special requirements
     */
    renderField(field, lessonSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId) {
        // Render standard field
        return this.renderStandardField(
            field, 
            lessonSettings, 
            dynamicOptions, 
            selectedOptions, 
            loading, 
            updateSetting, 
            postId
        );
    }

    /**
     * Render a section with its fields
     */
    renderSection(section, lessonSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId) {
        // In the new structure, section.fields already contains the field objects
        const sectionFields = section.fields.filter(field => 
            this.shouldDisplayField(field, lessonSettings)
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
                                lessonSettings,
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
        const { lessonSettings, isLoading, error, updateSetting, postId } = this.props;
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
                <div className="splms-error-message" style={{ padding: '15px', background: '#f8d7da', border: '1px solid #f5c6cb', borderRadius: '4px', color: '#721c24', margin: '20px' }}>
                    {__('Configuration Error:', 'skillpulse-lms')} {configError}
                </div>
            );
        }

        // Show error if config is invalid
        if (!config || !config.sections) {
            return (
                <div className="splms-error-message" style={{ padding: '15px', background: '#f8d7da', border: '1px solid #f5c6cb', borderRadius: '4px', color: '#721c24', margin: '20px' }}>
                    {__('Invalid configuration: No sections found', 'skillpulse-lms')}
                </div>
            );
        }

        if (isLoading || typeof lessonSettings !== 'object' || Object.keys(lessonSettings).length === 0) {
            return (
                <div className="settings-loading">
                    <Spinner />
                    <p>{__('Loading lesson settings...', 'skillpulse-lms')}</p>
                </div>
            );
        }

        if (error) {
            if (window.skillpulseToast) {
                window.skillpulseToast.error(error.message);
            }
            return (
                <div className="splms-error-message" style={{ padding: '15px', background: '#f8d7da', border: '1px solid #f5c6cb', borderRadius: '4px', color: '#721c24', margin: '20px' }}>
                    {error.message}
                </div>
            );
        }

        return (
            <div className="enhanced-lesson-settings enhanced-settings-wrapper">
                <div className="settings-header">
                    <div className="header-content">
                        <SplmsIcon mode="wp" name="welcome-learn-more" />
                        <div>
                            <h2>{__('Lesson Settings', 'skillpulse-lms')}</h2>
                            <p>{__('Configure your lesson content, delivery, and completion settings', 'skillpulse-lms')}</p>
                        </div>
                    </div>
                    {this.renderSaveStatus()}
                </div>

                <div className="settings-sections">
                    {config.sections.map((section) =>
                        this.renderSection(
                            section,
                            lessonSettings,
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
        const { fetchSettings, updateSetting, updateAllLessonSetting, fetchSettingsSuccess } = dispatch('splms/lesson-settings');
        return {
            fetchSettings,
            updateSetting,
            updateAllLessonSetting,
            setInitialSettings: fetchSettingsSuccess,
        };
    }),
    withSelect((select) => {
        const { getEditedPostAttribute, isSavingPost } = select("core/editor");
        const { getLessonSettings, isLoading, getError } = select("splms/lesson-settings");
        const postId = getEditedPostAttribute ? getEditedPostAttribute("id") : null;
        const { getActiveTab } = select('splms/lesson-tabs');
        return {
            postId,
            lessonSettings: getLessonSettings(),
            isLoading: isLoading(),
            isSaving: isSavingPost(),
            error: getError(),
            activeTab: getActiveTab(),
        };
    }),
])(Settings);