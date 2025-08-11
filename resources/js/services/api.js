import axios from 'axios'

// Create a single axios instance for the whole app
const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    Accept: 'application/json',
  },
})

// Attach Authorization header if token exists
api.interceptors.request.use((config) => {
  try {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers = config.headers ?? {}
      config.headers.Authorization = `Bearer ${token}`
    } else if (config.headers && 'Authorization' in config.headers) {
      delete config.headers.Authorization
    }
  } catch (_) {}
  return config
})

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
}

export default api
