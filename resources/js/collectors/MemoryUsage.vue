<script setup>
  import { ref, computed, onMounted } from 'vue'
  import ToolbarItem from '@/components/ToolbarItem.vue'
  import { useToolbar } from '@/composables/useToolbar'
  import Panel from '@/components/Panel.vue'
  import SectionHeader from '@/components/SectionHeader.vue'
  import Section from '@/components/Section.vue'
  import DataListItem from '@/components/DataListItem.vue'

  const { data } = useToolbar()

  const isOpen = ref(false);



</script>

<template>
  <div @mouseenter="isOpen = true" @mouseleave="isOpen = false">
    <Panel v-if="isOpen" size="xxs">
      <SectionHeader>
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
            <path d="M6 6v4h4V6H6Z" />
            <path fill-rule="evenodd" d="M5.75 1a.75.75 0 0 0-.75.75V3a2 2 0 0 0-2 2H1.75a.75.75 0 0 0 0 1.5H3v.75H1.75a.75.75 0 0 0 0 1.5H3v.75H1.75a.75.75 0 0 0 0 1.5H3a2 2 0 0 0 2 2v1.25a.75.75 0 0 0 1.5 0V13h.75v1.25a.75.75 0 0 0 1.5 0V13h.75v1.25a.75.75 0 0 0 1.5 0V13a2 2 0 0 0 2-2h1.25a.75.75 0 0 0 0-1.5H13v-.75h1.25a.75.75 0 0 0 0-1.5H13V6.5h1.25a.75.75 0 0 0 0-1.5H13a2 2 0 0 0-2-2V1.75a.75.75 0 0 0-1.5 0V3h-.75V1.75a.75.75 0 0 0-1.5 0V3H6.5V1.75A.75.75 0 0 0 5.75 1ZM11 4.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5h6Z" clip-rule="evenodd" />
          </svg>
        </template>
        <template #label>
          Memory Usage
        </template>
        <template #secondaryLabel>

        </template>
      </SectionHeader>
      <Section>
        <DataListItem>
          <template #label>
            Peak Memory Usage
          </template>
          <template #value>
            {{data.request?.memory}}MB
          </template>
        </DataListItem>
      </Section>
    </Panel>
    <ToolbarItem class="!border-x-0" >
      <div class="flex gap-1 items-center py-0.5">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
          <path d="M6 6v4h4V6H6Z" />
          <path fill-rule="evenodd" d="M5.75 1a.75.75 0 0 0-.75.75V3a2 2 0 0 0-2 2H1.75a.75.75 0 0 0 0 1.5H3v.75H1.75a.75.75 0 0 0 0 1.5H3v.75H1.75a.75.75 0 0 0 0 1.5H3a2 2 0 0 0 2 2v1.25a.75.75 0 0 0 1.5 0V13h.75v1.25a.75.75 0 0 0 1.5 0V13h.75v1.25a.75.75 0 0 0 1.5 0V13a2 2 0 0 0 2-2h1.25a.75.75 0 0 0 0-1.5H13v-.75h1.25a.75.75 0 0 0 0-1.5H13V6.5h1.25a.75.75 0 0 0 0-1.5H13a2 2 0 0 0-2-2V1.75a.75.75 0 0 0-1.5 0V3h-.75V1.75a.75.75 0 0 0-1.5 0V3H6.5V1.75A.75.75 0 0 0 5.75 1ZM11 4.5a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 1 .5-.5h6Z" clip-rule="evenodd" />
        </svg>
        <span>{{data.request?.memory}}MB</span>
      </div>
    </ToolbarItem>
  </div>

</template>