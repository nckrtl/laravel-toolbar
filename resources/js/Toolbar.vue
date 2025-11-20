<script setup>
import BreakpointIndicator from './components/BreakpointIndicator.vue'
import Request from './collectors/Request.vue'
import Timings from './collectors/Timings.vue'
import MemoryUsage from './collectors/MemoryUsage.vue'
import Toggle from './components/Toggle.vue'
import Group from './components/Group.vue'
import Queries from './collectors/Queries.vue'
import Laravel from './collectors/Laravel.vue'
import PHP from './collectors/PHP.vue'
import Vue from './collectors/Vue.vue'
import Models from './collectors/Models.vue'
import { useToolbar } from './composables/useToolbar'

const { data } = useToolbar()

</script>

<template>
  <div class="bottom-0 fixed flex left-0 z-[999999] font-mono justify-between p-1 w-full font-medium laravel-toolbar">
    <div class="flex gap-1">
      <Group>
          <Request />
          <Timings v-if="data.profiler" />
          <MemoryUsage v-if="data.profiler" />
          <Queries />
      </Group>
    </div>
    <div class="flex gap-1 items-center">
      <Group>
        <Vue />
        <Laravel />
        <PHP />
      </Group>
      <Group>
        <BreakpointIndicator />
      </Group>
      <Group>
        <Toggle />
      </Group>
    </div>
  </div>
</template>

