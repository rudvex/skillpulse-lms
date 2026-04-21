import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

// Action Creators
export const fetchEmailTemplates = () => async ({ dispatch }) => {
    dispatch({ type: 'FETCH_EMAIL_TEMPLATES_REQUEST' });

    try {
        const response = await apiFetch({
            path: '/splms/v1/email-templates',
            method: 'GET',
        });

        if (response.success) {
            dispatch({
                type: 'FETCH_EMAIL_TEMPLATES_SUCCESS',
                templates: response.data
            });
        } else {
            throw new Error(response.message || __('Failed to fetch email templates', 'skillpulse-lms'));
        }
    } catch (error) {
        dispatch({
            type: 'FETCH_EMAIL_TEMPLATES_FAILURE',
            error: error.message
        });
    }
};

export const saveEmailTemplate = (templateData) => async ({ dispatch }) => {
    dispatch({ type: 'SAVE_EMAIL_TEMPLATE_REQUEST' });

    try {
        const response = await apiFetch({
            path: '/splms/v1/email-templates',
            method: 'POST',
            data: templateData
        });

        if (response.success) {
            dispatch({
                type: 'SAVE_EMAIL_TEMPLATE_SUCCESS',
                template: response.data
            });
        } else {
            throw new Error(response.message || __('Failed to save email template', 'skillpulse-lms'));
        }
    } catch (error) {
        dispatch({
            type: 'SAVE_EMAIL_TEMPLATE_FAILURE',
            error: error.message
        });
    }
};

export const testEmailTemplate = (templateData) => async ({ dispatch }) => {
    dispatch({ type: 'TEST_EMAIL_TEMPLATE_REQUEST' });

    try {
        const response = await apiFetch({
            path: '/splms/v1/email-templates/test',
            method: 'POST',
            data: templateData
        });

        if (response.success) {
            dispatch({
                type: 'TEST_EMAIL_TEMPLATE_SUCCESS'
            });
        } else {
            throw new Error(response.message || __('Failed to send test email', 'skillpulse-lms'));
        }
    } catch (error) {
        dispatch({
            type: 'TEST_EMAIL_TEMPLATE_FAILURE',
            error: error.message
        });
    }
};

export const resetEmailTemplate = (templateKey) => async ({ dispatch }) => {
    try {
        const response = await apiFetch({
            path: `/splms/v1/email-templates/${templateKey}/reset`,
            method: 'POST'
        });

        if (response.success) {
            dispatch({
                type: 'RESET_EMAIL_TEMPLATE_SUCCESS',
                templateKey: templateKey,
                template: response.data
            });
        } else {
            throw new Error(response.message || __('Failed to reset email template', 'skillpulse-lms'));
        }
    } catch (error) {
        dispatch({
            type: 'SAVE_EMAIL_TEMPLATE_FAILURE',
            error: error.message
        });
    }
};

export const clearEmailTemplatesError = () => ({ dispatch }) => {
    dispatch({ type: 'CLEAR_EMAIL_TEMPLATES_ERROR' });
};

export const resetSaveStatus = () => ({ dispatch }) => {
    dispatch({ type: 'RESET_SAVE_STATUS' });
};

export const resetTestStatus = () => ({ dispatch }) => {
    dispatch({ type: 'RESET_TEST_STATUS' });
}; 