<script setup>
import { ref } from 'vue';
import { useToolbar } from '@/composables/useToolbar';
import ToolbarItem from '@/components/ToolbarItem.vue';
import Panel from '@/components/Panel.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import Section from '@/components/Section.vue';
import DataListItem from '@/components/DataListItem.vue';
import LaravelIcon from '@/icons/LaravelIcon.vue';
import InertiaIcon from '@/icons/InertiaIcon.vue';
import { Square3Stack3DIcon } from '@heroicons/vue/16/solid';
import VueIcon from '@/icons/VueIcon.vue';
import TailwindIcon from '@/icons/TailwindIcon.vue';

const props = defineProps({
    config: {
        type: Object,
        required: false,
    },
    itemClasses: {
        type: Object,
        required: false,
        default: {},
    },
});

const { data } = useToolbar();

const isOpen = ref(false);
</script>

<template>
    <div class="flex justify-end" @mouseenter="isOpen = true" @mouseleave="isOpen = false">
        <Panel v-if="isOpen" align="right" size="xxs">
            <SectionHeader>
                <template #icon>
                    <LaravelIcon />
                </template>
                <template #label> Laravel </template>
                <template #secondaryLabel>
                    <a
                        class="hover:underline"
                        :href="data.laravel?.version_editor_url"
                        target="_blank"
                    >
                        {{ data.laravel?.version }}
                    </a>
                </template>
            </SectionHeader>
            <Section>
                <DataListItem>
                    <template #label> Environment </template>
                    <template #value>
                        <a
                            class="hover:underline"
                            :href="data.laravel?.environment_editor_url"
                            target="_blank"
                        >
                            {{ data.laravel?.environment }}
                        </a>
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Timezone </template>
                    <template #value>
                        <a
                            class="hover:underline"
                            :href="data.laravel?.timezone_editor_url"
                            target="_blank"
                        >
                            {{ data.laravel?.timezone }}
                        </a>
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Locale </template>
                    <template #value>
                        <a
                            class="hover:underline"
                            :href="data.laravel?.locale_editor_url"
                            target="_blank"
                        >
                            {{ data.laravel?.locale }}
                        </a>
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Debug </template>
                    <template #value>
                        <div
                            class="text-xxs uppercase"
                            :class="{
                                'text-emerald-300': data.laravel?.debug,
                                'text-red-300': !data.laravel?.debug,
                            }"
                        >
                            <a
                                class="hover:underline"
                                :href="data.laravel?.debug_editor_url"
                                target="_blank"
                            >
                                {{ data.laravel?.debug ? 'true' : 'false' }}
                            </a>
                        </div>
                    </template>
                </DataListItem>
            </Section>
            <SectionHeader v-if="config?.inertia !== false && data.inertia" class="mt-1.5">
                <template #icon>
                    <InertiaIcon class="size-4" />
                </template>
                <template #label> Inertia </template>
                <template #secondaryLabel>
                    {{ data.inertia?.version }}
                </template>
            </SectionHeader>

            <SectionHeader v-if="config?.vue !== false && data.vue" class="">
                <template #icon>
                    <VueIcon class="size-4" />
                </template>
                <template #label> Vue </template>
                <template #secondaryLabel>
                    <a class="hover:underline" :href="data.vue?.version_editor_url" target="_blank">
                        {{ data.vue?.version }}
                    </a>
                </template>
            </SectionHeader>
            <SectionHeader v-if="config?.tailwind !== false && data.tailwind" class="">
                <template #icon>
                    <TailwindIcon class="size-4" />
                </template>
                <template #label> Tailwind </template>
                <template #secondaryLabel>
                    {{ data.tailwind?.version }}
                </template>
            </SectionHeader>
        </Panel>
        <ToolbarItem :isActive="isOpen" :class="itemClasses">
            <div class="py-[3px]">
                <Square3Stack3DIcon class="size-4" />
            </div>
        </ToolbarItem>
    </div>
</template>
