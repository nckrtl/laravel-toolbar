<script setup></script>

<template>
    <div class="flex w-full items-end justify-between px-1 py-0.5">
        <div class="flex items-center gap-2 pr-2 text-white/70 uppercase">
            <slot name="label" />
        </div>
        <div class="mb-1 min-w-6 grow border-b-2 border-dotted border-white/20 px-2"></div>
        <div class="overflow-hidden pl-2 text-ellipsis">
            <slot name="value" />
        </div>
    </div>
</template>
