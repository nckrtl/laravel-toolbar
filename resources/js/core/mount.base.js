import { createApp } from 'vue'
import Toolbar from '@/Toolbar.vue'
import { log, logData } from '@/core/utils/logger'
import { setShadowRoot } from '@/core/utils/hmr'

let isToolbarMounted = false

export function setupShadowDOM() {
  log('📦 Setting up Shadow DOM from template')

  const shadowHost = document.getElementById('laravel-toolbar-shadow-host')
  if (!shadowHost) {
    throw new Error('Shadow host not found - toolbar HTML not injected?')
  }

  const template = document.getElementById('laravel-toolbar-template')
  if (!template) {
    throw new Error('Toolbar template not found')
  }

  // Attach shadow root and clone template content into it
  const shadowRoot = shadowHost.attachShadow({ mode: 'open' })
  shadowRoot.appendChild(template.content.cloneNode(true))

  setShadowRoot(shadowRoot)

  const appContainer = shadowRoot.getElementById('laravel-toolbar-root')
  if (!appContainer) {
    throw new Error('Toolbar root not found in template')
  }

  log('✅ Shadow DOM created from template (CSS already present)')
  return { shadowRoot, appContainer }
}

export function mountVueApp(appContainer) {
  log('🎯 Mounting Vue app')

  const app = createApp(Toolbar)

  app.config.errorHandler = (err, instance, info) => {
    log('❌ Vue error:', err)
    log('Component:', instance)
    log('Error info:', info)
  }

  app.mount(appContainer)
  log('✅ Vue app mounted in Shadow DOM')
}

export function cleanupFailedMount() {
  log('🧹 Cleaning up after failed mount')

  const shadowHost = document.getElementById('laravel-toolbar-shadow-host')
  if (shadowHost) {
    shadowHost.remove()
    log('Removed shadow host from DOM')
  }

  isToolbarMounted = false
}

export function guardMount() {
  if (isToolbarMounted) {
    log('⚠️ Toolbar already mounted, skipping')
    return false
  }
  isToolbarMounted = true
  return true
}

export function mountSuccess() {
  log('✅ Toolbar mounted successfully')
  logData()
}