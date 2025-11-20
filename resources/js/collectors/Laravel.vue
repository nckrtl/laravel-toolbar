<script setup>
import { ref } from 'vue'
import { useToolbar } from '@/composables/useToolbar'
import ToolbarItem from '@/components/ToolbarItem.vue'
import Panel from '@/components/Panel.vue'
import SectionHeader from '@/components/SectionHeader.vue'
import Section from '@/components/Section.vue'
import DataListItem from '@/components/DataListItem.vue'
import LaravelIcon from '@/icons/LaravelIcon.vue'
import Pill from '@/components/Pill.vue'

const { data } = useToolbar()

const isOpen = ref(false)


</script>

<template>
  <div class="flex justify-end" @mouseenter="isOpen = true" @mouseleave="isOpen = false">
    <Panel v-if="isOpen" align="right" size="xxs">
      <SectionHeader>
          <template #icon>
            <LaravelIcon />
          </template>
          <template #label>
            Laravel
          </template>
          <template #secondaryLabel>
            Docs
          </template>
      </SectionHeader>
      <Section>
        <DataListItem>
          <template #label>
            Version
          </template>
          <template #value>
            {{ data.laravel?.version }}
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Environment
          </template>
          <template #value>
            {{ data.laravel?.environment }}
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Timezone
          </template>
          <template #value>
            {{ data.laravel?.timezone }}
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Locale
          </template>
          <template #value>
            {{ data.laravel?.locale }}
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Debug
          </template>
          <template #value>
            <div class="uppercase text-xxs" :class="{ 'text-emerald-300': data.laravel?.debug, 'text-red-300': !data.laravel?.debug }">
              {{ data.laravel?.debug ? 'true' : 'false' }}
            </div>
          </template>
        </DataListItem>
      </Section>
    </Panel>
    <ToolbarItem :isActive="isOpen" class=" !border-x-0">
      <div class="py-[3px] ">
      <LaravelIcon />
      </div>
    </ToolbarItem>
  </div>
</template>