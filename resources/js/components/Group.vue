<script setup>

const props = defineProps({
   class: {
      type: String,
      default: '',
   },
});
</script>

<template>
   <div :class="['rounded-full overflow-hidden flex', props.class]">
      <slot />
   </div>
</template>