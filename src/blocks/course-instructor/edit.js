import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Placeholder, Spinner, ComboboxControl, Disabled } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();
	const [ users, setUsers ] = useState( [] );
	const [ loading, setLoading ] = useState( true );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/users?per_page=100&_fields=id,name&roles=administrator,author,editor' } )
			.then( ( results ) => setUsers( results.map( ( u ) => ( { value: u.id, label: u.name } ) ) ) )
			.catch( () => setUsers( [] ) )
			.finally( () => setLoading( false ) );
	}, [] );

	const userSelector = loading ? <Spinner /> : (
		<ComboboxControl
			label={ __( 'Select Instructor', 'skillpulse-lms' ) }
			value={ attributes.instructorId || '' }
			options={ users }
			onChange={ ( value ) => setAttributes( { instructorId: value ? parseInt( value, 10 ) : 0 } ) }
			__next40pxDefaultSize
			__nextHasNoMarginBottom
		/>
	);

	if ( ! attributes.instructorId ) {
		return (
			<div { ...blockProps }>
				<Placeholder icon="businessman" label={ __( 'Course Instructor', 'skillpulse-lms' ) }
					instructions={ __( 'Select an instructor to display.', 'skillpulse-lms' ) }>
					{ userSelector }
				</Placeholder>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Instructor', 'skillpulse-lms' ) }>
					{ userSelector }
				</PanelBody>
				<PanelBody title={ __( 'Display Options', 'skillpulse-lms' ) } initialOpen={ false }>
					<ToggleControl label={ __( 'Show Bio', 'skillpulse-lms' ) }
						checked={ attributes.showBio }
						onChange={ ( showBio ) => setAttributes( { showBio } ) }
						__nextHasNoMarginBottom />
					<ToggleControl label={ __( 'Show Course Count', 'skillpulse-lms' ) }
						checked={ attributes.showCourseCount }
						onChange={ ( showCourseCount ) => setAttributes( { showCourseCount } ) }
						__nextHasNoMarginBottom />
					<ToggleControl label={ __( 'Show Student Count', 'skillpulse-lms' ) }
						checked={ attributes.showStudentCount }
						onChange={ ( showStudentCount ) => setAttributes( { showStudentCount } ) }
						__nextHasNoMarginBottom />
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender block="splms/course-instructor" attributes={ attributes } />
			</Disabled>
		</div>
	);
};

export default Edit;
