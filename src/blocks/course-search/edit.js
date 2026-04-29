/**
 * Course Search Block - Editor Component
 *
 * @since 1.0.0
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

const Edit = ( { attributes } ) => {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<ServerSideRender
				block="splms/course-search"
				attributes={ attributes }
			/>
		</div>
	);
};

export default Edit;
