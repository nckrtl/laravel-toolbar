<script setup>
import ToolbarItem from "@/components/ToolbarItem.vue";
import { useToolbar } from "@/composables/useToolbar";
import { usePinnedPanel } from "@/composables/usePinnedPanel";

const props = defineProps({
    config: { type: Object, required: false },
    itemClasses: { type: Object, required: false },
    toolIndex: { type: Number, required: false, default: 0 },
});

const { data } = useToolbar();

const {
    isVisible: isOpen,
    togglePin,
    onMouseEnter,
    onMouseLeave,
} = usePinnedPanel("memory", {
    size: "md",
    align: "center",
    index: props.toolIndex,
});
</script>

<template>
    <div @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <ToolbarItem :isActive="isOpen" :class="itemClasses" @click="togglePin">
            <div class="flex items-center gap-1 py-0.5">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    class="size-4"
                >
                    <path d="M6 6v4h4V6H6Z" />
                    <path
                        fill-rule="evenodd"
                        d="M5.75 1a.75.75 0 0 0-.75.75V3a2 2 0 0 0-2 2H1.75a.75.75 0 0 0 0 1.5H3v.75H1.75a.75.75 0 0 0 0 1.5H3v.75H1.75a.75.75 0 0 0 0 1.5H3a2 2 0 0 0 2 2v1.25a.75.75 0 0 0 1.5 0V13h.75v1.25a.75.75 0 0 0 1.5 0V13h.75v1.25a.75.75 0 0 0 1.5 0V13a2 2 0 0 0 2-2h1.25a.75.75 0 0 0 0-1.5H13v-.75h1.25a.75.75 0 0 0 0-1.5H13V6.5h1.25a.75.75 0 0 0 0-1.5H13a2 2 0 0 0-2-2V1.75a.75.75 0 0 0-1.5 0V3h-.75V1.75a.75.75 0 0 0-1.5 0V3H6.5V1.75A.75.75 0 0 0 5.75 1ZM11 4.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5h6Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span>{{ data.profiler?.total_allocated_memory?.formattedValue }}</span>
            </div>
        </ToolbarItem>
    </div>
</template>
