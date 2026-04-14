export const getSectionSettings = (state) => state.sectionSettings || {};
export const isSaving = (state) => !!state.isSaving;
export const isLoading = (state) => !!state.isLoading;
export const getError = (state) => state.error || null;
