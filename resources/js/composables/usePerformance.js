import { ref, onMounted, onBeforeUnmount } from 'vue'

export function usePerformance() {
  const metrics = ref({})
  const observer = ref(null)

  // Measure component mount time
  const measureMountTime = (componentName) => {
    const startTime = performance.now()

    onMounted(() => {
      const mountTime = performance.now() - startTime
      metrics.value[componentName] = { mountTime }

      if (mountTime > 100) {
        console.warn(`Slow component mount: ${componentName} took ${mountTime.toFixed(2)}ms`)
      }
    })
  }

  // Measure API call performance
  const measureApiCall = async (apiCall, callName) => {
    const startTime = performance.now()
    try {
      const result = await apiCall()
      const duration = performance.now() - startTime

      metrics.value[callName] = { duration, success: true }

      if (duration > 1000) {
        console.warn(`Slow API call: ${callName} took ${duration.toFixed(2)}ms`)
      }

      return result
    } catch (error) {
      const duration = performance.now() - startTime
      metrics.value[callName] = { duration, success: false, error }
      throw error
    }
  }

  // Setup intersection observer for lazy loading
  const setupIntersectionObserver = (callback, options = {}) => {
    if (!window.IntersectionObserver) return

    observer.value = new IntersectionObserver(callback, {
      rootMargin: '50px',
      threshold: 0.1,
      ...options
    })

    onBeforeUnmount(() => {
      if (observer.value) {
        observer.value.disconnect()
      }
    })

    return observer.value
  }

  // Measure memory usage
  const measureMemory = () => {
    if (performance.memory) {
      return {
        used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
        total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024),
        limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024)
      }
    }
    return null
  }

  // Debounce function for performance
  const debounce = (func, wait) => {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  }

  // Throttle function for performance
  const throttle = (func, limit) => {
    let inThrottle
    return function executedFunction(...args) {
      if (!inThrottle) {
        func.apply(this, args)
        inThrottle = true
        setTimeout(() => inThrottle = false, limit)
      }
    }
  }

  // Get performance metrics
  const getMetrics = () => metrics.value

  // Clear metrics
  const clearMetrics = () => {
    metrics.value = {}
  }

  return {
    measureMountTime,
    measureApiCall,
    setupIntersectionObserver,
    measureMemory,
    debounce,
    throttle,
    getMetrics,
    clearMetrics
  }
}
