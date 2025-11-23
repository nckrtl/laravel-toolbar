import { log, logData } from '@/core/utils/logger'

export const setupInterceptors = () => {
  log('Setting up interceptors BEFORE Inertia loads')

  setupFetchInterceptor()
  setupXHRInterceptor()

  log('Interceptors installed successfully')
}

const setupFetchInterceptor = () => {
  const originalFetch = window.fetch

  window.fetch = async function(...args) {
    const response = await originalFetch.apply(this, args)

    // Handle toolbar data if present
    handleToolbarHeader(response)

    return response
  }
}

const setupXHRInterceptor = () => {
  const originalSend = XMLHttpRequest.prototype.send

  XMLHttpRequest.prototype.send = function(...args) {
    this.addEventListener('load', function() {
      handleToolbarHeader(this)
    })

    return originalSend.apply(this, args)
  }
}

const handleToolbarHeader = (responseObject) => {
  // Handle both fetch Response and XMLHttpRequest
  const toolbarHeader = responseObject.headers
    ? responseObject.headers.get('x-toolbar')  // fetch Response
    : responseObject.getResponseHeader('x-toolbar')  // XMLHttpRequest

  if (toolbarHeader) {
    try {
      const decoded = atob(toolbarHeader)
      const newData = JSON.parse(decoded)

      log('🟢 Toolbar data updated from response')

      window.dispatchEvent(new CustomEvent('laravel-toolbar:update', {
        detail: { data: newData }
      }))

      logData()
    } catch (e) {
      log('🔴 Failed to parse toolbar data', e)
    }
  }
}