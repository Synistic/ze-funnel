import { writable } from 'svelte/store'

/**
 * Funnel state management store
 */
function createFunnelStore() {
  const { subscribe, set, update } = writable({
    funnelData: null,
    answers: new Map(),
    currentQuestionIndex: 0,
    isInitialized: false
  })

  return {
    subscribe,
    
    /**
     * Initialize store with funnel data
     */
    initialize(funnelData) {
      set({
        funnelData,
        answers: new Map(),
        currentQuestionIndex: 0,
        isInitialized: true
      })
    },

    /**
     * Set answer for a question
     */
    setAnswer(questionId, answer) {
      update(state => {
        const newAnswers = new Map(state.answers)
        newAnswers.set(questionId, answer)
        
        return {
          ...state,
          answers: newAnswers
        }
      })
    },

    /**
     * Get answer for a question
     */
    getAnswer(questionId) {
      let answer = null
      this.subscribe(state => {
        answer = state.answers.get(questionId)
      })()
      return answer
    },

    /**
     * Get all answers
     */
    getAllAnswers() {
      let allAnswers = {}
      this.subscribe(state => {
        state.answers.forEach((answer, questionId) => {
          allAnswers[questionId] = answer
        })
      })()
      return allAnswers
    },

    /**
     * Check if current question is answered
     */
    isCurrentQuestionAnswered(questionIndex) {
      let isAnswered = false
      this.subscribe(state => {
        if (!state.funnelData) return false
        
        const question = state.funnelData.questions[questionIndex]
        if (!question) return false
        
        const answer = state.answers.get(question.id)
        
        // Check if answer exists and is valid
        if (answer === null || answer === undefined) {
          isAnswered = false
          return
        }

        // Validate based on question type and requirements
        switch (question.type) {
          case 'text_input':
            isAnswered = typeof answer === 'string' && answer.trim().length > 0
            break
          case 'image_selection':
          case 'icon_selection':
          case 'text_selection':
            if (question.options && question.options.multiple) {
              isAnswered = Array.isArray(answer) && answer.length > 0
            } else {
              isAnswered = answer !== null && answer !== undefined
            }
            break
          case 'multi_input':
            isAnswered = typeof answer === 'object' && answer !== null && 
                        Object.keys(answer).length > 0
            break
          default:
            isAnswered = answer !== null && answer !== undefined
        }

        // Apply validation rules if question is required
        if (question.required && !isAnswered) {
          isAnswered = false
        }
      })()
      
      return isAnswered
    },

    /**
     * Validate specific answer
     */
    validateAnswer(questionId, answer) {
      let validation = { isValid: true, errors: [] }
      
      this.subscribe(state => {
        if (!state.funnelData) return
        
        const question = state.funnelData.questions.find(q => q.id === questionId)
        if (!question) return

        // Required validation
        if (question.required) {
          const isEmpty = answer === null || answer === undefined || 
                         (typeof answer === 'string' && answer.trim().length === 0) ||
                         (Array.isArray(answer) && answer.length === 0)
          
          if (isEmpty) {
            validation.isValid = false
            validation.errors.push('This field is required')
            return
          }
        }

        // Type-specific validation
        if (question.validation) {
          validation = this.applyValidationRules(answer, question.validation, validation)
        }
      })()

      return validation
    },

    /**
     * Apply validation rules
     */
    applyValidationRules(answer, rules, validation) {
      if (rules.minLength && typeof answer === 'string' && answer.length < rules.minLength) {
        validation.isValid = false
        validation.errors.push(`Minimum ${rules.minLength} characters required`)
      }

      if (rules.maxLength && typeof answer === 'string' && answer.length > rules.maxLength) {
        validation.isValid = false
        validation.errors.push(`Maximum ${rules.maxLength} characters allowed`)
      }

      if (rules.pattern && typeof answer === 'string') {
        const regex = new RegExp(rules.pattern)
        if (!regex.test(answer)) {
          validation.isValid = false
          validation.errors.push(rules.patternMessage || 'Invalid format')
        }
      }

      if (rules.min && typeof answer === 'number' && answer < rules.min) {
        validation.isValid = false
        validation.errors.push(`Minimum value is ${rules.min}`)
      }

      if (rules.max && typeof answer === 'number' && answer > rules.max) {
        validation.isValid = false
        validation.errors.push(`Maximum value is ${rules.max}`)
      }

      if (rules.email && typeof answer === 'string') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(answer)) {
          validation.isValid = false
          validation.errors.push('Please enter a valid email address')
        }
      }

      return validation
    },

    /**
     * Clear all answers
     */
    clearAnswers() {
      update(state => ({
        ...state,
        answers: new Map()
      }))
    },

    /**
     * Reset store
     */
    reset() {
      set({
        funnelData: null,
        answers: new Map(),
        currentQuestionIndex: 0,
        isInitialized: false
      })
    }
  }
}

export const funnelStore = createFunnelStore()