import type { ComputedRef } from 'vue';
import { activeToolbarData } from '@/core/request-history';
import type { ToolbarData } from '@/types';

export const useToolbar = (): { data: ComputedRef<ToolbarData> } => {
    return {
        data: activeToolbarData,
    };
};
