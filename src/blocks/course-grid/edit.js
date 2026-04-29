/**
 * Course Grid Block - Editor Component
 *
 * @since 1.0.0
 */

import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	SelectControl,
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
	const [ tagOptions, setTagOptions ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	// Load categories and tags for selectors.
	useEffect( () => {
		Promise.all( [
			apiFetch( { path: '/wp/v2/sp-course-category?per_page=100&_fields=id,name' } ).catch( () => [] ),
			apiFetch( { path: '/wp/v2/sp-course-tag?per_page=100&_fields=id,name' } ).catch( () => [] ),
		] ).then( ( [ cats, tags ] ) => {
			setCategoryOptions( cats.map( ( c ) => ( { id: c.id, name: c.name } ) ) );
			setTagOptions( tags.map( ( t ) => ( { id: t.id, name: t.name } ) ) );
			setLoading( false );
		} );
	}, [] );

	// Helpers for FormTokenField — convert between IDs and names.
	const selectedCategoryNames = attributes.categories
		.map( ( id ) => categoryOptions.find( ( c ) => c.id === id )?.name )
		.filter( Boolean );

	const selectedTagNames = attributes.tags
		.map( ( id ) => tagOptions.find( ( t ) => t.id === id )?.name )
		.filter( Boolean );

	const onCategoriesChange = ( names ) => {
		const ids = names
			.map( ( name ) => categoryOptions.find( ( c ) => c.name === name )?.id )
			.filter( Boolean );
		setAttributes( { categories: ids } );
	};

	const onTagsChange = ( names ) => {
		const ids = names
			.map( ( name ) => tagOptions.find( ( t ) => t.name === name )?.id )
			.filter( Boolean );
		setAttributes( { tags: ids } );
	};

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'skillpulse-lms' ) }>
					<SelectControl
						label={ __( 'Display Layout', 'skillpulse-lms' ) }
						value={ attributes.layout }
						options={ [
							{ value: 'grid', label: __( 'Grid', 'skillpulse-lms' ) },
							{ value: 'list', label: __( 'List', 'skillpulse-lms' ) },
						] }
						onChange={ ( layout ) => setAttributes( { layout } ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<RangeControl
						label={ __( 'Columns', 'skillpulse-lms' ) }
						value={ attributes.columns }
						onChange={ ( columns ) => setAttributes( { columns } ) }
						min={ 1 }
						max={ 4 }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<RangeControl
						label={ __( 'Courses Per Page', 'skillpulse-lms' ) }
						value={ attributes.perPage }
						onChange={ ( perPage ) => setAttributes( { perPage } ) }
						min={ 1 }
						max={ 24 }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Filters', 'skillpulse-lms' ) } initialOpen={ false }>
					{ loading ? (
						<Spinner />
					) : (
						<>
							<FormTokenField
								label={ __( 'Categories', 'skillpulse-lms' ) }
								value={ selectedCategoryNames }
								suggestions={ categoryOptions.map( ( c ) => c.name ) }
								onChange={ onCategoriesChange }
								__experimentalExpandOnFocus
								__nextHasNoMarginBottom
							/>
							<FormTokenField
								label={ __( 'Tags', 'skillpulse-lms' ) }
								value={ selectedTagNames }
								suggestions={ tagOptions.map( ( t ) => t.name ) }
								onChange={ onTagsChange }
								__experimentalExpandOnFocus
								__nextHasNoMarginBottom
							/>
						</>
					) }
					<SelectControl
						label={ __( 'Difficulty', 'skillpulse-lms' ) }
						value={ attributes.difficulty }
						options={ [
							{ value: 'all', label: __( 'All Levels', 'skillpulse-lms' ) },
							{ value: 'beginner', label: __( 'Beginner', 'skillpulse-lms' ) },
							{ value: 'intermediate', label: __( 'Intermediate', 'skillpulse-lms' ) },
							{ value: 'advanced', label: __( 'Advanced', 'skillpulse-lms' ) },
						] }
						onChange={ ( difficulty ) => setAttributes( { difficulty } ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Order By', 'skillpulse-lms' ) }
						value={ attributes.orderBy }
						options={ [
							{ value: 'date', label: __( 'Date', 'skillpulse-lms' ) },
							{ value: 'title', label: __( 'Title', 'skillpulse-lms' ) },
							{ value: 'menu_order', label: __( 'Menu Order', 'skillpulse-lms' ) },
						] }
						onChange={ ( orderBy ) => setAttributes( { orderBy } ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Order', 'skillpulse-lms' ) }
						value={ attributes.order }
						options={ [
							{ value: 'DESC', label: __( 'Newest First', 'skillpulse-lms' ) },
							{ value: 'ASC', label: __( 'Oldest First', 'skillpulse-lms' ) },
						] }
						onChange={ ( order ) => setAttributes( { order } ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>

				<PanelBody title={ __( 'Display Options', 'skillpulse-lms' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show Pagination', 'skillpulse-lms' ) }
						checked={ attributes.showPagination }
						onChange={ ( showPagination ) => setAttributes( { showPagination } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="splms/course-grid"
				attributes={ attributes }
			/>
		</div>
	);
};

export default Edit;
