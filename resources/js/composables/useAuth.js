import { ref, computed } from 'vue'

const token = ref(localStorage.getItem('auth_token'))
const user = ref(null)
const isAuthenticated = computed(() => !!token.value && !!user.value)

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
}

const setUser = (userData) => {
    user.value = userData
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
            return null
        }
    } catch (error) {
        console.error('Error fetching user:', error)
        setToken(null)
        setUser(null)
        return null
    }
}

const handleTokenFromUrl = () => {
    const urlParams = new URLSearchParams(window.location.search)
    const urlToken = urlParams.get('token')

    if (urlToken) {
        setToken(urlToken)
        // Clean the URL
        window.history.replaceState({}, document.title, window.location.pathname)
        // Fetch user data
        fetchUser()
        // After successful token handling, attempt to restore intended path
        try {
            const intendedPath = sessionStorage.getItem('intended_path')
            if (intendedPath) {
                sessionStorage.removeItem('intended_path')
                // Use location change to ensure fresh navigation after OAuth redirect landing
                if (window.location.pathname + window.location.search !== intendedPath) {
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
    }
}
