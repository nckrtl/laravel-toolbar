import { log } from '@/core/utils/logger'
import toolbarCSS from '/resources/css/toolbar.css?inline'

interface HMRState {
  adoptedStyleSheet: CSSStyleSheet | null
}

interface HMRData {
  adoptedStyleSheet?: CSSStyleSheet | null
}

// Use HMR data to persist state across reloads
const getState = (): HMRState => {
  if (import.meta.hot?.data) {
    log('Retrieving saved state from HMR data')
    return import.meta.hot.data as HMRState
  }
  log('Creating new HMR state')
  return { adoptedStyleSheet: null }
}

const state = getState()

const updateCSS = async (): Promise<void> => {
  log('Updating CSS...')

  if (!state.adoptedStyleSheet) {
    log('adoptedStyleSheet not ready yet')
    return
  }

  try {
    const timestamp = Date.now()
    const newModule = (await import(
      /* @vite-ignore */ `/resources/css/toolbar.css?inline&t=${timestamp}`
    )) as { default: string }
    const newCssText = newModule.default

    const oldRules = state.adoptedStyleSheet.cssRules.length
    await state.adoptedStyleSheet.replace(newCssText)
    const newRules = state.adoptedStyleSheet.cssRules.length

    log(`Adopted stylesheet updated: ${oldRules} -> ${newRules} rules`)
  } catch (error) {
    log('Failed to update CSS', error)
  }
}

export const loadInitialCSS = (): void => {
  log('Loading initial CSS for dev mode')

  if (state.adoptedStyleSheet) {
    state.adoptedStyleSheet.replace(toolbarCSS)
    log(`Initial CSS loaded: ${toolbarCSS.length} chars`)
  }
}

export const setupHMR = (): void => {
  if (!import.meta.hot) {
    log('Not in dev mode, skipping HMR setup')
    return
  }

  log('Setting up HMR handlers')

  // Persist state across HMR reloads
  import.meta.hot.dispose((data: HMRData) => {
    log('Disposing HMR - saving state')
    data.adoptedStyleSheet = state.adoptedStyleSheet
  })

  // Accept CSS changes (direct edits to toolbar.css)
  import.meta.hot.accept('/resources/css/toolbar.css?inline', async () => {
    log('CSS file directly edited')
    await updateCSS()
  })

  import.meta.hot.on('vite:beforeUpdate', async (payload: { updates: Array<{ path: string }> }) => {
    const vueUpdate = payload.updates.some((u) => u.path.includes('.vue'))
    if (vueUpdate) {
      log('🔥 Vue file changed, updating CSS')
      await updateCSS()
    }
  })

  import.meta.hot.accept(() => {
    log('hmr.ts module reloaded')
  })

  log('HMR handlers registered')
}

export const setAdoptedStyleSheet = (sheet: CSSStyleSheet): void => {
  log('Storing adoptedStyleSheet reference')
  state.adoptedStyleSheet = sheet
}

setupHMR()
