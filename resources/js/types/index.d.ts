/// <reference types="vite/client" />

declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<object, object, unknown>
  export default component
}

declare module '*?inline' {
  const css: string
  export default css
}

export interface ToolbarData {
  metadata?: {
    debug?: boolean
    wall_time?: {
      collectors: Record<string, number>
      total: number
    }
  }
  queries?: NckRtl.Toolbar.Data.QueriesData
  profiler?: NckRtl.Toolbar.Data.ProfilerData
  request?: NckRtl.Toolbar.Data.RequestData
  response?: NckRtl.Toolbar.Data.ResponseData
  laravel?: NckRtl.Toolbar.Data.LaravelData
  php?: NckRtl.Toolbar.Data.PhpData
  vue?: NckRtl.Toolbar.Data.VueData
  [key: string]: unknown
}

export interface ToolbarUpdateEvent extends CustomEvent {
  detail: {
    data: ToolbarData
  }
}

declare global {
  interface Window {
    __LARAVEL_TOOLBAR_DATA__: ToolbarData
    __LARAVEL_TOOLBAR_CSS_URL__: string
    __TOOLBAR_SHADOW_PRECREATED__?: ShadowRoot
    __TOOLBAR_STYLESHEET__?: CSSStyleSheet
  }

  interface WindowEventMap {
    'laravel-toolbar:update': ToolbarUpdateEvent
  }
}
