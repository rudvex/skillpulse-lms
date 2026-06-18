/**
 * Global utility for retrieving application URLs.
 */
import { addQueryArgs } from '@wordpress/url';

/**
 * Retrieves the base site/home URL, preserving subdirectories.
 */
export const getSiteUrl = () => {
    return window.SPLMSCore_Data?.siteUrl 
        || window.SPLMSCore_Data?.homeUrl 
        || window.splms_frontend?.homeUrl;
};

/**
 * Retrieves the WordPress admin URL.
 */
export const getAdminUrl = () => {
    return window.SPLMSCore_Data?.adminUrl 
        || `${getSiteUrl()}/wp-admin`;
};

/**
 * Retrieves the URL to create a new post of a specific type.
 */
export const getPostTypeCreateUrl = (postType) => {
    return addQueryArgs(`${getAdminUrl()}/post-new.php`, { post_type: postType });
};

/**
 * Retrieves the URL to edit a specific post.
 */
export const getPostTypeEditUrl = (postType, postId) => {
    return addQueryArgs(`${getAdminUrl()}/post.php`, { post: postId, action: 'edit' });
};

/**
 * Retrieves the URL to edit a taxonomy term.
 */
export const getTaxonomyEditUrl = (taxonomy, postType) => {
    return addQueryArgs(`${getAdminUrl()}/edit-tags.php`, { taxonomy, post_type: postType });
};

/**
 * Retrieves the URL for a specific admin menu page.
 */
export const getAdminPageUrl = (page, args = {}) => {
    return addQueryArgs(`${getAdminUrl()}/admin.php`, { page, ...args });
};

/**
 * Retrieves the URL to edit a user profile.
 */
export const getUserEditUrl = (userId) => {
    return addQueryArgs(`${getAdminUrl()}/user-edit.php`, { user_id: userId });
};

/**
 * Retrieves the AJAX URL.
 */
export const getAjaxUrl = () => {
    return window.SPLMSCore_Data?.ajax_url 
        || window.splms_frontend?.ajax_url 
        || window.ajaxurl 
        || `${getAdminUrl()}/admin-ajax.php`;
};
