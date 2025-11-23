import { ref } from 'vue';

// ✅ Initialize with data BEFORE any component mounts
const data = ref(window.__LARAVEL_TOOLBAR_DATA__);

window.addEventListener('laravel-toolbar:update', (event) => {
  if (event.detail && event.detail.data) {
    data.value = event.detail.data
  }
})

export const useToolbar = () => {
  return {
    data,
  }
}