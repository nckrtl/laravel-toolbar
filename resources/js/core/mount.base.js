import { createApp } from 'vue'
import Toolbar from '@/Toolbar.vue'
import { log, logData } from '@/core/utils/logger'

let isToolbarMounted = false
let shadowRootRef = null

export function setShadowRoot(shadowRoot) {
  log('Storing shadowRoot reference')
  shadowRootRef = shadowRoot
}

export function getShadowRoot() {
  return shadowRootRef
}

export function setupShadowDOM() {
  log('📦 Setting up Shadow DOM')

  const shadowHost = document.getElementById('laravel-toolbar-shadow-host')
  if (!shadowHost) {
    throw new Error('Shadow host not found - toolbar HTML not injected?')
  }

  // Check if shadow was already created (from cache or template)
  let shadowRoot = shadowHost.shadowRoot

  if (!shadowRoot) {
    // No cache, no template - create fresh
    log('Creating fresh Shadow DOM (first visit)')
    shadowRoot = shadowHost.attachShadow({ mode: 'open' })

    // Create the root container
    const rootDiv = document.createElement('div')
    rootDiv.id = 'laravel-toolbar-root'
    shadowRoot.appendChild(rootDiv)
  } else {
    log('Shadow DOM already exists (from cache)')
  }

  setShadowRoot(shadowRoot)

  const appContainer = shadowRoot.getElementById('laravel-toolbar-root')
  if (!appContainer) {
    throw new Error('Toolbar root not found in shadow DOM')
  }

  log('✅ Shadow DOM ready')
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