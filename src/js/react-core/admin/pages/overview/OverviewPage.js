import { __ } from '@wordpress/i18n';
import { Button, Card, CardBody, CardHeader, Spinner } from '@wordpress/components';
import { withDispatch, withSelect } from "@wordpress/data";
import { compose } from '@wordpress/compose';
import { Component, Fragment } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { SplmsIcon } from "../../../components/SplmsIcon";
import AdminHeader from "../../../components/AdminHeader";

import './styles/index.scss';
import { getAdminUrl, getPostTypeCreateUrl } from '../../../utility/url';


class OverviewPage extends Component {
    constructor(props) {
        super(props);
        this.state = {
            isLoading: true,
            stats: {
                courses: 0,
                lessons: 0,
                quizzes: 0,
                enrollments: 0
            },
            recentActivity: []
        };
    }

    componentDidMount() {
        this.loadOverviewData();
    }

    loadOverviewData = async () => {
        try {
            this.setState({ isLoading: true });
            
            // Fetch stats data
            const statsResponse = await apiFetch({
                path: '/splms/v1/admin/stats',
                method: 'GET',
            });

            // Fetch recent activity
            const activityResponse = await apiFetch({
                path: '/splms/v1/admin/activity',
                method: 'GET',
            });

            this.setState({
                stats: statsResponse || this.state.stats,
                recentActivity: activityResponse || [],
                isLoading: false
            });
        } catch (error) {
            console.error('Error loading overview data:', error);
            this.setState({ isLoading: false });
        }
    };

    renderStatCard = (title, value, icon, color = '#7e75ff') => (
        <div className="stat-card">
            <div className="stat-icon" style={{ backgroundColor: color }}>
                <SplmsIcon mode="wp" icon={icon} size={24} />
            </div>
            <div className="stat-content">
                <div className="stat-number">{value}</div>
                <div className="stat-label">{title}</div>
            </div>
        </div>
    );

    renderQuickActions = () => {        const adminUrl = getAdminUrl();        return (
        <Card className="quick-actions-card">
            <CardHeader>
                <h3>{__('Quick Actions', 'skillpulse-lms')}</h3>
            </CardHeader>
            <CardBody>
                <div className="quick-actions-grid">
                    <Button 
                        isPrimary 
                        href={getPostTypeCreateUrl('sp-course')} 
                        className="quick-action-button"
                    >
                        <SplmsIcon mode="wp" icon="plus" />
                        {__('Create New Course', 'skillpulse-lms')}
                    </Button>
                    <Button 
                        isSecondary 
                        href={getPostTypeCreateUrl('sp-lesson')} 
                        className="quick-action-button"
                    >
                        <SplmsIcon mode="wp" icon="plus" />
                        {__('Create New Lesson', 'skillpulse-lms')}
                    </Button>
                    <Button 
                        isSecondary 
                        href={getPostTypeCreateUrl('sp-quiz')} 
                        className="quick-action-button"
                    >
                        <SplmsIcon mode="wp" icon="plus" />
                        {__('Create New Quiz', 'skillpulse-lms')}
                    </Button>
                    <Button 
                        isSecondary 
                        href={getAdminPageUrl('splms-settings')}
                        className="quick-action-button"
                    >
                        <SplmsIcon mode="wp" icon="admin-settings" />
                        {__('Plugin Settings', 'skillpulse-lms')}
                    </Button>
                </div>
            </CardBody>
        </Card>
        );
    };

    renderRecentActivity = () => (
        <Card className="recent-activity-card">
            <CardHeader>
                <h3>{__('Recent Activity', 'skillpulse-lms')}</h3>
            </CardHeader>
            <CardBody>
                {this.state.recentActivity.length === 0 ? (
                    <p className="no-activity">
                        {__('No recent activity found.', 'skillpulse-lms')}
                    </p>
                ) : (
                    <div className="activity-list">
                        {this.state.recentActivity.map((activity, index) => (
                            <div key={index} className="activity-item">
                                <div className="activity-icon">
                                    <SplmsIcon mode="wp" icon={this.getActivityIcon(activity.type)} size={16} />
                                </div>
                                <div className="activity-content">
                                    <div className="activity-title">{activity.title}</div>
                                    <div className="activity-time">{activity.time}</div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </CardBody>
        </Card>
    );

    getActivityIcon = (type) => {
        switch (type) {
            case 'course_created':
                return 'book';
            case 'lesson_created':
                return 'welcome-learn-more';
            case 'quiz_created':
                return 'clipboard';
            case 'enrollment':
                return 'groups';
            default:
                return 'admin-generic';
        }
    };

    render() {
        const { isLoading, stats } = this.state;
        return (
            <div className="splms-container" role="main">
                <AdminHeader
                    title={__("Overview", "skillpulse-lms")}
                    helpUrl="https://skillpulselms.com/docs/overview"
                    helpText={__("Overview Help", "skillpulse-lms")}
                />

                <main className="splms-content">
                    {isLoading ? (
                        <div className="splms-loading-container">
                            <Spinner />
                            <p>{__('Loading overview data...', 'skillpulse-lms')}</p>
                        </div>
                    ) : (
                        <Fragment>
                            {/* Stats Cards */}
                            <div className="stats-grid">
                                {this.renderStatCard(
                                    __('Total Courses', 'skillpulse-lms'),
                                    stats.courses,
                                    'book',
                                    '#7e75ff'
                                )}
                                {this.renderStatCard(
                                    __('Total Lessons', 'skillpulse-lms'),
                                    stats.lessons,
                                    'welcome-learn-more',
                                    '#059669'
                                )}
                                {this.renderStatCard(
                                    __('Total Quizzes', 'skillpulse-lms'),
                                    stats.quizzes,
                                    'clipboard',
                                    '#d97706'
                                )}
                                {this.renderStatCard(
                                    __('Total Enrollments', 'skillpulse-lms'),
                                    stats.enrollments,
                                    'groups',
                                    '#dc2626'
                                )}
                            </div>

                            {/* Content Grid */}
                            <div className="content-grid">
                                {this.renderQuickActions()}
                                {this.renderRecentActivity()}
                            </div>
                        </Fragment>
                    )}
                </main>
            </div>
        );
    }
}

export default OverviewPage; 