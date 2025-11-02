<script setup>
import { ref } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import { useToolbar } from '@/composables/useToolbar';
import Panel from '@/components/Panel.vue';
import EmptyListIcon from '@/icons/EmptyListIcon.vue';
import { FunnelIcon } from '@heroicons/vue/16/solid';

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

const onlyUnique = (value, index, array) => {
    return array.indexOf(value) === index;
};
</script>

<template>
    <div>
        <Panel
            v-if="isOpen"
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            size="full"
            minHeight="h-[385px]"
        >
            <div class="flex items-center justify-between">
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
                <div class="relative min-w-64">
                    <!-- <input
                        placeholder="Search"
                        class="min-w-64 rounded-md border border-white/10 bg-white/3 px-3 py-2 text-white placeholder-white/40 focus:border-white/70 focus:ring-2 focus:ring-white/20 focus:outline-none"
                        type="text"
                        v-model="searchPhrase"
                    />

                    <div class="absolute top-2.5 right-2">
                        <div
                            class="absolute -top-[5px] -right-[3px] size-2 rounded-full border-1 border-black/80 bg-red-500"
                        ></div>
                        <FunnelIcon class="size-4 text-white/50" />
                    </div> -->
                </div>
            </div>
            <div class="h-2 w-full"></div>
            <div class="relative">
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
                            <th
                                class="shadow-3xl sticky top-0 z-10 my-0.5 w-[20%] text-[#A3A3A3] uppercase shadow-black"
                            >
                                <div class="pb-0.5">
                                    <div
                                        class="rounded-r-md border-y border-r border-white/7.5 bg-white/3 px-3 py-2"
                                    >
                                        File
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                </table>
                <div
                    ref="queriesTable"
                    class="relative max-h-[190px] overflow-y-auto rounded-b-lg pb-3"
                >
                    <table
                        ref="queriesTableInner"
                        class="relative mt-0 w-full table-fixed text-left"
                    >
                        <tbody>
                            <template v-if="Object.values(data.models ?? []).length === 0">
                                <tr>
                                    <td colspan="4" class="text-center text-white/50">
                                        <div
                                            class="flex flex-col items-center justify-center gap-4 py-16"
                                        >
                                            <EmptyListIcon class="w-24" />
                                            <span class="text-white/75"
                                                >No {{ filter }} queries found</span
                                            >
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr
                                v-else
                                v-for="(model, index) in data.models"
                                :key="index"
                                class="group relative"
                            >
                                <td class="w-[60%]">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden rounded-l-md border-y border-l border-white/7.5 bg-white/3 px-3 py-2 text-ellipsis group-hover:bg-white/5"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ index }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[10%] text-right">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y border-white/7.5 bg-white/3 px-3 py-2 text-ellipsis group-hover:bg-white/5"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ model.count }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[20%] text-right">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden rounded-r-md border-y border-r border-white/7.5 bg-white/3 px-3 py-2 text-ellipsis group-hover:bg-white/5"
                                        >
                                            <span class="whitespace-nowrap"> - </span>
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
