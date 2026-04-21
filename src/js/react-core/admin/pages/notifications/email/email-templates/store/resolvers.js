// Resolvers
export const getTemplates = () => async ({ dispatch }) => {
    await dispatch('splms/email-templates').fetchEmailTemplates();
};

export const getTemplate = () => async ({ dispatch }) => {
    await dispatch('splms/email-templates').fetchEmailTemplates();
}; 