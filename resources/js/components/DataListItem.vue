<script setup>
defineProps({
    align: { type: String, default: "end" },
});
</script>

<template>
    <div
        class="flex w-full justify-between px-1 py-0.5"
        :class="align === 'start' ? 'items-start' : 'items-end'"
    >
        <div class="flex items-center gap-2 pr-2 text-white/70 whitespace-nowrap uppercase">
            <slot name="label" />
        </div>
        <div class="mb-1 min-w-6 grow border-b-2 border-dotted border-white/20 px-2"></div>
        <div class="overflow-hidden pl-2 text-ellipsis">
            <slot name="value" />
        </div>
    </div>
</template>
