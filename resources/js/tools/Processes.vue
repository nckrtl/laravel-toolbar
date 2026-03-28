<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import ToolbarItem from '@/components/ToolbarItem.vue';
import Panel from '@/components/Panel.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import Section from '@/components/Section.vue';
import DataListItem from '@/components/DataListItem.vue';
import Pill from '@/components/Pill.vue';

defineProps({
    config: { type: Object, required: false },
    itemClasses: { type: Object, required: false },
});

const isOpen = ref(false);
const available = ref(false);
const processes = ref([]);

const runningCount = computed(() => processes.value.filter(p => p.status === 'running').length);
const totalCount = computed(() => processes.value.length);
const allRunning = computed(() => runningCount.value === totalCount.value && totalCount.value > 0);

let eventSource = null;

function connectSSE() {
    const domain = window.location.host;
    eventSource = new EventSource(`/_toolbar/processes/stream?domain=${encodeURIComponent(domain)}`);

    eventSource.addEventListener('state-changed', (event) => {
        try {
            const data = JSON.parse(event.data);
            processes.value = data.processes ?? [];
            available.value = processes.value.length > 0;
        } catch {
            // Ignore parse errors
        }
    });
}

async function fetchInitialStatus() {
    try {
        const domain = window.location.host;
        const response = await fetch(`/_toolbar/processes/status?domain=${encodeURIComponent(domain)}`);
        const data = await response.json();

        available.value = data.available ?? false;
        processes.value = data.processes ?? [];
    } catch {
        available.value = false;
    }
}

onMounted(async () => {
    await fetchInitialStatus();

    if (available.value) {
        connectSSE();
    }
});

onUnmounted(() => {
    eventSource?.close();
});
</script>

<template>
    <div v-if="available && processes.length > 0">
        <Panel v-if="isOpen" @mouseenter="isOpen = true" @mouseleave="isOpen = false" size="xs" style="right: 5px; left: auto;">
            <SectionHeader>
                <template #icon>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M8.074.945A4.993 4.993 0 0 0 6 5v.032a7.94 7.94 0 0 1 3.475-.017 2.993 2.993 0 0 1 4.209-2.462A5.01 5.01 0 0 0 8.074.945ZM3.5 5a4.994 4.994 0 0 0-1.584 2.99 3 3 0 0 1 3.044 1.353A7.94 7.94 0 0 1 8 8c1.126 0 2.197.233 3.166.652a2.998 2.998 0 0 1 4.725 1.361A4.992 4.992 0 0 0 13.5 5h-10ZM2.741 13.088a8.04 8.04 0 0 1-.228-.278A2.993 2.993 0 0 0 .39 10.092 5.005 5.005 0 0 0 2.741 13.088ZM5 15a4.995 4.995 0 0 0 3-1 4.995 4.995 0 0 0 3 1 4.98 4.98 0 0 0 2.377-.601 2.998 2.998 0 0 1-4.742-1.924A7.958 7.958 0 0 0 8 12.6a7.96 7.96 0 0 0-.635-.025 2.998 2.998 0 0 1-4.742 1.924A4.98 4.98 0 0 0 5 15Z" clip-rule="evenodd" />
                    </svg>
                </template>
                <template #label>Processes</template>
                <template #secondaryLabel>{{ runningCount }}/{{ totalCount }}</template>
            </SectionHeader>
            <Section>
                <DataListItem v-for="process in processes" :key="process.name">
                    <template #label>{{ process.name }}</template>
                    <template #value>
                        <Pill
                            :color="process.status === 'running' ? 'green' : 'red'"
                            size="compact"
                        >
                            {{ process.status }}
                        </Pill>
                    </template>
                </DataListItem>
            </Section>
        </Panel>

        <ToolbarItem
            @mouseenter="isOpen = true"
            @mouseleave="isOpen = false"
            :isActive="isOpen"
            :class="itemClasses"
        >
            <div class="flex items-center gap-1 py-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                    <path fill-rule="evenodd" d="M8.074.945A4.993 4.993 0 0 0 6 5v.032a7.94 7.94 0 0 1 3.475-.017 2.993 2.993 0 0 1 4.209-2.462A5.01 5.01 0 0 0 8.074.945ZM3.5 5a4.994 4.994 0 0 0-1.584 2.99 3 3 0 0 1 3.044 1.353A7.94 7.94 0 0 1 8 8c1.126 0 2.197.233 3.166.652a2.998 2.998 0 0 1 4.725 1.361A4.992 4.992 0 0 0 13.5 5h-10ZM2.741 13.088a8.04 8.04 0 0 1-.228-.278A2.993 2.993 0 0 0 .39 10.092 5.005 5.005 0 0 0 2.741 13.088ZM5 15a4.995 4.995 0 0 0 3-1 4.995 4.995 0 0 0 3 1 4.98 4.98 0 0 0 2.377-.601 2.998 2.998 0 0 1-4.742-1.924A7.958 7.958 0 0 0 8 12.6a7.96 7.96 0 0 0-.635-.025 2.998 2.998 0 0 1-4.742 1.924A4.98 4.98 0 0 0 5 15Z" clip-rule="evenodd" />
                </svg>
                <Pill :color="allRunning ? 'green' : 'red'" size="compact" class="px-1.5">
                    {{ runningCount }}/{{ totalCount }}
                </Pill>
            </div>
        </ToolbarItem>
    </div>

    <!-- Hidden when orbit not available or no processes configured -->
    <span v-else></span>
</template>
