/**
 * Breadcrumbs component for WordPress-style navigation
 */

import React from 'react';

const Breadcrumbs = ( { items = [] } ) => {
	if ( 0 === items.length ) {
		return null;
	}

	return (
		<div className="splms-breadcrumbs">
			{items.map( ( item, index ) => (
				<span key={index}>
					{index > 0 && <span className="separator"> › </span>}
					{item.url ? (
						<a
							href={item.url}
							onClick={item.onClick || null}
							className="breadcrumb-link"
						>
							{item.label}
						</a>
					) : (
						<span className="breadcrumb-current">{item.label}</span>
					)}
				</span>
			) )}
		</div>
	);
};

export default Breadcrumbs;
