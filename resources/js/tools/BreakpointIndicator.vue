<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import ToolbarItem from '@/components/ToolbarItem.vue'

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

const breakpoint = ref('xs')
const width = ref(0)
const breakpoints = ref({})
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

  const sorted = Object.entries(bp).sort((a, b) => {
    return toPixels(b[1]) - toPixels(a[1])
  })

  // Check which breakpoint is active
  for (const [name, value] of sorted) {
    if (window.matchMedia(`(min-width: ${value})`).matches) {
      breakpoint.value = name
      return
    }
  }

  breakpoint.value = sorted[sorted.length - 1][0];
}

const toPixels = (value) => {
  const num = parseFloat(value)
  if (value.endsWith('rem')) {
    return num * 16 // assuming 1rem = 16px
  }
  return num
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
  <ToolbarItem @mouseenter="isActive = true" @mouseleave="isActive = false" :isActive="isActive" :class="itemClasses">
    <div class="flex items-center gap-1.5 py-0.5" @click="config.show_pixels = !config.show_pixels">
      <span class="uppercase">{{ breakpoint }}</span> <span v-if="config.show_pixels" class="text-xxs leading-none opacity-50">{{ width }}px</span>
    </div>
  </ToolbarItem>
</template>
