<script setup>
import ToolbarItem from "@/components/ToolbarItem.vue";
import VueIcon from "@/icons/VueIcon.vue";
import { usePinnedPanel } from "@/composables/usePinnedPanel";

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
} = usePinnedPanel("vue", {
    size: "xxs",
    align: "center",
    index: props.toolIndex,
});
</script>

<template>
    <div @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <ToolbarItem :isActive="isOpen" :class="itemClasses" @click="togglePin">
            <div class="py-[3px]">
                <VueIcon />
            </div>
        </ToolbarItem>
    </div>
</template>
