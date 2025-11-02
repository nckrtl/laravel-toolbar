<script setup>
const props = defineProps({
    isActive: {
        type: Boolean,
        default: false,
    },
    class: {
        type: String,
        default: 'px-2',
    },
    innerPadding: {
        type: String,
        default: 'px-2',
    },
});
</script>

<template>
    <div class="text-xxs relative border-0! py-[3px] text-white" :class="[props.class]">
        <div
            class="relative cursor-default rounded-full p-px"
            :class="{ 'bg-linear-to-tr from-white/25 via-white/15 to-white/25': isActive }"
        >
            <div
                class="flex items-center gap-2 rounded-full p-0.5"
                :class="[innerPadding, { 'bg-[#101010]/30 backdrop-blur-sm': isActive }]"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
