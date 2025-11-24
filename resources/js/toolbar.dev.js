import { initToolbar } from '@/core/base'
import { log } from '@/core/utils/logger'
import { setAdoptedStyleSheet, loadInitialCSS, setupHMR } from '@/core/utils/hmr'
import {
  guardMount,
  setupShadowDOM,
  mountVueApp,
  cleanupFailedMount,
  mountSuccess,
  setShadowRoot
} from '@/core/mount.base'
import { setupCacheSaving } from '@/core/utils/cache'

export const mountToolbar = async () => {
  if (!guardMount()) return

  setupHMR()

  log('🚀 mountToolbar() called (DEV)')

  try {
    let shadowRoot, appContainer

    // Check if shadow was pre-created from cache
    if (window.__TOOLBAR_SHADOW_PRECREATED__) {
      log('⚡ Using pre-created shadow DOM from cache')
      shadowRoot = window.__TOOLBAR_SHADOW_PRECREATED__
      appContainer = shadowRoot.getElementById('laravel-toolbar-root')
      setShadowRoot(shadowRoot)

      // Reuse the cached stylesheet for HMR
      if (window.__TOOLBAR_STYLESHEET__) {
        setAdoptedStyleSheet(window.__TOOLBAR_STYLESHEET__)
      }
    } else {
      // First visit - no cache
      const result = setupShadowDOM()
      shadowRoot = result.shadowRoot
      appContainer = result.appContainer

      await setupStyles(shadowRoot)
    }

    mountVueApp(appContainer)
    setupCacheSaving(shadowRoot)
    mountSuccess()
  } catch (error) {
    log('❌ Failed to mount toolbar:', error)
    cleanupFailedMount()
    throw error
  }
}

async function setupStyles(shadowRoot) {
  log('🎨 Setting up HMR styles (Development mode)')

  const sheet = new CSSStyleSheet()
  shadowRoot.adoptedStyleSheets = [sheet]
  setAdoptedStyleSheet(sheet)

  await loadInitialCSS()

  log('✅ HMR styles loaded')
}

initToolbar(mountToolbar)