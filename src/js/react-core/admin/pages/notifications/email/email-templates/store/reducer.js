import { __ } from '@wordpress/i18n';

const DEFAULT_STATE = {
    templates: window.SPLMSCore_Data?.email_templates || {},
    isLoading: false,
    error: null,
    saveStatus: 'idle',
    testStatus: 'idle'
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case 'FETCH_EMAIL_TEMPLATES_REQUEST':
            return {
                ...state,
                isLoading: true,
                error: null
            };

        case 'FETCH_EMAIL_TEMPLATES_SUCCESS':
            return {
                ...state,
                templates: action.templates,
                isLoading: false,
                error: null
            };

        case 'FETCH_EMAIL_TEMPLATES_FAILURE':
            return {
                ...state,
                isLoading: false,
                error: action.error
            };

        case 'SAVE_EMAIL_TEMPLATE_REQUEST':
            return {
                ...state,
                saveStatus: 'loading',
                error: null
            };

        case 'SAVE_EMAIL_TEMPLATE_SUCCESS':
            return {
                ...state,
                templates: {
                    ...state.templates,
                    [action.template.template_key]: action.template
                },
                saveStatus: 'succeeded',
                error: null
            };

        case 'SAVE_EMAIL_TEMPLATE_FAILURE':
            return {
                ...state,
                saveStatus: 'failed',
                error: action.error
            };

        case 'TEST_EMAIL_TEMPLATE_REQUEST':
            return {
                ...state,
                testStatus: 'loading',
                error: null
            };

        case 'TEST_EMAIL_TEMPLATE_SUCCESS':
            return {
                ...state,
                testStatus: 'succeeded',
                error: null
            };

        case 'TEST_EMAIL_TEMPLATE_FAILURE':
            return {
                ...state,
                testStatus: 'failed',
                error: action.error
            };

        case 'RESET_EMAIL_TEMPLATE_SUCCESS':
            return {
                ...state,
                templates: {
                    ...state.templates,
                    [action.templateKey]: action.template
                },
                error: null
            };

        case 'CLEAR_EMAIL_TEMPLATES_ERROR':
            return {
                ...state,
                error: null
            };

        case 'RESET_SAVE_STATUS':
            return {
                ...state,
                saveStatus: 'idle'
            };

        case 'RESET_TEST_STATUS':
            return {
                ...state,
                testStatus: 'idle'
            };

        default:
            return state;
    }
} 