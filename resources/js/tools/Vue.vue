<script setup>
import { ref, onMounted } from 'vue';

import ToolbarItem from '@/components/ToolbarItem.vue';
import VueIcon from '@/icons/VueIcon.vue';
import Panel from '@/components/Panel.vue';
import SectionHeader from '@/components/SectionHeader.vue';
import Section from '@/components/Section.vue';
import DataListItem from '@/components/DataListItem.vue';
import { useToolbar } from '@/composables/useToolbar';

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

const toggleVueDevtools = () => {
    let el = document.getElementById('__vue-devtools-container__');

    if (el) {
        if (el.style.display === 'none') {
            el.style.cssText = 'display: block !important; opacity: 1 !important';
        } else {
            el.style.cssText = 'display: none !important; opacity: 1 !important';
        }
    }
};
</script>

<template>
    <div class="" @mouseenter="isOpen = true" @mouseleave="isOpen = false">
        <Panel v-if="isOpen" size="xxs">
            <SectionHeader>
                <template #icon>
                    <VueIcon />
                </template>
                <template #label> Vue </template>
                <template #secondaryLabel> Docs </template>
            </SectionHeader>
            <Section>
                <DataListItem>
                    <template #label> Version </template>
                    <template #value>
                        <span @click="toggleVueDevtools">{{ data.vue?.version }}</span>
                    </template>
                </DataListItem>
            </Section>
        </Panel>
        <ToolbarItem :isActive="isOpen" :class="itemClasses">
            <div class="py-[3px]">
                <VueIcon />
            </div>
        </ToolbarItem>
    </div>
</template>
