<script setup>
import { computed, ref, watch, nextTick, onMounted } from "vue";
import DataList from "@/components/DataList.vue";
import DataListItem from "@/components/DataListItem.vue";
import Section from "@/components/Section.vue";
import SectionHeader from "@/components/SectionHeader.vue";
import InertiaIcon from "@/icons/InertiaIcon.vue";
import { useToolbar } from "@/composables/useToolbar";

// Search state — request section
const isRequestSearching = ref(false);
const requestSearchQuery = ref("");
const requestSearchInputRef = ref(null);

// Search state — response section
const isResponseSearching = ref(false);
const responseSearchQuery = ref("");
const responseSearchInputRef = ref(null);

const startRequestSearch = () => {
    isRequestSearching.value = true;
    nextTick(() => {
        requestSearchInputRef.value?.focus();
    });
};

const stopRequestSearch = () => {
    isRequestSearching.value = false;
    requestSearchQuery.value = "";
};

const startResponseSearch = () => {
    isResponseSearching.value = true;
    nextTick(() => {
        responseSearchInputRef.value?.focus();
    });
};

const stopResponseSearch = () => {
    isResponseSearching.value = false;
    responseSearchQuery.value = "";
};

const { data } = useToolbar();

const REQUEST_TAB_KEY = "toolbar_request_tab";
const RESPONSE_TAB_KEY = "toolbar_response_tab";

const activeRequestTab = ref(localStorage.getItem(REQUEST_TAB_KEY) ?? "summary");
const activeResponseTab = ref(localStorage.getItem(RESPONSE_TAB_KEY) ?? "summary");
const responseTabs = ["summary", "middleware", "headers", "cookies"];

const requestSummaryRef = ref(null);
const responseSummaryRef = ref(null);
const requestContentHeight = ref(0);
const responseContentHeight = ref(0);

const measureRequestHeight = async () => {
    await nextTick();
    if (requestSummaryRef.value) {
        requestContentHeight.value = requestSummaryRef.value.offsetHeight;
    }
};

const measureResponseHeight = async () => {
    await nextTick();
    if (responseSummaryRef.value) {
        responseContentHeight.value = responseSummaryRef.value.offsetHeight;
    }
};

watch(activeRequestTab, (newTab, oldTab) => {
    localStorage.setItem(REQUEST_TAB_KEY, newTab);
    if (newTab === "summary") {
        measureRequestHeight();
    } else if (oldTab === "summary" && requestSummaryRef.value) {
        requestContentHeight.value = requestSummaryRef.value.offsetHeight;
    }
});

watch(activeResponseTab, (newTab, oldTab) => {
    localStorage.setItem(RESPONSE_TAB_KEY, newTab);
    if (newTab === "summary") {
        measureResponseHeight();
    } else if (oldTab === "summary" && responseSummaryRef.value) {
        responseContentHeight.value = responseSummaryRef.value.offsetHeight;
    }
});

const request = computed(() => data.value.request);
const response = computed(() => data.value.response);

const requestTabs = computed(() => {
    const tabs = ["summary", "middleware"];
    if (request.value?.view_data && Object.keys(request.value.view_data).length > 0) {
        tabs.push("data");
    }
    tabs.push("headers");
    if (request.value?.query_parameters && Object.keys(request.value.query_parameters).length > 0) {
        tabs.push("query params");
    }
    return tabs;
});

watch([request, response], () => {
    if (activeRequestTab.value === "summary") {
        measureRequestHeight();
    }
    if (activeResponseTab.value === "summary") {
        measureResponseHeight();
    }
});

onMounted(() => {
    if (requestSummaryRef.value) {
        requestContentHeight.value = requestSummaryRef.value.offsetHeight;
    }
    if (responseSummaryRef.value) {
        responseContentHeight.value = responseSummaryRef.value.offsetHeight;
    }
});

const requestCodeMessage = (code) => {
    return (
        {
            200: "OK",
            201: "Created",
            202: "Accepted",
            204: "No Content",
            206: "Partial Content",
            207: "Multi-Status",
            208: "Already Reported",
            226: "IM Used",
            300: "Multiple Choices",
            301: "Moved Permanently",
            302: "Found",
            303: "See Other",
            304: "Not Modified",
            305: "Use Proxy",
            306: "Switch Proxy",
            307: "Temporary Redirect",
            308: "Permanent Redirect",
            400: "Bad Request",
            401: "Unauthorized",
            403: "Forbidden",
            404: "Not Found",
            405: "Method Not Allowed",
            406: "Not Acceptable",
            407: "Proxy Authentication Required",
            408: "Request Timeout",
            409: "Conflict",
            410: "Gone",
            411: "Length Required",
            412: "Precondition Failed",
            413: "Payload Too Large",
            414: "URI Too Long",
            415: "Unsupported Media Type",
            416: "Range Not Satisfiable",
            417: "Expectation Failed",
            418: "I'm a teapot",
            422: "Unprocessable Entity",
            423: "Locked",
            424: "Failed Dependency",
            425: "Too Early",
            426: "Upgrade Required",
            428: "Precondition Required",
            429: "Too Many Requests",
            431: "Request Header Fields Too Large",
            451: "Unavailable For Legal Reasons",
            500: "Internal Server Error",
            501: "Not Implemented",
            502: "Bad Gateway",
            503: "Service Unavailable",
            504: "Gateway Timeout",
            505: "HTTP Version Not Supported",
            506: "Variant Also Negotiates",
            507: "Insufficient Storage",
            508: "Loop Detected",
            510: "Not Extended",
            511: "Network Authentication Required",
        }[code] ?? "Unknown"
    );
};

const requestCodeColor = (code) => {
    if (typeof code !== "number") return "text-danger";
    if (code >= 200 && code < 300) return "text-emerald-300";
    if (code >= 300 && code < 400) return "text-yellow-500";
    return "text-danger";
};

const methodColor = (method = "") => {
    return (
        {
            GET: "text-lime-400",
            POST: "text-blue-400",
            PUT: "text-yellow-300",
            DELETE: "text-danger",
            PATCH: "text-indigo-400",
            OPTIONS: "text-gray-400",
            HEAD: "text-gray-400",
        }[method] ?? "text-gray-400"
    );
};

const controllerActionLabel = (controllerAction) => {
    if (!controllerAction) return "—";
    const parts = controllerAction.split("\\");
    return parts[parts.length - 1] ?? controllerAction;
};

const formatValue = (value) => {
    if (value === null || value === undefined || value === "") return "—";
    if (Array.isArray(value)) return value.map((item) => formatValue(item)).join(", ");
    if (typeof value === "object") {
        try {
            return JSON.stringify(value);
        } catch {
            return "—";
        }
    }
    return String(value);
};

const normalizeEntries = (values) => {
    if (!values || typeof values !== "object") return [];
    return Object.entries(values).map(([label, value]) => ({ label, value: formatValue(value) }));
};

const normalizeHeaderEntries = (headers) => {
    if (!headers || typeof headers !== "object") return [];
    return Object.entries(headers).map(([label, value]) => ({
        label,
        value: Array.isArray(value) ? value.join(", ") : formatValue(value),
    }));
};

const routeParameters = computed(() => normalizeEntries(request.value?.route_parameters));
const queryParameters = computed(() => normalizeEntries(request.value?.query_parameters));
const requestHeaders = computed(() => normalizeHeaderEntries(request.value?.headers));
const responseHeaders = computed(() => normalizeHeaderEntries(response.value?.headers));
const middlewareList = computed(() =>
    Array.isArray(request.value?.middleware) ? request.value.middleware : [],
);
const outboundMiddlewareList = computed(() =>
    middlewareList.value.filter((m) => m.has_outbound === true),
);

// Filtered variants for request search
const filteredMiddlewareList = computed(() => {
    const q = requestSearchQuery.value.toLowerCase();
    if (!isRequestSearching.value || !q) return middlewareList.value;
    return middlewareList.value.filter((m) => m.class.toLowerCase().includes(q));
});

const filteredRequestHeaders = computed(() => {
    const q = requestSearchQuery.value.toLowerCase();
    if (!isRequestSearching.value || !q) return requestHeaders.value;
    return requestHeaders.value.filter(
        (h) => h.label.toLowerCase().includes(q) || h.value.toLowerCase().includes(q),
    );
});

const filteredQueryParameters = computed(() => {
    const q = requestSearchQuery.value.toLowerCase();
    if (!isRequestSearching.value || !q) return queryParameters.value;
    return queryParameters.value.filter(
        (p) => p.label.toLowerCase().includes(q) || p.value.toLowerCase().includes(q),
    );
});

const filteredViewData = computed(() => {
    const q = requestSearchQuery.value.toLowerCase();
    if (!isRequestSearching.value || !q || !request.value?.view_data) {
        return request.value?.view_data ?? {};
    }
    return Object.fromEntries(
        Object.entries(request.value.view_data).filter(([key]) => key.toLowerCase().includes(q)),
    );
});

// Filtered variants for response search
const filteredOutboundMiddlewareList = computed(() => {
    const q = responseSearchQuery.value.toLowerCase();
    if (!isResponseSearching.value || !q) return outboundMiddlewareList.value;
    return outboundMiddlewareList.value.filter((m) => m.class.toLowerCase().includes(q));
});

const filteredResponseHeaders = computed(() => {
    const q = responseSearchQuery.value.toLowerCase();
    if (!isResponseSearching.value || !q) return responseHeaders.value;
    return responseHeaders.value.filter(
        (h) => h.label.toLowerCase().includes(q) || h.value.toLowerCase().includes(q),
    );
});

const filteredResponseCookies = computed(() => {
    const q = responseSearchQuery.value.toLowerCase();
    if (!isResponseSearching.value || !q) return responseCookies.value;
    return responseCookies.value.filter(
        (c) =>
            (c.name ?? "").toLowerCase().includes(q) || (c.value ?? "").toLowerCase().includes(q),
    );
});

const middlewareBasename = (className) => {
    const parts = className.split("\\");
    return parts[parts.length - 1] ?? className;
};
const uploadedFiles = computed(() =>
    Array.isArray(request.value?.uploaded_files) ? request.value.uploaded_files : [],
);

const responseCookies = computed(() =>
    Array.isArray(response.value?.cookies) ? response.value.cookies : [],
);
const responseContentType = computed(() => response.value?.content_type ?? "—");
const redirectTarget = computed(() => response.value?.redirect_to ?? null);
const responseSize = computed(() => response.value?.size?.formattedValue ?? "—");

const cookieSummary = (cookie) => {
    const parts = [];
    if (cookie?.value) parts.push(cookie.value);
    if (cookie?.path) parts.push(`Path: ${cookie.path}`);
    if (cookie?.domain) parts.push(`Domain: ${cookie.domain}`);
    if (cookie?.same_site) parts.push(`SameSite: ${cookie.same_site}`);
    if (cookie?.secure) parts.push("Secure");
    if (cookie?.http_only) parts.push("HttpOnly");
    return parts.join(" • ") || "—";
};

const uploadedFileSummary = (file) => {
    const parts = [];
    if (file?.name) parts.push(file.name);
    if (file?.size) parts.push(file.size);
    if (file?.mime_type) parts.push(file.mime_type);
    return parts.join(" • ") || "—";
};

const openLink = (url) => {
    if (url) window.open(url, "_blank");
};

const matchesSearch = (query, ...values) => {
    if (!query) return true;
    const q = query.toLowerCase();
    return values.some((v) =>
        String(v ?? "")
            .toLowerCase()
            .includes(q),
    );
};

const requestSummaryHasMatches = computed(() => {
    const q = requestSearchQuery.value;
    if (!isRequestSearching.value || !q) return true;
    return (
        matchesSearch(q, "Method", request.value?.method) ||
        matchesSearch(q, "URI", request.value?.uri) ||
        matchesSearch(q, "Pattern", request.value?.route_uri) ||
        matchesSearch(q, "Name", request.value?.route_name) ||
        matchesSearch(
            q,
            "Action",
            request.value?.controller_action,
            controllerActionLabel(request.value?.controller_action),
        ) ||
        matchesSearch(q, request.value?.is_inertia ? "Page" : "View", request.value?.view_name) ||
        matchesSearch(q, "Type", request.value?.is_inertia ? "Inertia" : "Full page") ||
        uploadedFiles.value.some((file) =>
            matchesSearch(q, "Uploaded Files", file.field, uploadedFileSummary(file)),
        )
    );
});

const responseSummaryHasMatches = computed(() => {
    const q = responseSearchQuery.value;
    if (!isResponseSearching.value || !q) return true;
    return (
        matchesSearch(
            q,
            "Status",
            response.value?.status_code,
            requestCodeMessage(response.value?.status_code),
        ) ||
        matchesSearch(q, "Duration", data.value.profiler?.total_wall_time?.formattedValue) ||
        matchesSearch(q, "Content Type", responseContentType.value) ||
        matchesSearch(q, "Size", responseSize.value) ||
        matchesSearch(q, "Redirect", redirectTarget.value) ||
        matchesSearch(
            q,
            "Type",
            request.value?.is_inertia
                ? "Inertia"
                : responseContentType.value?.includes("text/html")
                  ? "HTML"
                  : responseContentType.value?.includes("application/json")
                    ? "JSON"
                    : responseContentType.value,
        )
    );
});

const requestHeadersList = ref(null);
const requestMiddlewareList = ref(null);
const requestQueryParamsList = ref(null);
const responseMiddlewareList = ref(null);
const responseHeadersList = ref(null);
const responseCookiesList = ref(null);
const requestDataList = ref(null);
const fadeClasses = ["fade-to-bottom", "fade-to-top", "fade-to-top-and-bottom"];

const requestHeaderListMaxHeight = computed(() => {
    const ch = requestContentHeight.value;
    return ch > 0 ? `${ch - 6}px` : "260px";
});

const responseHeaderListMaxHeight = computed(() => {
    const ch = responseContentHeight.value;
    return ch > 0 ? `${ch - 6}px` : "260px";
});

const headerListClasses = (count) => (count > 0 ? "overflow-y-auto overflow-hidden" : "");

const updateScrollFade = (el) => {
    if (!el) return;
    const isScrollable = el.scrollHeight > el.clientHeight + 1;
    const scrollTop = el.scrollTop;
    const atBottom = scrollTop + el.clientHeight >= el.scrollHeight - 1;
    el.classList.remove(...fadeClasses);
    if (!isScrollable) return;
    if (atBottom) {
        el.classList.add("fade-to-top");
    } else if (scrollTop > 1) {
        el.classList.add("fade-to-top-and-bottom");
    } else {
        el.classList.add("fade-to-bottom");
    }
};

const attachScrollFade = (el) => {
    if (!el) return;
    el.addEventListener("scroll", () => updateScrollFade(el));
    nextTick(() => updateScrollFade(el));
};

watch(requestHeadersList, attachScrollFade);
watch(requestMiddlewareList, attachScrollFade);
watch(requestQueryParamsList, attachScrollFade);
watch(responseMiddlewareList, attachScrollFade);
watch(responseHeadersList, attachScrollFade);
watch(responseCookiesList, attachScrollFade);
watch(requestDataList, attachScrollFade);

// Re-evaluate fade when tab changes
watch(activeRequestTab, () =>
    nextTick(() => {
        [
            requestHeadersList,
            requestMiddlewareList,
            requestQueryParamsList,
            requestDataList,
        ].forEach((r) => updateScrollFade(r.value));
    }),
);
watch(activeResponseTab, () =>
    nextTick(() => {
        [responseMiddlewareList, responseHeadersList, responseCookiesList].forEach((r) =>
            updateScrollFade(r.value),
        );
    }),
);

// Re-evaluate fade when search query changes (filtered content changes size)
watch(
    [
        requestSearchQuery,
        filteredMiddlewareList,
        filteredRequestHeaders,
        filteredQueryParameters,
        filteredViewData,
    ],
    () =>
        nextTick(() => {
            [
                requestHeadersList,
                requestMiddlewareList,
                requestQueryParamsList,
                requestDataList,
            ].forEach((r) => updateScrollFade(r.value));
        }),
);
watch(
    [
        responseSearchQuery,
        filteredOutboundMiddlewareList,
        filteredResponseHeaders,
        filteredResponseCookies,
    ],
    () =>
        nextTick(() => {
            [responseMiddlewareList, responseHeadersList, responseCookiesList].forEach((r) =>
                updateScrollFade(r.value),
            );
        }),
);
</script>

<template>
    <div class="grid grid-cols-2 -mx-2 -my-2 divide-x divide-white/8 pb-2">
        <div class="flex flex-col gap-1 py-2 pl-2 pr-3">
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
                    <div class="flex items-center gap-2">
                        <InertiaIcon
                            v-if="request?.is_inertia"
                            class="h-2.5 w-auto"
                            title="Inertia request"
                        />
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 16 16"
                            fill="currentColor"
                            class="size-4"
                            title="Full page request"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M3.757 4.5c.18.217.376.42.586.608.153-.61.354-1.175.596-1.678A5.53 5.53 0 0 0 3.757 4.5ZM8 1a6.994 6.994 0 0 0-7 7 7 7 0 1 0 7-7Zm0 1.5c-.476 0-1.091.386-1.633 1.427-.293.564-.531 1.267-.683 2.063A5.48 5.48 0 0 0 8 6.5a5.48 5.48 0 0 0 2.316-.51c-.152-.796-.39-1.499-.683-2.063C9.09 2.886 8.476 2.5 8 2.5Zm3.657 2.608a8.823 8.823 0 0 0-.596-1.678c.444.298.842.659 1.182 1.07-.18.217-.376.42-.586.608Zm-1.166 2.436A6.983 6.983 0 0 1 8 8a6.983 6.983 0 0 1-2.49-.456 10.703 10.703 0 0 0 .202 2.6c.72.231 1.49.356 2.288.356.798 0 1.568-.125 2.29-.356a10.705 10.705 0 0 0 .2-2.6Zm1.433 1.85a12.652 12.652 0 0 0 .018-2.609c.405-.276.78-.594 1.117-.947a5.48 5.48 0 0 1 .44 2.262 7.536 7.536 0 0 1-1.575 1.293Zm-2.172 2.435a9.046 9.046 0 0 1-3.504 0c.039.084.078.166.12.244C6.907 13.114 7.523 13.5 8 13.5s1.091-.386 1.633-1.427c.04-.078.08-.16.12-.244Zm1.31.74a8.5 8.5 0 0 0 .492-1.298c.457-.197.893-.43 1.307-.696a5.526 5.526 0 0 1-1.8 1.995Zm-6.123 0a8.507 8.507 0 0 1-.493-1.298 8.985 8.985 0 0 1-1.307-.696 5.526 5.526 0 0 0 1.8 1.995ZM2.5 8.1c.463.5.993.935 1.575 1.293a12.652 12.652 0 0 1-.018-2.608 7.037 7.037 0 0 1-1.117-.947 5.48 5.48 0 0 0-.44 2.262Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <a
                            v-if="request?.uuid"
                            class="hover:underline"
                            :href="`/telescope/requests/${request?.uuid}`"
                        >
                            {{ request?.uuid?.slice(0, 6) }}...{{ request?.uuid?.slice(-6) }}
                        </a>
                    </div>
                </template>
            </SectionHeader>
            <div class="flex items-center justify-between px-1.5 mt-1">
                <div v-show="!isRequestSearching" class="flex gap-4">
                    <button
                        v-for="tab in requestTabs"
                        :key="tab"
                        @click="activeRequestTab = tab"
                        class="text-xxs uppercase tracking-wide pb-1 -mb-px transition-colors"
                        :class="
                            activeRequestTab === tab
                                ? 'text-white'
                                : 'text-white/40 hover:text-white/60'
                        "
                    >
                        {{ tab }}
                    </button>
                </div>
                <input
                    v-show="isRequestSearching"
                    ref="requestSearchInputRef"
                    v-model="requestSearchQuery"
                    type="text"
                    placeholder="Search..."
                    class="outline-none bg-transparent text-xs uppercase font-medium leading-[18px] pb-[3px] text-white placeholder:text-white/30 flex-1"
                />
                <button
                    @click="isRequestSearching ? stopRequestSearch() : startRequestSearch()"
                    class="text-white/40 hover:text-white/70 transition-colors shrink-0 pb-1 mr-[7px]"
                >
                    <svg
                        v-if="!isRequestSearching"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-3.5"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-3.5"
                    >
                        <path
                            d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"
                        />
                    </svg>
                </button>
            </div>
            <div
                class="overflow-hidden"
                :style="{ height: requestContentHeight > 0 ? requestContentHeight + 'px' : 'auto' }"
            >
                <div
                    v-if="activeRequestTab === 'summary'"
                    ref="requestSummaryRef"
                    class="flex flex-col gap-2"
                >
                    <Section>
                        <DataList>
                            <DataListItem
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(requestSearchQuery, 'Method', request?.method)
                                "
                            >
                                <template #label>Method</template>
                                <template #value>
                                    <span class="uppercase" :class="methodColor(request?.method)">{{
                                        request?.method
                                    }}</span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(requestSearchQuery, 'URI', request?.uri)
                                "
                            >
                                <template #label>URI</template>
                                <template #value>
                                    <span class="whitespace-nowrap" :title="request?.uri">{{
                                        request?.uri
                                    }}</span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(requestSearchQuery, 'Pattern', request?.route_uri)
                                "
                            >
                                <template #label>Pattern</template>
                                <template #value>
                                    <span class="whitespace-nowrap" :title="request?.route_uri">
                                        {{ request?.route_uri ?? "—" }}
                                    </span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(requestSearchQuery, 'Name', request?.route_name)
                                "
                            >
                                <template #label>Name</template>
                                <template #value>
                                    <span
                                        :class="
                                            request?.route_editor_url
                                                ? 'cursor-pointer hover:underline'
                                                : ''
                                        "
                                        :title="request?.route_editor_url"
                                        @click="openLink(request?.route_editor_url)"
                                    >
                                        {{ request?.route_name ?? "—" }}
                                    </span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(
                                        requestSearchQuery,
                                        'Action',
                                        request?.controller_action,
                                        controllerActionLabel(request?.controller_action),
                                    )
                                "
                            >
                                <template #label>Action</template>
                                <template #value>
                                    <span
                                        :class="
                                            request?.editor_url
                                                ? 'cursor-pointer hover:underline'
                                                : ''
                                        "
                                        :title="request?.controller_action"
                                        @click="openLink(request?.editor_url)"
                                    >
                                        {{ controllerActionLabel(request?.controller_action) }}
                                    </span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-if="request?.view_name"
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(
                                        requestSearchQuery,
                                        request?.is_inertia ? 'Page' : 'View',
                                        request?.view_name,
                                    )
                                "
                            >
                                <template #label>
                                    <span v-if="request?.is_inertia">Page</span>
                                    <span v-else>View</span>
                                </template>
                                <template #value>
                                    <span class="whitespace-nowrap" :title="request?.view_name">{{
                                        request?.view_name
                                    }}</span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(
                                        requestSearchQuery,
                                        'Type',
                                        request?.is_inertia ? 'Inertia' : 'Full page',
                                    )
                                "
                            >
                                <template #label>Type</template>
                                <template #value>
                                    <span v-if="request?.is_inertia" class="text-white/70"
                                        >Inertia</span
                                    >
                                    <span v-else class="text-white/70">Full page</span>
                                </template>
                            </DataListItem>
                        </DataList>
                    </Section>

                    <Section
                        v-if="uploadedFiles.length > 0"
                        v-show="
                            !isRequestSearching ||
                            uploadedFiles.some((file) =>
                                matchesSearch(
                                    requestSearchQuery,
                                    'Uploaded Files',
                                    file.field,
                                    uploadedFileSummary(file),
                                ),
                            )
                        "
                    >
                        <div class="px-1 text-white/50 uppercase">Uploaded Files</div>
                        <DataList>
                            <DataListItem
                                v-for="file in uploadedFiles"
                                :key="`${file.field}-${file.name}`"
                                v-show="
                                    !isRequestSearching ||
                                    matchesSearch(
                                        requestSearchQuery,
                                        'Uploaded Files',
                                        file.field,
                                        uploadedFileSummary(file),
                                    )
                                "
                            >
                                <template #label>{{ file.field }}</template>
                                <template #value>
                                    <span
                                        class="whitespace-nowrap"
                                        :title="uploadedFileSummary(file)"
                                    >
                                        {{ uploadedFileSummary(file) }}
                                    </span>
                                </template>
                            </DataListItem>
                        </DataList>
                    </Section>
                    <div
                        v-if="isRequestSearching && requestSearchQuery && !requestSummaryHasMatches"
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No results found
                    </div>
                </div>

                <div v-if="activeRequestTab === 'middleware'" class="flex flex-col gap-2">
                    <Section v-if="filteredMiddlewareList.length > 0">
                        <div
                            ref="requestMiddlewareList"
                            class="overflow-y-auto overflow-hidden"
                            :style="{ maxHeight: requestHeaderListMaxHeight }"
                        >
                            <DataList>
                                <DataListItem
                                    v-for="(middleware, index) in filteredMiddlewareList"
                                    :key="middleware.class"
                                >
                                    <template #label>
                                        <span class="tabular-nums">#{{ index + 1 }}</span>
                                    </template>
                                    <template #value>
                                        <a
                                            v-if="middleware.editor_url"
                                            :href="middleware.editor_url"
                                            :title="middleware.class"
                                            target="_blank"
                                            class="whitespace-nowrap hover:underline cursor-pointer"
                                        >
                                            {{ middlewareBasename(middleware.class) }}
                                        </a>
                                        <span
                                            v-else
                                            :title="middleware.class"
                                            class="whitespace-nowrap"
                                        >
                                            {{ middlewareBasename(middleware.class) }}
                                        </span>
                                    </template>
                                </DataListItem>
                            </DataList>
                        </div>
                    </Section>
                    <Section v-else-if="!isRequestSearching || !requestSearchQuery">
                        <div class="px-1 py-1 text-white/40 text-xs">No middleware</div>
                    </Section>
                    <div
                        v-if="
                            isRequestSearching &&
                            requestSearchQuery &&
                            filteredMiddlewareList.length === 0
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No middleware found
                    </div>
                </div>

                <div v-if="activeRequestTab === 'data'" class="flex flex-col gap-2">
                    <Section v-if="Object.keys(filteredViewData).length > 0">
                        <div
                            ref="requestDataList"
                            class="flex flex-col gap-3 overflow-y-auto overflow-hidden"
                            :style="{ maxHeight: requestHeaderListMaxHeight }"
                        >
                            <div
                                v-for="(value, key) in filteredViewData"
                                :key="`data-${key}`"
                                class="flex flex-col gap-0.5"
                            >
                                <div class="text-white/50 text-xxs uppercase tracking-wide px-1">
                                    {{ key }}
                                </div>
                                <pre
                                    class="text-xs font-mono text-white/80 whitespace-pre-wrap break-all px-1"
                                    >{{
                                        value !== null &&
                                        value !== undefined &&
                                        typeof value === "object"
                                            ? JSON.stringify(value, null, 2)
                                            : String(value ?? "—")
                                    }}</pre
                                >
                            </div>
                        </div>
                    </Section>
                    <div
                        v-if="
                            isRequestSearching &&
                            requestSearchQuery &&
                            Object.keys(filteredViewData).length === 0
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No data found
                    </div>
                </div>

                <div v-if="activeRequestTab === 'headers'" class="flex flex-col gap-2">
                    <Section v-if="filteredRequestHeaders.length > 0">
                        <div
                            ref="requestHeadersList"
                            :class="headerListClasses(filteredRequestHeaders.length)"
                            :style="{ maxHeight: requestHeaderListMaxHeight }"
                        >
                            <DataList>
                                <DataListItem
                                    v-for="header in filteredRequestHeaders"
                                    :key="`request-header-${header.label}`"
                                >
                                    <template #label>{{ header.label }}</template>
                                    <template #value>
                                        <span class="whitespace-nowrap" :title="header.value">{{
                                            header.value
                                        }}</span>
                                    </template>
                                </DataListItem>
                            </DataList>
                        </div>
                    </Section>
                    <div
                        v-if="
                            isRequestSearching &&
                            requestSearchQuery &&
                            filteredRequestHeaders.length === 0
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No headers found
                    </div>
                </div>

                <div v-if="activeRequestTab === 'query params'" class="flex flex-col gap-2">
                    <Section v-if="filteredQueryParameters.length > 0">
                        <div
                            ref="requestQueryParamsList"
                            :class="headerListClasses(filteredQueryParameters.length)"
                            :style="{ maxHeight: requestHeaderListMaxHeight }"
                        >
                            <DataList>
                                <DataListItem
                                    v-for="param in filteredQueryParameters"
                                    :key="`query-${param.label}`"
                                >
                                    <template #label>{{ param.label }}</template>
                                    <template #value>
                                        <span class="whitespace-nowrap" :title="param.value">{{
                                            param.value
                                        }}</span>
                                    </template>
                                </DataListItem>
                            </DataList>
                        </div>
                    </Section>
                    <div
                        v-if="
                            isRequestSearching &&
                            requestSearchQuery &&
                            filteredQueryParameters.length === 0
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No query params found
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-1 py-2 pl-3 pr-2">
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
                            d="M14 8a.75.75 0 0 1-.75.75H4.56l1.22 1.22a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1 0-1.06l2.5-2.5a.75.75 0 0 1 1.06 1.06L4.56 7.25h8.69A.75.75 0 0 1 14 8Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </template>
                <template #label>Response</template>
                <template #secondaryLabel>
                    <InertiaIcon
                        v-if="request?.is_inertia"
                        class="h-2.5 w-auto"
                        title="Inertia response"
                    />
                    <svg
                        v-else-if="redirectTarget"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-3"
                        title="Redirect response"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3.5 9.75A2.75 2.75 0 0 1 6.25 7h5.19L9.22 9.22a.75.75 0 1 0 1.06 1.06l3.5-3.5a.75.75 0 0 0 0-1.06l-3.5-3.5a.75.75 0 1 0-1.06 1.06l2.22 2.22H6.25a4.25 4.25 0 0 0 0 8.5h1a.75.75 0 0 0 0-1.5h-1A2.75 2.75 0 0 1 3.5 9.75Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        v-else-if="responseContentType?.includes('text/html')"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-4"
                        title="HTML response"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3.757 4.5c.18.217.376.42.586.608.153-.61.354-1.175.596-1.678A5.53 5.53 0 0 0 3.757 4.5ZM8 1a6.994 6.994 0 0 0-7 7 7 7 0 1 0 7-7Zm0 1.5c-.476 0-1.091.386-1.633 1.427-.293.564-.531 1.267-.683 2.063A5.48 5.48 0 0 0 8 6.5a5.48 5.48 0 0 0 2.316-.51c-.152-.796-.39-1.499-.683-2.063C9.09 2.886 8.476 2.5 8 2.5Zm3.657 2.608a8.823 8.823 0 0 0-.596-1.678c.444.298.842.659 1.182 1.07-.18.217-.376.42-.586.608Zm-1.166 2.436A6.983 6.983 0 0 1 8 8a6.983 6.983 0 0 1-2.49-.456 10.703 10.703 0 0 0 .202 2.6c.72.231 1.49.356 2.288.356.798 0 1.568-.125 2.29-.356a10.705 10.705 0 0 0 .2-2.6Zm1.433 1.85a12.652 12.652 0 0 0 .018-2.609c.405-.276.78-.594 1.117-.947a5.48 5.48 0 0 1 .44 2.262 7.536 7.536 0 0 1-1.575 1.293Zm-2.172 2.435a9.046 9.046 0 0 1-3.504 0c.039.084.078.166.12.244C6.907 13.114 7.523 13.5 8 13.5s1.091-.386 1.633-1.427c.04-.078.08-.16.12-.244Zm1.31.74a8.5 8.5 0 0 0 .492-1.298c.457-.197.893-.43 1.307-.696a5.526 5.526 0 0 1-1.8 1.995Zm-6.123 0a8.507 8.507 0 0 1-.493-1.298 8.985 8.985 0 0 1-1.307-.696 5.526 5.526 0 0 0 1.8 1.995ZM2.5 8.1c.463.5.993.935 1.575 1.293a12.652 12.652 0 0 1-.018-2.608 7.037 7.037 0 0 1-1.117-.947 5.48 5.48 0 0 0-.44 2.262Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        v-else-if="responseContentType?.includes('application/json')"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-3"
                        title="JSON response"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4.78 4.97a.75.75 0 0 1 0 1.06L2.81 8l1.97 1.97a.75.75 0 1 1-1.06 1.06l-2.5-2.5a.75.75 0 0 1 0-1.06l2.5-2.5a.75.75 0 0 1 1.06 0ZM11.22 4.97a.75.75 0 0 0 0 1.06L13.19 8l-1.97 1.97a.75.75 0 1 0 1.06 1.06l2.5-2.5a.75.75 0 0 0 0-1.06l-2.5-2.5a.75.75 0 0 0-1.06 0ZM8.856 2.008a.75.75 0 0 1 .636.848l-1.5 10.5a.75.75 0 0 1-1.484-.212l1.5-10.5a.75.75 0 0 1 .848-.636Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </template>
            </SectionHeader>
            <div class="flex items-center justify-between px-1.5 mt-1">
                <div v-show="!isResponseSearching" class="flex gap-4">
                    <button
                        v-for="tab in responseTabs"
                        :key="tab"
                        @click="activeResponseTab = tab"
                        class="text-xxs uppercase tracking-wide pb-1 -mb-px transition-colors"
                        :class="
                            activeResponseTab === tab
                                ? 'text-white'
                                : 'text-white/40 hover:text-white/60'
                        "
                    >
                        {{ tab }}
                    </button>
                </div>
                <input
                    v-show="isResponseSearching"
                    ref="responseSearchInputRef"
                    v-model="responseSearchQuery"
                    type="text"
                    placeholder="Search..."
                    class="outline-none bg-transparent text-xs uppercase font-medium leading-[18px] pb-[3px] text-white placeholder:text-white/30 flex-1"
                />
                <button
                    @click="isResponseSearching ? stopResponseSearch() : startResponseSearch()"
                    class="text-white/40 hover:text-white/70 transition-colors shrink-0 pb-1 mr-[7px]"
                >
                    <svg
                        v-if="!isResponseSearching"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-3.5"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        class="size-3.5"
                    >
                        <path
                            d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"
                        />
                    </svg>
                </button>
            </div>
            <div
                class="overflow-hidden"
                :style="{
                    height: responseContentHeight > 0 ? responseContentHeight + 'px' : 'auto',
                }"
            >
                <div
                    v-if="activeResponseTab === 'summary'"
                    ref="responseSummaryRef"
                    class="flex flex-col gap-2"
                >
                    <Section>
                        <DataList>
                            <DataListItem
                                v-show="
                                    !isResponseSearching ||
                                    matchesSearch(
                                        responseSearchQuery,
                                        'Status',
                                        response?.status_code,
                                        requestCodeMessage(response?.status_code),
                                    )
                                "
                            >
                                <template #label>Status</template>
                                <template #value>
                                    <span
                                        class="uppercase"
                                        :class="requestCodeColor(response?.status_code)"
                                    >
                                        {{ response?.status_code }}
                                        {{ requestCodeMessage(response?.status_code) }}
                                    </span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isResponseSearching ||
                                    matchesSearch(
                                        responseSearchQuery,
                                        'Duration',
                                        data.profiler?.total_wall_time?.formattedValue,
                                    )
                                "
                            >
                                <template #label>Duration</template>
                                <template #value>{{
                                    data.profiler?.total_wall_time?.formattedValue ?? "—"
                                }}</template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isResponseSearching ||
                                    matchesSearch(
                                        responseSearchQuery,
                                        'Content Type',
                                        responseContentType,
                                    )
                                "
                            >
                                <template #label>Content Type</template>
                                <template #value>
                                    <span class="whitespace-nowrap" :title="responseContentType">{{
                                        responseContentType
                                    }}</span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isResponseSearching ||
                                    matchesSearch(responseSearchQuery, 'Size', responseSize)
                                "
                            >
                                <template #label>Size</template>
                                <template #value>{{ responseSize }}</template>
                            </DataListItem>
                            <DataListItem
                                v-if="redirectTarget"
                                v-show="
                                    !isResponseSearching ||
                                    matchesSearch(responseSearchQuery, 'Redirect', redirectTarget)
                                "
                            >
                                <template #label>Redirect</template>
                                <template #value>
                                    <span class="whitespace-nowrap" :title="redirectTarget">{{
                                        redirectTarget
                                    }}</span>
                                </template>
                            </DataListItem>
                            <DataListItem
                                v-show="
                                    !isResponseSearching ||
                                    matchesSearch(
                                        responseSearchQuery,
                                        'Type',
                                        request?.is_inertia
                                            ? 'Inertia'
                                            : responseContentType?.includes('text/html')
                                              ? 'HTML'
                                              : responseContentType?.includes('application/json')
                                                ? 'JSON'
                                                : responseContentType,
                                    )
                                "
                            >
                                <template #label>Type</template>
                                <template #value>
                                    <span v-if="request?.is_inertia" class="text-white/70"
                                        >Inertia</span
                                    >
                                    <span
                                        v-else-if="responseContentType?.includes('text/html')"
                                        class="text-white/70"
                                        >HTML</span
                                    >
                                    <span
                                        v-else-if="
                                            responseContentType?.includes('application/json')
                                        "
                                        class="text-white/70"
                                        >JSON</span
                                    >
                                    <span v-else class="text-white/70">{{
                                        responseContentType
                                    }}</span>
                                </template>
                            </DataListItem>
                        </DataList>
                    </Section>
                    <div
                        v-if="
                            isResponseSearching && responseSearchQuery && !responseSummaryHasMatches
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No results found
                    </div>
                </div>

                <div v-if="activeResponseTab === 'middleware'" class="flex flex-col gap-2">
                    <Section v-if="filteredOutboundMiddlewareList.length > 0">
                        <div
                            ref="responseMiddlewareList"
                            class="overflow-y-auto overflow-hidden"
                            :style="{ maxHeight: responseHeaderListMaxHeight }"
                        >
                            <DataList>
                                <DataListItem
                                    v-for="(middleware, index) in filteredOutboundMiddlewareList"
                                    :key="middleware.class"
                                >
                                    <template #label>
                                        <span class="tabular-nums">#{{ index + 1 }}</span>
                                    </template>
                                    <template #value>
                                        <a
                                            v-if="middleware.editor_url"
                                            :href="middleware.editor_url"
                                            :title="middleware.class"
                                            target="_blank"
                                            class="whitespace-nowrap hover:underline cursor-pointer"
                                        >
                                            {{ middlewareBasename(middleware.class) }}
                                        </a>
                                        <span
                                            v-else
                                            :title="middleware.class"
                                            class="whitespace-nowrap"
                                        >
                                            {{ middlewareBasename(middleware.class) }}
                                        </span>
                                    </template>
                                </DataListItem>
                            </DataList>
                        </div>
                    </Section>
                    <Section v-else-if="!isResponseSearching || !responseSearchQuery">
                        <div class="px-1 py-1 text-white/40 text-xs">No outbound middleware</div>
                    </Section>
                    <div
                        v-if="
                            isResponseSearching &&
                            responseSearchQuery &&
                            filteredOutboundMiddlewareList.length === 0
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No middleware found
                    </div>
                </div>

                <div v-if="activeResponseTab === 'cookies'" class="flex flex-col gap-2">
                    <Section v-if="filteredResponseCookies.length > 0">
                        <div
                            ref="responseCookiesList"
                            :class="headerListClasses(filteredResponseCookies.length)"
                            :style="{ maxHeight: responseHeaderListMaxHeight }"
                        >
                            <DataList>
                                <DataListItem
                                    v-for="cookie in filteredResponseCookies"
                                    :key="`response-cookie-${cookie.name}`"
                                >
                                    <template #label>{{ cookie.name }}</template>
                                    <template #value>
                                        <span
                                            class="whitespace-nowrap"
                                            :title="cookieSummary(cookie)"
                                        >
                                            {{ cookieSummary(cookie) }}
                                        </span>
                                    </template>
                                </DataListItem>
                            </DataList>
                        </div>
                    </Section>
                    <div
                        v-if="
                            isResponseSearching &&
                            responseSearchQuery &&
                            filteredResponseCookies.length === 0
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No cookies found
                    </div>
                </div>

                <div v-if="activeResponseTab === 'headers'" class="flex flex-col gap-2">
                    <Section v-if="filteredResponseHeaders.length > 0">
                        <div
                            ref="responseHeadersList"
                            :class="headerListClasses(filteredResponseHeaders.length)"
                            :style="{ maxHeight: responseHeaderListMaxHeight }"
                        >
                            <DataList>
                                <DataListItem
                                    v-for="header in filteredResponseHeaders"
                                    :key="`response-header-${header.label}`"
                                >
                                    <template #label>{{ header.label }}</template>
                                    <template #value>
                                        <span class="whitespace-nowrap" :title="header.value">{{
                                            header.value
                                        }}</span>
                                    </template>
                                </DataListItem>
                            </DataList>
                        </div>
                    </Section>
                    <div
                        v-if="
                            isResponseSearching &&
                            responseSearchQuery &&
                            filteredResponseHeaders.length === 0
                        "
                        class="px-1.5 py-2 text-xxs text-white/30"
                    >
                        No headers found
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
