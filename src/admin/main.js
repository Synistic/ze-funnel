import { createApp } from 'vue'
import AdminApp from './components/AdminApp.vue'

/**
 * Ze Funnel Admin - Global initialization
 */
function initZeFunnelAdmin() {
  // Make sure we don't initialize twice
  if (window.ZeFunnelAdminInitialized) {
    return
  }

  const container = document.getElementById('ze-funnel-admin-app')
  
  if (!container) {
    console.warn('Ze Funnel Admin: Container not found')
    return
  }

  try {
    console.log('Ze Funnel Admin: Starting initialization...')
    
    // Create Vue app
    const app = createApp(AdminApp, {
      funnelId: container.dataset.funnelId || null,
      apiUrl: window.zeFunnelAdmin?.restUrl || '',
      nonce: window.zeFunnelAdmin?.nonce || ''
    })

    // Mount app
    app.mount(container)
    
    // Store reference globally
    window.ZeFunnelAdminApp = app
    window.ZeFunnelAdminInitialized = true
    
    console.log('Ze Funnel Admin: Initialized successfully')
  } catch (error) {
    console.error('Ze Funnel Admin: Initialization failed', error)
    
    // Show fallback message
    if (container) {
      container.innerHTML = `
        <div class="notice notice-error">
          <p><strong>Ze Funnel Admin Error:</strong> Failed to load admin interface.</p>
          <p>Please refresh the page or check the browser console for details.</p>
        </div>
      `
    }
  }
}

// Create simple API for WordPress to call
window.ZeFunnelAdmin = {
  init: initZeFunnelAdmin,
  initialized: false
}

// Auto-initialize
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initZeFunnelAdmin)
} else {
  // DOM already loaded
  initZeFunnelAdmin()
}