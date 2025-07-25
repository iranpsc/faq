import { ref, onMounted, onBeforeUnmount } from 'vue'

export function useInfiniteScroll(loadMoreCallback, options = {}) {
  const sentinel = ref(null)
  const observer = ref(null)
  const isIntersecting = ref(false)
  const isLoading = ref(false)

  const defaultOptions = {
    threshold: 0.1,
    rootMargin: '200px 0px',
    ...options
  }

  // Debounce function to prevent too many rapid calls
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

  // Throttle function for additional protection
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

  // Combined debounced and throttled load more function
  const debouncedLoadMore = debounce(throttle(async () => {
    if (isLoading.value) return

    isLoading.value = true
    try {
      await loadMoreCallback()
    } catch (error) {
      console.error('Error in infinite scroll load more:', error)
    } finally {
      isLoading.value = false
    }
  }, 1000), 300)

  const observerCallback = (entries) => {
    const [entry] = entries
    isIntersecting.value = entry.isIntersecting

    if (entry.isIntersecting && !isLoading.value) {
      // Call the load more function when the sentinel comes into view
      debouncedLoadMore()
    }
  }

  const initializeObserver = () => {
    if (!sentinel.value) return

    observer.value = new IntersectionObserver(observerCallback, defaultOptions)
    observer.value.observe(sentinel.value)
  }

  const disconnectObserver = () => {
    if (observer.value) {
      observer.value.disconnect()
      observer.value = null
    }
  }

  const reconnectObserver = () => {
    disconnectObserver()
    if (sentinel.value) {
      initializeObserver()
    }
  }

  // Pause and resume observation
  const pauseObserver = () => {
    if (observer.value && sentinel.value) {
      observer.value.unobserve(sentinel.value)
    }
  }

  const resumeObserver = () => {
    if (observer.value && sentinel.value) {
      observer.value.observe(sentinel.value)
    }
  }

  onMounted(() => {
    // Initialize observer when component is mounted
    if (sentinel.value) {
      initializeObserver()
    }
  })

  onBeforeUnmount(() => {
    // Clean up observer when component is unmounted
    disconnectObserver()
  })

  return {
    sentinel,
    isIntersecting,
    isLoading,
    initializeObserver,
    disconnectObserver,
    reconnectObserver,
    pauseObserver,
    resumeObserver
  }
}
