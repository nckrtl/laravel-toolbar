<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';

const props = defineProps({
    config: { type: Object, required: false },
    itemClasses: { type: Object, required: false, default: () => ({}) },
});

const annotationCount = ref(0);

function activate() {
    window.dispatchEvent(
        new CustomEvent('toolbar:external-tool:activate', {
            detail: { tool: 'agentation' },
        }),
    );
}

function onStateChange(e) {
    if ('annotationCount' in (e.detail || {})) {
        annotationCount.value = e.detail.annotationCount;
    }
}

onMounted(() => {
    window.addEventListener('toolbar:agentation:state', onStateChange);
    // Request current state from the bridge
    window.dispatchEvent(new CustomEvent('toolbar:agentation:request-state'));
});

onUnmounted(() => {
    window.removeEventListener('toolbar:agentation:state', onStateChange);
});
</script>

<template>
    <div>
        <ToolbarItem :class="itemClasses" class="cursor-pointer" @click="activate">
            <div class="flex items-center gap-1 py-0.5">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 16 16"
                    fill="currentColor"
                    class="size-4 mt-[1px]"
                >
                    <path
                        d="M7.628 1.099a.4.4 0 0 1 .744 0l1.27 3.227a3 3 0 0 0 1.726 1.642l3.227 1.18a.4.4 0 0 1 0 .752l-3.227 1.18a3 3 0 0 0-1.726 1.642l-1.27 3.227a.4.4 0 0 1-.744 0l-1.27-3.227A3 3 0 0 0 4.632 9.08L1.405 7.9a.4.4 0 0 1 0-.752l3.227-1.18a3 3 0 0 0 1.726-1.642l1.27-3.227Z"
                    />
                </svg>
                <span v-if="annotationCount > 0">{{ annotationCount }}</span>
            </div>
        </ToolbarItem>
    </div>
</template>
