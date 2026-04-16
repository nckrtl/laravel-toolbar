<script setup>
import { usePinnedPanel } from "@/composables/usePinnedPanel";
import ToolbarItem from "@/components/ToolbarItem.vue";
import { Square3Stack3DIcon } from "@heroicons/vue/16/solid";

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
} = usePinnedPanel("techstack", {
    size: "xxs",
    align: "right",
    index: props.toolIndex,
    config: props.config ?? null,
});
</script>

<template>
    <div class="flex justify-end" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <ToolbarItem :isActive="isOpen" :class="itemClasses" @click="togglePin">
            <div class="py-[3px]">
                <Square3Stack3DIcon class="size-4" />
            </div>
        </ToolbarItem>
    </div>
</template>
