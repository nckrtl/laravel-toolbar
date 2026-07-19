<script setup>
import { computed, nextTick, onMounted, onUpdated, ref } from "vue";

defineProps({
    minWidth: {
        type: String,
        required: true,
    },
    bodyClass: {
        type: String,
        default: "",
    },
});

const horizontalScroller = ref(null);
const bodyScroller = ref(null);
const showTopFade = ref(false);
const showBottomFade = ref(false);
const showLeftFade = ref(false);
const showRightFade = ref(false);

const horizontalMaskStyle = computed(() => {
    let maskImage = "none";

    if (showLeftFade.value && showRightFade.value) {
        maskImage =
            "linear-gradient(to right, transparent 0px, black 40px, black calc(100% - 40px), transparent 100%)";
    } else if (showLeftFade.value) {
        maskImage = "linear-gradient(to right, transparent 0px, black 40px, black 100%)";
    } else if (showRightFade.value) {
        maskImage =
            "linear-gradient(to right, black 0px, black calc(100% - 40px), transparent 100%)";
    }

    return { maskImage, WebkitMaskImage: maskImage };
});

const verticalMaskStyle = computed(() => {
    let maskImage = "none";

    if (showTopFade.value && showBottomFade.value) {
        maskImage =
            "linear-gradient(to bottom, transparent 0px, black 32px, black calc(100% - 32px), transparent 100%)";
    } else if (showTopFade.value) {
        maskImage = "linear-gradient(to bottom, transparent 0px, black 32px, black 100%)";
    } else if (showBottomFade.value) {
        maskImage =
            "linear-gradient(to bottom, black 0px, black calc(100% - 32px), transparent 100%)";
    }

    return { maskImage, WebkitMaskImage: maskImage };
});

const updateHorizontalFades = () => {
    const element = horizontalScroller.value;
    if (!element) return;

    const overflows = element.scrollWidth > element.clientWidth;
    const atRight = element.scrollLeft + element.clientWidth >= element.scrollWidth - 2;

    showLeftFade.value = overflows && element.scrollLeft > 1;
    showRightFade.value = overflows && !atRight;
};

const updateVerticalFades = () => {
    const element = bodyScroller.value;
    if (!element) return;

    const overflows = element.scrollHeight > element.clientHeight;
    const atBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - 2;

    showTopFade.value = overflows && element.scrollTop > 1;
    showBottomFade.value = overflows && !atBottom;
};

const refresh = () => {
    nextTick(() => {
        updateHorizontalFades();
        updateVerticalFades();
    });
};

onMounted(refresh);
onUpdated(refresh);
</script>

<template>
    <div class="scrollable-table relative -mx-2 min-h-0 flex-1">
        <div
            ref="horizontalScroller"
            class="scrollable-table-horizontal-scroller scrollbar-none h-full overflow-x-auto overflow-y-hidden"
            :style="horizontalMaskStyle"
            @scroll="updateHorizontalFades"
        >
            <div
                class="scrollable-table-track box-border h-full w-full px-2"
                :style="{ minWidth: `calc(${minWidth} + 1rem)` }"
            >
                <div
                    class="scrollable-table-content flex h-full min-h-0 flex-col"
                    :style="{ minWidth }"
                >
                    <div class="scrollable-table-header relative z-40 shrink-0">
                        <slot name="header" />
                    </div>
                    <div
                        ref="bodyScroller"
                        class="scrollable-table-body relative min-h-0 overflow-y-auto"
                        :class="bodyClass"
                        :style="verticalMaskStyle"
                        @scroll="updateVerticalFades"
                    >
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
