import { createApp } from 'vue'
import Toolbar from './Toolbar.vue'
import '../css/toolbar.css'

const log = (message, data = {}) => {
  if (import.meta.env.VITE_DEBUG && import.meta.env.VITE_DEBUG === 'true') {
    console.log(`Toolbar: ${message}`, data)
  }
}

const logData = () => {
  if(window.__LARAVEL_TOOLBAR_DATA__.metadata?.debug) {
    console.log('Data available:', window.__LARAVEL_TOOLBAR_DATA__)
  }else{
    log('Data available:', window.__LARAVEL_TOOLBAR_DATA__)
  }
}

// Set up interceptors IMMEDIATELY, before anything else loads
log('Setting up interceptors BEFORE Inertia loads')

// Intercept fetch requests (modern Inertia uses fetch)
const originalFetch = window.fetch
window.fetch = async function(...args) {
  log('Fetch intercepted', {
    url: args[0],
    options: args[1],
    hasOptions: !!args[1],
    hasHeaders: !!args[1]?.headers
  })

  const response = await originalFetch.apply(this, args)

  // Check if this is an Inertia request - Inertia sets headers on the request
  const requestHeaders = args[1]?.headers

  // Log ALL response headers to see what we're getting
  const responseHeaders = {}
  response.headers.forEach((value, key) => {
    responseHeaders[key] = value
  })
  log('🔵 Response headers:', responseHeaders)

  // Check various ways headers might be structured
  let isInertia = false
  if (requestHeaders) {
    log('🔵 Request headers type:', requestHeaders.constructor.name)
    if (requestHeaders instanceof Headers) {
      isInertia = requestHeaders.get('X-Inertia') === 'true'
      log('🔵 Headers object - X-Inertia:', requestHeaders.get('X-Inertia'))
    } else if (typeof requestHeaders === 'object') {
      isInertia = requestHeaders['X-Inertia'] === 'true' ||
                  requestHeaders['x-inertia'] === 'true'
      log('🔵 Plain object - X-Inertia:', requestHeaders['X-Inertia'] || requestHeaders['x-inertia'])
    }
  } else {
    log('🔵 No request headers found')
  }

  log('🔵 Is Inertia request?', isInertia)

  // ALWAYS check for debug header, even if we don't detect Inertia
  // This will help us see if the header is being sent
  const toolbarHeader = response.headers.get('x-toolbar')
  log('🔵 Debug header present?', !!toolbarHeader, toolbarHeader ? `(length: ${toolbarHeader.length})` : '')

  if (toolbarHeader) {
    try {
      const decoded = atob(toolbarHeader)
      const newData = JSON.parse(decoded)
      log('🟢 Updated data from fetch response', newData)

      // Dispatch custom event with new data
      window.dispatchEvent(new CustomEvent('laravel-toolbar:update', {
        detail: { data: newData }
      }))

      logData();
    } catch (e) {
      console.error('🔴 Failed to parse data from fetch', e)
    }
  }

  return response
}

// Also intercept XMLHttpRequest (older Inertia versions)
const originalOpen = XMLHttpRequest.prototype.open
const originalSend = XMLHttpRequest.prototype.send
const originalSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader

XMLHttpRequest.prototype.open = function(method, url, ...rest) {
  log('XHR opened', { method, url })
  this._url = url
  this._method = method
  this._requestHeaders = {}
  return originalOpen.call(this, method, url, ...rest)
}

XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
  if (!this._requestHeaders) {
    this._requestHeaders = {}
  }
  this._requestHeaders[header] = value
  return originalSetRequestHeader.call(this, header, value)
}

XMLHttpRequest.prototype.send = function(...args) {
  log('🟡 XHR send', {
    url: this._url,
    headers: this._requestHeaders
  })

  this.addEventListener('load', function() {
    const isInertia = this._requestHeaders &&
                      (this._requestHeaders['X-Inertia'] === 'true' ||
                       this._requestHeaders['x-inertia'] === 'true')

    log('🟡 XHR load event', {
      isInertia,
      headers: this._requestHeaders
    })

    // ALWAYS check for debug header
    const toolbarHeader = this.getResponseHeader('x-toolbar')
    log('🟡 Debug header toolbar present?', !!toolbarHeader, toolbarHeader ? `(length: ${toolbarHeader.length})` : '')

    if (toolbarHeader) {
      try {
        const decoded = atob(toolbarHeader)
        const newData = JSON.parse(decoded)
        log('🟢 Updated data from XHR response', newData)

        // Dispatch custom event with new data
        window.dispatchEvent(new CustomEvent('laravel-toolbar:update', {
          detail: { data: newData }
        }))

        logData();
      } catch (e) {
        console.error('🔴 Failed to parse data from XHR', e)
      }
    }
  })

  return originalSend.apply(this, args)
}

log('Interceptors installed successfully')

// Create and mount the debugbar app
const mountToolbar = () => {
  log('Attempting to mount...')
  const root = document.getElementById('laravel-toolbar-root')

  if (!root) {
    console.error('Root element not found')
    return
  }

  log('Root element found, mounting Vue app')

  logData();

  const app = createApp(Toolbar)
  app.mount(root)

  log('Vue app mounted successfully')
}

// Mount when DOM is ready
if (document.readyState === 'loading') {
  log('Waiting for DOMContentLoaded...')
  document.addEventListener('DOMContentLoaded', mountToolbar)
} else {
  log('DOM already loaded, mounting immediately')
  mountToolbar()
}
