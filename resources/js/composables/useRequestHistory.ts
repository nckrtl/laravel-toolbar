import {
    activeRequestId,
    activeToolbarData,
    clearRequestPreview,
    previewRequestId,
    previewRequest,
    requestCount,
    requestHistory,
    selectedRequestId,
    selectRequest,
} from '@/core/request-history';

export const useRequestHistory = () => {
    return {
        activeRequestId,
        clearPreview: clearRequestPreview,
        data: activeToolbarData,
        previewRequest,
        previewRequestId,
        requestCount,
        requestHistory,
        selectedRequestId,
        selectRequest,
    };
};
