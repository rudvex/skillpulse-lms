/**
 * Global utility for retrieving application URLs.
 */

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
    return `${getAdminUrl()}/post-new.php?post_type=${postType}`;
};

/**
 * Retrieves the URL to edit a specific post.
 */
export const getPostTypeEditUrl = (postType, postId) => {
    return `${getAdminUrl()}/post.php?post=${postId}&action=edit`;
};

/**
 * Retrieves the URL to edit a taxonomy term.
 */
export const getTaxonomyEditUrl = (taxonomy, postType) => {
    return `${getAdminUrl()}/edit-tags.php?taxonomy=${taxonomy}&post_type=${postType}`;
};

/**
 * Retrieves the URL to edit a user profile.
 */
export const getUserEditUrl = (userId) => {
    return `${getAdminUrl()}/user-edit.php?user_id=${userId}`;
};
