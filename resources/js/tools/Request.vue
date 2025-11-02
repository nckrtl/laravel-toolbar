<script setup>
import { ref, computed, onMounted } from 'vue';
import DataListItem from '@/components/DataListItem.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import Panel from '@/components/Panel.vue';
import Section from '@/components/Section.vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import { useToolbar } from '@/composables/useToolbar';
import Pill from '@/components/Pill.vue';

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
    <div class="">
        <Panel v-if="isOpen" @mouseenter="isOpen = true" @mouseleave="isOpen = false">
            <SectionHeader>
                <template #icon>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="absolute size-3"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M2 8c0 .414.336.75.75.75h8.69l-1.22 1.22a.75.75 0 1 0 1.06 1.06l2.5-2.5a.75.75 0 0 0 0-1.06l-2.5-2.5a.75.75 0 1 0-1.06 1.06l1.22 1.22H2.75A.75.75 0 0 0 2 8Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </template>
                <template #label> Request </template>
                <template #secondaryLabel>
                    <a
                        v-if="data.request?.uuid"
                        class="hover:underline"
                        :href="`/telescope/requests/${data.request?.uuid}`"
                        >{{ data.request?.uuid?.slice(0, 6) }}...{{
                            data.request?.uuid?.slice(-6)
                        }}</a
                    >
                    <!-- <span v-else-if="data.request?.is_inertia" class="hover:underline">Inertia</span>
            <span v-else class="hover:underline">HTTP</span> -->
                </template>
            </SectionHeader>

            <Section>
                <DataListItem>
                    <template #label> Method </template>
                    <template #value>
                        <span class="uppercase" :class="methodColor(data.request?.method)">{{
                            data.request?.method
                        }}</span>
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> URI </template>
                    <template #value>
                        <span class="whitespace-nowrap" :title="data.request?.uri">{{
                            data.request?.uri
                        }}</span>
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Name </template>
                    <template #value>
                        <span
                            :class="
                                data.request?.route_editor_url
                                    ? 'cursor-pointer hover:underline'
                                    : ''
                            "
                            @click="openRouteEditor(data.request?.route_editor_url)"
                            :title="data.request?.route_editor_url"
                            >{{ data.request?.route_name }}</span
                        >
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Middleware </template>
                    <template #value>
                        <span :title="data.request?.middleware.join(', ')">{{
                            data.request?.middleware.length
                        }}</span>
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Action </template>
                    <template #value>
                        <span
                            :class="
                                data.request?.controller_action_editor_url
                                    ? 'cursor-pointer hover:underline'
                                    : ''
                            "
                            @click="openRouteEditor(data.request?.controller_action_editor_url)"
                            :title="data.request?.controller_action_editor_url"
                            >{{
                                data.request?.controller_action.split('\\')[
                                    data.request?.controller_action.split('\\').length - 1
                                ]
                            }}</span
                        >
                    </template>
                </DataListItem>
            </Section>

            <SectionHeader class="mt-3">
                <template #icon>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="absolute size-3 rotate-180"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M2 8c0 .414.336.75.75.75h8.69l-1.22 1.22a.75.75 0 1 0 1.06 1.06l2.5-2.5a.75.75 0 0 0 0-1.06l-2.5-2.5a.75.75 0 1 0-1.06 1.06l1.22 1.22H2.75A.75.75 0 0 0 2 8Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </template>
                <template #label>Response</template>
                <template #secondaryLabel>{{
                    data.profiler?.total_wall_time?.formattedValue
                }}</template>
            </SectionHeader>

            <Section>
                <DataListItem>
                    <template #label>Status</template>
                    <template #value>
                        <span
                            class="uppercase"
                            :class="requestCodeColor(data.response?.status_code)"
                            >{{ data.response?.status_code }} -
                            {{ requestCodeMessage(data.response?.status_code) }}</span
                        >
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label>Size</template>
                    <template #value>
                        <span>{{ data.response?.size.formattedValue }}</span>
                    </template>
                </DataListItem>
            </Section>
        </Panel>

        <ToolbarItem
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            :isActive="isOpen"
            :class="itemClasses"
            innerPadding="pl-0.5"
        >
            <Pill color="green">{{ data.response?.status_code }}</Pill>
            <!-- <span>{{ data.request?.method }}</span>
            <span>
                <span>{{ data.request?.uri.slice(0, 32) }}</span
                ><span v-if="data.request?.uri.length > 32">...</span>
            </span> -->
        </ToolbarItem>
    </div>
</template>
