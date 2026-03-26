<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';

const props = defineProps({
    config: { type: Object, required: false },
    itemClasses: { type: Object, required: false, default: () => ({}) },
});

const annotationCount = ref(0);
let observer = null;

function getAgentationToolbar() {
    return document.querySelector('[data-agentation-toolbar]');
}

function getToolbarHost() {
    return document.getElementById('laravel-toolbar-shadow-host');
}

function activate() {
    const agentation = getAgentationToolbar();
    if (!agentation) return;

    getToolbarHost()?.classList.add('toolbar-external-active');
    agentation.classList.add('agentation-visible');

    requestAnimationFrame(() => {
        agentation.querySelector('[tabindex]')?.click();
    });
}

function deactivate() {
    getAgentationToolbar()?.classList.remove('agentation-visible');
    getToolbarHost()?.classList.remove('toolbar-external-active');
}

function onStateChange(e) {
    if ('annotationCount' in (e.detail || {})) {
        annotationCount.value = e.detail.annotationCount;
    }
}

/**
 * Watch Agentation's toolbar container for class changes.
 * When it collapses (user pressed Escape or clicked exit),
 * restore the laravel-toolbar.
 */
function observeAgentationState() {
    const check = () => {
        const toolbar = getAgentationToolbar();
        if (!toolbar) {
            requestAnimationFrame(check);
            return;
        }

        const container = toolbar.firstElementChild;
        if (!container) return;

        let wasExpanded = container.className?.includes('expanded') ?? false;

        observer = new MutationObserver(() => {
            const isExpanded = container.className?.includes('expanded') ?? false;

            if (wasExpanded && !isExpanded) {
                deactivate();
            }

            wasExpanded = isExpanded;
        });

        observer.observe(container, {
            attributes: true,
            attributeFilter: ['class'],
        });
    };

    check();
}

onMounted(() => {
    window.addEventListener('toolbar:agentation:state', onStateChange);
    window.dispatchEvent(new CustomEvent('toolbar:agentation:request-state'));
    observeAgentationState();
});

onUnmounted(() => {
    window.removeEventListener('toolbar:agentation:state', onStateChange);
    observer?.disconnect();
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
                    class="mt-[1px] size-4"
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
