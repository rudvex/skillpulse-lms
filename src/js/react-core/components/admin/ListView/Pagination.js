/**
 * Reusable WordPress-style pagination component
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

const Pagination = ({
	currentPage = 1,
	totalPages = 1,
	totalItems = 0,
	perPage = 20,
	onPageChange,
	showInfo = true,
	maxPageButtons = 10,
}) => {
	if ( totalPages <= 1 ) {
		return null;
	}

	const startItem = totalItems > 0 ? ( currentPage - 1 ) * perPage + 1 : 0;
	const endItem = Math.min( currentPage * perPage, totalItems );

	// Calculate which page numbers to show.
	const getPageNumbers = () => {
		if ( totalPages <= maxPageButtons ) {
			// Show all pages.
			return Array.from( { length: totalPages }, ( _, i ) => i + 1 );
		}

		// Show first, last, and pages around current.
		const pages = [];
		const halfButtons = Math.floor( maxPageButtons / 2 );
		let startPage = Math.max( 1, currentPage - halfButtons );
		const endPage = Math.min( totalPages, startPage + maxPageButtons - 1 );

		// Adjust start if we're near the end.
		if ( endPage - startPage < maxPageButtons - 1 ) {
			startPage = Math.max( 1, endPage - maxPageButtons + 1 );
		}

		for ( let i = startPage; i <= endPage; i++ ) {
			pages.push( i );
		}

		return pages;
	};

	const pageNumbers = getPageNumbers();

	return (
		<div className="splms-pagination">
			{showInfo && (
				<div className="splms-pagination-info">
					{__( 'Showing', 'skillpulse-lms' )} {startItem} - {endItem} {__( 'of', 'skillpulse-lms' )} {totalItems}
				</div>
			)}
			<div className="splms-pagination-controls">
				<Button
					isSmall
					disabled={currentPage === 1}
					onClick={() => onPageChange( currentPage - 1 )}
				>
					{__( 'Previous', 'skillpulse-lms' )}
				</Button>
				<span className="splms-page-numbers">
					{pageNumbers.map( ( pageNum ) => (
						<Button
							key={pageNum}
							isSmall
							isPrimary={pageNum === currentPage}
							onClick={() => onPageChange( pageNum )}
						>
							{pageNum}
						</Button>
					) )}
				</span>
				<Button
					isSmall
					disabled={currentPage === totalPages}
					onClick={() => onPageChange( currentPage + 1 )}
				>
					{__( 'Next', 'skillpulse-lms' )}
				</Button>
			</div>
		</div>
	);
};

export default Pagination;
