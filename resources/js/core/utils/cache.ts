// cache.ts - HTML and CSS shell caching utilities

const HTML_KEY = 'laravel-toolbar-html-cache'
const CSS_KEY = 'laravel-toolbar-css-cache'

/**
 * Save toolbar HTML and CSS to sessionStorage before page unload
 */
export function setupCacheSaving(shadowRoot: ShadowRoot): void {
  const saveCache = (): void => {
    const toolbarRoot = shadowRoot.getElementById('laravel-toolbar-root')
    if (toolbarRoot) {
      try {
        sessionStorage.setItem(HTML_KEY, toolbarRoot.innerHTML)

        const sheet = shadowRoot.adoptedStyleSheets[0]
        if (sheet) {
          const rules = Array.from(sheet.cssRules || [])
          const cssText = rules.map((rule) => rule.cssText).join('\n')
          if (cssText) {
            sessionStorage.setItem(CSS_KEY, cssText)
          }
        }
      } catch (e) {
        console.error('❌ Error saving cache:', e)
      }
    }
  }

  // Save after a delay to ensure everything is loaded
  setTimeout(saveCache, 2000)

  window.addEventListener('beforeunload', saveCache)
}

/**
 * Restore cached HTML into the toolbar root (if available)
 * Returns true if cache was restored
 */
export function restoreCachedHtml(appContainer: HTMLElement): boolean {
  const cached = sessionStorage.getItem(HTML_KEY)
  if (cached) {
    appContainer.innerHTML = cached
    return true
  }
  return false
}

/**
 * Clear the cached HTML and CSS
 */
export function clearCache(): void {
  sessionStorage.removeItem(HTML_KEY)
  sessionStorage.removeItem(CSS_KEY)
}
