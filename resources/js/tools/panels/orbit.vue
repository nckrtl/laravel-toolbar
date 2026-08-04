<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import SectionHeader from '@/components/SectionHeader.vue';
import Section from '@/components/Section.vue';
import DataList from '@/components/DataList.vue';
import DataListItem from '@/components/DataListItem.vue';
import { ArrowPathIcon, EllipsisHorizontalIcon, PlayIcon, StopIcon } from '@heroicons/vue/16/solid';
import { useOrbitProcesses } from '@/composables/useOrbitProcesses';

const props = defineProps({
    config: { type: Object, required: false },
});

const { processes, error, loading, pendingByKey, subscribe, runAction } = useOrbitProcesses();

const menuOpen = ref(false);
const menuRoot = ref(null);

const processUrl = (key) => props.config?.process_urls?.[key] ?? null;

let unsubscribe = null;

onMounted(() => {
    unsubscribe = subscribe(props.config?.gateway_url);
    document.addEventListener('pointerdown', onDocumentPointerDown, true);
    document.addEventListener('keydown', onDocumentKeydown, true);
});

onUnmounted(() => {
    unsubscribe?.();
    unsubscribe = null;
    document.removeEventListener('pointerdown', onDocumentPointerDown, true);
    document.removeEventListener('keydown', onDocumentKeydown, true);
});

const statusDotClass = (status) => {
    switch (status) {
        case 'running':
            return 'bg-emerald-400';
        case 'crashed':
            return 'bg-red-400';
        case 'stopped':
            return 'bg-white/35';
        case 'starting':
        case 'restarting':
            return 'bg-blue-400';
        case 'stopping':
        case 'unknown':
        default:
            return 'bg-yellow-400';
    }
};

const isPending = (key) => Boolean(pendingByKey.value[key]);

const isStartDisabled = (process) =>
    isPending(process.key) ||
    process.status === 'running' ||
    process.status === 'starting' ||
    process.status === 'restarting' ||
    process.status === 'stopping';

const isStopDisabled = (process) =>
    isPending(process.key) || process.status === 'stopped' || process.status === 'stopping';

const isRestartDisabled = (process) =>
    isPending(process.key) ||
    process.status === 'starting' ||
    process.status === 'stopping' ||
    process.status === 'restarting';

const startAllTargets = computed(() =>
    processes.value.filter((process) => !isStartDisabled(process)),
);
const restartAllTargets = computed(() =>
    processes.value.filter((process) => !isRestartDisabled(process)),
);
const stopAllTargets = computed(() =>
    processes.value.filter((process) => !isStopDisabled(process)),
);

const isStartAllPending = computed(() =>
    processes.value.some((process) => pendingByKey.value[process.key] === 'start'),
);
const isRestartAllPending = computed(() =>
    processes.value.some((process) => pendingByKey.value[process.key] === 'restart'),
);
const isStopAllPending = computed(() =>
    processes.value.some((process) => pendingByKey.value[process.key] === 'stop'),
);

const isAnyBulkPending = computed(
    () => isStartAllPending.value || isRestartAllPending.value || isStopAllPending.value,
);

const canStartAll = computed(() => startAllTargets.value.length > 0);
const canRestartAll = computed(() => restartAllTargets.value.length > 0);
const canStopAll = computed(() => stopAllTargets.value.length > 0);

const handleAction = async (action, process) => {
    await runAction(action, process.key, props.config?.gateway_url);
};

const closeMenu = () => {
    menuOpen.value = false;
};

const toggleMenu = () => {
    menuOpen.value = !menuOpen.value;
};

const onDocumentPointerDown = (event) => {
    if (!menuOpen.value || !menuRoot.value) {
        return;
    }

    // composedPath is required in Shadow DOM; event.target is retargeted to the host.
    const path = typeof event.composedPath === 'function' ? event.composedPath() : [];
    if (!path.includes(menuRoot.value)) {
        closeMenu();
    }
};

const onDocumentKeydown = (event) => {
    if (event.key === 'Escape' && menuOpen.value) {
        closeMenu();
    }
};

const handleBulkAction = async (action) => {
    const targets =
        action === 'start'
            ? startAllTargets.value
            : action === 'restart'
              ? restartAllTargets.value
              : stopAllTargets.value;

    closeMenu();

    if (targets.length === 0) {
        return;
    }

    await Promise.all(
        targets.map((process) => runAction(action, process.key, props.config?.gateway_url)),
    );
};
</script>

<template>
    <div class="pb-2">
        <SectionHeader>
            <template #icon>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 100 100"
                    fill="none"
                    class="size-4"
                    aria-hidden="true"
                >
                    <path
                        d="M50 25C77.6143 25 100 36.1929 100 50C99.9996 63.8069 77.614 75 50 75C22.386 75 0.000366987 63.8069 0 50C0 36.1929 22.3858 25 50 25ZM49.7764 32.0107C32.7857 32.0108 15.7344 38.9923 15.7344 46.9102C15.7346 54.8279 28.3485 61.2461 49.5654 61.2461C70.7823 61.2461 83.3962 54.8279 83.3965 46.9102C83.3965 38.9923 66.7672 32.0107 49.7764 32.0107Z"
                        fill="currentColor"
                    />
                </svg>
            </template>
            <template #label>Orbit</template>
            <template #secondaryLabel>
                <div v-if="processes.length > 0" ref="menuRoot" class="relative">
                    <button
                        type="button"
                        class="rounded p-0.5 text-white/80 transition-colors hover:bg-white/10 hover:text-white"
                        :class="{ 'animate-pulse text-white': isAnyBulkPending }"
                        aria-label="Bulk process actions"
                        aria-haspopup="menu"
                        :aria-expanded="menuOpen"
                        @click.stop="toggleMenu"
                    >
                        <EllipsisHorizontalIcon class="size-3.5" />
                    </button>

                    <div
                        v-if="menuOpen"
                        class="absolute top-full right-0 z-10 mt-1 min-w-[8.5rem] rounded-lg border border-white/10 bg-[#1a1a1a] py-1 shadow-lg shadow-black/40"
                        role="menu"
                        aria-label="Bulk process actions"
                    >
                        <button
                            type="button"
                            role="menuitem"
                            class="flex w-full items-center gap-1.5 px-2.5 py-1.5 text-left text-white/80 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/80"
                            :class="{ 'animate-pulse text-white': isStartAllPending }"
                            :disabled="!canStartAll"
                            @click="handleBulkAction('start')"
                        >
                            <PlayIcon class="size-3.5 shrink-0" />
                            Start all
                        </button>
                        <button
                            type="button"
                            role="menuitem"
                            class="flex w-full items-center gap-1.5 px-2.5 py-1.5 text-left text-white/80 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/80"
                            :class="{ 'animate-pulse text-white': isRestartAllPending }"
                            :disabled="!canRestartAll"
                            @click="handleBulkAction('restart')"
                        >
                            <ArrowPathIcon class="size-3.5 shrink-0" />
                            Restart all
                        </button>
                        <button
                            type="button"
                            role="menuitem"
                            class="flex w-full items-center gap-1.5 px-2.5 py-1.5 text-left text-white/80 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/80"
                            :class="{ 'animate-pulse text-white': isStopAllPending }"
                            :disabled="!canStopAll"
                            @click="handleBulkAction('stop')"
                        >
                            <StopIcon class="size-3.5 shrink-0" />
                            Stop all
                        </button>
                    </div>
                </div>
            </template>
        </SectionHeader>

        <div
            v-if="error"
            class="mt-1 rounded-lg border border-red-500/30 bg-red-500/10 px-2 py-1.5 text-red-200"
            role="alert"
        >
            {{ error }}
        </div>

        <Section class="mt-1">
            <DataList>
                <template v-if="loading && processes.length === 0 && !error">
                    <DataListItem>
                        <template #label>
                            <span class="text-white/40 normal-case">Loading…</span>
                        </template>
                        <template #value>
                            <span class="text-white/30">—</span>
                        </template>
                    </DataListItem>
                </template>

                <template v-else-if="processes.length === 0">
                    <DataListItem>
                        <template #label>
                            <span class="text-white/40 normal-case">No processes</span>
                        </template>
                        <template #value>
                            <span class="text-white/30">—</span>
                        </template>
                    </DataListItem>
                </template>

                <DataListItem v-for="process in processes" :key="process.key" align="start">
                    <template #label>
                        <div class="flex items-center gap-1.5 normal-case">
                            <span
                                class="inline-block size-1.5 shrink-0 rounded-full"
                                :class="statusDotClass(process.status)"
                                :aria-label="`Status: ${process.status}`"
                                role="img"
                            />
                            <a
                                v-if="processUrl(process.key)"
                                :href="processUrl(process.key)"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-white/80 transition-colors hover:text-white"
                                :title="process.label"
                            >
                                {{ process.label }}
                            </a>
                            <span
                                v-else
                                class="text-white/80"
                                :title="process.label"
                            >
                                {{ process.label }}
                            </span>
                        </div>
                    </template>
                    <template #value>
                        <div class="flex items-center gap-0.5">
                            <button
                                type="button"
                                class="rounded p-0.5 text-white/55 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/55"
                                :class="{
                                    'animate-pulse text-white':
                                        pendingByKey[process.key] === 'start',
                                }"
                                :disabled="isStartDisabled(process)"
                                :title="`Start ${process.label}`"
                                :aria-label="`Start ${process.label}`"
                                @click="handleAction('start', process)"
                            >
                                <PlayIcon class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                class="rounded p-0.5 text-white/55 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/55"
                                :class="{
                                    'animate-pulse text-white':
                                        pendingByKey[process.key] === 'restart',
                                }"
                                :disabled="isRestartDisabled(process)"
                                :title="`Restart ${process.label}`"
                                :aria-label="`Restart ${process.label}`"
                                @click="handleAction('restart', process)"
                            >
                                <ArrowPathIcon class="size-3.5" />
                            </button>
                            <button
                                type="button"
                                class="rounded p-0.5 text-white/55 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/55"
                                :class="{
                                    'animate-pulse text-white':
                                        pendingByKey[process.key] === 'stop',
                                }"
                                :disabled="isStopDisabled(process)"
                                :title="`Stop ${process.label}`"
                                :aria-label="`Stop ${process.label}`"
                                @click="handleAction('stop', process)"
                            >
                                <StopIcon class="size-3.5" />
                            </button>
                        </div>
                    </template>
                </DataListItem>
            </DataList>
        </Section>
    </div>
</template>
