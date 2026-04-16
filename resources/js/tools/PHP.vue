<script setup>
import { usePinnedPanel } from "@/composables/usePinnedPanel";
import ToolbarItem from "@/components/ToolbarItem.vue";
import PhpIcon from "@/icons/PhpIcon.vue";

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
} = usePinnedPanel("php", {
    size: "xxs",
    align: "right",
    index: props.toolIndex,
});
</script>

<template>
    <div class="flex justify-end" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <ToolbarItem
            :isActive="isOpen"
            class="rounded-r-full !border-l-0 pr-0.5"
            @click="togglePin"
        >
            <div class="py-[3px]">
                <PhpIcon />
            </div>
        </ToolbarItem>
    </div>
</template>
