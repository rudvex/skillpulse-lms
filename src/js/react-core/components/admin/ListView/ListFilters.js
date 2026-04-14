/**
 * Reusable filter bar component with dynamic filter types
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import {
	Flex,
	FlexItem,
	SearchControl,
	SelectControl,
	Button,
} from '@wordpress/components';

class ListFilters extends Component {
	constructor( props ) {
		super( props );
		this.debounceSearch = this.debounce( ( searchTerm ) => {
			const { onSearchChange, autoApply, onApply } = this.props;
			if ( onSearchChange ) {
				onSearchChange( searchTerm );
			}
			// Auto-apply after search if enabled.
			if ( autoApply && onApply ) {
				setTimeout( () => {
					onApply();
				}, 100 );
			}
		}, 300 );
	}

	debounce( func, wait ) {
		let timeout;
		return function executedFunction( ...args ) {
			const later = () => {
				clearTimeout( timeout );
				func( ...args );
			};
			clearTimeout( timeout );
			timeout = setTimeout( later, wait );
		};
	}

	handleSearch = ( searchTerm ) => {
		this.debounceSearch( searchTerm );
	};

	renderFilter( filter ) {
		const { id, type, label, value, options, onChange, placeholder, startValue, endValue, onStartChange, onEndChange } = filter;

		switch ( type ) {
			case 'search':
				return (
					<FlexItem key={id} style={{ flex: filter.flex || 1 }}>
						<SearchControl
							value={value || ''}
							onChange={this.handleSearch}
							placeholder={placeholder || __( 'Search...', 'skillpulse-lms' )}
							className="splms-field splms-field-search"
						/>
					</FlexItem>
				);

			case 'select':
				return (
					<FlexItem key={id}>
						<SelectControl
							label={label}
							value={value || ''}
							options={options || []}
							onChange={onChange}
							className="splms-field splms-field-select"
						/>
					</FlexItem>
				);

			case 'date-range':
				return (
					<React.Fragment key={id}>
						<FlexItem>
							<input
								type="date"
								value={startValue || ''}
								onChange={( e ) => onStartChange( e.target.value )}
								className="splms-field splms-field-date"
								placeholder={__( 'Start Date', 'skillpulse-lms' )}
							/>
						</FlexItem>
						<FlexItem>
							<input
								type="date"
								value={endValue || ''}
								onChange={( e ) => onEndChange( e.target.value )}
								className="splms-field splms-field-date"
								placeholder={__( 'End Date', 'skillpulse-lms' )}
							/>
						</FlexItem>
					</React.Fragment>
				);

			default:
				return null;
		}
	}

	render() {
		const {
			filters = [],
			showApplyButton = true,
			applyButtonLabel = __( 'Apply Filters', 'skillpulse-lms' ),
			onApply,
		} = this.props;

		if ( 0 === filters.length ) {
			return null;
		}

		return (
			<div className="splms-filters" style={{ marginBottom: '20px' }}>
				<Flex wrap>
					{filters.map( ( filter ) => this.renderFilter( filter ) )}
					{showApplyButton && onApply && (
						<FlexItem>
							<Button isPrimary onClick={onApply}>
								{applyButtonLabel}
							</Button>
						</FlexItem>
					)}
				</Flex>
			</div>
		);
	}
}

export default ListFilters;
