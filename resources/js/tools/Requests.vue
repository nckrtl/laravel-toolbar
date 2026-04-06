<script setup>
import { computed, ref } from 'vue';
import Panel from '@/components/Panel.vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import { useRequestHistory } from '@/composables/useRequestHistory';

const props = defineProps({
    config: {
        type: Object,
        required: false,
    },
    itemClasses: {
        type: Object,
        required: false,
    },
});

const isOpen = ref(false);
const {
    activeRequestId,
    clearPreview,
    previewRequest,
    requestCount,
    requestHistory,
    selectedRequestId,
    selectRequest,
} = useRequestHistory();

const rows = computed(() => requestHistory.value ?? []);

const methodColor = (method = '') => {
    return (
        {
            GET: 'text-lime-400',
            POST: 'text-blue-400',
            PUT: 'text-yellow-300',
            DELETE: 'text-danger',
            PATCH: 'text-indigo-400',
            OPTIONS: 'text-gray-400',
            HEAD: 'text-gray-400',
        }[method] ?? 'text-gray-400'
    );
};

const actionLabel = (action = null) => {
    if (!action) {
        return '—';
    }

    const parts = action.split('\\');

    return parts[parts.length - 1] ?? action;
};

const statusCodeColor = (statusCode) => {
    if (typeof statusCode !== 'number') {
        return 'text-white/40';
    }

    if (statusCode >= 200 && statusCode < 300) {
        return 'text-emerald-300';
    }

    if (statusCode >= 300 && statusCode < 400) {
        return 'text-yellow-500';
    }

    return 'text-danger';
};

const rowSurfaceClasses = (requestId) => {
    if (isSelectedRow(requestId)) {
        return 'border-white/7.5 bg-white/10 group-hover:bg-white/10';
    }

    if (isActiveRow(requestId)) {
        return 'border-white/7.5 bg-white/6 group-hover:bg-white/6';
    }

    return 'border-white/7.5 bg-white/3 group-hover:bg-white/6';
};

const isSelectedRow = (requestId) => {
    return selectedRequestId.value === requestId;
};

const isActiveRow = (requestId) => {
    return activeRequestId.value === requestId;
};

const handleRowEnter = (requestId) => {
    void previewRequest(requestId);
};

const handleRowClick = async (requestId) => {
    try {
        await selectRequest(requestId);
    } catch {}
};

const handlePanelLeave = () => {
    clearPreview();
    isOpen.value = false;
};
</script>

<template>
    <div>
        <Panel
            v-if="isOpen"
            size="full"
            @mouseenter="isOpen = true"
            @mouseleave="handlePanelLeave"
        >
            <div class="flex items-center justify-between p-1.5">
                <div class="flex items-center gap-3">
                    <div
                        class="relative flex h-7 w-7 items-center justify-center rounded-md border border-white/8 bg-white/6"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 16 16"
                            fill="currentColor"
                            class="size-4"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8 3.5c-.771 0-1.537.022-2.297.066a1.124 1.124 0 0 0-1.058 1.028l-.018.214a.75.75 0 1 1-1.495-.12l.018-.221a2.624 2.624 0 0 1 2.467-2.399 41.628 41.628 0 0 1 4.766 0 2.624 2.624 0 0 1 2.467 2.399c.056.662.097 1.329.122 2l.748-.748a.75.75 0 1 1 1.06 1.06l-2 2.001a.75.75 0 0 1-1.061 0l-2-1.999a.75.75 0 0 1 1.061-1.06l.689.688a39.89 39.89 0 0 0-.114-1.815 1.124 1.124 0 0 0-1.058-1.028A40.138 40.138 0 0 0 8 3.5ZM3.22 7.22a.75.75 0 0 1 1.061 0l2 2a.75.75 0 1 1-1.06 1.06l-.69-.69c.025.61.062 1.214.114 1.816.048.56.496.996 1.058 1.028a40.112 40.112 0 0 0 4.594 0 1.124 1.124 0 0 0 1.058-1.028 39.2 39.2 0 0 0 .018-.219.75.75 0 1 1 1.495.12l-.018.226a2.624 2.624 0 0 1-2.467 2.399 41.648 41.648 0 0 1-4.766 0 2.624 2.624 0 0 1-2.467-2.399 41.395 41.395 0 0 1-.122-2l-.748.748A.75.75 0 1 1 1.22 9.22l2-2Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <span>Requests</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xxs font-medium text-white/50 uppercase"> Count </span>
                    <span>{{ requestCount }}</span>
                </div>
            </div>

            <div class="h-2 w-full"></div>

            <div class="relative">
                <table class="relative mt-0 w-full table-fixed text-left">
                    <thead v-if="rows.length > 0">
                        <tr>
                            <th class="sticky top-0 z-10 my-0.5 w-[8%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div
                                        class="rounded-l-md border-y border-l border-white/7.5 bg-white/3 px-3 py-2"
                                    >
                                        Type
                                    </div>
                                </div>
                            </th>
                            <th class="sticky top-0 z-10 my-0.5 w-[7%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Method
                                    </div>
                                </div>
                            </th>
                            <th class="sticky top-0 z-10 my-0.5 w-[15%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        URI
                                    </div>
                                </div>
                            </th>
                            <th class="sticky top-0 z-10 my-0.5 w-[12%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Name
                                    </div>
                                </div>
                            </th>
                            <th class="sticky top-0 z-10 my-0.5 w-[18%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Action
                                    </div>
                                </div>
                            </th>
                            <th
                                class="sticky top-0 z-10 my-0.5 w-[8%] text-right text-[#A3A3A3] uppercase"
                            >
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Middleware
                                    </div>
                                </div>
                            </th>
                            <th class="sticky top-0 z-10 my-0.5 w-[10%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Response
                                    </div>
                                </div>
                            </th>
                            <th
                                class="sticky top-0 z-10 my-0.5 w-[8%] text-right text-[#A3A3A3] uppercase"
                            >
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Status
                                    </div>
                                </div>
                            </th>
                            <th
                                class="sticky top-0 z-10 my-0.5 w-[6%] text-right text-[#A3A3A3] uppercase"
                            >
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Size
                                    </div>
                                </div>
                            </th>
                            <th
                                class="sticky top-0 z-10 my-0.5 w-[8%] text-right text-[#A3A3A3] uppercase"
                            >
                                <div class="pb-0.5">
                                    <div
                                        class="rounded-r-md border-y border-r border-white/7.5 bg-white/3 px-3 py-2"
                                    >
                                        Duration
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                </table>

                <div
                    class="requests-table max-h-[320px] overflow-y-auto rounded-b-lg pb-3"
                    @mouseleave="clearPreview"
                >
                    <table class="relative mt-0 w-full table-fixed text-left">
                        <tbody>
                            <template v-if="rows.length === 0">
                                <tr>
                                    <td colspan="10" class="text-center text-white/50">
                                        <div class="flex items-center justify-center py-12">
                                            No requests captured
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <tr
                                v-else
                                v-for="request in rows"
                                :key="request.id"
                                :data-request-id="request.id"
                                class="group relative cursor-pointer"
                                @mouseenter="handleRowEnter(request.id)"
                                @click="handleRowClick(request.id)"
                            >
                                <td class="w-[8%]">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden rounded-l-md border-y border-l px-3 py-2 text-ellipsis text-white"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ request.is_xhr ? 'Async' : 'Page' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[7%] uppercase">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap" :class="methodColor(request.method)">
                                                {{ request.method }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[15%]">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap" :title="request.uri">
                                                {{ request.uri }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[12%]">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis text-white"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap" :title="request.name ?? ''">
                                                {{ request.name ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[18%]">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis text-white"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap" :title="request.action ?? ''">
                                                {{ actionLabel(request.action) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[8%] text-right">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis text-white"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ request.middleware_count ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[10%]">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis text-white"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap" :title="request.response_type ?? ''">
                                                {{ request.response_type ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[8%] text-right">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap" :class="statusCodeColor(request.status_code)">
                                                {{ request.status_code ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[6%] text-right">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis text-white"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ request.size ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[8%] text-right">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden rounded-r-md border-y border-r px-3 py-2 text-ellipsis text-white"
                                            :class="rowSurfaceClasses(request.id)"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ request.duration ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </Panel>

        <ToolbarItem
            :class="itemClasses"
            :isActive="isOpen"
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
        >
            <div class="flex items-center gap-1 py-0.5">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    class="size-4"
                >
                    <path
                        fill-rule="evenodd"
                        d="M8 3.5c-.771 0-1.537.022-2.297.066a1.124 1.124 0 0 0-1.058 1.028l-.018.214a.75.75 0 1 1-1.495-.12l.018-.221a2.624 2.624 0 0 1 2.467-2.399 41.628 41.628 0 0 1 4.766 0 2.624 2.624 0 0 1 2.467 2.399c.056.662.097 1.329.122 2l.748-.748a.75.75 0 1 1 1.06 1.06l-2 2.001a.75.75 0 0 1-1.061 0l-2-1.999a.75.75 0 0 1 1.061-1.06l.689.688a39.89 39.89 0 0 0-.114-1.815 1.124 1.124 0 0 0-1.058-1.028A40.138 40.138 0 0 0 8 3.5ZM3.22 7.22a.75.75 0 0 1 1.061 0l2 2a.75.75 0 1 1-1.06 1.06l-.69-.69c.025.61.062 1.214.114 1.816.048.56.496.996 1.058 1.028a40.112 40.112 0 0 0 4.594 0 1.124 1.124 0 0 0 1.058-1.028 39.2 39.2 0 0 0 .018-.219.75.75 0 1 1 1.495.12l-.018.226a2.624 2.624 0 0 1-2.467 2.399 41.648 41.648 0 0 1-4.766 0 2.624 2.624 0 0 1-2.467-2.399 41.395 41.395 0 0 1-.122-2l-.748.748A.75.75 0 1 1 1.22 9.22l2-2Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>{{ requestCount }}</span>
            </div>
        </ToolbarItem>
    </div>
</template>
