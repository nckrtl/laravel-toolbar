<script setup>

const props = defineProps({
   class: {
      type: String,
      default: '',
   },
});
</script>

<template>
   <div :class="['rounded-full overflow-hidden flex bg-[#171717]/95', props.class]">
      <slot />
   </div>
</template>