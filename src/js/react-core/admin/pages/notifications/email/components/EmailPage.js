import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import {
    Card,
    CardHeader,
    CardBody,
    Button,
    Spinner
} from '@wordpress/components';
import {
    withSelect,
    withDispatch
} from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { SplmsIcon } from '../../../../../components/SplmsIcon';
import BrandLogo from "../../../../../components/BrandLogo";
import EmailTemplatesPage from '../email-templates/EmailTemplatesPage';
import InAppNotificationsPage from '../../in-app/components/InAppNotificationsPage';

class EmailPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            mainTab: 'email', // 'email' or 'in-app'
            inAppSubTab: 'history', // 'history' or 'templates'
            isLoading: false,
            isInitialLoad: true
        };
    }

    componentDidMount() {
        // Parse URL hash for nested tabs
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            this.parseHash(hash);
        } else {
            // Default to email templates
            this.setState({ mainTab: 'email' });
        }

        // Set initial load to false immediately (no artificial delay)
        this.setState({ isInitialLoad: false });
    }

    parseHash = (hash) => {
        // Handle hash structure: email, in-app-history, etc.
        if (hash === 'email' || hash.startsWith('email-')) {
            this.setState({ mainTab: 'email' });
        } else if (hash.startsWith('in-app-')) {
            const subTab = hash.replace('in-app-', '');
            if (['history', 'templates'].includes(subTab)) {
                this.setState({ mainTab: 'in-app', inAppSubTab: subTab });
            }
        } else if (hash === 'in-app') {
            this.setState({ mainTab: 'in-app', inAppSubTab: 'history' });
        }
    }

    componentDidUpdate(prevProps) {
        // Handle success and error messages from child components via toasts
        if (prevProps.successMessage !== this.props.successMessage && this.props.successMessage) {
            if (window.skillpulseToast) {
                window.skillpulseToast.success(this.props.successMessage);
            }
        }

        if (prevProps.errorMessage !== this.props.errorMessage && this.props.errorMessage) {
            if (window.skillpulseToast) {
                window.skillpulseToast.error(this.props.errorMessage);
            }
        }
    }

    handleMainTabChange = (tab) => {
        this.setState({ mainTab: tab });
        if (tab === 'email') {
            this.updateHash('email');
        } else {
            this.updateHash('in-app', this.state.inAppSubTab);
        }
    }

    handleInAppSubTabChange = (subTab) => {
        this.setState({ inAppSubTab: subTab, mainTab: 'in-app' });
        this.updateHash('in-app', subTab);
    }

    updateHash = (mainTab, subTab = null) => {
        const hash = subTab ? `${mainTab}-${subTab}` : mainTab;
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, `#${hash}`);
        } else {
            window.location.hash = `#${hash}`;
        }
    }


    mainTabClassName(tabName) {
        return this.state.mainTab === tabName ? 'active' : '';
    }

    render() {
        const { isLoading, isInitialLoad, mainTab, inAppSubTab } = this.state;

        // Show initial loading state
        if (isInitialLoad) {
            return (
                <div className="splms-container" role="main">
                    <div className="splms-loading-container">
                        <Spinner />
                        <p>{__('Loading notification management...', 'skillpulse-lms')}</p>
                    </div>
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
                                    {__("Notification Management", "skillpulse-lms")}
                                </h1>
                            </div>
                            <div className="splms-header-icon">
                                <div className="splms-header-icon-help">
                                    <Button
                                        className="splms-header-icon-help-button"
                                        onClick={() => {
                                            window.open("https://skillpulselms.com/docs/notification-management", "_blank", "noopener,noreferrer");
                                        }}
                                        aria-label={__("Get help with Notification Management", "skillpulse-lms")}
                                    >
                                        <SplmsIcon name="help" size={20} />
                                        <span className="splms-header-icon-help-text">
                                            {__("Help & Documentation", "skillpulse-lms")}
                                        </span>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        {/* Main Tabs Navigation */}
                        <nav className="splms-header-nav" role="navigation" aria-label={__("Notification management navigation", "skillpulse-lms")}>
                            <ul role="tablist" className="splms-main-tabs">
                                <li className={this.mainTabClassName('email')} role="presentation">
                                    <a
                                        onClick={(e) => {
                                            e.preventDefault();
                                            this.handleMainTabChange('email');
                                        }}
                                        href="#email"
                                        role="tab"
                                        aria-selected={mainTab === 'email'}
                                        aria-controls="panel-email"
                                        tabIndex={mainTab === 'email' ? 0 : -1}
                                    >
                                        {__('Email Notification', 'skillpulse-lms')}
                                    </a>
                                </li>
                                <li className={this.mainTabClassName('in-app')} role="presentation">
                                    <a
                                        onClick={(e) => {
                                            e.preventDefault();
                                            this.handleMainTabChange('in-app');
                                        }}
                                        href="#in-app"
                                        role="tab"
                                        aria-selected={mainTab === 'in-app'}
                                        aria-controls="panel-in-app"
                                        tabIndex={mainTab === 'in-app' ? 0 : -1}
                                    >
                                        {__('In App Notification', 'skillpulse-lms')}
                                    </a>
                                </li>
                            </ul>
                        </nav>

                        {/* Sub-tabs Navigation */}
                        {mainTab === 'in-app' && (
                            <nav className="splms-sub-nav" role="navigation" aria-label={__("In-app notification sub-navigation", "skillpulse-lms")}>
                                <ul role="tablist" className="splms-sub-tabs">
                                    <li className={inAppSubTab === 'history' ? 'active' : ''} role="presentation">
                                        <a
                                            onClick={(e) => {
                                                e.preventDefault();
                                                this.handleInAppSubTabChange('history');
                                            }}
                                            href="#in-app-history"
                                            role="tab"
                                            aria-selected={inAppSubTab === 'history'}
                                            aria-controls="panel-in-app-history"
                                        >
                                            {__('History', 'skillpulse-lms')}
                                        </a>
                                    </li>
                                    <li className={inAppSubTab === 'templates' ? 'active' : ''} role="presentation">
                                        <a
                                            onClick={(e) => {
                                                e.preventDefault();
                                                this.handleInAppSubTabChange('templates');
                                            }}
                                            href="#in-app-templates"
                                            role="tab"
                                            aria-selected={inAppSubTab === 'templates'}
                                            aria-controls="panel-in-app-templates"
                                        >
                                            {__('Templates', 'skillpulse-lms')}
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        )}
                    </header>

                    <div className="splms-content">
                        {isLoading ? (
                            <div className="splms-loading-container">
                                <Spinner />
                                <p>{__('Loading notification management...', 'skillpulse-lms')}</p>
                            </div>
                        ) : (
                            mainTab === 'email' ? this.renderEmailView() : this.renderInAppView()
                        )}
                    </div>
                </Fragment>
            </div>
        );
    }

    renderEmailView() {
        return (
            <div
                id="panel-email-templates"
                role="tabpanel"
                aria-labelledby="tab-email"
            >
                <EmailTemplatesPage />
            </div>
        );
    }

    renderInAppView() {
        const { inAppSubTab } = this.state;
        return (
            <div
                id="panel-in-app"
                role="tabpanel"
                aria-labelledby="tab-in-app"
            >
                <InAppNotificationsPage initialTab={inAppSubTab} />
            </div>
        );
    }
}

const mapSelectToProps = (select) => ({
    // Remove non-existent selectors
    // successMessage: select('splms/email-templates').getSuccessMessage(),
    // errorMessage: select('splms/email-templates').getErrorMessage(),
});

const mapDispatchToProps = (dispatch) => ({
    // Add any dispatch actions if needed
});

export default compose(
    withSelect(mapSelectToProps),
    withDispatch(mapDispatchToProps)
)(EmailPage);
