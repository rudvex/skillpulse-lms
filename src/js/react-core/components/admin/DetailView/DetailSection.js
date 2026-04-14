/**
 * DetailView section component for tabbed or collapsible content
 */

import React, { Component } from 'react';
import { Card, CardBody, TabPanel } from '@wordpress/components';
import { SplmsIcon } from '../../SplmsIcon';

class DetailSection extends Component {
	render() {
		const { sections = [], mode = 'tabs', defaultSection, activeTab } = this.props;

		if ( 0 === sections.length ) {
			return null;
		}

		if ( 'tabs' === mode ) {
			// If activeTab is provided (from AdminHeader), render only active section.
			if ( activeTab ) {
				const section = sections.find( ( s ) => s.id === activeTab );
				return section && section.render ? (
					<div className="splms-detail-sections">
						<div className="splms-detail-section-content">
							{section.render()}
						</div>
					</div>
				) : null;
			}

			// Otherwise, use TabPanel for standalone mode.
			// Find default section or use first.
			const defaultSectionId = defaultSection || sections.find( ( s ) => s.default )?.id || sections[0]?.id;

			// Build tabs for TabPanel.
			const tabs = sections.map( ( section ) => ({
				name: section.id,
				title: (
					<span>
						{section.icon && <SplmsIcon mode="wp" icon={section.icon} />}
						{section.label}
					</span>
				),
				className: 'splms-detail-tab',
			}) );

			return (
				<div className="splms-detail-sections">
					<TabPanel
						className="splms-detail-tabs"
						activeClass="is-active"
						tabs={tabs}
						initialTabName={defaultSectionId}
					>
						{( tab ) => {
							const section = sections.find( ( s ) => s.id === tab.name );
							return section && section.render ? (
								<div className="splms-detail-section-content">
									{section.render()}
								</div>
							) : null;
						}}
					</TabPanel>
				</div>
			);
		}

		// Simple mode - render all sections.
		return (
			<div className="splms-detail-sections">
				{sections.map( ( section ) => (
					<Card key={section.id} className="splms-detail-section">
						{section.label && (
							<div className="splms-detail-section-header">
								{section.icon && <SplmsIcon mode="wp" icon={section.icon} />}
								<h2>{section.label}</h2>
							</div>
						)}
						<CardBody>
							{section.render ? section.render() : null}
						</CardBody>
					</Card>
				) )}
			</div>
		);
	}
}

export default DetailSection;
