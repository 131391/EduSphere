import axios from 'axios';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Setup Axios with CSRF token
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Get CSRF token from meta tag
const token = document?.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

// Initialize Alpine.js via Vite bundle (replaces CDN loading)
Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

// Global error handling
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        if (error.response?.status === 403) {
            console.error('Access denied');
        }
        return Promise.reject(error);
    }
);

