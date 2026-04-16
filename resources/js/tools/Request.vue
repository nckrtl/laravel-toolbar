<script setup>
import { computed, ref, watch, nextTick, onMounted } from "vue";
import DataListItem from "@/components/DataListItem.vue";
import Panel from "@/components/Panel.vue";
import Pill from "@/components/Pill.vue";
import Section from "@/components/Section.vue";
import SectionHeader from "@/components/SectionHeader.vue";
import ToolbarItem from "@/components/ToolbarItem.vue";
import InertiaIcon from "@/icons/InertiaIcon.vue";
import { useToolbar } from "@/composables/useToolbar";
import { usePinnedPanel } from "@/composables/usePinnedPanel";

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

const {
    isVisible: isOpen,
    togglePin,
    onMouseEnter,
    onMouseLeave,
} = usePinnedPanel("request", {
    size: "xl",
});
const activeRequestTab = ref("summary");
const activeResponseTab = ref("summary");
const requestTabs = ["summary", "headers"];
const responseTabs = ["summary", "headers", "cookies"];

const requestSummaryRef = ref(null);
const responseSummaryRef = ref(null);
const requestContentHeight = ref(0);
const responseContentHeight = ref(0);

const responseSectionHeight = computed(() => {
    return responseContentHeight.value > 0 ? `${responseContentHeight.value}px` : "auto";
});

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
    if (newTab === "summary") {
        measureRequestHeight();
    } else if (oldTab === "summary" && requestSummaryRef.value) {
        // Watcher fires pre-DOM-update — summary is still mounted, capture now
        requestContentHeight.value = requestSummaryRef.value.offsetHeight;
    }
});

watch(activeResponseTab, (newTab, oldTab) => {
    if (newTab === "summary") {
        measureResponseHeight();
    } else if (oldTab === "summary" && responseSummaryRef.value) {
        responseContentHeight.value = responseSummaryRef.value.offsetHeight;
    }
});

const request = computed(() => data.value.request);
const response = computed(() => data.value.response);

watch([request, response], () => {
    if (activeRequestTab.value === "summary") {
        measureRequestHeight();
    }
    if (activeResponseTab.value === "summary") {
        measureResponseHeight();
    }
});

onMounted(() => {
    // DOM is already rendered at mount — measure synchronously, no nextTick needed
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
    if (typeof code !== "number") {
        return "text-danger";
    }

    if (code >= 200 && code < 300) {
        return "text-emerald-300";
    }

    if (code >= 300 && code < 400) {
        return "text-yellow-500";
    }

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
    if (!controllerAction) {
        return "—";
    }

    const parts = controllerAction.split("\\");

    return parts[parts.length - 1] ?? controllerAction;
};

const formatValue = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }

    if (Array.isArray(value)) {
        return value.map((item) => formatValue(item)).join(", ");
    }

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
    if (!values || typeof values !== "object") {
        return [];
    }

    return Object.entries(values).map(([label, value]) => ({
        label,
        value: formatValue(value),
    }));
};

const normalizeHeaderEntries = (headers) => {
    if (!headers || typeof headers !== "object") {
        return [];
    }

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
        parts.push("Secure");
    }

    if (cookie?.http_only) {
        parts.push("HttpOnly");
    }

    return parts.join(" • ") || "—";
};

const uploadedFileSummary = (file) => {
    const parts = [];

    if (file?.name) {
        parts.push(file.name);
    }

    if (file?.size) {
        parts.push(file.size);
    }

    if (file?.mime_type) {
        parts.push(file.mime_type);
    }

    return parts.join(" • ") || "—";
};

const openLink = (url) => {
    if (url) {
        window.open(url, "_blank");
    }
};

const requestHeadersList = ref(null);
const responseHeadersList = ref(null);
const fadeClasses = ["fade-to-bottom", "fade-to-top", "fade-to-top-and-bottom"];

// Section overhead: mt-1(4) + border(2) + p-2 vertical(16) = ~22px
const requestHeaderListMaxHeight = computed(() => {
    const ch = requestContentHeight.value;
    return ch > 0 ? `${ch - 22}px` : "260px";
});

const responseHeaderListMaxHeight = computed(() => {
    const ch = responseContentHeight.value;
    return ch > 0 ? `${ch - 22}px` : "260px";
});

const headerListClasses = (count) =>
    count > 0 ? "flex flex-col gap-2 overflow-y-auto overflow-hidden" : "flex flex-col gap-2";

const updateScrollFade = (el) => {
    if (!el) {
        return;
    }

    const isScrollable = el.scrollHeight > el.clientHeight + 1;
    const scrollTop = el.scrollTop;
    const atBottom = scrollTop + el.clientHeight >= el.scrollHeight - 1;

    el.classList.remove(...fadeClasses);

    if (!isScrollable) {
        return;
    }

    if (atBottom) {
        el.classList.add("fade-to-top");
    } else if (scrollTop > 1) {
        el.classList.add("fade-to-top-and-bottom");
    } else {
        el.classList.add("fade-to-bottom");
    }
};

const attachScrollFade = (el) => {
    if (!el) {
        return;
    }

    el.addEventListener("scroll", () => updateScrollFade(el));
    nextTick(() => updateScrollFade(el));
};

watch(requestHeadersList, attachScrollFade);
watch(responseHeadersList, attachScrollFade);
</script>

<template>
    <div>
        <ToolbarItem
            @mouseenter="onMouseEnter"
            @mouseleave="onMouseLeave"
            @click="togglePin"
            :isActive="isOpen"
            :class="itemClasses"
            innerPadding="pl-0.5"
        >
            <Pill class="px-1.5 py-[5px]" color="green">{{ response?.status_code ?? "--" }}</Pill>
        </ToolbarItem>
    </div>
</template>
