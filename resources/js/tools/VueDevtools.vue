<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import VueIcon from '@/icons/VueIcon.vue';
import Panel from '@/components/Panel.vue';

const props = defineProps({
    config: {
        type: Object,
        required: false,
    },
    itemClasses: {
        type: Object,
        required: false,
        default: () => ({}),
    },
});

const isOpen = ref(false);
const vueDevtoolsContainer = ref(null);
const vueDevtoolsFrame = ref(null);
const isInitialized = ref(false);
let persistentStyleObserver = null;

function waitForElement(selector, condition = () => true, timeout = 5000) {
    return new Promise((resolve, reject) => {
        const element = document.querySelector(selector);
        if (element && condition(element)) {
            return resolve(element);
        }

        const observer = new MutationObserver((mutations, obs) => {
            const element = document.querySelector(selector);
            if (element && condition(element)) {
                obs.disconnect();
                resolve(element);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['src'],
        });

        setTimeout(() => {
            observer.disconnect();
            const element = document.querySelector(selector);
            if (element && condition(element)) {
                resolve(element);
            } else {
                reject(new Error(`Timeout waiting for ${selector}`));
            }
        }, timeout);
    });
}

function applyOurStyles(frame, iframe) {
    frame.setAttribute(
        'style',
        'width: 100% !important; height: 100% !important; z-index: 99999 !important;',
    );
    if (iframe) {
        iframe.setAttribute('style', 'width: 100%; height: 100%;');
    }
}

function setupPersistentStyleGuard(frame, iframe) {
    persistentStyleObserver = new MutationObserver(() => {
        if (!frame.style.width.includes('100%')) {
            applyOurStyles(frame, iframe);
        }
    });

    persistentStyleObserver.observe(frame, {
        attributes: true,
        attributeFilter: ['style'],
    });
}

async function initDevtools() {
    try {
        const frame = await waitForElement('.vue-devtools-frame');

        let iframe = frame.querySelector('iframe');

        if (!iframe) {
            const toggleBtn = document.querySelector(
                '#__vue-devtools-container__ .panel-entry-btn',
            );
            toggleBtn?.click();

            iframe = await waitForElement(
                '.vue-devtools-frame iframe',
                (el) => el.hasAttribute('src') && el.src !== '',
            );
        }

        vueDevtoolsFrame.value = frame;
        vueDevtoolsContainer.value.appendChild(frame);

        applyOurStyles(frame, iframe);
        setupPersistentStyleGuard(frame, iframe);

        setTimeout(() => {
            isInitialized.value = true;
        }, 500);
    } catch (error) {
        console.warn('Vue DevTools initialization failed:', error);
    }
}

onMounted(() => {
    initDevtools();
});

onUnmounted(() => {
    if (persistentStyleObserver) {
        persistentStyleObserver.disconnect();
        persistentStyleObserver = null;
    }
});
</script>

<template>
    <div>
        <Panel
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            size="full"
            minHeight="h-[480px] !p-0 rounded-2xl overflow-hidden"
            class="transition-[opacity,visibility] duration-0"
            :class="isOpen ? 'visible opacity-100' : 'invisible opacity-0'"
            v-cloak
        >
            <div class="relative h-full w-full">
                <div
                    class="absolute top-0 left-0 h-full w-full"
                    :class="{ invisible: !isInitialized }"
                    ref="vueDevtoolsContainer"
                ></div>
            </div>
        </Panel>
        <ToolbarItem
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            :isActive="isOpen"
            :class="itemClasses"
        >
            <div class="py-[3px]">
                <VueIcon />
            </div>
        </ToolbarItem>
    </div>
</template>
