<script>
  import { createEventDispatcher } from 'svelte'
  import TextInput from './inputs/TextInput.svelte'
  import ImageSelection from './inputs/ImageSelection.svelte'
  import IconSelection from './inputs/IconSelection.svelte'
  import TextSelection from './inputs/TextSelection.svelte'
  import MultiInput from './inputs/MultiInput.svelte'

  export let question
  export const questionIndex = 0

  const dispatch = createEventDispatcher()

  function handleAnswer(event) {
    dispatch('answer', {
      questionId: question.id,
      answer: event.detail.answer
    })
  }
</script>

<div class="ze-question ze-fade-in">
  <h2 class="ze-question-title">{question.text}</h2>
  
  {#if question.description}
    <p class="ze-question-description">{question.description}</p>
  {/if}

  <div class="ze-question-input">
    {#if question.type === 'text_input'}
      <TextInput {question} on:answer={handleAnswer} />
    {:else if question.type === 'image_selection'}
      <ImageSelection {question} on:answer={handleAnswer} />
    {:else if question.type === 'icon_selection'}
      <IconSelection {question} on:answer={handleAnswer} />
    {:else if question.type === 'text_selection'}
      <TextSelection {question} on:answer={handleAnswer} />
    {:else if question.type === 'multi_input'}
      <MultiInput {question} on:answer={handleAnswer} />
    {:else}
      <div class="ze-error">Unsupported question type: {question.type}</div>
    {/if}
  </div>
</div>

<style>
  .ze-question {
    margin-bottom: 32px;
  }

  .ze-question-title {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 16px;
    line-height: 1.3;
  }

  .ze-question-description {
    font-size: 16px;
    color: #6b7280;
    margin-bottom: 24px;
    line-height: 1.5;
  }

  .ze-error {
    padding: 16px;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 8px;
    text-align: center;
  }

  @keyframes ze-fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .ze-fade-in {
    animation: ze-fade-in 0.3s ease-out;
  }
</style>