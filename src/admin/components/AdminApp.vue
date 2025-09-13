<template>
  <div class="ze-admin-app">
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>{{ loading ? loadingText : 'Loading...' }}</p>
    </div>

    <div v-else-if="error" class="error-state">
      <div class="notice notice-error">
        <p><strong>Error:</strong> {{ error }}</p>
        <button @click="retry" class="button button-secondary">
          Try Again
        </button>
      </div>
    </div>

    <div v-else class="admin-content">
      <!-- Funnel Builder will be implemented here -->
      <div class="placeholder-content">
        <div class="funnel-header">
          <h2 v-if="isEditMode">Edit Funnel: {{ funnelData?.name }}</h2>
          <h2 v-else>Create New Funnel</h2>
        </div>

        <div class="funnel-form">
          <div class="form-section">
            <label for="funnel-name">Funnel Name</label>
            <input 
              id="funnel-name"
              v-model="funnelData.name"
              type="text" 
              class="regular-text"
              placeholder="Enter funnel name..."
            />
          </div>

          <div class="form-section">
            <label for="funnel-description">Description</label>
            <textarea 
              id="funnel-description"
              v-model="funnelData.description"
              rows="3"
              class="large-text"
              placeholder="Describe your funnel..."
            ></textarea>
          </div>

          <div class="form-section">
            <label for="funnel-status">Status</label>
            <select id="funnel-status" v-model="funnelData.status">
              <option value="draft">Draft</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="form-actions">
            <button @click="saveFunnel" :disabled="isSaving" class="button button-primary">
              <span v-if="isSaving">Saving...</span>
              <span v-else>{{ isEditMode ? 'Update' : 'Create' }} Funnel</span>
            </button>
            
            <a :href="cancelUrl" class="button button-secondary">
              Cancel
            </a>
          </div>
        </div>

        <!-- Future: Question Builder Component -->
        <div class="questions-section">
          <h3>Questions (Coming Soon)</h3>
          <p class="description">
            The drag & drop question builder will be implemented in the next version.
            For now, you can create basic funnels and add questions via the database.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'AdminApp',
  props: {
    funnelId: {
      type: String,
      default: null
    },
    apiUrl: {
      type: String,
      required: true
    },
    nonce: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const isLoading = ref(true)
    const isSaving = ref(false)
    const error = ref(null)
    const loadingText = ref('Loading funnel builder...')
    
    const funnelData = ref({
      name: '',
      description: '',
      status: 'draft'
    })

    const isEditMode = computed(() => props.funnelId && props.funnelId !== '0')
    const cancelUrl = computed(() => {
      const adminUrl = window.location.origin + '/wp-admin/admin.php?page=ze-funnel'
      return adminUrl
    })

    /**
     * Load funnel data if editing
     */
    const loadFunnel = async () => {
      if (!isEditMode.value) {
        isLoading.value = false
        return
      }

      try {
        loadingText.value = 'Loading funnel data...'
        
        const response = await fetch(`${props.apiUrl}funnel/${props.funnelId}`, {
          headers: {
            'X-WP-Nonce': props.nonce
          }
        })

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`)
        }

        const data = await response.json()
        
        funnelData.value = {
          name: data.name || '',
          description: data.description || '',
          status: data.status || 'draft'
        }

      } catch (err) {
        console.error('Failed to load funnel:', err)
        error.value = 'Failed to load funnel data. Please refresh and try again.'
      } finally {
        isLoading.value = false
      }
    }

    /**
     * Save funnel
     */
    const saveFunnel = async () => {
      if (!funnelData.value.name.trim()) {
        alert('Please enter a funnel name')
        return
      }

      isSaving.value = true
      error.value = null

      try {
        const url = isEditMode.value 
          ? `${props.apiUrl}admin/funnel/${props.funnelId}`
          : `${props.apiUrl}admin/funnel`
        
        const method = isEditMode.value ? 'PUT' : 'POST'

        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': props.nonce
          },
          body: JSON.stringify(funnelData.value)
        })

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`)
        }

        const result = await response.json()
        
        // Redirect to main page or stay for editing
        if (!isEditMode.value && result.id) {
          // Redirect to edit the new funnel
          window.location.href = `${cancelUrl.value}-create&edit=${result.id}`
        } else {
          // Show success message
          const notice = document.createElement('div')
          notice.className = 'notice notice-success is-dismissible'
          notice.innerHTML = '<p>Funnel saved successfully!</p>'
          
          const wrap = document.querySelector('.wrap')
          if (wrap) {
            wrap.insertBefore(notice, wrap.firstChild)
          }
        }

      } catch (err) {
        console.error('Failed to save funnel:', err)
        error.value = 'Failed to save funnel. Please try again.'
      } finally {
        isSaving.value = false
      }
    }

    /**
     * Retry loading
     */
    const retry = () => {
      error.value = null
      isLoading.value = true
      loadFunnel()
    }

    onMounted(() => {
      loadFunnel()
    })

    return {
      isLoading,
      isSaving,
      error,
      loadingText,
      funnelData,
      isEditMode,
      cancelUrl,
      saveFunnel,
      retry
    }
  }
}
</script>

<style scoped>
.ze-admin-app {
  margin-top: 20px;
}

.loading-state {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #ddd;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.error-state {
  margin: 20px 0;
}

.placeholder-content {
  background: white;
  padding: 20px;
  border: 1px solid #ccd0d4;
  border-radius: 4px;
}

.funnel-header {
  margin-bottom: 30px;
  padding-bottom: 15px;
  border-bottom: 1px solid #ddd;
}

.funnel-header h2 {
  margin: 0;
  color: #23282d;
}

.form-section {
  margin-bottom: 20px;
}

.form-section label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #23282d;
}

.form-section input,
.form-section textarea,
.form-section select {
  width: 100%;
  max-width: 500px;
}

.form-actions {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #ddd;
}

.form-actions .button {
  margin-right: 10px;
}

.questions-section {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 2px solid #ddd;
}

.questions-section h3 {
  color: #23282d;
  margin-bottom: 10px;
}

.questions-section .description {
  color: #666;
  font-style: italic;
}
</style>