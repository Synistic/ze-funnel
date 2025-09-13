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
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
  }

  .ze-option {
    padding: 20px;
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
  }

  .ze-option-text {
    font-weight: 500;
    color: #111827;
    font-size: 16px;
  }

  .ze-option-checkbox {
    position: absolute;
    top: 8px;
    right: 8px;
  }

  .ze-checkbox {
    width: 20px;
    height: 20px;
    border: 2px solid #e5e7eb;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    font-size: 12px;
    font-weight: bold;
    color: #3b82f6;
    transition: all 0.2s ease-in-out;
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
  }
</style>