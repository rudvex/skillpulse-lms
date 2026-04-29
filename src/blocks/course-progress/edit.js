import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl, Placeholder, Spinner, ComboboxControl, Disabled } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();
	const [ courses, setCourses ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/sp-course?per_page=100&status=publish&_fields=id,title' } )
			.then( ( results ) => setCourses( results.map( ( c ) => ( { value: c.id, label: c.title.rendered } ) ) ) )
			.catch( () => setCourses( [] ) )
			.finally( () => setLoading( false ) );
	}, [] );

	const courseSelector = loading ? <Spinner /> : (
		<ComboboxControl
			label={ __( 'Select Course', 'skillpulse-lms' ) }
			value={ attributes.courseId || '' }
			options={ courses }
			onChange={ ( value ) => setAttributes( { courseId: value ? parseInt( value, 10 ) : 0 } ) }
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	);

	if ( ! attributes.courseId ) {
		return (
			<div { ...blockProps }>
				<Placeholder icon="chart-bar" label={ __( 'Course Progress', 'skillpulse-lms' ) }
					instructions={ __( 'Select a course to display progress.', 'skillpulse-lms' ) }>
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
					<ToggleControl label={ __( 'Show Percentage', 'skillpulse-lms' ) }
						checked={ attributes.showPercentage }
						onChange={ ( showPercentage ) => setAttributes( { showPercentage } ) }
						__nextHasNoMarginBottom />
					<ToggleControl label={ __( 'Show Item Count', 'skillpulse-lms' ) }
						checked={ attributes.showItemCount }
						onChange={ ( showItemCount ) => setAttributes( { showItemCount } ) }
						__nextHasNoMarginBottom />
					<TextControl label={ __( 'Custom Label', 'skillpulse-lms' ) }
						value={ attributes.label }
						onChange={ ( label ) => setAttributes( { label } ) }
						placeholder={ __( 'Progress', 'skillpulse-lms' ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom />
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender block="splms/course-progress" attributes={ attributes } />
			</Disabled>
		</div>
	);
};

export default Edit;
