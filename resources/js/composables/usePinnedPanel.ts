import { computed, ref } from "vue";

const STORAGE_KEY = "toolbar-pinned-tool";
const HOVER_IN_DELAY = 100;
const HOVER_OUT_DELAY = 200;

// Module-level shared state — all tool instances share these refs.
const pinnedTool = ref<string | null>(localStorage.getItem(STORAGE_KEY));
const hoveredTool = ref<string | null>(null);
let hoverInTimer: ReturnType<typeof setTimeout> | null = null;
let hoverOutTimer: ReturnType<typeof setTimeout> | null = null;

export function usePinnedPanel(toolId: string) {
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
        } else {
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
