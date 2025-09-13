<script>
  import { createEventDispatcher } from 'svelte'

  export let canGoBack = false
  export let canGoNext = false
  export let isLoading = false
  export let isLastQuestion = false

  const dispatch = createEventDispatcher()

  function handleNext() {
    if (!canGoNext || isLoading) return
    dispatch('next')
  }

  function handleBack() {
    if (!canGoBack || isLoading) return
    dispatch('back')
  }
</script>

<div class="ze-navigation">
  <div class="ze-nav-left">
    {#if canGoBack}
      <button 
        class="ze-btn ze-btn-secondary" 
        on:click={handleBack}
        disabled={isLoading}
        type="button"
      >
        ← Zurück
      </button>
    {:else}
      <div></div>
    {/if}
  </div>

  <div class="ze-nav-right">
    <button 
      class="ze-btn ze-btn-primary" 
      on:click={handleNext}
      disabled={!canGoNext || isLoading}
      type="button"
    >
      {#if isLoading}
        <span class="ze-spinner"></span>
        Wird übermittelt...
      {:else if isLastQuestion}
        Absenden
      {:else}
        Weiter →
      {/if}
    </button>
  </div>
</div>

<style>
  .ze-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 32px;
    gap: 16px;
  }

  .ze-nav-left,
  .ze-nav-right {
    flex: 0 0 auto;
  }

  .ze-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
  }

  .ze-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .ze-btn-primary {
    background: #3b82f6;
    color: white;
  }

  .ze-btn-primary:hover:not(:disabled) {
    background: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }

  .ze-btn-secondary {
    background: #f9fafb;
    color: #374151;
    border: 1px solid #e5e7eb;
  }

  .ze-btn-secondary:hover:not(:disabled) {
    background: #f3f4f6;
  }

  .ze-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  @media (max-width: 768px) {
    .ze-navigation {
      flex-direction: column-reverse;
      gap: 12px;
    }
    
    .ze-btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>