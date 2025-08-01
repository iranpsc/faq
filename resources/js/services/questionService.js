import { reactive } from 'vue'

// Global question service for component communication
export const questionService = reactive({
  listeners: new Map(),

  // Subscribe to question events
  subscribe(eventType, callback) {
    if (!this.listeners.has(eventType)) {
      this.listeners.set(eventType, new Set())
    }
    this.listeners.get(eventType).add(callback)

    // Return unsubscribe function
    return () => {
      const callbacks = this.listeners.get(eventType)
      if (callbacks) {
        callbacks.delete(callback)
      }
    }
  },

  // Emit question events
  emit(eventType, data) {
    const callbacks = this.listeners.get(eventType)
    if (callbacks) {
      callbacks.forEach(callback => {
        try {
          callback(data)
        } catch (error) {
          console.error('Error in question service callback:', error)
        }
      })
    }
  },

  // Convenience methods
  questionCreated(question) {
    this.emit('question-created', question)
  },

  questionUpdated(question) {
    this.emit('question-updated', question)
  },

  questionDeleted(questionId) {
    this.emit('question-deleted', questionId)
  }
})

export default questionService
