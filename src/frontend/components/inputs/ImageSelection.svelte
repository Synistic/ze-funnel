<script>
  import { createEventDispatcher } from 'svelte'

  export let question

  const dispatch = createEventDispatcher()
  
  let selectedOptions = question.options?.multiple ? [] : null

  function handleOptionSelect(optionValue) {
    if (question.options?.multiple) {
      if (selectedOptions.includes(optionValue)) {
        selectedOptions = selectedOptions.filter(val => val !== optionValue)
      } else {
        selectedOptions = [...selectedOptions, optionValue]
      }
      dispatch('answer', { answer: selectedOptions })
    } else {
      selectedOptions = optionValue
      dispatch('answer', { answer: optionValue })
    }
  }

  function isSelected(optionValue) {
    if (question.options?.multiple) {
      return selectedOptions.includes(optionValue)
    }
    return selectedOptions === optionValue
  }
</script>

<div class="ze-options-grid">
  {#each question.options?.choices || [] as option}
    <button
      type="button"
      class="ze-option"
      class:selected={isSelected(option.value)}
      on:click={() => handleOptionSelect(option.value)}
    >
      {#if option.image}
        <img 
          src={option.image} 
          alt={option.label || option.text || option.value}
          class="ze-option-image"
          loading="lazy"
        />
      {:else}
        <div class="ze-option-placeholder">
          📷
        </div>
      {/if}
      
      <div class="ze-option-text">
        {option.label || option.text || option.value}
      </div>
      
      {#if question.options?.multiple}
        <div class="ze-option-checkbox">
          <div class="ze-checkbox" class:checked={isSelected(option.value)}>
            {#if isSelected(option.value)}✓{/if}
          </div>
        </div>
      {/if}
    </button>
  {/each}
</div>

<style>
  .ze-options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
  }

  .ze-option {
    padding: 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    text-align: center;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
  }

  .ze-option:hover {
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .ze-option.selected {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.2);
  }

  .ze-option-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
    background: #f3f4f6;
  }

  .ze-option-placeholder {
    width: 100%;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    border-radius: 6px;
    font-size: 48px;
    color: #9ca3af;
  }

  .ze-option-text {
    font-weight: 500;
    color: #111827;
    font-size: 16px;
    word-break: break-word;
  }

  .ze-option-checkbox {
    position: absolute;
    top: 8px;
    right: 8px;
  }

  .ze-checkbox {
    width: 24px;
    height: 24px;
    border: 2px solid #e5e7eb;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    font-size: 14px;
    font-weight: bold;
    color: #3b82f6;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .ze-checkbox.checked {
    border-color: #3b82f6;
    background: #3b82f6;
    color: white;
  }

  @media (max-width: 768px) {
    .ze-options-grid {
      grid-template-columns: 1fr;
    }
    
    .ze-option-image,
    .ze-option-placeholder {
      height: 100px;
    }
  }
</style>