import { initToolbar } from '@/core/base'
import { log } from '@/core/utils/logger'
import { setAdoptedStyleSheet, loadInitialCSS, setupHMR } from '@/core/utils/hmr'
import {
  guardMount,
  setupShadowDOM,
  mountVueApp,
  cleanupFailedMount,
  mountSuccess
} from '@/core/mount.base'

export const mountToolbar = async () => {
  if (!guardMount()) return

  setupHMR()

  log('🚀 mountToolbar() called (DEV)')

  try {
    const { shadowRoot, appContainer } = setupShadowDOM()

    await setupStyles(shadowRoot)

    mountVueApp(appContainer)
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

initToolbar(mountToolbar);