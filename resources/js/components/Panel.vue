<script setup>
const props = defineProps({
  size: {
    type: String,
    default: 'sm'
  },
  minHeight: {
    type: String,
    default: 'auto'
  },
  align: {
    type: String,
    default: 'left'
  }
})
</script>

<template>
  <div  class="absolute p-2" :class="[{
    'min-w-xxs max-w-xxs w-full bottom-[34px]': size === 'xxs',
    'min-w-xs max-w-xs w-full bottom-[34px]': size === 'xs',
    'min-w-sm max-w-sm w-full bottom-[34px]': size === 'sm',
    'min-w-md max-w-md w-full bottom-[34px]': size === 'md',
    'w-[calc(100%+8px)] bottom-[34px] left-1': size === 'full',
    'translate-x-2': align === 'right',
    '-translate-x-2': align === 'left',
  }, $attrs.class]">
    <div class="bg-[#111111]/95 backdrop-blur-xl border border-white/10 rounded-2xl p-2 text-white text-xxs" :class="[minHeight]">
      <slot />
    </div>
  </div>
</template>