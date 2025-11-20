import { ref, onMounted } from 'vue';

window.addEventListener('laravel-toolbar:update', (event) => {
  if (event.detail && event.detail.data) {
    data.value = event.detail.data
  }
})

const data = ref({});

export const useToolbar = () => {
  onMounted(() => {
    if(Object.keys(data.value).length === 0) {
      data.value = window.__LARAVEL_TOOLBAR_DATA__ || {}
    }
  })

  return {
    data,
  }
}