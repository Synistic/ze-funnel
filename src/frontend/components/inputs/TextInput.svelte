<script>
  import { createEventDispatcher } from 'svelte'

  export let question

  const dispatch = createEventDispatcher()
  
  let value = ''
  let errors = []

  function handleInput() {
    // Basic validation
    errors = []
    
    if (question.required && !value.trim()) {
      errors.push('Dieses Feld ist erforderlich')
    }
    
    if (question.validation) {
      if (question.validation.minLength && value.length < question.validation.minLength) {
        errors.push(`Mindestens ${question.validation.minLength} Zeichen erforderlich`)
      }
      
      if (question.validation.email && value && !isValidEmail(value)) {
        errors.push('Bitte geben Sie eine gültige E-Mail-Adresse ein')
      }
    }

    // Dispatch answer
    dispatch('answer', { answer: value.trim() })
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  }
</script>

<div class="ze-input-group">
  {#if question.validation?.email}
    <input
      type="email"
      class="ze-text-input"
      class:error={errors.length > 0}
      placeholder={question.placeholder || 'Ihre Antwort...'}
      bind:value
      on:input={handleInput}
      on:blur={handleInput}
    />
  {:else}
    <input
      type="text"
      class="ze-text-input"
      class:error={errors.length > 0}
      placeholder={question.placeholder || 'Ihre Antwort...'}
      bind:value
      on:input={handleInput}
      on:blur={handleInput}
    />
  {/if}
  
  {#if errors.length > 0}
    <div class="ze-field-errors">
      {#each errors as error}
        <div class="ze-field-error">{error}</div>
      {/each}
    </div>
  {/if}
</div>

<style>
  .ze-input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .ze-text-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.2s ease-in-out;
    background: #ffffff;
  }

  .ze-text-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .ze-text-input.error {
    border-color: #ef4444;
  }

  .ze-field-errors {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .ze-field-error {
    color: #ef4444;
    font-size: 14px;
  }
</style>