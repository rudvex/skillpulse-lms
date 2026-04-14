/**
 * Reusable bulk actions component
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { SelectControl, Button, Flex, FlexItem } from '@wordpress/components';

class BulkActions extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			selectedAction: '',
		};
	}

	handleApply = () => {
		const { selectedAction } = this.state;
		const { selectedItems, onApply } = this.props;

		if ( ! selectedAction || 0 === selectedItems.length ) {
			return;
		}

		if ( onApply ) {
			onApply( selectedAction, selectedItems );
		}

		// Reset selected action after applying.
		this.setState( { selectedAction: '' } );
	};

	render() {
		const {
			actions = [],
			selectedItems = [],
			selectedCount,
		} = this.props;
		const { selectedAction } = this.state;

		if ( 0 === actions.length ) {
			return null;
		}

		const count = selectedCount || selectedItems.length;
		const hasSelection = count > 0;

		// Build options with default "Bulk Actions" option.
		const options = [
			{ value: '', label: __( 'Bulk Actions', 'skillpulse-lms' ), disabled: true },
			...actions,
		];

		return (
			<div className="splms-bulk-actions" style={{ marginBottom: '20px' }}>
				<Flex>
					<FlexItem>
						<SelectControl
							value={selectedAction}
							options={options}
							onChange={( value ) => this.setState( { selectedAction: value } )}
							disabled={! hasSelection}
							className="splms-field splms-field-select"
						/>
					</FlexItem>
					<FlexItem>
						<Button
							onClick={this.handleApply}
							disabled={! hasSelection || ! selectedAction}
						>
							{__( 'Apply', 'skillpulse-lms' )}
						</Button>
					</FlexItem>
					{hasSelection && (
						<FlexItem>
							<span className="splms-selected-count">
								{count} {count === 1 ? __( 'item selected', 'skillpulse-lms' ) : __( 'items selected', 'skillpulse-lms' )}
							</span>
						</FlexItem>
					)}
				</Flex>
			</div>
		);
	}
}

export default BulkActions;
