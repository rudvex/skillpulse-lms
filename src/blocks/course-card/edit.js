/**
 * Course Card Block - Editor Component
 *
 * @since 1.0.0
 */

import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	ToggleControl,
	Placeholder,
	Spinner,
	ComboboxControl,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();
	const [ courses, setCourses ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	// Load courses for the selector.
	useEffect( () => {
		apiFetch( { path: '/wp/v2/sp-course?per_page=100&status=publish&_fields=id,title' } )
			.then( ( results ) => {
				setCourses( results.map( ( c ) => ( {
					value: c.id,
					label: c.title.rendered,
				} ) ) );
			} )
			.catch( () => setCourses( [] ) )
			.finally( () => setLoading( false ) );
	}, [] );

	// Course selector used in both placeholder and sidebar.
	const courseSelector = loading ? (
		<Spinner />
	) : (
		<ComboboxControl
			label={ __( 'Select Course', 'skillpulse-lms' ) }
			value={ attributes.courseId || '' }
			options={ courses }
			onChange={ ( value ) => setAttributes( { courseId: value ? parseInt( value, 10 ) : 0 } ) }
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	);

	// Show placeholder if no course selected.
	if ( ! attributes.courseId ) {
		return (
			<div { ...blockProps }>
				<Placeholder
					icon="welcome-learn-more"
					label={ __( 'Course Card', 'skillpulse-lms' ) }
					instructions={ __( 'Select a course to display.', 'skillpulse-lms' ) }
				>
					{ courseSelector }
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Course', 'skillpulse-lms' ) }>
					{ courseSelector }
				</PanelBody>

				<PanelBody title={ __( 'Display Options', 'skillpulse-lms' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Show Thumbnail', 'skillpulse-lms' ) }
						checked={ attributes.showThumbnail }
						onChange={ ( showThumbnail ) => setAttributes( { showThumbnail } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show Excerpt', 'skillpulse-lms' ) }
						checked={ attributes.showExcerpt }
						onChange={ ( showExcerpt ) => setAttributes( { showExcerpt } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show Price', 'skillpulse-lms' ) }
						checked={ attributes.showPrice }
						onChange={ ( showPrice ) => setAttributes( { showPrice } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show Rating', 'skillpulse-lms' ) }
						checked={ attributes.showRating }
						onChange={ ( showRating ) => setAttributes( { showRating } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show Instructor', 'skillpulse-lms' ) }
						checked={ attributes.showInstructor }
						onChange={ ( showInstructor ) => setAttributes( { showInstructor } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show Difficulty', 'skillpulse-lms' ) }
						checked={ attributes.showDifficulty }
						onChange={ ( showDifficulty ) => setAttributes( { showDifficulty } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Show Action Button', 'skillpulse-lms' ) }
						checked={ attributes.showActionButton }
						onChange={ ( showActionButton ) => setAttributes( { showActionButton } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>

			<ServerSideRender
				block="splms/course-card"
				attributes={ attributes }
			/>
		</div>
	);
};

export default Edit;
