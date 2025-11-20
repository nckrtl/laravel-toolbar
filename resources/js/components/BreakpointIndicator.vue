<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import ToolbarItem from '@/components/ToolbarItem.vue'

const breakpoint = ref('xs')
const width = ref(0)
const breakpoints = ref({})
const showPixels = ref(true)
const isActive = ref(false)
const getHostBreakpoints = () => {
  const styles = getComputedStyle(document.documentElement)
  const allProps = Array.from(styles)

  // Filter for breakpoint properties
  const breakpointProps = allProps.filter(prop => prop.startsWith('--breakpoint-'))

  // Map to object
  return breakpointProps.reduce((acc, prop) => {
    const name = prop.substring('--breakpoint-'.length)
    const value = styles.getPropertyValue(prop).trim()
    acc[name] = value
    return acc
  }, {})
}

const checkBreakpoint = () => {
  width.value = document.documentElement.clientWidth

  const bp = getHostBreakpoints()
  breakpoints.value = bp

  // Sort breakpoints by value (largest first)
  const sorted = Object.entries(bp).sort((a, b) => {
    const aVal = parseFloat(a[1])
    const bVal = parseFloat(b[1])
    return bVal - aVal
  })

  // Check which breakpoint is active
  for (const [name, value] of sorted) {
    if (window.matchMedia(`(min-width: ${value})`).matches) {
      breakpoint.value = name
      return
    }
  }

  breakpoint.value = 'xs';
}

onMounted(() => {
  checkBreakpoint()
  window.addEventListener('resize', checkBreakpoint)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkBreakpoint)
})
</script>

<template>
  <ToolbarItem @mouseenter="isActive = true" @mouseleave="isActive = false" :isActive="isActive" class="px-0.5 rounded-full" innerPadding="px-2">
    <div class="flex items-center gap-1.5 py-0.5" @click="showPixels = !showPixels">
      <span class="uppercase">{{ breakpoint }}</span> <span v-if="showPixels" class="text-xxs leading-none opacity-50">{{ width }}px</span>
    </div>
  </ToolbarItem>
</template>
