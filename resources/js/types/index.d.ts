/// <reference types="vite/client" />

declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

interface Window {
  __LARAVEL_TOOLBAR_DATA__: Record<string, any>
  __LARAVEL_TOOLBAR_CSS_URL__: string
}
