import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Function to update axios authorization header
const updateAxiosAuthHeader = () => {
    const authToken = localStorage.getItem('auth_token');
    if (authToken) {
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
    } else {
        delete window.axios.defaults.headers.common['Authorization'];
    }
};

// Set initial authorization header
updateAxiosAuthHeader();

// Listen for storage changes to update axios header when token changes
window.addEventListener('storage', (e) => {
    if (e.key === 'auth_token') {
        updateAxiosAuthHeader();
    }
});

// Add a method to manually update axios headers (for same-tab token updates)
window.updateAxiosAuth = updateAxiosAuthHeader;

// Add request interceptor to ensure latest token is always used
window.axios.interceptors.request.use(
    config => {
        const authToken = localStorage.getItem('auth_token');
        if (authToken) {
            config.headers.Authorization = `Bearer ${authToken}`;
        }
        return config;
    },
    error => Promise.reject(error)
);

// Intercept responses to handle token refresh or logout
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Remove invalid token
            localStorage.removeItem('auth_token');
            // Update axios headers
            updateAxiosAuthHeader();
            // Dispatch custom event for auth state change
            window.dispatchEvent(new CustomEvent('auth:logout'));
        }
        return Promise.reject(error);
    }
);
