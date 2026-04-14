/**
 * Reusable ListView component for WordPress-like admin list pages
 *
 * Provides consistent UI patterns including:
 * - Search and filters
 * - Sortable table columns
 * - Bulk actions
 * - Pagination
 * - Loading and empty states
 *
 * @example
 * <SPLMS_ListView
 *   items={attempts}
 *   totalItems={100}
 *   columns={[
 *     { id: 'id', label: 'ID', sortable: true, render: (item) => `#${item.id}` }
 *   ]}
 *   filters={[
 *     { id: 'status', type: 'select', options: [...], onChange: ... }
 *   ]}
 *   bulkActions={[{ value: 'delete', label: 'Delete' }]}
 *   currentPage={1}
 *   onPageChange={(page) => {}}
 * />
 */

import React, { Component } from 'react';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, Spinner, Button } from '@wordpress/components';
import { SplmsIcon } from '../../SplmsIcon';
import ListTable from './ListTable';
import ListFilters from './ListFilters';
import BulkActions from './BulkActions';
import Pagination from './Pagination';

class SPLMS_ListView extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			selectedItems: props.selectedItems || [],
		};
	}

	componentDidUpdate( prevProps ) {
		// Sync selectedItems if controlled from parent.
		if ( prevProps.selectedItems !== this.props.selectedItems && this.props.selectedItems ) {
			this.setState( { selectedItems: this.props.selectedItems } );
		}
	}

	handleSelectChange = ( selectedItems ) => {
		this.setState( { selectedItems } );
		if ( this.props.onSelectChange ) {
			this.props.onSelectChange( selectedItems );
		}
	};

	handleBulkAction = ( action, selectedItems ) => {
		if ( this.props.onBulkAction ) {
			this.props.onBulkAction( action, selectedItems );
		}
		// Clear selection after bulk action.
		this.setState( { selectedItems: [] } );
	};

	renderEmptyState() {
		const {
			emptyIcon = 'list-view',
			emptyTitle = __( 'No items found', 'skillpulse-lms' ),
			emptyMessage = __( 'Try adjusting your filters', 'skillpulse-lms' ),
			emptyAction,
		} = this.props;

		return (
			<div className="splms-empty-state" style={{ textAlign: 'center', padding: '60px 20px' }}>
				<SplmsIcon mode="wp" icon={emptyIcon} size={48} />
				<h3>{emptyTitle}</h3>
				{emptyMessage && <p>{emptyMessage}</p>}
				{emptyAction && <div className="splms-empty-action">{emptyAction}</div>}
			</div>
		);
	}

	renderLoadingState() {
		return (
			<div style={{ textAlign: 'center', padding: '40px' }}>
				<Spinner />
			</div>
		);
	}

	render() {
		const {
			// Data.
			items = [],
			totalItems = 0,
			isLoading = false,

			// Columns.
			columns = [],

			// Sorting.
			sortBy = '',
			sortOrder = 'asc',
			onSortChange,

			// Selection and bulk actions.
			selectable = false,
			bulkActions = [],
			onBulkAction,

			// Filters.
			filters = [],
			searchValue,
			searchPlaceholder,
			onSearchChange,
			onApplyFilters,
			showApplyButton = true,

			// Pagination.
			currentPage = 1,
			perPage = 20,
			onPageChange,
			onPerPageChange,

			// Row click.
			onRowClick,

			// Row actions (WordPress-style hover actions).
			rowActions = null,
			primaryColumn = null,

			// Custom className.
			className = '',
		} = this.props;

		const { selectedItems } = this.state;

		const totalPages = Math.ceil( totalItems / perPage );
		const showBulkActions = selectable && bulkActions.length > 0;
		const showFilters = filters.length > 0 || onSearchChange;

		return (
			<Card className={`splms-list-view ${className}`}>
				<CardBody>
					{/* Bulk Actions */}
					{showBulkActions && (
						<BulkActions
							actions={bulkActions}
							selectedItems={selectedItems}
							onApply={this.handleBulkAction}
						/>
					)}

					{/* Filters */}
					{showFilters && (
						<ListFilters
							filters={filters}
							searchValue={searchValue}
							searchPlaceholder={searchPlaceholder}
							onSearchChange={onSearchChange}
							onApply={onApplyFilters}
							showApplyButton={showApplyButton}
						/>
					)}

					{/* Table or Loading/Empty State */}
					{isLoading && 0 === items.length ? (
						this.renderLoadingState()
					) : 0 === items.length ? (
						this.renderEmptyState()
					) : (
						<>
							<ListTable
								items={items}
								columns={columns}
								sortBy={sortBy}
								sortOrder={sortOrder}
								onSortChange={onSortChange}
								selectable={selectable}
								selectedItems={selectedItems}
								onSelectChange={this.handleSelectChange}
								onRowClick={onRowClick}
								rowActions={rowActions}
								primaryColumn={primaryColumn}
							/>
							<Pagination
								currentPage={currentPage}
								totalPages={totalPages}
								totalItems={totalItems}
								perPage={perPage}
								onPageChange={onPageChange}
							/>
						</>
					)}
				</CardBody>
			</Card>
		);
	}
}

export default SPLMS_ListView;
export { SPLMS_ListView };
