<script setup>

</script>

<template>
  <div class="flex justify-between items-end w-full px-1 py-0.5">
    <div class="flex items-center gap-2 uppercase pr-2 text-white/70">
      <slot name="label" />
    </div>
    <div class="px-2 min-w-6 grow mb-1 border-b-2 border-dotted border-white/20"></div>
    <div class="pl-2 overflow-hidden text-ellipsis">
      <slot name="value" />
    </div>
  </div>
</template>