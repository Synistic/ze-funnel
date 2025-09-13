<script>
  import { createEventDispatcher } from 'svelte'

  export let question

  const dispatch = createEventDispatcher()
  
  let formData = {}
  let errors = {}

  function handleInput(fieldName) {
    // Clear previous errors for this field
    delete errors[fieldName]
    errors = { ...errors }

    // Validate field
    const field = question.options?.fields?.find(f => f.name === fieldName)
    if (field) {
      validateField(field, formData[fieldName])
    }

    // Dispatch current form data
    dispatch('answer', { answer: { ...formData } })
  }

  function validateField(field, value) {
    const fieldErrors = []

    if (field.required && (!value || value.toString().trim() === '')) {
      fieldErrors.push('Dieses Feld ist erforderlich')
    }

    if (value && field.validation) {
      if (field.validation.email && !isValidEmail(value)) {
        fieldErrors.push('Bitte geben Sie eine gültige E-Mail-Adresse ein')
      }

      if (field.validation.minLength && value.length < field.validation.minLength) {
        fieldErrors.push(`Mindestens ${field.validation.minLength} Zeichen erforderlich`)
      }

      if (field.validation.pattern) {
        const regex = new RegExp(field.validation.pattern)
        if (!regex.test(value)) {
          fieldErrors.push(field.validation.patternMessage || 'Ungültiges Format')
        }
      }
    }

    if (fieldErrors.length > 0) {
      errors[field.name] = fieldErrors
      errors = { ...errors }
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
  }

</script>

<div class="ze-multi-input">
  {#each question.options?.fields || [] as field}
    <div class="ze-field-group">
      <label class="ze-field-label" for="field-{field.name}">
        {field.label}
        {#if field.required}
          <span class="ze-required">*</span>
        {/if}
      </label>

      {#if field.type === 'textarea'}
        <textarea
          id="field-{field.name}"
          class="ze-textarea"
          class:error={errors[field.name]}
          placeholder={field.placeholder || ''}
          bind:value={formData[field.name]}
          on:input={() => handleInput(field.name)}
          on:blur={() => handleInput(field.name)}
          rows={field.rows || 3}
        ></textarea>
      {:else if field.type === 'select'}
        <select
          id="field-{field.name}"
          class="ze-select"
          class:error={errors[field.name]}
          bind:value={formData[field.name]}
          on:change={() => handleInput(field.name)}
        >
          <option value="">Bitte wählen...</option>
          {#each field.options || [] as option}
            <option value={option.value}>
              {option.label || option.value}
            </option>
          {/each}
        </select>
      {:else if field.type === 'checkbox'}
        <label class="ze-checkbox-label">
          <input
            type="checkbox"
            class="ze-checkbox-input"
            bind:checked={formData[field.name]}
            on:change={() => handleInput(field.name)}
          />
          <span class="ze-checkbox-text">{field.text || field.label}</span>
        </label>
      {:else if field.type === 'email'}
        <input
          id="field-{field.name}"
          type="email"
          class="ze-text-input"
          class:error={errors[field.name]}
          placeholder={field.placeholder || ''}
          bind:value={formData[field.name]}
          on:input={() => handleInput(field.name)}
          on:blur={() => handleInput(field.name)}
        />
      {:else if field.type === 'tel' || field.type === 'phone'}
        <input
          id="field-{field.name}"
          type="tel"
          class="ze-text-input"
          class:error={errors[field.name]}
          placeholder={field.placeholder || ''}
          bind:value={formData[field.name]}
          on:input={() => handleInput(field.name)}
          on:blur={() => handleInput(field.name)}
        />
      {:else if field.type === 'number'}
        <input
          id="field-{field.name}"
          type="number"
          class="ze-text-input"
          class:error={errors[field.name]}
          placeholder={field.placeholder || ''}
          bind:value={formData[field.name]}
          on:input={() => handleInput(field.name)}
          on:blur={() => handleInput(field.name)}
        />
      {:else if field.type === 'url'}
        <input
          id="field-{field.name}"
          type="url"
          class="ze-text-input"
          class:error={errors[field.name]}
          placeholder={field.placeholder || ''}
          bind:value={formData[field.name]}
          on:input={() => handleInput(field.name)}
          on:blur={() => handleInput(field.name)}
        />
      {:else}
        <input
          id="field-{field.name}"
          type="text"
          class="ze-text-input"
          class:error={errors[field.name]}
          placeholder={field.placeholder || ''}
          bind:value={formData[field.name]}
          on:input={() => handleInput(field.name)}
          on:blur={() => handleInput(field.name)}
        />
      {/if}

      {#if errors[field.name]}
        <div class="ze-field-errors">
          {#each errors[field.name] as error}
            <div class="ze-field-error">{error}</div>
          {/each}
        </div>
      {/if}
    </div>
  {/each}
</div>

<style>
  .ze-multi-input {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .ze-field-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .ze-field-label {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
  }

  .ze-required {
    color: #ef4444;
  }

  .ze-text-input,
  .ze-textarea,
  .ze-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.2s ease-in-out;
    background: #ffffff;
  }

  .ze-text-input:focus,
  .ze-textarea:focus,
  .ze-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .ze-text-input.error,
  .ze-textarea.error,
  .ze-select.error {
    border-color: #ef4444;
  }

  .ze-textarea {
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
  }

  .ze-checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    padding: 8px 0;
  }

  .ze-checkbox-input {
    width: 18px;
    height: 18px;
    margin: 0;
    margin-top: 2px;
  }

  .ze-checkbox-text {
    flex: 1;
    font-size: 16px;
    line-height: 1.5;
    color: #374151;
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

  @media (max-width: 768px) {
    .ze-multi-input {
      gap: 16px;
    }
  }
</style>