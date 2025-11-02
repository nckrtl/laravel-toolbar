<script setup>
import { ref, computed } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import Panel from '@/components/Panel.vue';
import Section from '@/components/Section.vue';
import DataListItem from '@/components/DataListItem.vue';
import { useToolbar } from '@/composables/useToolbar';

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

const hoverIndex = ref(null);

const computedStages = computed(() => {
    return data.value.profiler?.stages.filter((stage) => {
        return stage.wall_time.measurement.value !== 0;
    });
});

const requestCodeMessage = (code) => {
    return (
        {
            200: 'OK',
            201: 'Created',
            202: 'Accepted',
            204: 'No Content',
            206: 'Partial Content',
            207: 'Multi-Status',
            208: 'Already Reported',
            226: 'IM Used',
            300: 'Multiple Choices',
            301: 'Moved Permanently',
            302: 'Found',
            303: 'See Other',
            304: 'Not Modified',
            305: 'Use Proxy',
            306: 'Switch Proxy',
            307: 'Temporary Redirect',
            308: 'Permanent Redirect',
            400: 'Bad Request',
            401: 'Unauthorized',
            403: 'Forbidden',
            404: 'Not Found',
            405: 'Method Not Allowed',
            406: 'Not Acceptable',
            407: 'Proxy Authentication Required',
            408: 'Request Timeout',
            409: 'Conflict',
            410: 'Gone',
            411: 'Length Required',
            412: 'Precondition Failed',
            413: 'Payload Too Large',
            414: 'URI Too Long',
            415: 'Unsupported Media Type',
            416: 'Range Not Satisfiable',
            417: 'Expectation Failed',
            418: "I'm a teapot",
            422: 'Unprocessable Entity',
            423: 'Locked',
            424: 'Failed Dependency',
            425: 'Too Early',
            426: 'Upgrade Required',
            428: 'Precondition Required',
            429: 'Too Many Requests',
            431: 'Request Header Fields Too Large',
            451: 'Unavailable For Legal Reasons',
            500: 'Internal Server Error',
            501: 'Not Implemented',
            502: 'Bad Gateway',
            503: 'Service Unavailable',
            504: 'Gateway Timeout',
            505: 'HTTP Version Not Supported',
            506: 'Variant Also Negotiates',
            507: 'Insufficient Storage',
            508: 'Loop Detected',
            510: 'Not Extended',
            511: 'Network Authentication Required',
        }[code] ?? 'Unknown'
    );
};

const requestCodeColor = (code) => {
    if (code >= 200 && code < 300) {
        return 'text-emerald-300';
    } else if (code >= 300 && code < 400) {
        return 'text-yellow-500';
    } else {
        return 'text-danger';
    }
};

const methodColor = (method) => {
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

const openRouteEditor = (url) => {
    if (url) {
        window.open(url, '_blank');
    }
};
</script>

<template>
    <div>
        <Panel v-if="isOpen" size="md" @mouseenter="isOpen = true" @mouseleave="isOpen = false">
            <SectionHeader>
                <template #icon>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-3.5"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0V8c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5v-3.5Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </template>
                <template #label> Timings </template>
                <template #secondaryLabel>
                    {{ data.profiler?.total_wall_time?.formattedValue }}
                </template>
            </SectionHeader>

            <Section>
                <div class="flex w-full px-0.5">
                    <template v-for="(requestStage, index) in computedStages" :key="index">
                        <div
                            @mouseenter="hoverIndex = index"
                            @mouseleave="hoverIndex = null"
                            class="flex min-w-2 py-1.5"
                            :style="{ width: requestStage.wall_time.percentage + '%' }"
                        >
                            <div
                                class="mx-auto h-1 rounded-full"
                                :style="{
                                    backgroundColor: requestStage.color,
                                    width: 'calc(100% - 4px)',
                                }"
                            ></div>
                        </div>
                    </template>
                </div>

                <DataListItem
                    v-for="(requestStage, index) in data.profiler?.stages"
                    :key="index"
                    class=""
                    :class="{ 'opacity-35': hoverIndex !== index && hoverIndex !== null }"
                >
                    <template #label>
                        <div class="flex items-center gap-1.5">
                            <div
                                class="h-1.5 w-1.5 rounded-full"
                                :style="{ backgroundColor: requestStage.color }"
                            ></div>
                            <span>{{ requestStage.label }}</span>
                        </div>
                    </template>
                    <template #value>
                        <span v-if="requestStage.wall_time.measurement.value === 0">N/A</span>
                        <span v-else-if="hoverIndex !== index">{{
                            requestStage.wall_time.measurement.formattedValue
                        }}</span>
                        <span v-else>{{ requestStage.wall_time.percentage }}%</span>
                    </template>
                </DataListItem>
            </Section>
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
                    class="size-3.5"
                >
                    <path
                        fill-rule="evenodd"
                        d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0V8c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5v-3.5Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>{{ data.profiler?.total_wall_time?.formattedValue }}</span>
            </div>
        </ToolbarItem>
    </div>
</template>
