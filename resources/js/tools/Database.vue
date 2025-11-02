<script setup>
import { ref, watch, computed } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import Panel from '@/components/Panel.vue';
import { useToolbar } from '@/composables/useToolbar';
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
const searchPhrase = ref('');
const filter = ref('none');

const filteredQueries = computed(() => {
    let queries = data.value?.queries?.queries || [];

    if (searchPhrase.value !== '') {
        queries = queries.filter(
            (query) =>
                query.sql.includes(searchPhrase.value) || query.file.includes(searchPhrase.value),
        );
    }

    if (filter.value !== 'none') {
        queries = queries.filter((query) =>
            filter.value === 'slow' ? query.isSlow : query.isDuplicate,
        );
    }

    return queries;
});

const queriesTable = ref(null);
const queriesTableInner = ref(null);

watch(queriesTable, (newVal) => {
    if (newVal) {
        newVal.addEventListener('scroll', () => {
            const scrollTop = newVal.scrollTop;

            if (
                scrollTop + queriesTable.value.clientHeight ==
                queriesTableInner.value.clientHeight + 12
            ) {
                queriesTable.value.classList.remove('fade-to-top-and-bottom');
                queriesTable.value.classList.add('fade-to-top');
            } else if (scrollTop > 1) {
                queriesTable.value.classList.remove('fade-to-bottom', 'fade-to-top');
                queriesTable.value.classList.add('fade-to-top-and-bottom');
            } else {
                queriesTable.value.classList.remove('fade-to-bottom-and-top');
                queriesTable.value.classList.add('fade-to-bottom');
            }
        });
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
                                d="M8 7c3.314 0 6-1.343 6-3s-2.686-3-6-3-6 1.343-6 3 2.686 3 6 3Z"
                            />
                            <path
                                d="M8 8.5c1.84 0 3.579-.37 4.914-1.037A6.33 6.33 0 0 0 14 6.78V8c0 1.657-2.686 3-6 3S2 9.657 2 8V6.78c.346.273.72.5 1.087.683C4.42 8.131 6.16 8.5 8 8.5Z"
                            />
                            <path
                                d="M8 12.5c1.84 0 3.579-.37 4.914-1.037.366-.183.74-.41 1.086-.684V12c0 1.657-2.686 3-6 3s-6-1.343-6-3v-1.22c.346.273.72.5 1.087.683C4.42 12.131 6.16 12.5 8 12.5Z"
                            />
                        </svg>
                    </div>
                    <span>Queries</span>
                </div>

                <div class="flex items-center gap-10">
                    <div class="flex items-center gap-2">
                        <span class="text-xxs font-medium text-white/50 uppercase"> Queries </span>
                        <span>
                            {{ filteredQueries.length }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xxs font-medium text-white/50 uppercase"> Duration </span>
                        <span>
                            <!-- <span v-if="data.queries?.totalTimeFilteredQueries"
                                >{{
                                    Math.round(data.queries?.totalTimeFilteredQueries * 100) / 100
                                }}ms of
                            </span> -->
                            <span>{{ Math.round(data.queries?.totalTime) }}ms</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xxs font-medium text-white/50 uppercase">
                            Connection
                        </span>
                        <span>
                            {{ data.queries?.connections.join(', ') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xxs font-medium text-white/50 uppercase"> Driver </span>
                        <span>
                            {{ data.queries?.drivers.join(', ') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xxs font-medium text-white/50 uppercase"> Database </span>
                        <span
                            v-for="(database, index) in data.queries?.databases"
                            :key="database.name"
                        >
                            <a
                                :href="database.tablePlusConnectionUrl ?? '#'"
                                target="_blank"
                                class="text-xxs font-medium text-white"
                                :class="{ 'hover:underline': database.tablePlusConnectionUrl }"
                            >
                                {{ database.name }}
                            </a>
                            <span v-if="index < data.queries?.databases.length - 1">, </span>
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
                    <thead v-if="filteredQueries.length > 0">
                        <tr>
                            <th class="sticky top-0 z-10 my-0.5 w-[60%] text-[#A3A3A3] uppercase">
                                <div class="pb-0.5">
                                    <div
                                        class="rounded-l-md border-y border-l border-white/7.5 bg-white/3 px-3 py-2"
                                    >
                                        Query
                                    </div>
                                </div>
                            </th>
                            <th
                                class="sticky top-0 z-10 my-0.5 w-[10%] text-right text-[#A3A3A3] uppercase"
                            >
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Duration
                                    </div>
                                </div>
                            </th>
                            <th
                                class="shadow-3xl sticky top-0 z-10 my-0.5 w-[10%] text-center text-[#A3A3A3] uppercase shadow-black"
                            >
                                <div class="pb-0.5">
                                    <div class="border-y border-white/7.5 bg-white/3 px-3 py-2">
                                        Issue
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
                                        Location
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                </table>
                <div
                    ref="queriesTable"
                    class="relative max-h-[290px] overflow-y-auto rounded-b-lg pb-3"
                >
                    <table
                        ref="queriesTableInner"
                        class="relative mt-0 w-full table-fixed text-left"
                    >
                        <tbody>
                            <template v-if="filteredQueries.length === 0">
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
                                v-for="(query, index) in filteredQueries"
                                :key="index"
                                class="group relative"
                            >
                                <td class="w-[60%]">
                                    <div
                                        class="absolute bottom-0.5 left-2.5 h-px w-[calc(100%-20px)]"
                                    >
                                        <div
                                            class="absolute h-full bg-[#9684FF]/50"
                                            :style="{
                                                width: `${query.percentage * 100}%`,
                                                left: `${query.offset * 100}%`,
                                            }"
                                        ></div>
                                    </div>
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden rounded-l-md border-y border-l px-3 py-2 text-ellipsis"
                                            :class="{
                                                'border-yellow-400/10 bg-yellow-400/6 text-yellow-100 group-hover:bg-yellow-400/10':
                                                    query.isDuplicate,
                                                'border-cyan-400/15 bg-cyan-400/8 text-cyan-100 group-hover:bg-cyan-400/10':
                                                    query.isSlow,
                                                'border-white/7.5 bg-white/3 group-hover:bg-white/5':
                                                    !query.isDuplicate && !query.isSlow,
                                            }"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ query.sql }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[10%] text-right">
                                    <div class="py-0.5">
                                        <div
                                            class="overflow-hidden border-y px-3 py-2 text-ellipsis"
                                            :class="{
                                                'border-yellow-400/10 bg-yellow-400/6 text-yellow-100 group-hover:bg-yellow-400/10':
                                                    query.isDuplicate,
                                                'border-cyan-400/15 bg-cyan-400/8 text-cyan-100 group-hover:bg-cyan-400/10':
                                                    query.isSlow,
                                                'border-white/7.5 bg-white/3 group-hover:bg-white/5':
                                                    !query.isDuplicate && !query.isSlow,
                                            }"
                                        >
                                            <span class="whitespace-nowrap">
                                                {{ query.duration }}ms
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[10%] text-center">
                                    <div class="h-10 py-0.5">
                                        <div
                                            class="h-full overflow-hidden border-y px-3 py-2 text-ellipsis"
                                            :class="{
                                                'border-yellow-400/10 bg-yellow-400/6 text-yellow-100 group-hover:bg-yellow-400/10':
                                                    query.isDuplicate,
                                                'border-cyan-400/15 bg-cyan-400/8 text-cyan-100 group-hover:bg-cyan-400/10':
                                                    query.isSlow,
                                                'border-white/7.5 bg-white/3 group-hover:bg-white/5':
                                                    !query.isDuplicate && !query.isSlow,
                                            }"
                                        >
                                            <span
                                                v-if="query.isDuplicate"
                                                class="text-xxxs rounded bg-yellow-400/10 px-2 py-2 font-bold tracking-wider whitespace-nowrap text-yellow-400 uppercase"
                                            >
                                                Dupe
                                            </span>
                                            <span
                                                v-if="query.isSlow"
                                                class="text-xxxs rounded bg-cyan-400/10 px-2 py-2 font-bold tracking-wider whitespace-nowrap text-cyan-400 uppercase"
                                            >
                                                Slow
                                            </span>
                                            <span v-else class="opacity-20">-</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="w-[20%]">
                                    <div class="h-10 py-0.5">
                                        <div
                                            class="h-full overflow-hidden rounded-r-md border-y border-r px-3 py-2 text-ellipsis"
                                            :class="{
                                                'border-yellow-400/10 bg-yellow-400/6 text-yellow-100 group-hover:bg-yellow-400/10':
                                                    query.isDuplicate,
                                                'border-cyan-400/15 bg-cyan-400/8 text-cyan-100 group-hover:bg-cyan-400/10':
                                                    query.isSlow,
                                                'border-white/7.5 bg-white/3 group-hover:bg-white/5':
                                                    !query.isDuplicate && !query.isSlow,
                                            }"
                                        >
                                            <span class="whitespace-nowrap">
                                                <a
                                                    class="cursor-pointer hover:underline"
                                                    :href="query.controller_action_editor_url"
                                                    target="_blank"
                                                    >{{ query.file.split('/').pop() }}:{{
                                                        query.line
                                                    }}</a
                                                >
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
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            :isActive="isOpen"
            :class="itemClasses"
        >
            <div class="flex items-center gap-1 py-0.5">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    class="size-4"
                >
                    <path d="M8 7c3.314 0 6-1.343 6-3s-2.686-3-6-3-6 1.343-6 3 2.686 3 6 3Z" />
                    <path
                        d="M8 8.5c1.84 0 3.579-.37 4.914-1.037A6.33 6.33 0 0 0 14 6.78V8c0 1.657-2.686 3-6 3S2 9.657 2 8V6.78c.346.273.72.5 1.087.683C4.42 8.131 6.16 8.5 8 8.5Z"
                    />
                    <path
                        d="M8 12.5c1.84 0 3.579-.37 4.914-1.037.366-.183.74-.41 1.086-.684V12c0 1.657-2.686 3-6 3s-6-1.343-6-3v-1.22c.346.273.72.5 1.087.683C4.42 12.131 6.16 12.5 8 12.5Z"
                    />
                </svg>

                <span
                    >{{ data.queries?.queries.length }}<span class="px-0.5 text-white/50">:</span
                    >{{ Math.round(data.queries?.totalTime) }}ms</span
                >
            </div>
        </ToolbarItem>
    </div>
</template>
