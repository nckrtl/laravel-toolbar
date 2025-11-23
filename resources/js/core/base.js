import { setupInterceptors } from '@/core/interceptors'

// Setup interceptors first (shared between dev and prod)
setupInterceptors()

/**
 * Shared mount orchestration
 * Import the appropriate mount function (dev or prod) and call this
 */
export async function initToolbar(mountFn) {
  try {
    await mountFn()
  } catch (error) {
    console.error('[Laravel Toolbar] Failed to mount:', error)
    throw error
  }
}