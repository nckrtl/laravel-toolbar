<script setup>
import { ref } from 'vue'
import { useToolbar } from '@/composables/useToolbar'
import ToolbarItem from '@/components/ToolbarItem.vue'
import Panel from '@/components/Panel.vue'
import SectionHeader from '@/components/SectionHeader.vue'
import Section from '@/components/Section.vue'
import DataListItem from '@/components/DataListItem.vue'
import PhpIcon from '@/icons/PhpIcon.vue'

const { data } = useToolbar()

const isOpen = ref(false)
</script>

<template>
  <div class="flex justify-end" @mouseenter="isOpen = true" @mouseleave="isOpen = false">
    <Panel v-if="isOpen" align="right" size="xxs">
      <SectionHeader iconClass="px-2">
        <template #icon>
          <PhpIcon />
        </template>
      </SectionHeader>
      <Section>
        <DataListItem>
          <template #label>
            Version
          </template>
          <template #value>
            {{ data.php?.version }}
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Memory Limit
          </template>
          <template #value>
            {{ data.php?.memory_limit }}
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Max Exec. Time
          </template>
          <template #value>
            {{ data.php?.max_execution_time }}s
          </template>
        </DataListItem>
      </Section>
    </Panel>
    <ToolbarItem :isActive="isOpen" class="!border-l-0 pr-0.5 rounded-r-full">
      <div class="py-[3px]">
        <PhpIcon />
      </div>
    </ToolbarItem>
  </div>
</template>