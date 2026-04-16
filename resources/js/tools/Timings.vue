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
} = usePinnedPanel("timings", {
    size: "md",
    align: "center",
    index: props.toolIndex,
});
</script>

<template>
    <div @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <ToolbarItem @click="togglePin" :isActive="isOpen" :class="itemClasses">
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
