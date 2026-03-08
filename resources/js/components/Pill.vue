<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: {
        type: String,
        default: 'green',
    },
    size: {
        type: String,
        default: 'default',
    },
});

const colors = computed(() => {
    return {
        blue: 'bg-blue-900 text-blue-200',
        green: 'bg-emerald-900 text-emerald-200',
        red: 'bg-red-900 text-red-200',
        slate: 'bg-white/10 text-white/60',
        yellow: 'bg-yellow-900 text-yellow-200',
    };
});

const sizes = computed(() => {
    return {
        compact: 'text-xxs px-2 py-0.5 font-medium',
        default: 'px-2 py-1 font-bold',
    };
});
</script>

<template>
    <span
        class="text-shadow relative inline-flex items-center rounded-full leading-none whitespace-nowrap"
        :class="[colors[color] ?? colors.slate, sizes[size] ?? sizes.default]"
        :style="{ textShadow: '0 0 4px rgba(255, 255, 255, 0.5)' }"
    >
        <div
            class="absolute top-0 right-0 h-full w-full rounded-full border-t border-white/10"
        ></div>
        <slot />
    </span>
</template>
