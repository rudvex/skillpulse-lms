import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { Component } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { Card, CardBody, CardHeader, Spinner, Flex, FlexBlock } from '@wordpress/components';
import Field from "../../../../components/Field";
import { SplmsIcon } from "../../../../components/SplmsIcon";
import { translateLabel, getConfig, getConfigSync, isVisibleField } from "../../../../utility/helper";
import { fetchApiData, formatOptions } from '../../../../utility/apiHelper';
import { AutoSaveManager, AutoSaveIndicator } from '../../../../utility/debounce';
import TrialLimitsPanel from '../Components/TrialLimitsPanel';

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
                delay: 2000, // 2 seconds for course settings (more complex)
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
    //   "course_access_settings": {
    //     "course_access_type": "public_free",
    //     "restrict_course_for_guests": true
    //   },
    //   "course_pricing_settings": {
    //     "course_price": 0,
    //     "course_discount": 0
    //   }
    // }
    //
    // NON-GROUPED STRUCTURE (without group property):
    // {
    //   "prerequisites_description": [],
    //   "learning_outcomes": []
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
        // Load configuration and extract embedded settings values
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
            const config = await getConfig('course_settings_config', 'admin', configParams);

            if (!config || !config.sections) {
                throw new Error('Invalid configuration structure received');
            }

            // Extract embedded settings values from config
            const extractedSettings = this.extractSettingsFromConfig(config);

            // Populate Redux store with extracted settings
            this.props.setInitialSettings(extractedSettings);

            this.setState({
                config,
                configLoading: false
            });

            // Load dynamic options for API fields after configuration is loaded
            // This ensures certificate templates load on initial page load when certificate is already enabled
            if (extractedSettings && Object.keys(extractedSettings).length > 0) {
                setTimeout(() => {
                    this.loadDynamicOptions(extractedSettings);
                }, 100); // Small delay to ensure state is updated
            }

        } catch (error) {
            console.error('Failed to load course settings configuration:', error);
            this.setState({
                configError: error.message || 'Failed to load configuration',
                configLoading: false
            });
        }
    }

    componentDidUpdate(prevProps) {
        const {
            postId,
            isSaving,
            courseSettings,
            updateAllCourseSetting,
            activeTab
        } = this.props;

        // Reload configuration if the post ID changes
        if (postId !== prevProps.postId) {
            this.loadConfiguration();
        }

        // Load dynamic options when settings change
        if (courseSettings && courseSettings !== prevProps.courseSettings) {
            this.loadDynamicOptions(courseSettings);
        }

        // Trigger settings update when the post is saved OR force save pending changes
        if (isSaving && !prevProps.isSaving && activeTab === 'settings') {
            // Force save any pending auto-save changes first
            this.forceSave().then(() => {
                updateAllCourseSetting(postId, courseSettings);
            });
        }
    }

    /**
     * Extract settings values from config fields (embedded values)
     */
    extractSettingsFromConfig(config) {
        const settings = {};

        if (!config || !config.sections) {
            return settings;
        }

        config.sections.forEach(section => {
            if (!section.fields || !Array.isArray(section.fields)) {
                return;
            }

            section.fields.forEach(field => {
                if (field.group) {
                    // Grouped field
                    if (!settings[field.group]) {
                        settings[field.group] = {};
                    }
                    settings[field.group][field.id] = field.value ?? field.default;
                } else {
                    // Non-grouped field
                    settings[field.id] = field.value ?? field.default;
                }
            });
        });

        return settings;
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
     * Load dynamic options for fields that require API data based on course access type.
     */
    loadDynamicOptions(courseSettings) {
        // Get course access type from grouped or non-grouped structure
        const courseAccessType = courseSettings?.course_access_settings?.course_access_type || 
                                courseSettings?.course_access_type;
        const allFields = this.getAllFields();

        allFields.forEach(field => {
            // Only fetch options for fields that are visible and have API configuration
            if (field.api && this.shouldDisplayField(field, courseSettings)) {
                this.fetchFieldOptions(field);
            }
        });
    }

    /**
     * Fetch options for a specific field.
     */
    async fetchFieldOptions(field) {
        const { postId, courseSettings } = this.props;
        
        // Double-check visibility before making API call
        if (!this.shouldDisplayField(field, courseSettings)) {
            console.log(`Skipping API call for ${field.id} - field not visible`);
            return;
        }
        this.setState(prevState => ({
            loading: { ...prevState.loading, [field.id]: true }
        }));
        try {

            const params = {
                ...field.api.params,
            };

            const data = await fetchApiData(field.api.endpoint, params);
            const formattedOptions = formatOptions(data, field.api.useProperties);
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
        const { postId, courseSettings } = this.props;
        if (!postId) return;

        // Deep clone the current settings to avoid mutations
        const updatedSettings = JSON.parse(JSON.stringify(courseSettings || {}));
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
        return await this.props.updateAllCourseSetting(postId, updatedSettings);
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
     * Handle input change for invited_users field.
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
            const formattedOptions = formatOptions(data, field.api.useProperties);
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
    shouldDisplayField(field, courseSettings) {
        if (!field.conditional) {
            return true;
        }

        return isVisibleField(field, courseSettings, this.getAllFields, this.getFieldValueFromSettings);
    }

    /**
     * Get field value from courseSettings - simple and direct approach
     */
    getFieldValueFromSettings(courseSettings, field) {
        if (!courseSettings) return undefined;

        if (field.group) {
            // For grouped fields, get from the group
            return courseSettings[field.group]?.[field.id];
        } else {
            // For non-grouped fields, get from root level
            return courseSettings[field.id];
        }
    }


    /**
     * Calculate final price based on course price and discount
     */
    calculateFinalPrice(courseSettings) {
        // Handle grouped pricing settings
        const pricingSettings = courseSettings?.course_pricing_settings || courseSettings;
        const coursePrice = parseFloat(pricingSettings?.course_price || 0);
        const courseDiscount = parseFloat(pricingSettings?.course_discount || 0);
        const discountType = pricingSettings?.course_discount_type || "percentage";

        if (discountType === "percentage") {
            return coursePrice - (coursePrice * (courseDiscount / 100));
        } else {
            return coursePrice - courseDiscount;
        }
    }

    /**
     * Get field value with proper type handling - simplified approach
     */
    getFieldValue(field, courseSettings, finalPrice) {
        if (field.id === "final_price") {
            return finalPrice.toFixed(2);
        }
        
        const rawValue = this.getFieldValueFromSettings(courseSettings, field);

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
        
        if (field.type === 'calculated') {
            classes.push('calculated-field');
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

        if (field.type === 'info') {
            return null;
        }

        return (
            <label className="field-label">
                <SplmsIcon mode={field.mode || 'wp'} name={field.icon || 'admin-generic'} size={16} />
                {translateLabel(field.label)}
                {field.type === 'calculated' && <span className="calculated-badge">Calculated</span>}
            </label>
        );
    }

    /**
     * Render enhanced difficulty level field with pill-style radio buttons
     */
    renderDifficultyLevel(field, value, onChange) {
        const difficultyLevels = field.options || [];

        return (
            <div className="enhanced-difficulty-selector">
                <div className="difficulty-options">
                    {difficultyLevels.map((option) => (
                        <button
                            key={option.value}
                            type="button"
                            className={`difficulty-option ${value === option.value ? 'selected' : ''}`}
                            onClick={() => onChange(option.value)}
                            style={{
                                '--level-color': option.color
                            }}
                        >
                            <SplmsIcon mode="wp" name={option.icon} size={16} />
                            <span>{translateLabel(option.label)}</span>
                            {value === option.value && <SplmsIcon mode="wp" name="yes" size={14} className="check-icon" />}
                        </button>
                    ))}
                </div>
            </div>
        );
    }

    /**
     * Render enhanced duration field combining value and unit
     */
    renderDurationField(courseSettings, updateSetting, postId) {
        // Handle grouped content settings
        const contentSettings = courseSettings?.course_content_settings || courseSettings;
        const durationValue = contentSettings?.course_duration_value || 1;
        const durationUnit = contentSettings?.course_duration_unit || 'months';

        const unitOptions = [
            { value: 'days', label: __('Days', 'skillpulse-lms'), icon: 'calendar' },
            { value: 'weeks', label: __('Weeks', 'skillpulse-lms'), icon: 'calendar-alt' },
            { value: 'months', label: __('Months', 'skillpulse-lms'), icon: 'calendar-alt' },
            { value: 'years', label: __('Years', 'skillpulse-lms'), icon: 'calendar-alt' }
        ];

        return (
            <div className="enhanced-duration-field">
                <label className="field-label">
                    <SplmsIcon mode="wp" name="clock" size={16} />
                    {__('Course Duration', 'skillpulse-lms')}
                </label>
                <div className="duration-inputs">
                    <div className="duration-value">
                        <input
                            type="number"
                            value={durationValue}
                            onChange={(e) => this.handleFieldChangeWithAutoSave(postId, 'course_duration_value', parseInt(e.target.value))}
                            min="1"
                            className="duration-number-input"
                        />
                    </div>
                    <div className="duration-unit">
                        <select
                            value={durationUnit}
                            onChange={(e) => this.handleFieldChangeWithAutoSave(postId, 'course_duration_unit', e.target.value)}
                            className="duration-unit-select"
                        >
                            {unitOptions.map(unit => (
                                <option key={unit.value} value={unit.value}>
                                    {unit.label}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
                <p className="field-help">
                    {__('Set the expected time to complete this course', 'skillpulse-lms')}
                </p>
            </div>
        );
    }

    /**
     * Render a standard field with Field component
     */
    renderStandardField(field, courseSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId, finalPrice) {
        const fieldValue = this.getFieldValue(field, courseSettings, finalPrice);
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
                    readonly={field.type === 'calculated'}
                    formData={courseSettings}
                />
            </div>
        );
    }

    /**
     * Render a field based on its type and special requirements
     */
    renderField(field, courseSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId, finalPrice) {

        // Special handling for difficulty level
        if (field.id === 'difficulty_level') {
            const fieldClasses = this.getFieldClasses(field);
            return (
                <div key={field.id} className={fieldClasses}>
                    {this.renderFieldLabel(field)}
                    {this.renderDifficultyLevel(
                        field,
                        this.getFieldValueFromSettings(courseSettings,field) || field.default,
                        (value) => this.handleFieldChangeWithAutoSave(postId, field.id, value)
                    )}
                    <p className="field-help">{translateLabel(field.help)}</p>
                </div>
            );
        }

        // Skip duration fields if they're handled together on course_duration_value.
        if (field.id === 'course_duration_unit') return null;
        
        if (field.id === 'course_duration_value') {
            return (
                <div key="duration-combined" className="enhanced-field">
                    {this.renderDurationField(courseSettings, updateSetting, postId)}
                </div>
            );
        }

        // Render standard field
        return this.renderStandardField(
            field, 
            courseSettings, 
            dynamicOptions, 
            selectedOptions, 
            loading, 
            updateSetting, 
            postId, 
            finalPrice
        );
    }

    /**
     * Render a section with its fields
     */
    renderSection(section, courseSettings, dynamicOptions, selectedOptions, loading, updateSetting, postId, finalPrice) {
        // Render all fields but pass visibility info to prevent API calls for hidden fields
        const sectionFields = section.fields;

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
                        {sectionFields.map((field) => {
                            // Only render visible fields, but pass visibility info to Field component
                            if (!this.shouldDisplayField(field, courseSettings)) {
                                return null;
                            }
                            
                            return this.renderField(
                                field,
                                courseSettings,
                                dynamicOptions,
                                selectedOptions,
                                loading,
                                updateSetting,
                                postId,
                                finalPrice
                            );
                        })}
                    </div>
                </CardBody>
            </Card>
        );
    }

    render() {
        const { courseSettings, isLoading, error, updateSetting, postId } = this.props;
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

        if (isLoading || typeof courseSettings !== 'object' || Object.keys(courseSettings).length === 0) {
            return (
                <div className="settings-loading">
                    <Spinner />
                    <p>{__('Loading course settings...', 'skillpulse-lms')}</p>
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

        const finalPrice = this.calculateFinalPrice(courseSettings);

        return (
            <div className="enhanced-course-settings enhanced-settings-wrapper">
                <div className="settings-header">
                    <div className="header-content">
                        <SplmsIcon mode="wp" name="admin-settings" />
                        <div>
                            <h2>{__('Course Settings', 'skillpulse-lms')}</h2>
                            <p>{__('Configure your course access, delivery method, and content structure', 'skillpulse-lms')}</p>
                        </div>
                    </div>
                    {this.renderSaveStatus()}
                </div>

                <TrialLimitsPanel />

                <div className="settings-sections">
                    {config.sections.map((section) => 
                        this.renderSection(
                            section, 
                            courseSettings, 
                            dynamicOptions, 
                            selectedOptions, 
                            loading, 
                            updateSetting, 
                            postId, 
                            finalPrice
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
        const { updateSetting, updateAllCourseSetting, setSettings } = dispatch('splms/course-settings');
        return {
            updateSetting,
            updateAllCourseSetting,
            setInitialSettings: setSettings, // Use setSettings to populate initial data
        };
    }),
    withSelect((select) => {
        const { getEditedPostAttribute, isSavingPost } = select("core/editor");
        const { getCourseSettings, isLoading, getError } = select("splms/course-settings");
        const postId = getEditedPostAttribute ? getEditedPostAttribute("id") : null;
        const { getActiveTab } = select('splms/course-tabs');
        return {
            postId,
            courseSettings: getCourseSettings(),
            isLoading: isLoading(),
            isSaving: isSavingPost(),
            error: getError(),
            activeTab: getActiveTab(),
        };
    }),
])(Settings);


