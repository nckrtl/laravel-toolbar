<script setup>
import { useToolbar } from "@/composables/useToolbar";
import SectionHeader from "@/components/SectionHeader.vue";
import Section from "@/components/Section.vue";
import DataList from "@/components/DataList.vue";
import DataListItem from "@/components/DataListItem.vue";
import VueIcon from "@/icons/VueIcon.vue";

const { data } = useToolbar();

const toggleVueDevtools = () => {
    const el = document.getElementById("__vue-devtools-container__");
    if (el) {
        if (el.style.display === "none") {
            el.style.cssText = "display: block !important; opacity: 1 !important";
        } else {
            el.style.cssText = "display: none !important; opacity: 1 !important";
        }
    }
};
</script>

<template>
    <div>
        <SectionHeader>
            <template #icon>
                <VueIcon />
            </template>
            <template #label> Vue </template>
            <template #secondaryLabel> Docs </template>
        </SectionHeader>
        <Section>
            <DataList>
                <DataListItem>
                    <template #label> Version </template>
                    <template #value>
                        <span @click="toggleVueDevtools">{{ data.vue?.version }}</span>
                    </template>
                </DataListItem>
            </DataList>
        </Section>
    </div>
</template>
