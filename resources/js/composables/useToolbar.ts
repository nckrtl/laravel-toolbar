import { ref, type Ref } from 'vue'
import type { ToolbarData, ToolbarUpdateEvent } from '@/types'

// ✅ Initialize with data BEFORE any component mounts
const data: Ref<ToolbarData> = ref(window.__LARAVEL_TOOLBAR_DATA__)

window.addEventListener('laravel-toolbar:update', (event: ToolbarUpdateEvent) => {
  if (event.detail?.data) {
    data.value = event.detail.data
  }
})

export const useToolbar = (): { data: Ref<ToolbarData> } => {
  return {
    data,
  }
}
