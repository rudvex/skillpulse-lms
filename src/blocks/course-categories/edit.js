/**
 * Course Categories Block - Editor Component
 *
 * @since 1.0.0
 */

import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	Spinner,
	FormTokenField,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();
	const [ categoryOptions, setCategoryOptions ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	// Load categories for the selector.
	useEffect( () => {
		apiFetch( { path: '/wp/v2/sp-course-category?per_page=100&_fields=id,name' } )
			.then( ( results ) => {
				setCategoryOptions( results.map( ( c ) => ( { id: c.id, name: c.name } ) ) );
			} )
			.catch( () => setCategoryOptions( [] ) )
			.finally( () => setLoading( false ) );
	}, [] );

	const selectedNames = attributes.categories
		.map( ( id ) => categoryOptions.find( ( c ) => c.id === id )?.name )
		.filter( Boolean );

	const onCategoriesChange = ( names ) => {
		const ids = names
			.map( ( name ) => categoryOptions.find( ( c ) => c.name === name )?.id )
			.filter( Boolean );
		setAttributes( { categories: ids } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'skillpulse-lms' ) }>
					<RangeControl
						label={ __( 'Columns', 'skillpulse-lms' ) }
						value={ attributes.columns }
						onChange={ ( columns ) => setAttributes( { columns } ) }
						min={ 1 }
						max={ 6 }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Categories', 'skillpulse-lms' ) } initialOpen={ false }>
					{ loading ? (
						<Spinner />
					) : (
						<FormTokenField
							label={ __( 'Specific Categories', 'skillpulse-lms' ) }
							value={ selectedNames }
							suggestions={ categoryOptions.map( ( c ) => c.name ) }
							onChange={ onCategoriesChange }
							__experimentalExpandOnFocus
							__nextHasNoMarginBottom
						/>
					) }
					<p style={ { fontSize: '12px', color: '#757575', marginTop: '4px' } }>
						{ __( 'Leave empty to show all categories.', 'skillpulse-lms' ) }
					</p>
				</PanelBody>

				<PanelBody title={ __( 'Display Options', 'skillpulse-lms' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show Course Count', 'skillpulse-lms' ) }
						checked={ attributes.showCount }
						onChange={ ( showCount ) => setAttributes( { showCount } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show Description', 'skillpulse-lms' ) }
						checked={ attributes.showDescription }
						onChange={ ( showDescription ) => setAttributes( { showDescription } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Hide Empty Categories', 'skillpulse-lms' ) }
						checked={ attributes.hideEmpty }
						onChange={ ( hideEmpty ) => setAttributes( { hideEmpty } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="splms/course-categories"
				attributes={ attributes }
			/>
		</div>
	);
};

export default Edit;
