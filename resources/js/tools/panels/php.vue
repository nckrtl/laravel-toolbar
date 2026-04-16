<script setup>
import { useToolbar } from "@/composables/useToolbar";
import SectionHeader from "@/components/SectionHeader.vue";
import Section from "@/components/Section.vue";
import DataList from "@/components/DataList.vue";
import DataListItem from "@/components/DataListItem.vue";
import PhpIcon from "@/icons/PhpIcon.vue";

const { data } = useToolbar();
</script>

<template>
    <div>
        <SectionHeader iconClass="px-2">
            <template #icon>
                <PhpIcon />
            </template>
        </SectionHeader>
        <Section>
            <DataList>
                <DataListItem>
                    <template #label> Version </template>
                    <template #value>
                        {{ data.php?.version }}
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Memory Limit </template>
                    <template #value>
                        {{ data.php?.memory_limit }}
                    </template>
                </DataListItem>
                <DataListItem>
                    <template #label> Max Exec. Time </template>
                    <template #value> {{ data.php?.max_execution_time }}s </template>
                </DataListItem>
            </DataList>
        </Section>
    </div>
</template>
