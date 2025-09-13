<script>
  import { onMount, createEventDispatcher } from 'svelte'
  import { funnelStore } from '../stores/funnelStore.js'
  import { analyticsStore } from '../stores/analyticsStore.js'
  
  import ProgressBar from './ProgressBar.svelte'
  import QuestionRenderer from './QuestionRenderer.svelte'
  import NavigationControls from './NavigationControls.svelte'
  import ThankYouScreen from './ThankYouScreen.svelte'

  export let funnelData
  export let containerId

  const dispatch = createEventDispatcher()

  let currentQuestionIndex = 0
  let isLoading = false
  let error = null
  let showThankYou = false

  // Initialize stores
  $: if (funnelData) {
    funnelStore.initialize(funnelData)
    analyticsStore.initialize(funnelData.tracking)
  }

  $: currentQuestion = funnelData?.questions?.[currentQuestionIndex]
  $: totalQuestions = funnelData?.questions?.length || 0
  $: progress = totalQuestions > 0 ? ((currentQuestionIndex + 1) / totalQuestions) * 100 : 0
  $: canGoBack = currentQuestionIndex > 0 && funnelData?.settings?.allowBack
  $: canGoNext = funnelStore.isCurrentQuestionAnswered(currentQuestionIndex)

  onMount(() => {
    // Track funnel start
    analyticsStore.trackEvent('funnel_start', {
      funnelId: funnelData.id,
      questionId: currentQuestion?.id
    })

    // Log initial view
    if (currentQuestion) {
      analyticsStore.trackEvent('question_view', {
        questionId: currentQuestion.id,
        questionIndex: currentQuestionIndex
      })
    }
  })

  /**
   * Navigate to next question
   */
  async function handleNext() {
    if (!canGoNext) return

    const answer = funnelStore.getAnswer(currentQuestion.id)
    
    // Track answer
    analyticsStore.trackEvent('question_answer', {
      questionId: currentQuestion.id,
      questionIndex: currentQuestionIndex,
      answer: answer
    })

    // Check if this is the last question
    if (currentQuestionIndex >= totalQuestions - 1) {
      await submitFunnel()
      return
    }

    // Move to next question
    const nextIndex = getNextQuestionIndex()
    if (nextIndex !== null) {
      currentQuestionIndex = nextIndex
      
      // Track next question view
      const nextQuestion = funnelData.questions[currentQuestionIndex]
      if (nextQuestion) {
        analyticsStore.trackEvent('question_view', {
          questionId: nextQuestion.id,
          questionIndex: currentQuestionIndex
        })
      }
    }
  }

  /**
   * Navigate to previous question
   */
  function handleBack() {
    if (!canGoBack) return

    currentQuestionIndex = Math.max(0, currentQuestionIndex - 1)
    
    // Track back navigation
    analyticsStore.trackEvent('navigation_back', {
      fromQuestionIndex: currentQuestionIndex + 1,
      toQuestionIndex: currentQuestionIndex
    })
  }

  /**
   * Get next question index based on conditional logic
   */
  function getNextQuestionIndex() {
    const answer = funnelStore.getAnswer(currentQuestion.id)
    
    // Check conditional logic
    if (currentQuestion.conditional && currentQuestion.conditional.length > 0) {
      for (const condition of currentQuestion.conditional) {
        if (evaluateCondition(condition, answer)) {
          if (condition.action === 'jump_to') {
            return findQuestionIndexById(condition.target)
          } else if (condition.action === 'skip_next') {
            return currentQuestionIndex + 2
          }
        }
      }
    }
    
    // Default: next sequential question
    return currentQuestionIndex + 1
  }

  /**
   * Evaluate conditional logic
   */
  function evaluateCondition(condition, answer) {
    switch (condition.operator) {
      case 'equals':
        return answer === condition.value
      case 'not_equals':
        return answer !== condition.value
      case 'contains':
        return Array.isArray(answer) ? answer.includes(condition.value) : false
      case 'greater_than':
        return Number(answer) > Number(condition.value)
      case 'less_than':
        return Number(answer) < Number(condition.value)
      default:
        return false
    }
  }

  /**
   * Find question index by ID
   */
  function findQuestionIndexById(questionId) {
    return funnelData.questions.findIndex(q => q.id === questionId)
  }

  /**
   * Submit completed funnel
   */
  async function submitFunnel() {
    isLoading = true
    error = null

    try {
      const submissionData = {
        funnelId: funnelData.id,
        answers: funnelStore.getAllAnswers(),
        sessionId: funnelData.tracking.sessionId,
        completedAt: new Date().toISOString()
      }

      const response = await fetch(funnelData.form.submitUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': funnelData.form.nonce
        },
        body: JSON.stringify(submissionData)
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const result = await response.json()

      // Track completion
      analyticsStore.trackEvent('funnel_complete', {
        funnelId: funnelData.id,
        totalQuestions: totalQuestions,
        completionTime: analyticsStore.getCompletionTime()
      })

      showThankYou = true
      dispatch('complete', { submissionData, result })

    } catch (err) {
      console.error('Funnel submission failed:', err)
      error = 'Failed to submit your responses. Please try again.'
      
      analyticsStore.trackEvent('submission_error', {
        funnelId: funnelData.id,
        error: err.message
      })
    } finally {
      isLoading = false
    }
  }

  /**
   * Handle answer change
   */
  function handleAnswerChange(event) {
    funnelStore.setAnswer(currentQuestion.id, event.detail.answer)
  }
</script>

<div class="ze-funnel" class:loading={isLoading}>
  {#if showThankYou}
    <ThankYouScreen {funnelData} />
  {:else}
    <!-- Progress Bar -->
    {#if funnelData.settings.progressBar}
      <ProgressBar {progress} current={currentQuestionIndex + 1} total={totalQuestions} />
    {/if}

    <!-- Error Message -->
    {#if error}
      <div class="ze-funnel-error" role="alert">
        <p>{error}</p>
        <button type="button" on:click={() => error = null}>Dismiss</button>
      </div>
    {/if}

    <!-- Question Content -->
    {#if currentQuestion}
      <QuestionRenderer 
        question={currentQuestion}
        questionIndex={currentQuestionIndex}
        on:answer={handleAnswerChange}
      />
    {/if}

    <!-- Navigation Controls -->
    <NavigationControls
      {canGoBack}
      {canGoNext}
      {isLoading}
      isLastQuestion={currentQuestionIndex >= totalQuestions - 1}
      on:next={handleNext}
      on:back={handleBack}
    />
  {/if}
</div>

<style>
  .ze-funnel {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }

  .ze-funnel.loading {
    opacity: 0.7;
    pointer-events: none;
  }

  .ze-funnel-error {
    background: #fee;
    border: 1px solid #fcc;
    border-radius: 4px;
    padding: 16px;
    margin-bottom: 20px;
    color: #c33;
  }

  .ze-funnel-error button {
    background: none;
    border: none;
    color: inherit;
    text-decoration: underline;
    cursor: pointer;
    font-size: inherit;
    margin-left: 8px;
  }
</style>