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
