import '../css/toolbar.css'
import { initToolbar } from '@/core/base'
import { log } from '@/core/utils/logger'
import {
  guardMount,
  setupShadowDOM,
  mountVueApp,
  cleanupFailedMount,
  mountSuccess,
  setShadowRoot,
} from '@/core/mount.base'
import { setupCacheSaving } from '@/core/utils/cache'

const mountToolbar = async (): Promise<void> => {
  if (!guardMount()) return

  log('🚀 mountToolbar() called (PROD)')

  try {
    let shadowRoot: ShadowRoot
    let appContainer: HTMLElement

    if (window.__TOOLBAR_SHADOW_PRECREATED__) {
      log('⚡ Using pre-created shadow DOM from cache')
      shadowRoot = window.__TOOLBAR_SHADOW_PRECREATED__
      const container = shadowRoot.getElementById('laravel-toolbar-root')
      if (!container) {
        throw new Error('Toolbar root not found in pre-created shadow DOM')
      }
      appContainer = container
      setShadowRoot(shadowRoot)
    } else {
      const result = setupShadowDOM()
      shadowRoot = result.shadowRoot
      appContainer = result.appContainer

      // First visit - need to load CSS
      await loadProductionCSS(shadowRoot)
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

async function loadProductionCSS(shadowRoot: ShadowRoot): Promise<void> {
  const cssUrl = window.__LARAVEL_TOOLBAR_CSS_URL__
  if (!cssUrl) return

  const response = await fetch(cssUrl)
  const css = await response.text()
  const sheet = new CSSStyleSheet()
  sheet.replaceSync(css)
  shadowRoot.adoptedStyleSheets = [sheet]
}

initToolbar(mountToolbar)
