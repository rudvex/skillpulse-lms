import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import {
    Card,
    CardHeader,
    CardBody,
    Spinner
} from '@wordpress/components';
import NotificationHistoryList from './NotificationHistoryList';
import InAppNotificationTemplates from './InAppNotificationTemplates';

class InAppNotificationsPage extends Component {
    constructor(props) {
        super(props);
        // Use initialTab from props if provided (from parent EmailPage), otherwise default to history
        const initialTab = props.initialTab || 'history';
        this.state = {
            currentTab: initialTab // 'history' or 'templates'
        };
    }

    componentDidMount() {
        // If initialTab prop is provided, use it (parent EmailPage handles URL hash)
        if (this.props.initialTab) {
            this.setState({ currentTab: this.props.initialTab });
        } else {
            // Fallback: Set initial tab from URL hash or default to history
            if (window.location.hash) {
                const hash = window.location.hash.substring(1);
                // Handle both 'in-app-history' format and just 'history'
                const tab = hash.startsWith('in-app-') ? hash.replace('in-app-', '') : hash;
                const validTabs = ['history', 'templates'];
                if (validTabs.includes(tab)) {
                    this.setState({ currentTab: tab });
                }
            }
        }

        // No artificial delay - child components handle their own loading states
    }

    componentDidUpdate(prevProps) {
        // Update tab if initialTab prop changes (from parent)
        if (prevProps.initialTab !== this.props.initialTab && this.props.initialTab) {
            this.setState({ currentTab: this.props.initialTab });
        }
    }

    handleTabChange = (tab) => {
        this.setState({ currentTab: tab });
        
        // Update URL hash with full path (in-app-tab)
        const hash = `in-app-${tab}`;
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, `#${hash}`);
        } else {
            window.location.hash = `#${hash}`;
        }
        
        // Notify parent if callback provided
        if (this.props.onTabChange) {
            this.props.onTabChange(tab);
        }
    }

    tabClassName(tabName) {
        return this.state.currentTab === tabName ? 'active' : '';
    }

    render() {
        return (
            <>

                {/* Note: Sub-tabs navigation is now handled by parent EmailPage component */}

                {/* Tab Content (no splms-content wrapper as parent EmailPage already has it) */}
                {/* Lazy load: Only render the active tab component to prevent unnecessary API calls */}
                {/* Child components handle their own loading states */}
                {this.state.currentTab === 'history' ? (
                    <NotificationHistoryList />
                ) : this.state.currentTab === 'templates' ? (
                    <InAppNotificationTemplates />
                ) : null}
            </>
        );
    }
}

export default InAppNotificationsPage;

