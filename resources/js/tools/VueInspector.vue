<script setup>
import { ref } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import TargetIcon from '@/icons/TargetIcon.vue';

const props = defineProps({
    config: {
        type: Object,
        required: false,
    },
    itemClasses: {
        type: Object,
        required: false,
        default: () => ({}),
    },
});

const isOpen = ref(false);
const isEnabled = ref(false);

const toggleVueInspector = () => {
    if (!window.__VUE_INSPECTOR__.enabled) {
        window.__VUE_INSPECTOR__.enable();
        isEnabled.value = true;

        // Re-apply styles after inspector enable potentially resets them
        const frame = document.querySelector('.vue-devtools-frame');
        if (frame) {
            frame.setAttribute(
                'style',
                'width: 100% !important; height: 100% !important; z-index: 99999 !important;',
            );
        }
    } else {
        window.__VUE_INSPECTOR__.disable();
        isEnabled.value = false;
    }
};
</script>

<style>
.vue-devtools-inspector-overlay {
    z-index: 999998 !important; /* Just below toolbar */
}
</style>

<template>
    <div>
        <ToolbarItem
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            :isActive="isOpen"
            :class="itemClasses"
            @click="toggleVueInspector()"
        >
            <div class="py-[3px]">
                <TargetIcon class="size-4" :class="isEnabled ? 'text-green-500' : 'text-white'" />
            </div>
        </ToolbarItem>
    </div>
</template>
