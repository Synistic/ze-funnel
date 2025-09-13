import { writable } from 'svelte/store'

/**
 * Analytics tracking store
 */
function createAnalyticsStore() {
  const { subscribe, set, update } = writable({
    tracking: null,
    startTime: null,
    events: [],
    isEnabled: true
  })

  return {
    subscribe,

    /**
     * Initialize analytics with tracking config
     */
    initialize(trackingConfig) {
      set({
        tracking: trackingConfig,
        startTime: Date.now(),
        events: [],
        isEnabled: trackingConfig?.analyticsEnabled ?? true
      })
    },

    /**
     * Track an event
     */
    trackEvent(eventType, eventData = {}) {
      update(state => {
        if (!state.isEnabled) return state

        const event = {
          type: eventType,
          data: eventData,
          timestamp: Date.now(),
          sessionId: state.tracking?.sessionId
        }

        // Send to backend if configured
        this.sendEvent(event)

        return {
          ...state,
          events: [...state.events, event]
        }
      })
    },

    /**
     * Send event to backend
     */
    async sendEvent(event) {
      try {
        // Only send if WordPress REST API is available
        if (typeof zeFunnelWP !== 'undefined' && zeFunnelWP.restUrl) {
          await fetch(`${zeFunnelWP.restUrl}analytics`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': zeFunnelWP.nonce
            },
            body: JSON.stringify(event)
          })
        }
      } catch (error) {
        console.warn('Analytics tracking failed:', error)
      }
    },

    /**
     * Get completion time in seconds
     */
    getCompletionTime() {
      let completionTime = 0
      this.subscribe(state => {
        if (state.startTime) {
          completionTime = Math.round((Date.now() - state.startTime) / 1000)
        }
      })()
      return completionTime
    },

    /**
     * Get all events
     */
    getEvents() {
      let events = []
      this.subscribe(state => {
        events = [...state.events]
      })()
      return events
    },

    /**
     * Get events by type
     */
    getEventsByType(eventType) {
      let filteredEvents = []
      this.subscribe(state => {
        filteredEvents = state.events.filter(event => event.type === eventType)
      })()
      return filteredEvents
    },

    /**
     * Track question timing
     */
    trackQuestionTiming(questionId, timeSpent) {
      this.trackEvent('question_timing', {
        questionId,
        timeSpent
      })
    },

    /**
     * Track user interaction
     */
    trackInteraction(interactionType, details = {}) {
      this.trackEvent('user_interaction', {
        interaction: interactionType,
        ...details
      })
    },

    /**
     * Enable/disable analytics
     */
    setEnabled(enabled) {
      update(state => ({
        ...state,
        isEnabled: enabled
      }))
    },

    /**
     * Clear all events
     */
    clearEvents() {
      update(state => ({
        ...state,
        events: []
      }))
    },

    /**
     * Reset analytics
     */
    reset() {
      set({
        tracking: null,
        startTime: null,
        events: [],
        isEnabled: true
      })
    }
  }
}

export const analyticsStore = createAnalyticsStore()