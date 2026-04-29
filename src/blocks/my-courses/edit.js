import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, ToggleControl, TextControl, Disabled } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'skillpulse-lms' ) }>
					<RangeControl label={ __( 'Columns', 'skillpulse-lms' ) }
						value={ attributes.columns }
						onChange={ ( columns ) => setAttributes( { columns } ) }
						min={ 1 } max={ 4 }
						__next40pxDefaultSize
						__nextHasNoMarginBottom />
				</PanelBody>
				<PanelBody title={ __( 'Filters', 'skillpulse-lms' ) } initialOpen={ false }>
					<SelectControl label={ __( 'Status', 'skillpulse-lms' ) }
						value={ attributes.status }
						options={ [
							{ value: 'all', label: __( 'All Courses', 'skillpulse-lms' ) },
							{ value: 'active', label: __( 'In Progress', 'skillpulse-lms' ) },
							{ value: 'completed', label: __( 'Completed', 'skillpulse-lms' ) },
						] }
						onChange={ ( status ) => setAttributes( { status } ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom />
				</PanelBody>
				<PanelBody title={ __( 'Display Options', 'skillpulse-lms' ) } initialOpen={ false }>
					<ToggleControl label={ __( 'Show Progress Bar', 'skillpulse-lms' ) }
						checked={ attributes.showProgress }
						onChange={ ( showProgress ) => setAttributes( { showProgress } ) }
						__nextHasNoMarginBottom />
					<TextControl label={ __( 'Empty Message', 'skillpulse-lms' ) }
						value={ attributes.emptyMessage }
						onChange={ ( emptyMessage ) => setAttributes( { emptyMessage } ) }
						placeholder={ __( 'You are not enrolled in any courses yet.', 'skillpulse-lms' ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom />
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<ServerSideRender block="splms/my-courses" attributes={ attributes } />
			</Disabled>
		</div>
	);
};

export default Edit;
