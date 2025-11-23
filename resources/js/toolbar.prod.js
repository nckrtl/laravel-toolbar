import '../css/toolbar.css'
import { initToolbar } from '@/core/base'
import { log } from '@/core/utils/logger'
import {
  guardMount,
  setupShadowDOM,
  mountVueApp,
  cleanupFailedMount,
  mountSuccess
} from '@/core/mount.base'

const mountToolbar = async () => {
  if (!guardMount()) return

  log('🚀 mountToolbar() called (PROD)')

  try {
    // Shadow DOM setup includes CSS from template
    const { appContainer } = setupShadowDOM()

    log('✅ Styles already present in Shadow DOM (from template)')

    // Mount Vue immediately
    mountVueApp(appContainer)
    mountSuccess()
  } catch (error) {
    log('❌ Failed to mount toolbar:', error)
    cleanupFailedMount()
    throw error
  }
}

initToolbar(mountToolbar);