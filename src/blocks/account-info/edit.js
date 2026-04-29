/**
 * Account Info Block - Editor Component
 *
 * Displays a selected user profile field for the logged-in user.
 * Uses SkillPulse LMS user fields instead of MemberPress fields.
 *
 * @since 1.0.0
 */

import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Disabled } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const FIELD_OPTIONS = [
	{
		label: __( '--- User Info ---', 'skillpulse-lms' ),
		value: '',
		disabled: true,
	},
	{ label: __( 'Display Name', 'skillpulse-lms' ), value: 'display_name' },
	{ label: __( 'First Name', 'skillpulse-lms' ), value: 'first_name' },
	{ label: __( 'Last Name', 'skillpulse-lms' ), value: 'last_name' },
	{ label: __( 'Full Name', 'skillpulse-lms' ), value: 'full_name' },
	{ label: __( 'Username', 'skillpulse-lms' ), value: 'user_login' },
	{ label: __( 'Email', 'skillpulse-lms' ), value: 'user_email' },
	{ label: __( 'Nickname', 'skillpulse-lms' ), value: 'nickname' },
	{ label: __( 'Bio / Description', 'skillpulse-lms' ), value: 'description' },
	{ label: __( 'Profile Picture', 'skillpulse-lms' ), value: 'avatar' },
	{ label: __( 'Registration Date', 'skillpulse-lms' ), value: 'user_registered' },
	{ label: __( 'User ID', 'skillpulse-lms' ), value: 'ID' },
	{ label: __( 'User Role', 'skillpulse-lms' ), value: 'user_role' },
	{
		label: __( '--- LMS Stats ---', 'skillpulse-lms' ),
		value: '',
		disabled: true,
	},
	{ label: __( 'Enrolled Courses Count', 'skillpulse-lms' ), value: 'enrolled_courses_count' },
	{ label: __( 'Completed Courses Count', 'skillpulse-lms' ), value: 'completed_courses_count' },
	{ label: __( 'Certificates Count', 'skillpulse-lms' ), value: 'certificates_count' },
];

const Edit = ( { attributes, setAttributes } ) => {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Field Settings', 'skillpulse-lms' ) }>
					<SelectControl
						label={ __( 'User Field', 'skillpulse-lms' ) }
						value={ attributes.field }
						options={ FIELD_OPTIONS }
						onChange={ ( field ) => setAttributes( { field } ) }
						help={ __( 'Select which user field to display.', 'skillpulse-lms' ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>

			<Disabled>
				<ServerSideRender
					block="splms/account-info"
					attributes={ attributes }
				/>
			</Disabled>
		</div>
	);
};

export default Edit;
