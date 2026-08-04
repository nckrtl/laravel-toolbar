<script setup>
import { onMounted, onUnmounted } from "vue";
import SectionHeader from "@/components/SectionHeader.vue";
import Section from "@/components/Section.vue";
import DataList from "@/components/DataList.vue";
import DataListItem from "@/components/DataListItem.vue";
import Pill from "@/components/Pill.vue";
import { PlayIcon, StopIcon, ArrowPathIcon } from "@heroicons/vue/16/solid";
import { useOrbitProcesses } from "@/composables/useOrbitProcesses";

const props = defineProps({
    config: { type: Object, required: false },
});

const {
    processes,
    error,
    loading,
    pendingByName,
    runningCount,
    totalCount,
    subscribe,
    runAction,
} = useOrbitProcesses();

let unsubscribe = null;

onMounted(() => {
    unsubscribe = subscribe(props.config?.gateway_url);
});

onUnmounted(() => {
    unsubscribe?.();
    unsubscribe = null;
});

const statusPillColor = (status) => {
    switch (status) {
        case "running":
            return "green";
        case "crashed":
            return "red";
        case "stopped":
            return "slate";
        case "starting":
        case "restarting":
            return "blue";
        case "stopping":
        case "unknown":
        default:
            return "yellow";
    }
};

const statusDotClass = (status) => {
    switch (status) {
        case "running":
            return "bg-emerald-400";
        case "crashed":
            return "bg-red-400";
        case "stopped":
            return "bg-white/35";
        case "starting":
        case "restarting":
            return "bg-blue-400";
        case "stopping":
        case "unknown":
        default:
            return "bg-yellow-400";
    }
};

const isPending = (name) => Boolean(pendingByName.value[name]);

const isStartDisabled = (process) =>
    isPending(process.name) ||
    process.status === "running" ||
    process.status === "starting" ||
    process.status === "restarting" ||
    process.status === "stopping";

const isStopDisabled = (process) =>
    isPending(process.name) || process.status === "stopped" || process.status === "stopping";

const isRestartDisabled = (process) =>
    isPending(process.name) ||
    process.status === "starting" ||
    process.status === "stopping" ||
    process.status === "restarting";

const handleAction = async (action, process) => {
    await runAction(action, process.name, props.config?.gateway_url);
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
                <span v-if="!error">{{ runningCount }}/{{ totalCount }}</span>
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
            <div class="px-1 text-white/50 uppercase">Processes</div>
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

                <DataListItem v-for="process in processes" :key="process.name" align="start">
                    <template #label>
                        <div class="flex items-center gap-1.5 normal-case">
                            <span
                                class="inline-block size-1.5 shrink-0 rounded-full"
                                :class="statusDotClass(process.status)"
                                :aria-label="`Status: ${process.status}`"
                                role="img"
                            />
                            <span class="text-white/80" :title="process.name">{{
                                process.name
                            }}</span>
                        </div>
                    </template>
                    <template #value>
                        <div class="flex items-center gap-1.5">
                            <Pill :color="statusPillColor(process.status)" size="compact">
                                {{ process.status }}
                            </Pill>

                            <div class="flex items-center gap-0.5">
                                <button
                                    type="button"
                                    class="rounded p-0.5 text-white/55 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/55"
                                    :class="{
                                        'animate-pulse text-white':
                                            pendingByName[process.name] === 'start',
                                    }"
                                    :disabled="isStartDisabled(process)"
                                    :title="`Start ${process.name}`"
                                    :aria-label="`Start ${process.name}`"
                                    @click="handleAction('start', process)"
                                >
                                    <PlayIcon class="size-3.5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-0.5 text-white/55 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/55"
                                    :class="{
                                        'animate-pulse text-white':
                                            pendingByName[process.name] === 'restart',
                                    }"
                                    :disabled="isRestartDisabled(process)"
                                    :title="`Restart ${process.name}`"
                                    :aria-label="`Restart ${process.name}`"
                                    @click="handleAction('restart', process)"
                                >
                                    <ArrowPathIcon class="size-3.5" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded p-0.5 text-white/55 transition-colors hover:bg-white/10 hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-white/55"
                                    :class="{
                                        'animate-pulse text-white':
                                            pendingByName[process.name] === 'stop',
                                    }"
                                    :disabled="isStopDisabled(process)"
                                    :title="`Stop ${process.name}`"
                                    :aria-label="`Stop ${process.name}`"
                                    @click="handleAction('stop', process)"
                                >
                                    <StopIcon class="size-3.5" />
                                </button>
                            </div>
                        </div>
                    </template>
                </DataListItem>
            </DataList>
        </Section>
    </div>
</template>
