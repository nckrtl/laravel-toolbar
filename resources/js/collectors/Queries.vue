<script setup>
import { ref, watch, computed } from 'vue'
import ToolbarItem from '@/components/ToolbarItem.vue'
import Panel from '@/components/Panel.vue'
import { useToolbar } from '@/composables/useToolbar'
import EmptyListIcon from '@/icons/EmptyListIcon.vue'

const { data } = useToolbar()
const isOpen = ref(false)
const searchPhrase = ref('')
const filter = ref('none')

const filteredQueries = computed(() => {

  let queries = data.value?.queries?.queries || []

  if (searchPhrase.value !== '') {
    queries = queries.filter(query => query.sql.includes(searchPhrase.value) || query.file.includes(searchPhrase.value))
  }

  if (filter.value !== 'none') {
    queries = queries.filter(query => filter.value === 'slow' ? query.isSlow : query.isDuplicate)
  }

  return queries
})

const queriesTable = ref(null)
const queriesTableInner = ref(null)

watch(queriesTable, (newVal) => {

  if (newVal) {

    newVal.addEventListener('scroll', () => {
      const scrollTop = newVal.scrollTop

      if (scrollTop + queriesTable.value.clientHeight == queriesTableInner.value.clientHeight + 12) {
        queriesTable.value.classList.remove('fade-to-top-and-bottom')
        queriesTable.value.classList.add('fade-to-top')
      } else if (scrollTop > 1) {
        queriesTable.value.classList.remove('fade-to-bottom','fade-to-top')
        queriesTable.value.classList.add('fade-to-top-and-bottom')
      } else {
        queriesTable.value.classList.remove('fade-to-bottom-and-top')
        queriesTable.value.classList.add('fade-to-bottom')
      }
    })
  }
})

</script>

<template>
  <div>
    <Panel v-if="isOpen" @mouseenter="isOpen = true" @mouseleave="isOpen = false" size="full" minHeight="h-[280px]">
      <div class="flex justify-between items-center">
         <div class="flex items-center gap-3 p-1.5 min-w-64">
              <div class="relative border border-white/8 bg-white/6 rounded-md w-7 h-7 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
                    <path d="M8 7c3.314 0 6-1.343 6-3s-2.686-3-6-3-6 1.343-6 3 2.686 3 6 3Z" />
                    <path d="M8 8.5c1.84 0 3.579-.37 4.914-1.037A6.33 6.33 0 0 0 14 6.78V8c0 1.657-2.686 3-6 3S2 9.657 2 8V6.78c.346.273.72.5 1.087.683C4.42 8.131 6.16 8.5 8 8.5Z" />
                    <path d="M8 12.5c1.84 0 3.579-.37 4.914-1.037.366-.183.74-.41 1.086-.684V12c0 1.657-2.686 3-6 3s-6-1.343-6-3v-1.22c.346.273.72.5 1.087.683C4.42 12.131 6.16 12.5 8 12.5Z" />
                  </svg>
              </div>
              <span>Queries</span>
          </div>

          <div class="flex gap-10 items-center">
            <div class="flex gap-2 items-center">
              <span class="uppercase text-white/50 text-xxs font-medium">
                Queries
              </span>
              <span>
                {{ filteredQueries.length }}
              </span>
            </div>
            <div class="flex gap-2 items-center">
              <span class="uppercase text-white/50 text-xxs font-medium">
                Duration
              </span>
              <span>
                {{ Math.round(data.queries?.totalTime) }}ms
              </span>
            </div>
            <div class="flex gap-2 items-center">
              <span class="uppercase text-white/50 text-xxs font-medium">
                Connection
              </span>
              <span>
                {{ data.queries?.connections.join(', ') }}
              </span>
            </div>
            <div class="flex gap-2 items-center">
              <span class="uppercase text-white/50 text-xxs font-medium">
                Driver
              </span>
              <span>
                {{ data.queries?.drivers.join(', ') }}
              </span>
            </div>
            <div class="flex gap-2 items-center">
              <span class="uppercase text-white/50 text-xxs font-medium">
                Database
              </span>
              <span>
                {{ data.queries?.databases.join(', ') }}
              </span>
            </div>
          </div>
          <div>
            <input placeholder="Search" class="px-3 min-w-64 placeholder-white/40 py-2 rounded-md border border-white/10 bg-white/3  text-white/50 " type="text" v-model="searchPhrase" />
          </div>
      </div>
      <div class="w-full h-2"></div>
      <div class="relative">
        <table class="w-full text-left table-fixed mt-0 relative">
            <thead v-if="filteredQueries.length > 0">
              <tr>
                <th class="uppercase text-[#A3A3A3] my-0.5 w-[60%] sticky top-0 z-10">
                  <div class="pb-0.5">
                  <div class="bg-white/3 border-y border-l border-white/7.5 rounded-l-md px-3 py-2">
                    Query
                  </div>
                  </div>
                </th>
                <th class="uppercase text-[#A3A3A3] text-right my-0.5 w-[10%] sticky top-0 z-10 ">
                  <div class="pb-0.5">
                  <div class="bg-white/3 border-y  border-white/7.5  px-3 py-2">
                    Duration
                  </div>
                  </div>
                </th>
                <th class="uppercase text-[#A3A3A3] my-0.5 w-[10%] text-center sticky top-0 z-10 shadow-3xl shadow-black">
                  <div class="pb-0.5">
                  <div class="bg-white/3 border-y  border-white/7.5 px-3 py-2">
                    Issue
                  </div>
                  </div>
                </th>
                <th class="uppercase text-[#A3A3A3] my-0.5 w-[20%] sticky top-0 z-10 shadow-3xl shadow-black">
                  <div class="pb-0.5">
                  <div class="bg-white/3 border-y border-r border-white/7.5 rounded-r-md px-3 py-2">
                    Location
                  </div>
                  </div>
                </th>
              </tr>
            </thead>
        </table>
        <div ref="queriesTable" class="max-h-[190px] overflow-y-auto relative rounded-b-lg pb-3">
          <table ref="queriesTableInner" class="w-full text-left table-fixed mt-0 relative">
            <tbody>
              <template v-if="filteredQueries.length === 0">
                <tr>
                  <td colspan="4" class="text-center text-white/50">
                    <div class="flex justify-center items-center flex-col gap-4 py-16">
                      <EmptyListIcon class="w-24" />
                      <span class="text-white/75">No {{ filter }} queries found</span>
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-else v-for="(query, index) in filteredQueries" :key="index" class="group relative">
                <td class="w-[60%]">
                  <div class="w-[calc(100%-20px)] h-[1px] absolute left-2.5 bottom-[2px]">
                    <div :nonce="data.csp_nonce" class="h-full absolute bg-[#9684FF]/50" :style="{ width: `${query.percentage * 100}%`, left: `${query.offset * 100}%` }"></div>
                  </div>
                  <div class=" py-0.5">
                    <div class="overflow-hidden text-ellipsis border-y border-l rounded-l-md px-3 py-2" :class="{
                      'bg-yellow-400/6 border-yellow-400/10 group-hover:bg-yellow-400/10 text-yellow-100': query.isDuplicate,
                      'bg-cyan-400/8 border-cyan-400/15 group-hover:bg-cyan-400/10 text-cyan-100': query.isSlow,
                      'bg-white/3 group-hover:bg-white/5 border-white/7.5': !query.isDuplicate && !query.isSlow
                    }">
                    <span class=" whitespace-nowrap">
                      {{ query.sql }}
                    </span>
                    </div>
                  </div>
                </td>
                <td class="text-right w-[10%]">
                  <div class="py-0.5">
                    <div class="overflow-hidden text-ellipsis border-y px-3 py-2" :class="{
                      'bg-yellow-400/6 border-yellow-400/10 group-hover:bg-yellow-400/10 text-yellow-100': query.isDuplicate,
                      'bg-cyan-400/8 border-cyan-400/15 group-hover:bg-cyan-400/10 text-cyan-100': query.isSlow,
                      'bg-white/3 group-hover:bg-white/5 border-white/7.5': !query.isDuplicate && !query.isSlow
                    }">
                    <span class=" whitespace-nowrap">
                      {{ query.duration }}ms
                    </span>
                    </div>
                  </div>
                </td>
                <td class="text-center w-[10%]">
                  <div class="py-0.5 h-10">
                    <div class=" overflow-hidden h-full text-ellipsis border-y px-3 py-2 " :class="{
                      'bg-yellow-400/6 text-yellow-100 border-yellow-400/10 group-hover:bg-yellow-400/10': query.isDuplicate,
                      'bg-cyan-400/8 text-cyan-100 border-cyan-400/15 group-hover:bg-cyan-400/10': query.isSlow,
                      'bg-white/3 group-hover:bg-white/5 border-white/7.5': !query.isDuplicate && !query.isSlow
                    }">
                      <span v-if="query.isDuplicate" class=" whitespace-nowrap uppercase tracking-wider font-bold text-xxxs bg-yellow-400/10 py-2 px-2 text-yellow-400 rounded">
                        Dupe
                      </span>
                      <span v-if="query.isSlow" class=" whitespace-nowrap uppercase tracking-wider font-bold text-xxxs bg-cyan-400/10 py-2 px-2 text-cyan-400 rounded">
                        Slow
                      </span>
                    </div>
                  </div>
                </td>
                <td class="w-[20%]">
                  <div class="py-0.5 h-10">
                    <div class="overflow-hidden h-full text-ellipsis border-y border-r rounded-r-md px-3 py-2" :class="{
                      'bg-yellow-400/6 text-yellow-100 border-yellow-400/10 group-hover:bg-yellow-400/10': query.isDuplicate,
                      'bg-cyan-400/8 text-cyan-100 border-cyan-400/15 group-hover:bg-cyan-400/10': query.isSlow,
                      'bg-white/3 group-hover:bg-white/5 border-white/7.5': !query.isDuplicate && !query.isSlow
                    }">
                    <span class=" whitespace-nowrap">
                      <a class="cursor-pointer hover:underline" :href="query.controller_action_editor_url" target="_blank">{{ query.file.split('/').pop() }}:{{ query.line }}</a>
                    </span>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Panel>

    <ToolbarItem @mouseenter="isOpen = true" @mouseleave="isOpen = false" :isActive="isOpen" class="!border-x-0 pr-[3px]">
      <div class="flex gap-1 items-center py-0.5">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4">
          <path d="M8 7c3.314 0 6-1.343 6-3s-2.686-3-6-3-6 1.343-6 3 2.686 3 6 3Z" />
          <path d="M8 8.5c1.84 0 3.579-.37 4.914-1.037A6.33 6.33 0 0 0 14 6.78V8c0 1.657-2.686 3-6 3S2 9.657 2 8V6.78c.346.273.72.5 1.087.683C4.42 8.131 6.16 8.5 8 8.5Z" />
          <path d="M8 12.5c1.84 0 3.579-.37 4.914-1.037.366-.183.74-.41 1.086-.684V12c0 1.657-2.686 3-6 3s-6-1.343-6-3v-1.22c.346.273.72.5 1.087.683C4.42 12.131 6.16 12.5 8 12.5Z" />
        </svg>

        <span>{{ data.queries?.queries.length }}<span class="text-white/50 px-0.5">:</span>{{ Math.round(data.queries?.totalTime) }}ms</span>
      </div>
    </ToolbarItem>
  </div>
</template>