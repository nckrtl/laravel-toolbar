<script setup>
import { computed } from "vue";
import { useToolbar } from "@/composables/useToolbar";

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
    padSurfaceVertically: {
        type: Boolean,
        default: true,
    },
});

const { data } = useToolbar();
const primaryColor = computed(() => data.value.primary_color ?? null);

const activeStyle = computed(() => {
    if (!props.isActive) {
        return {};
    }

    if (primaryColor.value) {
        return {
            backgroundColor: `color-mix(in srgb, ${primaryColor.value} 20%, transparent)`,
        };
    }

    return {
        backgroundImage:
            "linear-gradient(to top right, rgba(255,255,255,0.25), rgba(255,255,255,0.15), rgba(255,255,255,0.25))",
    };
});

const activeInnerStyle = computed(() => {
    if (!props.isActive || !primaryColor.value) {
        return {};
    }

    return {
        backgroundColor: primaryColor.value,
        color: "#000000",
    };
});
</script>

<template>
    <div
        class="text-xxs relative border-0! text-white"
        :class="[props.class, padSurfaceVertically ? 'py-0.5' : 'py-[3px]']"
    >
        <div class="relative cursor-default rounded-full p-[3px]" :style="activeStyle">
            <div
                class="relative flex items-center gap-2 rounded-full"
                :class="[
                    innerPadding,
                    {
                        'py-px': padSurfaceVertically,
                        'bg-[#101010]/30 backdrop-blur-sm': isActive && !primaryColor,
                    },
                ]"
                :style="activeInnerStyle"
            >
                <slot />
                <div
                    v-if="isActive && primaryColor"
                    class="active-top-border pointer-events-none absolute inset-0 rounded-full border-t border-white/20"
                ></div>
            </div>
        </div>
    </div>
</template>
