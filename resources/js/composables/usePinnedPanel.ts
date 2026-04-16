import { computed, ref } from "vue";

const STORAGE_KEY = "toolbar-pinned-tool";
const HOVER_IN_DELAY = 75;
const HOVER_OUT_DELAY = 100;

const EPHEMERAL_KEYS = ["toolbar_request_tab", "toolbar_response_tab"];

interface ToolRegistryEntry {
    size: string;
    align: string;
    index: number;
    config: object | null;
}

// Module-level shared state — all tool instances share these refs.
const pinnedTool = ref<string | null>(localStorage.getItem(STORAGE_KEY));

if (pinnedTool.value === null) {
    EPHEMERAL_KEYS.forEach((key) => localStorage.removeItem(key));
}
const hoveredTool = ref<string | null>(null);
const previousActiveTool = ref<string | null>(null);
let hoverInTimer: ReturnType<typeof setTimeout> | null = null;
let hoverOutTimer: ReturnType<typeof setTimeout> | null = null;

const toolRegistry = new Map<string, ToolRegistryEntry>();

// Exported reactive state for SharedPanel
export const activeToolId = computed(() => hoveredTool.value ?? pinnedTool.value);

export const transitionDirection = computed<"left" | "right" | null>(() => {
    const prev = previousActiveTool.value;
    const curr = activeToolId.value;
    if (!prev || !curr || prev === curr) return null;
    const prevIndex = toolRegistry.get(prev)?.index ?? -1;
    const currIndex = toolRegistry.get(curr)?.index ?? -1;
    if (prevIndex < 0 || currIndex < 0) return null;
    return currIndex > prevIndex ? "right" : "left";
});

export const activePanelConfig = computed(() => {
    const id = activeToolId.value;
    if (!id) return null;
    return toolRegistry.get(id) ?? null;
});

export const activeToolConfig = computed(() => {
    const id = activeToolId.value;
    if (!id) return null;
    return toolRegistry.get(id)?.config ?? null;
});

// SharedPanel hover handlers — keeps the panel open while hovering over it
export function onSharedPanelMouseEnter() {
    if (hoverOutTimer !== null) {
        clearTimeout(hoverOutTimer);
        hoverOutTimer = null;
    }
    if (hoverInTimer !== null) {
        clearTimeout(hoverInTimer);
    }
    const id = hoveredTool.value ?? pinnedTool.value;
    if (!id) return;
    hoverInTimer = setTimeout(() => {
        hoveredTool.value = id;
        hoverInTimer = null;
    }, HOVER_IN_DELAY);
}

export function onSharedPanelMouseLeave() {
    if (hoverInTimer !== null) {
        clearTimeout(hoverInTimer);
        hoverInTimer = null;
    }
    if (hoverOutTimer !== null) {
        clearTimeout(hoverOutTimer);
    }
    const id = hoveredTool.value;
    if (!id) return;
    hoverOutTimer = setTimeout(() => {
        if (hoveredTool.value === id) {
            hoveredTool.value = null;
        }
        hoverOutTimer = null;
    }, HOVER_OUT_DELAY);
}

export function usePinnedPanel(
    toolId: string,
    options?: {
        size?: string;
        align?: string;
        index?: number;
        config?: object | null;
    },
) {
    if (options !== undefined) {
        toolRegistry.set(toolId, {
            size: options.size ?? "sm",
            align: options.align ?? "left",
            index: options.index ?? 0,
            config: options.config ?? null,
        });
    }

    const isPinned = computed(() => pinnedTool.value === toolId);

    // Visible when:
    // - This tool is currently hovered (and debounce has passed), OR
    // - This tool is pinned and no other tool is being hovered
    const isVisible = computed(
        () => hoveredTool.value === toolId || (isPinned.value && hoveredTool.value === null),
    );

    const togglePin = () => {
        if (isPinned.value) {
            pinnedTool.value = null;
            localStorage.removeItem(STORAGE_KEY);
            if (hoverInTimer !== null) {
                clearTimeout(hoverInTimer);
                hoverInTimer = null;
            }
            if (hoverOutTimer !== null) {
                clearTimeout(hoverOutTimer);
                hoverOutTimer = null;
            }
            hoveredTool.value = null;
        } else {
            if (pinnedTool.value !== null && pinnedTool.value !== toolId) {
                previousActiveTool.value = pinnedTool.value;
            }
            pinnedTool.value = toolId;
            localStorage.setItem(STORAGE_KEY, toolId);
        }
    };

    const onMouseEnter = () => {
        if (hoverOutTimer !== null) {
            clearTimeout(hoverOutTimer);
            hoverOutTimer = null;
        }
        if (hoverInTimer !== null) {
            clearTimeout(hoverInTimer);
        }
        hoverInTimer = setTimeout(() => {
            if (hoveredTool.value !== null && hoveredTool.value !== toolId) {
                previousActiveTool.value = hoveredTool.value;
            }
            hoveredTool.value = toolId;
            hoverInTimer = null;
        }, HOVER_IN_DELAY);
    };

    const onMouseLeave = () => {
        if (hoverInTimer !== null) {
            clearTimeout(hoverInTimer);
            hoverInTimer = null;
        }
        if (hoverOutTimer !== null) {
            clearTimeout(hoverOutTimer);
        }
        hoverOutTimer = setTimeout(() => {
            if (hoveredTool.value === toolId) {
                hoveredTool.value = null;
            }
            hoverOutTimer = null;
        }, HOVER_OUT_DELAY);
    };

    return { isPinned, isVisible, togglePin, onMouseEnter, onMouseLeave };
}
