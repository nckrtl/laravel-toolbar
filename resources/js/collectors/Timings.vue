<script setup>
  import { ref, computed, onMounted } from 'vue'
  import ToolbarItem from '@/components/ToolbarItem.vue'
  import SectionHeader from '@/components/SectionHeader.vue'
  import Panel from '@/components/Panel.vue'
  import Section from '@/components/Section.vue'
  import DataListItem from '@/components/DataListItem.vue'
  import { useToolbar } from '@/composables/useToolbar'

  const { data } = useToolbar()

  const isOpen = ref(false);

  const hoverIndex = ref(null);
</script>

<template>
  <div>
    <Panel v-if="isOpen" size="md" @mouseenter="isOpen = true" @mouseleave="isOpen = false">
      <SectionHeader>
        <template #icon>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
            <path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0V8c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5v-3.5Z" clip-rule="evenodd" />
          </svg>
        </template>
        <template #label>
          Request lifecycle
        </template>
        <template #secondaryLabel>
          <span :title="Math.round(data.profiler?.total_wall_time?.value * 100) / 100 + 'ms'">
            {{data.profiler?.total_wall_time?.formattedValue}}
          </span>
        </template>
      </SectionHeader>
      <Section>
          <div class="px-0.5  flex w-full" v-if="data.profiler?.stages">
            <template v-for="(requestStage, index) in data.profiler?.stages" :key="index">
              <div @mouseenter="hoverIndex = index" @mouseleave="hoverIndex = null" class="flex min-w-2 py-1.5" :style="{ width: requestStage.wall_time.percentage + '%' }">
                <div  class="h-1 rounded-full mx-auto" :style="{ backgroundColor: requestStage.color, width: 'calc(100% - 4px)' }"></div>
              </div>
            </template>
          </div>

          <DataListItem v-for="(requestStage, index) in data.profiler?.stages" :key="index" class="" :class="{ 'opacity-35': hoverIndex !== index && hoverIndex !== null }">
            <template #label>
              <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: requestStage.color }"></div>
                <span>{{ requestStage.label }}</span>
              </div>
            </template>
            <template #value>
                <span v-if="hoverIndex !== index">{{ requestStage.wall_time.measurement.formattedValue }}</span>
        <span v-else>{{ requestStage.wall_time.percentage }}%</span>
            </template>
          </DataListItem>
      </Section>
    </Panel>
    <ToolbarItem @mouseenter="isOpen = true" @mouseleave="isOpen = false" :isActive="isOpen" class="!border-x-0">
      <div class="flex gap-1 items-center py-0.5">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
          <path fill-rule="evenodd" d="M1 8a7 7 0 1 1 14 0A7 7 0 0 1 1 8Zm7.75-4.25a.75.75 0 0 0-1.5 0V8c0 .414.336.75.75.75h3.25a.75.75 0 0 0 0-1.5h-2.5v-3.5Z" clip-rule="evenodd" />
        </svg>
        <span>{{ data.profiler?.total_wall_time?.formattedValue }}</span>
      </div>
    </ToolbarItem>
  </div>
</template>