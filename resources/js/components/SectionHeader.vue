<script setup>
const props = defineProps({
    iconClass: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="flex w-full items-center justify-between">
        <div class="flex items-center gap-3 p-1.5">
            <div
                class="relative flex h-7 min-w-7 items-center justify-center rounded-md border border-white/8 bg-white/3 shadow-lg shadow-black/20"
                :class="iconClass"
            >
                <slot name="icon" />
            </div>
            <slot name="label" />
        </div>
        <div class="px-3 opacity-50">
            <slot name="secondaryLabel" />
        </div>
    </div>
</template>
