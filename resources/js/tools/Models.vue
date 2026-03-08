<script setup>
import { computed, ref, watch, nextTick } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import { useToolbar } from '@/composables/useToolbar';
import Panel from '@/components/Panel.vue';
import Pill from '@/components/Pill.vue';
import EmptyListIcon from '@/icons/EmptyListIcon.vue';
import { ChevronRightIcon } from '@heroicons/vue/16/solid';

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

const { data } = useToolbar();

const isOpen = ref(false);
const expandedModels = ref({});
const queriesTable = ref(null);
const queriesTableInner = ref(null);

const onlyUnique = (value, index, array) => {
    return array.indexOf(value) === index;
};

const toggleModel = (modelClass) => {
    expandedModels.value[modelClass] = !expandedModels.value[modelClass];
};

const getSources = (model) => {
    return Object.values(model?.sources ?? {});
};

const getSourceCount = (model) => {
    return getSources(model).length;
};

const hasMultipleSources = (model) => {
    return getSourceCount(model) > 1;
};

const getSingleSource = (model) => {
    return getSources(model)[0] ?? null;
};

const getSourceLabel = (source) => {
    return `${source.file.split('/').pop()}:${source.line}`;
};

const getActionPillColor = (action) => {
    const normalizedAction = action?.toLowerCase();

    if (normalizedAction === 'retrieved') {
        return 'blue';
    }

    if (normalizedAction === 'created') {
        return 'green';
    }

    if (normalizedAction === 'deleted') {
        return 'red';
    }

    if (normalizedAction === 'updated') {
        return 'yellow';
    }

    return 'slate';
};

const isExpanded = (modelClass) => {
    return expandedModels.value[modelClass];
};

const visibleRowCount = computed(() => {
    return Object.entries(data.value?.models ?? {}).reduce((count, [modelClass, model]) => {
        const expanded = isExpanded(modelClass) && hasMultipleSources(model);
        return count + 1 + (expanded ? 1 : 0);
    }, 0);
});

const hasAnyExpanded = computed(() => {
    return Object.entries(data.value?.models ?? {}).some(
        ([modelClass, model]) => isExpanded(modelClass) && hasMultipleSources(model),
    );
});

const shouldUseFixedPanelHeight = computed(() => {
    return visibleRowCount.value > 8 || hasAnyExpanded.value;
});

const panelMinHeight = computed(() => {
    return shouldUseFixedPanelHeight.value ? 'h-[385px]' : '';
});

const tableBodyClasses = computed(() => {
    return shouldUseFixedPanelHeight.value
        ? 'relative min-h-0 flex-1 overflow-y-auto -mb-2'
        : 'relative overflow-visible rounded-b-lg pb-0';
});

// Scroll fade effect
const fadeClasses = ['fade-to-bottom', 'fade-to-top', 'fade-to-top-and-bottom'];

watch(queriesTable, (newVal) => {
    if (newVal) {
        newVal.addEventListener('scroll', () => {
            const scrollTop = newVal.scrollTop;

            if (
                scrollTop + queriesTable.value.clientHeight ==
                queriesTableInner.value.clientHeight + 12
            ) {
                queriesTable.value.classList.remove(...fadeClasses);
                queriesTable.value.classList.add('fade-to-top');
            } else if (scrollTop > 1) {
                queriesTable.value.classList.remove(...fadeClasses);
                queriesTable.value.classList.add('fade-to-top-and-bottom');
            } else {
                queriesTable.value.classList.remove(...fadeClasses);
                queriesTable.value.classList.add('fade-to-bottom');
            }
        });
    }
});

watch(shouldUseFixedPanelHeight, (useFixedPanelHeight) => {
    if (!queriesTable.value) {
        return;
    }

    if (useFixedPanelHeight) {
        nextTick(() => {
            if (queriesTable.value.scrollHeight > queriesTable.value.clientHeight) {
                queriesTable.value.classList.add('fade-to-bottom');
            }
        });
    } else {
        queriesTable.value.classList.remove(...fadeClasses);
        queriesTable.value.scrollTop = 0;
    }
});

</script>

<template>
    <div>
        <Panel
            v-if="isOpen"
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            size="full"
            :minHeight="panelMinHeight"
        >
            <div class="flex h-full flex-col">
            <div class="flex shrink-0 items-center justify-between">
                <div class="flex min-w-64 items-center gap-3 p-1.5">
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
                                d="M7.628 1.349a.75.75 0 0 1 .744 0l1.247.712a.75.75 0 1 1-.744 1.303L8 2.864l-.875.5a.75.75 0 0 1-.744-1.303l1.247-.712ZM4.65 3.914a.75.75 0 0 1-.279 1.023L4.262 5l.11.063a.75.75 0 0 1-.744 1.302l-.13-.073A.75.75 0 0 1 2 6.25V5a.75.75 0 0 1 .378-.651l1.25-.714a.75.75 0 0 1 1.023.279Zm6.698 0a.75.75 0 0 1 1.023-.28l1.25.715A.75.75 0 0 1 14 5v1.25a.75.75 0 0 1-1.499.042l-.129.073a.75.75 0 0 1-.744-1.302l.11-.063-.11-.063a.75.75 0 0 1-.28-1.023ZM6.102 6.915a.75.75 0 0 1 1.023-.279l.875.5.875-.5a.75.75 0 0 1 .744 1.303l-.869.496v.815a.75.75 0 0 1-1.5 0v-.815l-.869-.496a.75.75 0 0 1-.28-1.024ZM2.75 9a.75.75 0 0 1 .75.75v.815l.872.498a.75.75 0 0 1-.744 1.303l-1.25-.715A.75.75 0 0 1 2 11V9.75A.75.75 0 0 1 2.75 9Zm10.5 0a.75.75 0 0 1 .75.75V11a.75.75 0 0 1-.378.651l-1.25.715a.75.75 0 0 1-.744-1.303l.872-.498V9.75a.75.75 0 0 1 .75-.75Zm-4.501 3.708.126-.072a.75.75 0 0 1 .744 1.303l-1.247.712a.75.75 0 0 1-.744 0L6.38 13.94a.75.75 0 0 1 .744-1.303l.126.072a.75.75 0 0 1 1.498 0Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <span>Models</span>
                </div>

                <div class="flex items-center gap-10">
                    <div class="flex items-center gap-2">
                        <span class="text-xxs font-medium text-white/50 uppercase"> Models </span>
                        <span>
                            {{
                                Object.values(data.models ?? []).reduce(
                                    (acc, curr) => acc + (curr?.count ?? 0),
                                    0,
                                )
                            }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xxs font-medium text-white/50 uppercase">
                            Distinct Models
                        </span>
                        <span>
                            {{ Object.keys(data.models ?? []).filter(onlyUnique).length }}
                        </span>
                    </div>
                </div>
                <div class="relative min-w-64"></div>
            </div>
            <div class="h-2 w-full shrink-0"></div>
            <div class="relative flex min-h-0 flex-1 flex-col">
                <table class="relative mt-0 w-full table-fixed text-left">
                    <thead v-if="Object.values(data.models ?? []).length > 0">
                        <tr>
                            <th class="sticky top-0 z-10 my-0.5 w-[60%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div
                                        class="rounded-l-md border-y border-l border-white/7.5 bg-white/3 px-3 py-2"
                                    >
                                        Model
                                    </div>
                                </div>
                            </th>
                            <th
                                class="sticky top-0 z-10 my-0.5 w-[10%] text-right text-[#A3A3A3] uppercase"
                            >
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Count
                                    </div>
                                </div>
                            </th>
                            <th class="sticky top-0 z-10 my-0.5 w-[10%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Action
                                    </div>
                                </div>
                            </th>
                            <th
                                class="shadow-3xl sticky top-0 z-10 my-0.5 w-[30%] text-[#A3A3A3] uppercase shadow-black"
                            >
                                <div class="pb-0.5">
                                    <div
                                        class="rounded-r-md border-y border-r border-white/7.5 bg-white/3 px-3 py-2"
                                    >
                                        Source
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                </table>
                <div ref="queriesTable" :class="tableBodyClasses">
                    <table
                        ref="queriesTableInner"
                        class="relative mt-0 w-full table-fixed text-left"
                    >
                        <tbody>
                            <template v-if="Object.values(data.models ?? []).length === 0">
                                <tr>
                                    <td colspan="3" class="text-center text-white/50">
                                        <div
                                            class="flex flex-col items-center justify-center gap-4 py-16"
                                        >
                                            <EmptyListIcon class="w-24" />
                                            <span class="text-white/75">No models hydrated</span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template
                                v-else
                                v-for="(model, modelClass) in data.models"
                                :key="modelClass"
                            >
                                <tr
                                    class="group relative"
                                    :class="{ 'cursor-pointer': hasMultipleSources(model) }"
                                    @click="hasMultipleSources(model) && toggleModel(modelClass)"
                                >
                                    <td class="w-[60%]">
                                        <div
                                            :class="
                                                isExpanded(modelClass) && hasMultipleSources(model)
                                                    ? 'pt-0.5 pb-0'
                                                    : 'py-0.5'
                                            "
                                        >
                                            <div
                                                class="overflow-hidden border-y border-l border-white/7.5 bg-white/3 px-3 py-2 text-ellipsis group-hover:bg-white/5"
                                                :class="
                                                    isExpanded(modelClass) &&
                                                    hasMultipleSources(model)
                                                        ? 'rounded-tl-md border-b-0'
                                                        : 'rounded-l-md'
                                                "
                                            >
                                                <span
                                                    class="flex items-center gap-1.5 whitespace-nowrap"
                                                >
                                                    <ChevronRightIcon
                                                        v-if="hasMultipleSources(model)"
                                                        class="size-3.5 shrink-0 text-white/40 transition-transform duration-150"
                                                        :class="{
                                                            'rotate-90': expandedModels[modelClass],
                                                        }"
                                                    />
                                                    {{ modelClass }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="w-[10%] text-right">
                                        <div
                                            :class="
                                                isExpanded(modelClass) && hasMultipleSources(model)
                                                    ? 'pt-0.5 pb-0'
                                                    : 'py-0.5'
                                            "
                                        >
                                            <div
                                                class="overflow-hidden border-y border-white/7.5 bg-white/3 px-3 py-2 text-ellipsis group-hover:bg-white/5"
                                                :class="{
                                                    'border-b-0':
                                                        isExpanded(modelClass) &&
                                                        hasMultipleSources(model),
                                                }"
                                            >
                                                <span class="whitespace-nowrap">
                                                    {{ model.count }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="w-[10%]">
                                        <div
                                            :class="
                                                isExpanded(modelClass) && hasMultipleSources(model)
                                                    ? 'pt-0.5 pb-0'
                                                    : 'py-0.5'
                                            "
                                        >
                                            <div
                                                class="overflow-hidden border-y border-white/7.5 bg-white/3 px-3 py-[7px] text-ellipsis group-hover:bg-white/5"
                                                :class="{
                                                    'border-b-0':
                                                        isExpanded(modelClass) &&
                                                        hasMultipleSources(model),
                                                }"
                                            >
                                                <Pill :color="getActionPillColor(model.action)">
                                                    {{ model.action }}
                                                </Pill>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="w-[30%]">
                                        <div
                                            :class="
                                                isExpanded(modelClass) && hasMultipleSources(model)
                                                    ? 'pt-0.5 pb-0'
                                                    : 'py-0.5'
                                            "
                                        >
                                            <div
                                                class="overflow-hidden border-y border-r border-white/7.5 bg-white/3 px-3 py-2 text-ellipsis group-hover:bg-white/5"
                                                :class="
                                                    isExpanded(modelClass) &&
                                                    hasMultipleSources(model)
                                                        ? 'rounded-tr-md border-b-0'
                                                        : 'rounded-r-md'
                                                "
                                            >
                                                <template v-if="hasMultipleSources(model)">
                                                    <span class="whitespace-nowrap text-white/40">
                                                        {{ getSourceCount(model) }} sources
                                                    </span>
                                                </template>
                                                <template v-else-if="getSingleSource(model)">
                                                    <span class="whitespace-nowrap">
                                                        <a
                                                            class="cursor-pointer text-white/40 hover:underline"
                                                            :href="
                                                                getSingleSource(model)
                                                                    .editor_url
                                                            "
                                                            target="_blank"
                                                            @click.stop
                                                            >{{
                                                                getSourceLabel(
                                                                    getSingleSource(model),
                                                                )
                                                            }}</a
                                                        >
                                                    </span>
                                                </template>
                                                <span v-else class="whitespace-nowrap text-white/40"
                                                    >-</span
                                                >
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-if="isExpanded(modelClass) && hasMultipleSources(model)"
                                >
                                    <td colspan="4" class="p-0">
                                        <div class="pb-0.5">
                                            <div
                                                class="overflow-hidden rounded-b-md border-x border-b border-white/7.5 bg-white/3"
                                            >
                                                <table class="w-full text-left" style="table-layout: fixed">
                                                    <colgroup>
                                                        <col style="width: 60%" />
                                                        <col style="width: 10%" />
                                                        <col style="width: 10%" />
                                                        <col style="width: 30%" />
                                                    </colgroup>
                                                    <tbody>
                                                        <tr
                                                            v-for="(
                                                                source, sourceKey
                                                            ) in model.sources"
                                                            :key="sourceKey"
                                                            class="group/source border-t border-white/5"
                                                        >
                                                            <td
                                                                class="px-3 py-2 group-hover/source:bg-white/3"
                                                            ></td>
                                                            <td
                                                                class="px-3 py-2 text-right text-white/60 group-hover/source:bg-white/3"
                                                            >
                                                                {{ source.count }}
                                                            </td>
                                                            <td
                                                                class="group-hover/source:bg-white/3"
                                                            ></td>
                                                            <td
                                                                class="px-3 py-2 text-white/60 group-hover/source:bg-white/3"
                                                            >
                                                                <span class="whitespace-nowrap">
                                                                    <a
                                                                        class="cursor-pointer hover:underline"
                                                                        :href="
                                                                            source.editor_url
                                                                        "
                                                                        target="_blank"
                                                                        >{{
                                                                            getSourceLabel(source)
                                                                        }}</a
                                                                    >
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </Panel>
        <ToolbarItem
            :class="itemClasses"
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            :isActive="isOpen"
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
                        d="M7.628 1.349a.75.75 0 0 1 .744 0l1.247.712a.75.75 0 1 1-.744 1.303L8 2.864l-.875.5a.75.75 0 0 1-.744-1.303l1.247-.712ZM4.65 3.914a.75.75 0 0 1-.279 1.023L4.262 5l.11.063a.75.75 0 0 1-.744 1.302l-.13-.073A.75.75 0 0 1 2 6.25V5a.75.75 0 0 1 .378-.651l1.25-.714a.75.75 0 0 1 1.023.279Zm6.698 0a.75.75 0 0 1 1.023-.28l1.25.715A.75.75 0 0 1 14 5v1.25a.75.75 0 0 1-1.499.042l-.129.073a.75.75 0 0 1-.744-1.302l.11-.063-.11-.063a.75.75 0 0 1-.28-1.023ZM6.102 6.915a.75.75 0 0 1 1.023-.279l.875.5.875-.5a.75.75 0 0 1 .744 1.303l-.869.496v.815a.75.75 0 0 1-1.5 0v-.815l-.869-.496a.75.75 0 0 1-.28-1.024ZM2.75 9a.75.75 0 0 1 .75.75v.815l.872.498a.75.75 0 0 1-.744 1.303l-1.25-.715A.75.75 0 0 1 2 11V9.75A.75.75 0 0 1 2.75 9Zm10.5 0a.75.75 0 0 1 .75.75V11a.75.75 0 0 1-.378.651l-1.25.715a.75.75 0 0 1-.744-1.303l.872-.498V9.75a.75.75 0 0 1 .75-.75Zm-4.501 3.708.126-.072a.75.75 0 0 1 .744 1.303l-1.247.712a.75.75 0 0 1-.744 0L6.38 13.94a.75.75 0 0 1 .744-1.303l.126.072a.75.75 0 0 1 1.498 0Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>{{
                    Object.values(data.models ?? []).reduce(
                        (acc, curr) => acc + (curr?.count ?? 0),
                        0,
                    )
                }}</span>
            </div>
        </ToolbarItem>
    </div>
</template>
