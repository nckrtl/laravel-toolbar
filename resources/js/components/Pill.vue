<script setup>
import { computed } from 'vue';

const props = defineProps({
    color: {
        type: String,
        default: 'green',
    },
});

const colors = computed(() => {
    return {
        green: 'bg-emerald-900 text-emerald-200',
        red: 'bg-red-900 text-red-200',
        yellow: 'bg-yellow-900 text-yellow-200',
    };
});
</script>

<template>
    <div
        class="text-shadow relative rounded-full px-2 py-0.5 font-bold"
        :class="colors[color]"
        :style="{ textShadow: '0 0 4px rgba(255, 255, 255, 0.5)' }"
    >
        <div
            class="absolute top-0 right-0 h-full w-full rounded-full border-t border-white/10"
        ></div>
        <slot />
    </div>
</template>
