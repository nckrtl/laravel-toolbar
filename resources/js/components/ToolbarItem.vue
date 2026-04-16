<script setup>
const props = defineProps({
    isActive: {
        type: Boolean,
        default: false,
    },
    class: {
        type: String,
        default: "px-2",
    },
    innerPadding: {
        type: String,
        default: "px-2",
    },
});
</script>

<template>
    <div class="text-xxs relative border-0! py-[3px] text-white" :class="[props.class]">
        <div
            class="relative cursor-default rounded-full p-px"
            :style="
                isActive
                    ? {
                          backgroundImage:
                              'linear-gradient(to top right, rgba(255,255,255,0.25), rgba(255,255,255,0.15), rgba(255,255,255,0.25))',
                      }
                    : {}
            "
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
