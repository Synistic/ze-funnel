import { createApp } from 'vue'
import AdminApp from './components/AdminApp.vue'

/**
 * Initialize Ze Funnel Admin Application
 */
class ZeFunnelAdmin {
  constructor() {
    this.app = null
    this.isInitialized = false
  }

  /**
   * Initialize admin app
   */
  init() {
    const container = document.getElementById('ze-funnel-admin-app')
    
    if (!container) {
      console.warn('Ze Funnel Admin: Container not found')
      return
    }

    try {
      // Create Vue app
      this.app = createApp(AdminApp, {
        funnelId: container.dataset.funnelId || null,
        apiUrl: window.zeFunnelAdmin?.restUrl || '',
        nonce: window.zeFunnelAdmin?.nonce || ''
      })

      // Mount app
      this.app.mount(container)
      this.isInitialized = true
      
      console.log('Ze Funnel Admin: Initialized successfully')
    } catch (error) {
      console.error('Ze Funnel Admin: Initialization failed', error)
    }
  }

  /**
   * Destroy admin app
   */
  destroy() {
    if (this.app && this.isInitialized) {
      this.app.unmount()
      this.app = null
      this.isInitialized = false
    }
  }
}

// Global instance
window.ZeFunnelAdmin = new ZeFunnelAdmin()

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.ZeFunnelAdmin.init()
  })
} else {
  window.ZeFunnelAdmin.init()
}

export default ZeFunnelAdmin