<script setup>
import { computed, ref } from 'vue';
import DataListItem from '@/components/DataListItem.vue';
import Panel from '@/components/Panel.vue';
import Pill from '@/components/Pill.vue';
import Section from '@/components/Section.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
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
const activeTab = ref('request');
const request = computed(() => data.value.request);
const response = computed(() => data.value.response);

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
    if (typeof code !== 'number') {
        return 'text-danger';
    }

    if (code >= 200 && code < 300) {
        return 'text-emerald-300';
    }

    if (code >= 300 && code < 400) {
        return 'text-yellow-500';
    }

    return 'text-danger';
};

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

const controllerActionLabel = (controllerAction) => {
    if (!controllerAction) {
        return '—';
    }

    const parts = controllerAction.split('\\');

    return parts[parts.length - 1] ?? controllerAction;
};

const formatValue = (value) => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (Array.isArray(value)) {
        return value.map((item) => formatValue(item)).join(', ');
    }

    if (typeof value === 'object') {
        try {
            return JSON.stringify(value);
        } catch {
            return '—';
        }
    }

    return String(value);
};

const normalizeEntries = (values) => {
    if (!values || typeof values !== 'object') {
        return [];
    }

    return Object.entries(values).map(([label, value]) => ({
        label,
        value: formatValue(value),
    }));
};

const normalizeHeaderEntries = (headers) => {
    if (!headers || typeof headers !== 'object') {
        return [];
    }

    return Object.entries(headers).map(([label, value]) => ({
        label,
        value: Array.isArray(value) ? value.join(', ') : formatValue(value),
    }));
};

const routeParameters = computed(() => normalizeEntries(request.value?.route_parameters));
const queryParameters = computed(() => normalizeEntries(request.value?.query_parameters));
const requestHeaders = computed(() => normalizeHeaderEntries(request.value?.headers));
const responseHeaders = computed(() => normalizeHeaderEntries(response.value?.headers));
const middlewareList = computed(() =>
    Array.isArray(request.value?.middleware) ? request.value.middleware : [],
);
const uploadedFiles = computed(() =>
    Array.isArray(request.value?.uploaded_files) ? request.value.uploaded_files : [],
);
const responseCookies = computed(() =>
    Array.isArray(response.value?.cookies) ? response.value.cookies : [],
);
const responseContentType = computed(() => response.value?.content_type ?? '—');
const redirectTarget = computed(() => response.value?.redirect_to ?? null);
const responseSize = computed(() => response.value?.size?.formattedValue ?? '—');

const tabButtonClasses = (tab) => {
    return activeTab.value === tab
        ? 'border border-white/10 bg-white/10 text-white'
        : 'border border-transparent text-white/60 hover:bg-white/5 hover:text-white';
};

const cookieSummary = (cookie) => {
    const parts = [];

    if (cookie?.value) {
        parts.push(cookie.value);
    }

    if (cookie?.path) {
        parts.push(`Path: ${cookie.path}`);
    }

    if (cookie?.domain) {
        parts.push(`Domain: ${cookie.domain}`);
    }

    if (cookie?.same_site) {
        parts.push(`SameSite: ${cookie.same_site}`);
    }

    if (cookie?.secure) {
        parts.push('Secure');
    }

    if (cookie?.http_only) {
        parts.push('HttpOnly');
    }

    return parts.join(' • ') || '—';
};

const openLink = (url) => {
    if (url) {
        window.open(url, '_blank');
    }
};
</script>

<template>
    <div>
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
                <template #label>Request</template>
                <template #secondaryLabel>
                    <a
                        v-if="request?.uuid"
                        class="hover:underline"
                        :href="`/telescope/requests/${request?.uuid}`"
                    >
                        {{ request?.uuid?.slice(0, 6) }}...{{ request?.uuid?.slice(-6) }}
                    </a>
                </template>
            </SectionHeader>

            <div class="mt-1 flex rounded-lg border border-white/8 bg-white/3 p-1">
                <button
                    type="button"
                    class="flex-1 rounded-md px-3 py-1.5 text-xxs font-medium uppercase transition-colors"
                    :class="tabButtonClasses('request')"
                    @click="activeTab = 'request'"
                >
                    Request
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-md px-3 py-1.5 text-xxs font-medium uppercase transition-colors"
                    :class="tabButtonClasses('response')"
                    @click="activeTab = 'response'"
                >
                    Response
                </button>
            </div>

            <template v-if="activeTab === 'request'">
                <Section class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Route</div>
                    <DataListItem>
                        <template #label>Method</template>
                        <template #value>
                            <span class="uppercase" :class="methodColor(request?.method)">{{
                                request?.method
                            }}</span>
                        </template>
                    </DataListItem>
                    <DataListItem>
                        <template #label>URI</template>
                        <template #value>
                            <span class="whitespace-nowrap" :title="request?.uri">{{ request?.uri }}</span>
                        </template>
                    </DataListItem>
                    <DataListItem>
                        <template #label>Pattern</template>
                        <template #value>
                            <span class="whitespace-nowrap" :title="request?.route_uri">
                                {{ request?.route_uri ?? '—' }}
                            </span>
                        </template>
                    </DataListItem>
                    <DataListItem>
                        <template #label>Name</template>
                        <template #value>
                            <span
                                :class="request?.route_editor_url ? 'cursor-pointer hover:underline' : ''"
                                :title="request?.route_editor_url"
                                @click="openLink(request?.route_editor_url)"
                            >
                                {{ request?.route_name ?? '—' }}
                            </span>
                        </template>
                    </DataListItem>
                    <DataListItem>
                        <template #label>Action</template>
                        <template #value>
                            <span
                                :class="request?.editor_url ? 'cursor-pointer hover:underline' : ''"
                                :title="request?.controller_action"
                                @click="openLink(request?.editor_url)"
                            >
                                {{ controllerActionLabel(request?.controller_action) }}
                            </span>
                        </template>
                    </DataListItem>
                </Section>

                <Section v-if="routeParameters.length > 0" class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Route Parameters</div>
                    <div class="flex flex-col gap-1">
                        <div
                            v-for="entry in routeParameters"
                            :key="`route-${entry.label}`"
                            class="flex items-start justify-between gap-4 rounded-md border border-white/8 bg-black/10 px-3 py-2"
                        >
                            <span class="shrink-0 text-white/60 uppercase">{{ entry.label }}</span>
                            <span class="truncate text-right text-white" :title="entry.value">{{ entry.value }}</span>
                        </div>
                    </div>
                </Section>

                <Section v-if="queryParameters.length > 0" class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Query Parameters</div>
                    <div class="flex flex-col gap-1">
                        <div
                            v-for="entry in queryParameters"
                            :key="`query-${entry.label}`"
                            class="flex items-start justify-between gap-4 rounded-md border border-white/8 bg-black/10 px-3 py-2"
                        >
                            <span class="shrink-0 text-white/60 uppercase">{{ entry.label }}</span>
                            <span class="truncate text-right text-white" :title="entry.value">{{ entry.value }}</span>
                        </div>
                    </div>
                </Section>

                <Section class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Middleware</div>
                    <div class="flex flex-col gap-1">
                        <div
                            v-for="middleware in middlewareList"
                            :key="middleware"
                            class="rounded-md border border-white/8 bg-black/10 px-3 py-2 text-white"
                        >
                            {{ middleware }}
                        </div>
                    </div>
                </Section>

                <Section v-if="uploadedFiles.length > 0" class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Uploaded Files</div>
                    <div class="flex flex-col gap-1">
                        <div
                            v-for="file in uploadedFiles"
                            :key="`${file.field}-${file.name}`"
                            class="rounded-md border border-white/8 bg-black/10 px-3 py-2"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-white uppercase">{{ file.field }}</span>
                                <span class="text-white/70">{{ file.size ?? '—' }}</span>
                            </div>
                            <div class="mt-1 text-white">{{ file.name ?? '—' }}</div>
                            <div class="text-white/60">{{ file.mime_type ?? '—' }}</div>
                        </div>
                    </div>
                </Section>

                <Section v-if="requestHeaders.length > 0" class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Headers</div>
                    <div class="flex flex-col gap-1">
                        <div
                            v-for="header in requestHeaders"
                            :key="`request-header-${header.label}`"
                            class="flex items-start justify-between gap-4 rounded-md border border-white/8 bg-black/10 px-3 py-2"
                        >
                            <span class="shrink-0 text-white/60 uppercase">{{ header.label }}</span>
                            <span class="truncate text-right text-white" :title="header.value">{{ header.value }}</span>
                        </div>
                    </div>
                </Section>
            </template>

            <template v-else>
                <Section class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Summary</div>
                    <DataListItem>
                        <template #label>Status</template>
                        <template #value>
                            <span class="uppercase" :class="requestCodeColor(response?.status_code)">
                                {{ response?.status_code }} - {{ requestCodeMessage(response?.status_code) }}
                            </span>
                        </template>
                    </DataListItem>
                    <DataListItem>
                        <template #label>Size</template>
                        <template #value>
                            <span>{{ responseSize }}</span>
                        </template>
                    </DataListItem>
                    <DataListItem>
                        <template #label>Content Type</template>
                        <template #value>
                            <span class="whitespace-nowrap" :title="responseContentType">{{ responseContentType }}</span>
                        </template>
                    </DataListItem>
                    <DataListItem v-if="redirectTarget">
                        <template #label>Redirect</template>
                        <template #value>
                            <span class="whitespace-nowrap" :title="redirectTarget">{{ redirectTarget }}</span>
                        </template>
                    </DataListItem>
                </Section>

                <Section v-if="responseCookies.length > 0" class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Cookies</div>
                    <div class="flex flex-col gap-1">
                        <div
                            v-for="cookie in responseCookies"
                            :key="`response-cookie-${cookie.name}`"
                            class="rounded-md border border-white/8 bg-black/10 px-3 py-2"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-white uppercase">{{ cookie.name }}</span>
                                <span class="text-white/60">{{ cookie.expires_at ?? 'Session' }}</span>
                            </div>
                            <div class="mt-1 text-white" :title="cookieSummary(cookie)">
                                {{ cookieSummary(cookie) }}
                            </div>
                        </div>
                    </div>
                </Section>

                <Section v-if="responseHeaders.length > 0" class="mt-3">
                    <div class="px-1 text-white/50 uppercase">Headers</div>
                    <div class="flex flex-col gap-1">
                        <div
                            v-for="header in responseHeaders"
                            :key="`response-header-${header.label}`"
                            class="flex items-start justify-between gap-4 rounded-md border border-white/8 bg-black/10 px-3 py-2"
                        >
                            <span class="shrink-0 text-white/60 uppercase">{{ header.label }}</span>
                            <span class="truncate text-right text-white" :title="header.value">{{ header.value }}</span>
                        </div>
                    </div>
                </Section>
            </template>
        </Panel>

        <ToolbarItem
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            :isActive="isOpen"
            :class="itemClasses"
            innerPadding="pl-0.5"
        >
            <Pill class="px-1.5 py-[5px]" color="green">{{ response?.status_code ?? '--' }}</Pill>
        </ToolbarItem>
    </div>
</template>
