export const log = (message, data = {}) => {
  if (import.meta.env.VITE_DEBUG === 'true') {
    console.log(`Toolbar: ${message}`, data)
  }
}

export const logData = () => {
  if (window.__LARAVEL_TOOLBAR_DATA__?.metadata?.debug) {
    console.log('Data available:', window.__LARAVEL_TOOLBAR_DATA__)
  } else {
    log('Data available:', window.__LARAVEL_TOOLBAR_DATA__)
  }
}