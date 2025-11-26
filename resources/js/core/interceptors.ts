import { log, logData } from '@/core/utils/logger'
import type { ToolbarData } from '@/types'

type ResponseLike = Response | XMLHttpRequest

export const setupInterceptors = (): void => {
  log('Setting up interceptors BEFORE Inertia loads')

  setupFetchInterceptor()
  setupXHRInterceptor()

  log('Interceptors installed successfully')
}

const setupFetchInterceptor = (): void => {
  const originalFetch = window.fetch

  window.fetch = async function (...args: Parameters<typeof fetch>): Promise<Response> {
    const response = await originalFetch.apply(this, args)

    // Handle toolbar data if present
    handleToolbarHeader(response)

    return response
  }
}

const setupXHRInterceptor = (): void => {
  const originalSend = XMLHttpRequest.prototype.send

  XMLHttpRequest.prototype.send = function (
    this: XMLHttpRequest,
    ...args: Parameters<XMLHttpRequest['send']>
  ): void {
    this.addEventListener('load', function (this: XMLHttpRequest) {
      handleToolbarHeader(this)
    })

    return originalSend.apply(this, args)
  }
}

const handleToolbarHeader = (responseObject: ResponseLike): void => {
  // Handle both fetch Response and XMLHttpRequest
  const toolbarHeader =
    responseObject instanceof Response
      ? responseObject.headers.get('x-toolbar')
      : responseObject.getResponseHeader('x-toolbar')

  if (toolbarHeader) {
    try {
      const decoded = atob(toolbarHeader)
      const newData = JSON.parse(decoded) as ToolbarData

      log('🟢 Toolbar data updated from response')

      window.dispatchEvent(
        new CustomEvent('laravel-toolbar:update', {
          detail: { data: newData },
        })
      )

      logData()
    } catch (e) {
      log('🔴 Failed to parse toolbar data', e)
    }
  }
}
