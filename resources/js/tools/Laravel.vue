<script setup>
import { ref } from 'vue'
import { useToolbar } from '@/composables/useToolbar'
import ToolbarItem from '@/components/ToolbarItem.vue'
import Panel from '@/components/Panel.vue'
import SectionHeader from '@/components/SectionHeader.vue'
import Section from '@/components/Section.vue'
import DataListItem from '@/components/DataListItem.vue'
import LaravelIcon from '@/icons/LaravelIcon.vue'

const props = defineProps({
  config: {
    type: Object,
    required: false,
  },
  itemClasses: {
    type: Object,
    required: false,
    default: {},
  }
})

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
            <a :href="data.laravel?.environment_editor_url" target="_blank">
              {{ data.laravel?.environment }}
            </a>
          </template>
        </DataListItem>
        <DataListItem v-if="data.laravel?.host">
          <template #label>
            Host
          </template>
          <template #value>
            {{ data.laravel?.host }}
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Timezone
          </template>
          <template #value>
            <a :href="data.laravel?.timezone_editor_url" target="_blank">
              {{ data.laravel?.timezone }}
            </a>
          </template>
        </DataListItem>
        <DataListItem>
          <template #label>
            Locale
          </template>
          <template #value>
            <a :href="data.laravel?.locale_editor_url" target="_blank">
              {{ data.laravel?.locale }}
            </a>
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
    <ToolbarItem :isActive="isOpen" :class="itemClasses">
      <div class="py-[3px] ">
      <LaravelIcon />
      </div>
    </ToolbarItem>
  </div>
</template>