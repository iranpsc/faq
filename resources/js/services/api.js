import axios from 'axios'

// Request cache for GET requests
const requestCache = new Map()
const pendingRequests = new Map()

// Create a single axios instance for the whole app
const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
  timeout: 30000, // 30 second timeout
})

// Attach Authorization header and handle caching
api.interceptors.request.use((config) => {
  try {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers = config.headers ?? {}
      config.headers.Authorization = `Bearer ${token}`
    } else if (config.headers && 'Authorization' in config.headers) {
      delete config.headers.Authorization
    }

    // Add cache handling for GET requests
    if (config.method === 'get' && !config.skipCache) {
      const cacheKey = `${config.url}?${new URLSearchParams(config.params || {}).toString()}`

      // Check if request is already pending
      if (pendingRequests.has(cacheKey)) {
        config.cancelToken = new axios.CancelToken((cancel) => {
          pendingRequests.get(cacheKey).then(cancel).catch(cancel)
        })
      }

      // Check cache
      const cached = requestCache.get(cacheKey)
      if (cached && Date.now() - cached.timestamp < (config.cacheTimeout || 300000)) { // 5 min default
        config.adapter = () => Promise.resolve(cached.response)
      }
    }
  } catch (_) {}
  return config
})

// Response interceptor for caching
api.interceptors.response.use(
  (response) => {
    // Cache GET responses
    if (response.config.method === 'get' && !response.config.skipCache) {
      const cacheKey = `${response.config.url}?${new URLSearchParams(response.config.params || {}).toString()}`
      requestCache.set(cacheKey, {
        response: response,
        timestamp: Date.now()
      })

      // Clean up pending request
      pendingRequests.delete(cacheKey)

      // Limit cache size
      if (requestCache.size > 100) {
        const firstKey = requestCache.keys().next().value
        requestCache.delete(firstKey)
      }
    }
    return response
  },
  (error) => {
    // Clean up pending requests on error
    if (error.config && error.config.method === 'get') {
      const cacheKey = `${error.config.url}?${new URLSearchParams(error.config.params || {}).toString()}`
      pendingRequests.delete(cacheKey)
    }
    return Promise.reject(error)
  }
)

// Expose a small helper for auth composables to refresh header without reloading
// Keep a back-compat global because some code references window.updateAxiosAuth
if (typeof window !== 'undefined') {
  window.updateAxiosAuth = () => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      api.defaults.headers.common.Authorization = `Bearer ${token}`
    } else {
      delete api.defaults.headers.common.Authorization
    }
  }
  // Initialize once on load
  window.updateAxiosAuth()

  // Clear cache when needed
  window.clearApiCache = () => {
    requestCache.clear()
    pendingRequests.clear()
  }
}

export default api
