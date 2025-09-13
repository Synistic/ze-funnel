import FunnelApp from './components/FunnelApp.svelte'
import './styles/main.css'

/**
 * Initialize Ze Funnel frontend application
 */
class ZeFunnelInit {
  constructor() {
    this.apps = new Map()
    this.init()
  }

  init() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.mountFunnels())
    } else {
      this.mountFunnels()
    }
  }

  /**
   * Mount all funnel instances on the page
   */
  mountFunnels() {
    const containers = document.querySelectorAll('.ze-funnel-container')

    containers.forEach(container => {
      this.mountFunnel(container)
    })
  }

  /**
   * Mount a single funnel instance
   */
  mountFunnel(container) {
    const funnelId = container.dataset.funnelId
    const dataScript = container.querySelector('script[type="application/json"]')

    if (!dataScript) {
      console.error('Ze Funnel: No data found for funnel', funnelId)
      return
    }

    let funnelData
    try {
      funnelData = JSON.parse(dataScript.textContent)
    } catch (error) {
      console.error('Ze Funnel: Invalid JSON data', error)
      return
    }

    // Find the app container
    const appContainer = container.querySelector('.ze-funnel-app')
    if (!appContainer) {
      console.error('Ze Funnel: App container not found')
      return
    }

    try {
      // Create Svelte app instance
      const app = new FunnelApp({
        target: appContainer,
        props: {
          funnelData: funnelData,
          containerId: container.id
        }
      })

      // Store app instance for potential cleanup
      this.apps.set(funnelId, app)

      console.log(`Ze Funnel: Mounted funnel ${funnelId}`)
    } catch (error) {
      console.error('Ze Funnel: Failed to mount app', error)
      this.showError(appContainer, 'Failed to load funnel. Please refresh the page.')
    }
  }

  /**
   * Show error message
   */
  showError(container, message) {
    container.innerHTML = `
      <div class="ze-funnel-error">
        <p>${message}</p>
      </div>
    `
  }

  /**
   * Destroy all funnel instances
   */
  destroy() {
    this.apps.forEach(app => {
      if (app && typeof app.$destroy === 'function') {
        app.$destroy()
      }
    })
    this.apps.clear()
  }
}

// Global initialization
window.zeFunnelInit = new ZeFunnelInit()

// Export for potential manual usage
export default ZeFunnelInit