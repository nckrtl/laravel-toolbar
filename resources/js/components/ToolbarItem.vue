<script setup>
const props = defineProps({
  isActive: {
    type: Boolean,
    default: false
  },
  class: {
    type: String,
    default: 'px-2'
  },
  innerPadding: {
    type: String,
    default: 'px-2'
  }
})
</script>

<template>
  <div class="relative bg-[#171717]/95 py-[2px] backdrop-blur-sm text-white border border-white/10 text-xxs" :class="[props.class]">
    <div class="relative rounded-full p-[1px] cursor-default" :class="{ 'bg-gradient-to-tr from-white/50 via-white/15 to-white/35': isActive }" >
      <div class="p-0.5 flex items-center gap-2 rounded-full" :class="[innerPadding, { 'bg-[#353535] shadow-gray-900/50 shadow-md': isActive }]">
        <slot />
      </div>
    </div>
  </div>
</template>