<script setup>
import { onMounted, computed } from 'vue';

import Group from '@/components/Group.vue';
import { useToolbar } from '@/composables/useToolbar';
// import CommandPallete from '@/CommandPallete.vue';

const { data } = useToolbar();
</script>

<template>
    <div
        id="toolbar"
        class="fixed bottom-0 left-0 z-999999 flex w-full justify-between font-mono font-medium"
    >
        <template v-for="(groups, section) in data.layout.sections">
            <div
                class="flex gap-1"
                :class="{
                    'absolute bottom-0 left-0 min-h-9.5 justify-start p-[5px]': section === 'left',
                    'justify-center': section === 'center',
                    'absolute right-0 bottom-0 min-h-9.5 justify-end p-[5px]': section === 'right',
                    'w-full': section === 'center',
                }"
            >
                <template v-for="groupConfig in groups">
                    <Group :config="groupConfig" />
                </template>
            </div>
        </template>
    </div>
</template>
