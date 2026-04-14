import { __ } from '@wordpress/i18n';
import { Button, Spinner, Card, CardHeader, CardBody, Icon } from '@wordpress/components';
import { withDispatch, withSelect } from "@wordpress/data";
import { compose } from '@wordpress/compose';
import { Component, Fragment } from '@wordpress/element';

import './styles/index.scss';
import BrandLogo from "../../../components/BrandLogo";
import { SplmsIcon } from "../../../components/SplmsIcon";
import Field from "../../../components/Field";
import { translateLabel,getActiveTabConfig, getConfig, getConfigSync, isVisibleField } from '../../../utility/helper';
import { fetchApiData, formatOptions } from '../../../utility/apiHelper';

class SettingsPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            config: null,
            configLoading: true,
            configError: null,
            width: window.innerWidth,
            hasUnsavedChanges: false,
            validationErrors: {},
            isSubmitting: false,
            isInitialLoad: true,
            dynamicOptions: {},
            loading: {},
        };
        
        // Store timer references for cleanup
        this.timers = {
            initialLoad: null,
            saveSuccess: null
        };
    }

    async componentDidMount() {
        // Load configuration first - this will handle tab resolution internally
        await this.loadConfiguration();

        // Add resize listener
        window.addEventListener("resize", this.handleResize);

        // Add hash change listener for browser navigation
        window.addEventListener("hashchange", this.handleHashChange);

        // Add beforeunload listener for unsaved changes
        window.addEventListener("beforeunload", this.handleBeforeUnload);

        // Set initial load to false after a short delay to allow settings to load
        this.timers.initialLoad = setTimeout(() => {
            this.setState({ isInitialLoad: false });
        }, 1000);
    }

    /**
     * Load initial settings values from config into the store
     * Creates complete nested structure for all tabs/sections/fields
     */
    loadSettingsFromConfig() {
        if (!this.state.config?.tabs) {
            console.warn('loadSettingsFromConfig: No config tabs found');
            return;
        }

        const settingsData = {};
        let totalFields = 0;
        let fieldsWithValues = 0;

        this.state.config.tabs.forEach(tab => {
            if (!tab.id) {
                return;
            }

            settingsData[tab.id] = {};

            if (tab.sections) {
                tab.sections.forEach(section => {
                    if (!section.id) {
                        console.warn('loadSettingsFromConfig: Section without ID found', section);
                        return;
                    }

                    settingsData[tab.id][section.id] = {};

                    if (section.fields) {
                        section.fields.forEach(field => {
                            if (!field.id) {
                                console.warn('loadSettingsFromConfig: Field without ID found', field);
                                return;
                            }

                            totalFields++;

                            // Always set a value - prefer saved value, then default
                            let fieldValue;
                            if (field.value !== undefined && field.value !== null) {
                                fieldValue = field.value;
                                fieldsWithValues++;
                            } else {
                                fieldValue = field.default;
                            }

                            settingsData[tab.id][section.id][field.id] = fieldValue;
                        });
                    }
                });
            }
        });

        // Update the settings store with initial values from config
        this.props.setSettings(settingsData);
    }

    /**
     * Determine which tab should be active based on URL hash and available tabs
     * @param {Array} tabs - Available tabs from config
     * @returns {string} - Tab ID to set as active
     */
    resolveActiveTab(tabs) {
        if (!tabs || tabs.length === 0) {
            return null;
        }

        // Check URL hash first
        const hashTab = window.location.hash.substring(1);
        if (hashTab) {
            const validTab = tabs.find(tab => tab.id === hashTab);
            if (validTab) {
                return hashTab;
            } else {
                console.warn(`resolveActiveTab: Invalid tab "${hashTab}" in URL hash, available tabs:`, tabs.map(t => t.id));
            }
        }

        // Fallback to first tab
        const firstTab = tabs[0].id;
        return firstTab;
    }

    /**
     * Handle post-config loading tasks: settings loading and tab resolution
     * @param {Object} config - The loaded configuration object
     */
    handleConfigLoaded(config) {

        // Load initial settings values from config into store
        this.loadSettingsFromConfig();

        // Resolve and set the active tab based on URL hash and available tabs
        const activeTab = this.resolveActiveTab(config.tabs);
        if (activeTab) {
            this.props.updateTab(activeTab);

            // Update URL hash if it doesn't match the resolved tab
            if (window.location.hash.substring(1) !== activeTab) {
                window.location.hash = activeTab;
            }
        }

        // Load dynamic options for fields that have API configuration
        this.loadDynamicOptions();
    }

    /**
     * Load configuration from the new Config Loader API
     */
    async loadConfiguration() {
        try {
            this.setState({ configLoading: true, configError: null });

            // Try to get config synchronously first (from cache)
            const syncConfig = getConfigSync('settings_config');
            if (syncConfig && syncConfig.tabs) {
                this.setState({
                    config: syncConfig,
                    configLoading: false
                }, () => {
                    // After state is set, handle settings and tab resolution
                    this.handleConfigLoaded(syncConfig);
                });
                return;
            }

            // Fall back to async loading
            const config = await getConfig('settings_config', 'admin');

            if (!config || !config.tabs) {
                throw new Error('Invalid configuration structure received');
            }

            this.setState({
                config,
                configLoading: false
            }, () => {
                // After state is set, handle settings and tab resolution
                this.handleConfigLoaded(config);
            });

        } catch (error) {
            console.error('Failed to load settings configuration:', error);
            this.setState({
                configError: error.message || 'Failed to load configuration',
                configLoading: false
            });
        }
    }

    componentWillUnmount() {
        window.removeEventListener("resize", this.handleResize);
        window.removeEventListener("hashchange", this.handleHashChange);
        window.removeEventListener("beforeunload", this.handleBeforeUnload);
        
        // Clean up timers
        if (this.timers.initialLoad) {
            clearTimeout(this.timers.initialLoad);
        }
        if (this.timers.saveSuccess) {
            clearTimeout(this.timers.saveSuccess);
        }
    }

    componentDidUpdate(prevProps) {
        // Track unsaved changes - only if actual values changed and user initiated the change
        if (!this.state.isSubmitting && !this.state.isInitialLoad) {
            const { activeTab } = this.props;
            const prevTabSettings = prevProps.settings[activeTab] || {};
            const currentTabSettings = this.props.settings[activeTab] || {};

            // Deep compare the actual settings values
            if (this.hasSettingsChanged(prevTabSettings, currentTabSettings)) {
                this.setState({ hasUnsavedChanges: true });
            }
        }
    }

    hasSettingsChanged = (prev, current) => {
        // Compare the actual values, not object references
        const prevKeys = Object.keys(prev);
        const currentKeys = Object.keys(current);

        if (prevKeys.length !== currentKeys.length) {
            return true;
        }

        for (let key of currentKeys) {
            if (prev[key] !== current[key]) {
                return true;
            }
        }

        return false;
    };

    handleBeforeUnload = (event) => {
        if (this.state.hasUnsavedChanges) {
            const message = __('You have unsaved changes. Are you sure you want to leave?', 'skillpulse-lms');
            event.returnValue = message;
            return message;
        }
    };

    handleSubmit = async () => {
        const { activeTab, settings, updateSetting, setError, clearError } = this.props;
        
        // Clear previous errors
        clearError();
        this.setState({ validationErrors: {}, isSubmitting: true });

        try {
            // Get complete settings for the tab (merge existing + modified)
            const completeTabSettings = this.getCompleteTabSettings(activeTab);

            // Save settings
            await updateSetting(activeTab, completeTabSettings);

            // Update the store with the complete settings we just saved
            // This ensures the store has the full state for this tab
            this.props.setSettings({
                ...this.props.settings,
                [activeTab]: completeTabSettings
            });

            // Mark as saved
            this.setState({
                hasUnsavedChanges: false,
                isSubmitting: false,
                validationErrors: {}
            });

            // Show success toast
            if (window.skillpulseToast) {
                window.skillpulseToast.success(__('Settings saved successfully!', 'skillpulse-lms'));
            }

        } catch (error) {
            console.error("Error saving settings:", error);
            this.setState({ isSubmitting: false });
            // Show error toast
            if (window.skillpulseToast) {
                window.skillpulseToast.error(error.message || __('Failed to save settings. Please try again.', 'skillpulse-lms'));
            }
        }
    };

    handleResize = () => {
        this.setState({ width: window.innerWidth });
    };

    /**
     * Handle browser hash change events (back/forward, direct URL navigation)
     */
    handleHashChange = () => {
        const { config } = this.state;
        if (!config?.tabs) {
            console.warn('handleHashChange: Config not loaded yet');
            return;
        }

        const newTab = this.resolveActiveTab(config.tabs);
        if (newTab && newTab !== this.props.activeTab) {
            this.handleTabChange(newTab);
        }
    };

    tabClassName(tabName) {
        return this.props.activeTab === tabName ? "active" : "";
    }

    clearMessage = () => {
        this.props.clearError();
    };

    handleTabChange = (tabId) => {
        // Warn about unsaved changes
        if (this.state.hasUnsavedChanges) {
            if (!confirm(__('You have unsaved changes. Are you sure you want to switch tabs?', 'skillpulse-lms'))) {
                return;
            }
        }

        this.props.updateTab(tabId);
        this.setState({ hasUnsavedChanges: false, validationErrors: {}, isInitialLoad: true });
        
        // Reset initial load state after tab switch
        this.timers.saveSuccess = setTimeout(() => {
            this.setState({ isInitialLoad: false });
        }, 500);
        
        // Update URL hash
        window.location.hash = tabId;
    };

    /**
     * Load dynamic options for fields that require API data.
     */
    loadDynamicOptions() {
        const { config } = this.state;

        // Don't load dynamic options if config is not available yet
        if (!config || !config.tabs) {
            return;
        }

        // Go through all tabs and sections to find fields with API configuration
        config.tabs.forEach(tab => {
            tab.sections.forEach(section => {
                section.fields.forEach(field => {
                    if (field.api) {
                        this.fetchFieldOptions(field);
                    }
                });
            });
        });
    }

    /**
     * Fetch options for a specific field.
     */
    async fetchFieldOptions(field) {
        this.setState(prevState => ({
            loading: { ...prevState.loading, [field.id]: true }
        }));
        
        try {
            const data = await fetchApiData(field.api.endpoint);
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
     * Checks if a field should be displayed based on conditional logic.
     */
    /**
     * Get current field values from config for conditional logic
     */
    getFieldValuesFromConfig(tabConfig) {
        const values = {};
        tabConfig.sections?.forEach(section => {
            values[section.id] = {};
            section.fields?.forEach(field => {
                values[section.id][field.id] = field.value ?? field.default;
            });
        });
        return values;
    }

    /**
     * Get current field value with proper fallback chain
     * Priority: store value -> config value -> field default
     */
    getFieldValue(field, sectionId, activeTab, activeTabSettings) {
        // First check store state
        const storeValue = activeTabSettings?.[sectionId]?.[field.id];
        if (storeValue !== undefined && storeValue !== null) {
            return storeValue;
        }

        // Then check config value (current saved value)
        if (field.value !== undefined && field.value !== null) {
            return field.value;
        }

        return field.default;
    }

    /**
     * Get complete tab settings by merging current store state with existing config values
     * This ensures we don't lose existing saved values when saving changes
     */
    getCompleteTabSettings(tabId) {
        const { settings } = this.props;
        const { config } = this.state;

        // Find the tab configuration
        const tabConfig = config.tabs?.find(tab => tab.id === tabId);
        if (!tabConfig) {
            console.warn(`getCompleteTabSettings: Tab ${tabId} not found in config`);
            return {};
        }

        // Start with existing saved values from config
        const completeSettings = {};
        tabConfig.sections?.forEach(section => {
            if (!section.id) return;

            completeSettings[section.id] = {};
            section.fields?.forEach(field => {
                if (!field.id) return;

                // Use config value as base (existing saved value)
                completeSettings[section.id][field.id] = field.value ?? field.default;
            });
        });

        // Merge with current store state (modified values)
        const storeSettings = settings[tabId] || {};
        Object.keys(storeSettings).forEach(sectionId => {
            if (!completeSettings[sectionId]) {
                completeSettings[sectionId] = {};
            }
            Object.keys(storeSettings[sectionId] || {}).forEach(fieldId => {
                completeSettings[sectionId][fieldId] = storeSettings[sectionId][fieldId];
            });
        });

        return completeSettings;
    }

    shouldDisplayField( field, tabSettings ) {
        if ( !field.conditional ) {
            return true;
        }

        return isVisibleField( field, tabSettings );
    }

    render() {
        const { config, configLoading, configError, isSubmitting, dynamicOptions, loading } = this.state;
        const { settings, isLoading, activeTab, updateSettingState, updateTab } = this.props;

        // Show loading if configuration is still loading
        if (configLoading) {
            return (
                <div className="splms-container" role="main">
                    <div className="splms-loading-container">
                        <Spinner />
                        <p>{__('Loading configuration...', 'skillpulse-lms')}</p>
                    </div>
                </div>
            );
        }

        // Show error if configuration failed to load
        if (configError) {
            return (
                <div className="splms-container" role="main">
                    <div className="splms-error-message" style={{ padding: '15px', background: 'var(--splms-danger-bg, #f8d7da)', border: '1px solid var(--splms-danger-border, #f5c6cb)', borderRadius: 'var(--splms-border-radius, 4px)', color: 'var(--splms-danger-text, #721c24)', margin: '20px' }}>
                        {__('Configuration Error:', 'skillpulse-lms')} {configError}
                    </div>
                </div>
            );
        }

        // Show error if config is invalid
        if (!config || !config.tabs) {
            return (
                <div className="splms-container" role="main">
                    <div className="splms-error-message" style={{ padding: '15px', background: 'var(--splms-danger-bg, #f8d7da)', border: '1px solid var(--splms-danger-border, #f5c6cb)', borderRadius: 'var(--splms-border-radius, 4px)', color: 'var(--splms-danger-text, #721c24)', margin: '20px' }}>
                        {__('Invalid configuration: No tabs found', 'skillpulse-lms')}
                    </div>
                </div>
            );
        }

        const activeTabConfig = getActiveTabConfig(config.tabs, activeTab);
        const activeTabSettings = settings[activeTab] || {};

        if (!activeTabConfig) {
            return (
                <div className="splms-container" role="main">
                    <p>{__('No settings available for this tab.', 'skillpulse-lms')}</p>
                </div>
            );
        }


   

        return (
            <div className="splms-container" role="main">
                <Fragment>
                    <header id="splms-admin-header" role="banner">
                        <div className="splms-header-logo">
                            <div className="logo inline">
                                <BrandLogo />
                                <div className="splms-header-separator" />
                                <h1 className="splms-header-title inline">
                                    {__("Settings", "skillpulse-lms")}
                                </h1>
                            </div>
                            <div className="splms-header-icon">
                                <div className="splms-header-icon-help">
                                    <Button
                                        className="splms-header-icon-help-button"
                                        onClick={() => {
                                            window.open("https://skillpulselms.com/docs", "_blank", "noopener,noreferrer");
                                        }}
                                        aria-label={__("Get help with SkillPulse LMS", "skillpulse-lms")}
                                    >
                                        <SplmsIcon name="help" size={20} />
                                        <span className="splms-header-icon-help-text">
                                            {__("Help & Documentation", "skillpulse-lms")}
                                        </span>
                                    </Button>
                                </div>
                            </div>
                        </div>
                        
                        <nav className="splms-header-nav" role="navigation" aria-label={__("Settings navigation", "skillpulse-lms")}>
                            <ul role="tablist">
                                {config.tabs.map(tab => (
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

                    <div className="splms-content">
                        {isLoading ? (
                            <div className="splms-loading-container">
                                <Spinner />
                                <p>{__('Loading settings...', 'skillpulse-lms')}</p>
                            </div>
                        ) : activeTabConfig ? (
                            <div 
                                id={`panel-${activeTab}`}
                                role="tabpanel" 
                                aria-labelledby={`tab-${activeTab}`}
                            >

                                {activeTabConfig.sections.map(section => {
                                    // Filter fields based on conditional logic
                                    const visibleFields = section.fields.filter(field =>
                                        this.shouldDisplayField(field, activeTabSettings)
                                    );

                                    // Don't render section if no fields are visible
                                    if (visibleFields.length === 0) return null;

                                    return (
                                        <Card key={section.id} className="splms-card">
                                            <CardHeader>
                                                <h3>{section.title}</h3>
                                            </CardHeader>
                                            <CardBody>
                                                {visibleFields.map((field) => (
                                                    <div key={field.id} className={this.getFieldClasses(field)}>
                                                    <Field
                                                        {...field}
                                                        key={field.id}
                                                        value={this.getFieldValue(field, section.id, activeTab, activeTabSettings)}
                                                        onChange={(value) => {

                                                            // Only mark as changed if initial load is complete
                                                            if (!this.state.isInitialLoad) {
                                                                this.setState({ hasUnsavedChanges: true });
                                                            }

                                                            updateSettingState(field.id, value, section.id, activeTab);
                                                        }}
                                                        options={dynamicOptions[field.id] || field.options || []}
                                                        loading={loading[field.id] || false}
                                                        help={field.description}
                                                    />
                                                    </div>
                                                ))}
                                            </CardBody>
                                        </Card>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="splms-no-content">
                                <h2>{__('Tab not found', 'skillpulse-lms')}</h2>
                                <p>{__('The requested settings tab could not be found.', 'skillpulse-lms')}</p>
                            </div>
                        )}
                    </div>

                    <footer className="splms-footer">
                        <div className="splms-footer-actions">
                            <Button
                                isPrimary
                                onClick={this.handleSubmit}
                                disabled={isLoading || isSubmitting}
                                isBusy={isSubmitting}
                                aria-describedby="save-button-description"
                            >
                                {isSubmitting ? __('Saving...', 'skillpulse-lms') : __('Save Settings', 'skillpulse-lms')}
                            </Button>
                            <div id="save-button-description" className="screen-reader-text">
                                {this.state.hasUnsavedChanges ? 
                                    __('You have unsaved changes', 'skillpulse-lms') :
                                    __('All changes are saved', 'skillpulse-lms')
                                }
                            </div>
                        </div>

                        <div className="splms-footer-info">
                            <span className="splms-footer-text">
                                {__('SkillPulse LMS', 'skillpulse-lms')} v1.0.0
                            </span>
                            <div className="splms-footer-links">
                                <a 
                                    href="https://skillpulselms.com/docs"
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    aria-label={__('Documentation (opens in new tab)', 'skillpulse-lms')}
                                >
                                    {__('Documentation', 'skillpulse-lms')}
                                </a>
                                <a 
                                    href="https://skillpulselms.com/support"
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    aria-label={__('Support (opens in new tab)', 'skillpulse-lms')}
                                >
                                    {__('Support', 'skillpulse-lms')}
                                </a>
                            </div>
                        </div>
                    </footer>

                </Fragment>
            </div>
        );
    }
}

export default compose([
    withDispatch((dispatch, props) => {
        const { createSetting, updateSetting, deleteSetting, updateTab, updateSettingState, setError, clearError, setSettings } = dispatch( 'splms/settings' );
        return {
            createSetting,
            updateSetting,
            deleteSetting,
            updateTab,
            updateSettingState,
            setError,
            clearError,
            setSettings
        };
    }),
    withSelect((select, props) => {
        const { getSettings, getLoading, getSaving, getError, getActiveTab } = select( "splms/settings" );
        return {
            settings: getSettings(),
            isLoading: getLoading(),
            isSaving: getSaving(),
            error: getError(),
            activeTab: getActiveTab(),
        };
    }),
])(SettingsPage);