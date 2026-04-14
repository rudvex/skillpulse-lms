/**
 * Reusable table component with sorting and selection
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { SplmsIcon } from '../../SplmsIcon';

const ListTable = ({
	items = [],
	columns = [],
	sortBy = '',
	sortOrder = 'asc',
	onSortChange,
	selectable = false,
	selectedItems = [],
	onSelectChange,
	onRowClick,
	rowActions = null,
	primaryColumn = null,
	emptyMessage = __( 'No items found.', 'skillpulse-lms' ),
}) => {
	const handleSort = ( column ) => {
		if ( ! column.sortable || ! onSortChange ) {
			return;
		}
		const newOrder = sortBy === column.id && 'asc' === sortOrder ? 'desc' : 'asc';
		onSortChange( column.id, newOrder );
	};

	const handleSelectAll = ( event ) => {
		if ( event.target.checked ) {
			// Select all items.
			const allItemIds = items.map( ( item ) => item.id );
			onSelectChange( allItemIds );
		} else {
			// Deselect all.
			onSelectChange( [] );
		}
	};

	const handleSelectItem = ( itemId, event ) => {
		event.stopPropagation();
		if ( selectedItems.includes( itemId ) ) {
			// Remove from selection.
			onSelectChange( selectedItems.filter( ( id ) => id !== itemId ) );
		} else {
			// Add to selection.
			onSelectChange( [ ...selectedItems, itemId ] );
		}
	};

	const handleRowClick = ( item ) => {
		if ( onRowClick ) {
			onRowClick( item );
		}
	};

	const getSortIcon = ( column ) => {
		if ( ! column.sortable ) {
			return null;
		}

		if ( sortBy === column.id ) {
			return 'asc' === sortOrder ? 'arrow-up' : 'arrow-down';
		}

		return 'sort';
	};

	const allSelected = selectable && items.length > 0 && items.length === selectedItems.length;
	const someSelected = selectable && selectedItems.length > 0 && selectedItems.length < items.length;

	if ( 0 === items.length ) {
		return (
			<div className="splms-empty-state">
				<p>{emptyMessage}</p>
			</div>
		);
	}

	return (
		<table className="splms-table splms-list-table">
			<thead>
				<tr>
					{selectable && (
						<th className="splms-checkbox-column">
							<input
								type="checkbox"
								checked={allSelected}
								ref={( input ) => {
									if ( input ) {
										input.indeterminate = someSelected;
									}
								}}
								onChange={handleSelectAll}
							/>
						</th>
					)}
					{columns.map( ( column ) => (
						<th
							key={column.id}
							className={column.sortable ? `splms-sortable ${sortBy === column.id ? `sorted-${sortOrder}` : ''}` : ''}
							onClick={() => handleSort( column )}
							style={{
								width: column.width || 'auto',
								cursor: column.sortable ? 'pointer' : 'default',
							}}
						>
							{column.label}
							{column.sortable && (
								<SplmsIcon mode="wp" icon={getSortIcon( column )} />
							)}
						</th>
					) )}
				</tr>
			</thead>
			<tbody>
				{items.map( ( item ) => (
					<tr
						key={item.id}
						className={onRowClick ? 'splms-clickable-row' : ''}
					>
						{selectable && (
							<td className="splms-checkbox-column">
								<input
									type="checkbox"
									checked={selectedItems.includes( item.id )}
									onChange={( event ) => handleSelectItem( item.id, event )}
									onClick={( event ) => event.stopPropagation()}
								/>
							</td>
						)}
						{columns.map( ( column ) => {
							const isPrimaryColumn = primaryColumn && column.id === primaryColumn;
							const cellContent = column.render ? column.render( item ) : item[column.id];

							const tdClasses = [];
							if ( isPrimaryColumn ) {
								tdClasses.push( 'column-primary' );
							}
							if ( 'actions' === column.id ) {
								tdClasses.push( 'column-actions' );
							}

							return (
								<td
									key={column.id}
									className={tdClasses.join( ' ' )}
									style={{
										width: column.width || 'auto',
									}}
									onClick={'actions' !== column.id ? () => handleRowClick( item ) : undefined}
								>
									{cellContent}
									{isPrimaryColumn && rowActions && (
										<div className="row-actions">
											{rowActions( item ).map( ( action, index ) => (
												<span key={action.id || index}>
													<a
														href={action.href || "#"}
														className={action.className || ''}
														onClick={( e ) => {
															if ( action.href ) {
																e.stopPropagation();
															} else {
																e.preventDefault();
																e.stopPropagation();
																if ( action.onClick ) {
																	action.onClick( item );
																}
															}
														}}
													>
														{action.label}
													</a>
												</span>
											) )}
										</div>
									)}
								</td>
							);
						} )}
					</tr>
				) )}
			</tbody>
		</table>
	);
};

export default ListTable;
