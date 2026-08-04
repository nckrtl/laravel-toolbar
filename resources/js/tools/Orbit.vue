<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import { usePinnedPanel } from '@/composables/usePinnedPanel';
import { useOrbitProcesses } from '@/composables/useOrbitProcesses';

const props = defineProps({
    config: { type: Object, required: false },
    itemClasses: { type: Object, required: false },
    toolIndex: { type: Number, required: false, default: 0 },
});

const {
    isVisible: isOpen,
    togglePin,
    onMouseEnter,
    onMouseLeave,
} = usePinnedPanel('orbit', {
    size: 'sm',
    align: 'right',
    index: props.toolIndex,
    config: props.config ?? null,
});

const { runningCount, totalCount, error, subscribe } = useOrbitProcesses();

let unsubscribe = null;

onMounted(() => {
    unsubscribe = subscribe(props.config?.gateway_url);
});

onUnmounted(() => {
    unsubscribe?.();
    unsubscribe = null;
});

const healthDotClass = computed(() => {
    if (error.value || totalCount.value === 0) {
        return 'bg-white/35';
    }

    if (runningCount.value === totalCount.value) {
        return 'bg-emerald-400';
    }

    return 'bg-orange-400';
});

const healthAriaLabel = computed(() => {
    if (error.value) {
        return 'Orbit process health unavailable';
    }

    if (totalCount.value === 0) {
        return 'No Orbit process state';
    }

    if (runningCount.value === totalCount.value) {
        return 'All Orbit processes running';
    }

    return 'Some Orbit processes not running';
});
</script>

<template>
    <div @mouseenter="onMouseEnter" @mouseleave="onMouseLeave">
        <ToolbarItem @click="togglePin" :isActive="isOpen" :class="itemClasses">
            <div class="relative py-0.5">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 100 100"
                    fill="none"
                    class="size-4"
                    aria-hidden="true"
                >
                    <path
                        d="M50 25C77.6143 25 100 36.1929 100 50C99.9996 63.8069 77.614 75 50 75C22.386 75 0.000366987 63.8069 0 50C0 36.1929 22.3858 25 50 25ZM49.7764 32.0107C32.7857 32.0108 15.7344 38.9923 15.7344 46.9102C15.7346 54.8279 28.3485 61.2461 49.5654 61.2461C70.7823 61.2461 83.3962 54.8279 83.3965 46.9102C83.3965 38.9923 66.7672 32.0107 49.7764 32.0107Z"
                        fill="currentColor"
                    />
                </svg>
                <span
                    class="absolute -top-0.5 -right-0.5 size-1.5 rounded-full"
                    :class="healthDotClass"
                    role="img"
                    :aria-label="healthAriaLabel"
                />
            </div>
        </ToolbarItem>
    </div>
</template>
