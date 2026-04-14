import apiFetch from '@wordpress/api-fetch';

// controls.js
export const controls = {
    SAVE_CURRICULUM_DATA({ postId, sections }) {
        return apiFetch({
            path: '/skillpulse/v1/save-sections',
            method: 'POST',
            data: { post_id: postId, sections },
        });
    },
};
