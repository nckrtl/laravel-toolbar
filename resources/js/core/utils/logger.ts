export const log = (message: string, data: unknown = {}): void => {
  if (import.meta.env.VITE_DEBUG === 'true') {
    console.log(`Toolbar: ${message}`, data)
  }
}

export const logData = (data): void => {
  if (data?.metadata?.debug) {
    console.log('Data available:', data)
  } else {
    log('Data available:', data)
  }
}
