<script setup>
import { computed } from "vue";
import {
    activeToolId,
    activePanelConfig,
    activeToolConfig,
    onSharedPanelMouseEnter,
    onSharedPanelMouseLeave,
} from "@/composables/usePinnedPanel";
import { useToolbar } from "@/composables/useToolbar";

const { data } = useToolbar();
const panels = import.meta.glob("../tools/panels/*.vue", { eager: true });

const activeContent = computed(() => {
    const id = activeToolId.value;
    if (!id) return null;
    return panels[`../tools/panels/${id}.vue`]?.default ?? null;
});

const isVisible = computed(() => activeToolId.value !== null && activeContent.value !== null);

const config = computed(() => activeToolConfig.value);

const animationsEnabled = computed(() => data.value.animations !== false);

// Width constraints — all sizes expand/contract symmetrically from center
const sizeClass = computed(() => {
    const size = activePanelConfig.value?.size ?? "sm";
    return (
        {
            xxs: "w-full min-w-[calc(100vw-10px)] max-w-[calc(100vw-10px)] md:min-w-xxs md:max-w-xxs",
            xs: "w-full min-w-[calc(100vw-10px)] max-w-[calc(100vw-10px)] md:min-w-xs md:max-w-xs",
            sm: "w-full min-w-[calc(100vw-10px)] max-w-[calc(100vw-10px)] md:min-w-sm md:max-w-sm",
            md: "w-full min-w-[calc(100vw-10px)] max-w-[calc(100vw-10px)] md:min-w-md md:max-w-md",
            lg: "w-full min-w-[calc(100vw-10px)] max-w-[calc(100vw-10px)] md:min-w-lg md:max-w-lg",
            xl: "w-full min-w-0 max-w-4xl md:min-w-4xl",
            full: "w-full min-w-[calc(100vw-10px)] max-w-[calc(100vw-10px)]",
        }[size] ?? "w-full max-w-sm min-w-sm"
    );
});
</script>

<template>
    <Transition :name="animationsEnabled ? 'shared-panel' : ''">
        <div
            v-if="isVisible"
            class="fixed inset-x-0 mx-auto bottom-9 p-2 z-999999"
            :class="sizeClass"
            :style="
                animationsEnabled
                    ? 'transition: max-width 125ms ease-out, min-width 125ms ease-out;'
                    : ''
            "
            @mouseenter="onSharedPanelMouseEnter"
            @mouseleave="onSharedPanelMouseLeave"
        >
            <div
                class="text-xxs rounded-2xl border border-white/10 bg-[#111111]/95 px-2 pt-2 text-white backdrop-blur-xl overflow-hidden"
            >
                <component :is="activeContent" :key="activeToolId" :config="config" />
            </div>
        </div>
    </Transition>
</template>
