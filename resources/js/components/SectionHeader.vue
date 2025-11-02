<script setup>
const props = defineProps({
  iconClass: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div class="flex justify-between w-full items-center">
    <div class="flex items-center gap-3 p-1.5">
      <div class="relative border border-white/8 bg-white/3 rounded-md min-w-7 h-7  flex items-center justify-center shadow-lg shadow-black/20" :class="iconClass">
          <slot name="icon" />
      </div>
      <slot name="label" />
    </div>
    <div class="px-3 opacity-50">
      <slot name="secondaryLabel" />
    </div>
  </div>
</template>