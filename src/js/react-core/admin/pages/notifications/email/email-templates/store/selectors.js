// Selectors
export const getTemplates = (state) => state.templates;

export const getTemplate = (state, templateKey) => state.templates[templateKey];

export const isLoading = (state) => state.isLoading;

export const getError = (state) => state.error;

export const getSaveStatus = (state) => state.saveStatus;

export const getTestStatus = (state) => state.testStatus;

export const getActiveTemplates = (state) => {
    return Object.values(state.templates).filter(template => template.is_active);
};

export const getTemplateCount = (state) => Object.keys(state.templates).length;

export const getActiveTemplateCount = (state) => {
    return Object.values(state.templates).filter(template => template.is_active).length;
}; 