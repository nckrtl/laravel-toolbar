<script setup>
import { usePinnedPanel } from "@/composables/usePinnedPanel";
import ToolbarItem from "@/components/ToolbarItem.vue";
import LaravelIcon from "@/icons/LaravelIcon.vue";

const props = defineProps({
    config: { type: Object, required: false },
    itemClasses: { type: Object, required: false, default: () => ({}) },
    toolIndex: { type: Number, required: false, default: 0 },
});

const {
    isVisible: isOpen,
    togglePin,
    onMouseEnter,
    onMouseLeave,
} = usePinnedPanel("laravel", {
    size: "xxs",
    align: "right",
    index: props.toolIndex,
});
</script>

<template>
    <div class="flex justify-end" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <ToolbarItem :isActive="isOpen" :class="itemClasses" @click="togglePin">
            <div class="py-[3px]">
                <LaravelIcon />
            </div>
        </ToolbarItem>
    </div>
</template>
