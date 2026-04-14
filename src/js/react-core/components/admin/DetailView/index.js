/**
 * Reusable DetailView component for WordPress-like admin detail pages
 *
 * Provides consistent UI patterns including:
 * - Back navigation
 * - Header with title and actions
 * - Status badges
 * - Tabbed or simple sections
 * - Sidebar meta boxes
 * - Loading states
 * - Full-width mode for space-intensive interfaces
 * - Custom toolbar support
 *
 * @example
 * <SPLMS_DetailView
 *   title="Quiz Attempt #123"
 *   subtitle="By John Doe on 2026-01-07"
 *   onBack={() => navigate('/attempts')}
 *   actions={[
 *     { label: 'Verify', isPrimary: true, onClick: handleVerify }
 *   ]}
 *   sections={[
 *     { id: 'details', label: 'Details', render: () => <Details /> }
 *   ]}
 *   sidebar={[
 *     { title: 'User Info', render: () => <UserInfo /> }
 *   ]}
 *   fullWidth={false}
 *   toolbar={<CustomToolbar />}
 * />
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, Spinner, Button } from '@wordpress/components';
import AdminHeader from '../../AdminHeader';
import DetailSection from './DetailSection';
import { SplmsIcon } from '../../SplmsIcon';

class SPLMS_DetailView extends Component {
	constructor( props ) {
		super( props );

		const { sections = [], defaultSection } = props;
		const defaultTabId = defaultSection || sections.find( ( s ) => s.default )?.id || sections[0]?.id;

		this.state = {
			activeTab: defaultTabId,
		};
	}

	renderSidebar() {
		const { sidebar = [] } = this.props;

		if ( 0 === sidebar.length ) {
			return null;
		}

		return (
			<div className="splms-detail-sidebar">
				{sidebar.map( ( box, index ) => (
					<Card key={index} className="splms-detail-meta-box">
						{box.title && (
							<div className="splms-meta-box-title">
								<h3>{box.title}</h3>
							</div>
						)}
						<CardBody>
							{box.render ? box.render() : null}
						</CardBody>
					</Card>
				) )}
			</div>
		);
	}

	renderLoadingState() {
		return (
			<div className="splms-detail-loading" style={{ textAlign: 'center', padding: '60px 20px' }}>
				<Spinner />
			</div>
		);
	}

	render() {
		const {
			// Header.
			title = '',
			subtitle = '',
			icon,
			onBack,
			backLabel,
			actions = [],
			status,

			// Sections.
			sections = [],
			sectionMode = 'tabs',
			defaultSection,

			// Sidebar.
			sidebar = [],

			// Layout.
			fullWidth = false,
			toolbar = null,

			// Loading.
			isLoading = false,

			// Custom className.
			className = '',
		} = this.props;

		const hasSidebar = ! fullWidth && sidebar.length > 0;

		// Convert sections to tabs format for AdminHeader.
		const tabs = sections.map( ( section ) => ({
			id: section.id,
			title: section.label,
		}) );

		// Find default/active tab.
		const defaultTabId = defaultSection || sections.find( ( s ) => s.default )?.id || sections[0]?.id;

		// Build header title with optional subtitle.
		const headerTitle = subtitle ? `${title}` : title;

		// Build custom actions section with back button styled like header button.
		const customActions = onBack ? (
			<Button
				className="splms-header-icon-help-button"
				onClick={onBack}
				aria-label={backLabel || __( 'Back', 'skillpulse-lms' )}
			>
				<SplmsIcon name="arrowBack" size={20} />
				<span className="splms-header-icon-help-text">
					{backLabel || __( 'Back', 'skillpulse-lms' )}
				</span>
			</Button>
		) : null;

		return (
            <>
                <AdminHeader
                    title={headerTitle}
                    tabs={tabs}
                    activeTab={this.state?.activeTab || defaultTabId}
                    onTabChange={( tabId ) => {
                        if ( this.setState ) {
                            this.setState({ activeTab: tabId });
                        }
                    }}
                    customActions={customActions}
                    helpType={null}
                />
                {/* Subtitle and actions below header if provided */}
                {( subtitle || status || actions.length > 0 ) && (
                    <div className="splms-detail-subtitle">
                        <div className="splms-subtitle-left">
                            {subtitle && <p>{subtitle}</p>}
                            {status && (
                                <span className={`splms-status-badge splms-status-${status.type}`}>
                                {status.label}
                            </span>
                            )}
                        </div>
                        {actions.length > 0 && (
                            <div className="splms-detail-actions">
                                {actions.map( ( action, index ) => {
                                    const buttonProps = {
                                        key: index,
                                        onClick: action.onClick,
                                        disabled: action.disabled || false,
                                        className: action.className || 'splms-action-button',
                                    };

                                    if ( action.isPrimary ) {
                                        return <Button isPrimary {...buttonProps}>{action.label}</Button>;
                                    } else if ( action.isDestructive ) {
                                        return <Button isDestructive {...buttonProps}>{action.label}</Button>;
                                    } else {
                                        return <Button isSecondary {...buttonProps}>{action.label}</Button>;
                                    }
                                })}
                            </div>
                        )}
                    </div>
                )}

                {/* Custom toolbar content */}
                {toolbar && (
                    <div className={`splms-detail-toolbar ${fullWidth ? 'full-width' : ''}`}>
                        {toolbar}
                    </div>
                )}

                {isLoading ? (
                    this.renderLoadingState()
                ) : (
                    <div className={`splms-detail-body ${hasSidebar ? 'has-sidebar' : ''} ${fullWidth ? 'full-width' : ''} ${className}`}>
                        <div className="splms-detail-main">
                            <DetailSection
                                sections={sections}
                                mode={sectionMode}
                                defaultSection={defaultSection}
                                activeTab={this.state?.activeTab || defaultTabId}
                            />
                        </div>

                        {hasSidebar && this.renderSidebar()}
                    </div>
                )}
            </>
		);
	}
}

export default SPLMS_DetailView;
export { SPLMS_DetailView };
