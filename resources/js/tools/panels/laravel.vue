<script setup>
import { useToolbar } from "@/composables/useToolbar";
import SectionHeader from "@/components/SectionHeader.vue";
import Section from "@/components/Section.vue";
import DataList from "@/components/DataList.vue";
import DataListItem from "@/components/DataListItem.vue";
import LaravelIcon from "@/icons/LaravelIcon.vue";

const { data } = useToolbar();
</script>

<template>
    <div>
        <SectionHeader>
            <template #icon>
                <LaravelIcon />
            </template>
            <template #label> Laravel </template>
            <template #secondaryLabel> Docs </template>
        </SectionHeader>
        <Section>
            <DataList>
                <DataListItem>
                    <template #label> Version </template>
                    <template #value>
                        {{ data.laravel?.version }}
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Environment </template>
                    <template #value>
                        <a :href="data.laravel?.environment_editor_url" target="_blank">
                            {{ data.laravel?.environment }}
                        </a>
                    </template>
                </DataListItem>
                <DataListItem v-if="data.laravel?.host">
                    <template #label> Host </template>
                    <template #value>
                        {{ data.laravel?.host }}
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Timezone </template>
                    <template #value>
                        <a :href="data.laravel?.timezone_editor_url" target="_blank">
                            {{ data.laravel?.timezone }}
                        </a>
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Locale </template>
                    <template #value>
                        <a :href="data.laravel?.locale_editor_url" target="_blank">
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
                            {{ data.laravel?.debug ? "true" : "false" }}
                        </div>
                    </template>
                </DataListItem>
            </DataList>
        </Section>
    </div>
</template>
