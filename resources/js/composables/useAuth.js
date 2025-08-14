import { ref, computed } from 'vue'

const token = ref(localStorage.getItem('auth_token'))
// Hydrate user state from localStorage for immediate UI consistency after redirects
let initialUser = null
try {
    const raw = localStorage.getItem('auth_user')
    if (raw) {
        initialUser = JSON.parse(raw)
    }
} catch (_) {}
const user = ref(initialUser)
const isAuthenticated = computed(() => !!token.value && !!user.value)
// Track prior auth state to emit login/logout only on transitions
let wasAuthenticated = !!(token.value && user.value)
// Deduplicate in-flight /me requests
let fetchUserInFlight = null

const setToken = (newToken) => {
    token.value = newToken
    if (newToken) {
        localStorage.setItem('auth_token', newToken)
    } else {
        localStorage.removeItem('auth_token')
    }

    // Update axios authorization header
    if (window.updateAxiosAuth) {
        window.updateAxiosAuth();
    }

    // Notify listeners about token changes
    try {
        window.dispatchEvent(new CustomEvent('auth:token', { detail: { token: newToken } }))
    } catch (_) {}
}

const setUser = (userData) => {
    user.value = userData
    try {
        if (userData) {
            localStorage.setItem('auth_user', JSON.stringify(userData))
        } else {
            localStorage.removeItem('auth_user')
        }
    } catch (_) {}

    // Emit auth:login only on transition from guest -> authenticated
    const isNowAuthenticated = !!(token.value && user.value)
    if (!wasAuthenticated && isNowAuthenticated) {
        try { window.dispatchEvent(new CustomEvent('auth:login', { detail: { user: userData } })) } catch (_) {}
    }
    wasAuthenticated = isNowAuthenticated
}

const updateUser = (userData) => {
    if (user.value) {
        user.value = { ...user.value, ...userData }
    }
}

const fetchUser = async () => {
    if (!token.value) {
        return null
    }

    // Deduplicate concurrent calls
    if (fetchUserInFlight) {
        return fetchUserInFlight
    }

    fetchUserInFlight = (async () => {
        try {
            const response = await fetch('/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${token.value}`,
                    'Accept': 'application/json',
                },
            })

            if (response.ok) {
                const userData = await response.json()
                setUser(userData)
                return userData
            } else {
                // Token is invalid, clear it
                setToken(null)
                setUser(null)
                try { window.dispatchEvent(new Event('auth:logout')) } catch (_) {}
                return null
            }
        } catch (error) {
            console.error('Error fetching user:', error)
            setToken(null)
            setUser(null)
            try { window.dispatchEvent(new Event('auth:logout')) } catch (_) {}
            return null
        } finally {
            fetchUserInFlight = null
        }
    })()

    return fetchUserInFlight
}

const handleTokenFromUrl = () => {
    // Prefer URL fragment to avoid referrer leakage
    let tokenFromFragment = null
    try {
        if (window.location.hash && window.location.hash.length > 1) {
            const hashParams = new URLSearchParams(window.location.hash.substring(1))
            tokenFromFragment = hashParams.get('token')
        }
    } catch (_) {}

    // Backward compatibility: still support query param for any old links
    const urlParams = new URLSearchParams(window.location.search)
    const tokenFromQuery = urlParams.get('token')

    const urlToken = tokenFromFragment || tokenFromQuery

    if (urlToken) {
        setToken(urlToken)
        // Clean the URL (remove both search and hash)
        const cleanPath = window.location.pathname
        window.history.replaceState({}, document.title, cleanPath)
        // Fetch user data
        fetchUser()
        // After successful token handling, attempt to restore intended path
        try {
            const intendedPath = sessionStorage.getItem('intended_path')
            if (intendedPath) {
                sessionStorage.removeItem('intended_path')
                if (cleanPath !== intendedPath) {
                    window.location.replace(intendedPath)
                }
            }
        } catch (_) {}
        return true
    }
    return false
}

// --- Initialization ---
// This block runs once when the module is imported.
if (!handleTokenFromUrl()) {
    // If no token in URL, check local storage
    if (token.value) {
        fetchUser()
    }
}
// --- End Initialization ---

// --- Global listeners to keep auth state in sync (same-tab and cross-tab) ---
try {
    // React to explicit auth logout events (e.g., from axios interceptor on 401)
    window.addEventListener('auth:logout', () => {
        setToken(null)
        setUser(null)
    })
    // Do not refetch on auth:login; user is already set. Consumers can react if needed.

    // React to token change broadcasts within same tab
    window.addEventListener('auth:token', (e) => {
        const nextToken = e?.detail?.token || null
        if (nextToken !== token.value) {
            setToken(nextToken)
            if (nextToken) {
                // Ensure we have fresh user data
                fetchUser()
            } else {
                setUser(null)
            }
        }
    })

    // Cross-tab synchronization
    window.addEventListener('storage', (ev) => {
        if (ev.key === 'auth_token') {
            const nextToken = localStorage.getItem('auth_token')
            if (nextToken !== token.value) {
                setToken(nextToken)
                if (nextToken) {
                    fetchUser()
                } else {
                    setUser(null)
                    try { window.dispatchEvent(new Event('auth:logout')) } catch (_) {}
                }
            }
        }
        if (ev.key === 'auth_user') {
            try {
                user.value = ev.newValue ? JSON.parse(ev.newValue) : null
            } catch (_) {
                user.value = null
            }
        }
    })
} catch (_) {}

export function useAuth() {

    const logout = async () => {
        if (token.value) {
            try {
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token.value}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
            } catch (error) {
                console.error('Logout error:', error)
            }
        }

        setToken(null)
        setUser(null)

        // Redirect to clean URL
        window.history.replaceState({}, document.title, window.location.pathname)

        // Broadcast logout so any listener can react
        try { window.dispatchEvent(new Event('auth:logout')) } catch (_) {}
    }

    const handleLogin = async () => {
        try {
            // Persist current path so we can return here after auth if login is initiated manually
            try { sessionStorage.setItem('intended_path', window.location.pathname + window.location.search) } catch (_) {}
            const response = await fetch('/api/auth/redirect', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });
            if (response.ok) {
                const data = await response.json();
                window.location.href = data.redirect_url;
            } else {
                console.error('Failed to get redirect URL');
            }
        } catch (error) {
            console.error('Login error:', error);
        }
    }

    const getInitials = (name) => {
        if (!name) return '?'
        return name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2).toUpperCase()
    }

    const can = (permission, resource = null) => {
        if (!user.value || !isAuthenticated.value) {
            return false
        }

        // Basic permission checking logic
        // For now, let's assume all authenticated users can edit/delete their own content
        if (resource && resource.user && user.value) {
            return resource.user.id === user.value.id
        }

        // You can extend this with more sophisticated permission logic
        // based on user roles, permissions array, etc.
        return false
    }

    return {
        user,
        isAuthenticated,
        logout,
        handleLogin,
        getInitials,
        can,
        updateUser,
        fetchUser,
    }
}
