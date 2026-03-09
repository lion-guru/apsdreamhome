/**
 * APS Dream Home - API Client
 * Centralized API communication handler
 */

// ===== API CLIENT =====
class APSApiClient {
    constructor() {
        this.baseURL = window.BASE_URL || 'http://localhost/apsdreamhome/public';
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        this.timeout = 10000; // 10 seconds
        this.retryAttempts = 3;
        this.retryDelay = 1000; // 1 second
    }
    
    // ===== CORE HTTP METHODS =====
    
    async request(endpoint, options = {}) {
        const url = this.buildUrl(endpoint);
        const config = this.mergeConfig(options);
        
        try {
            // Add CSRF token if available
            this.addCsrfToken(config);
            
            // Add request timestamp
            config.headers['X-Request-Time'] = Date.now();
            
            // Make request with timeout
            const response = await this.makeRequest(url, config);
            
            // Handle response
            return await this.handleResponse(response);
            
        } catch (error) {
            return await this.handleError(error, endpoint, options);
        }
    }
    
    async get(endpoint, params = {}) {
        const url = this.buildUrl(endpoint, params);
        return this.request(url, { method: 'GET' });
    }
    
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    async patch(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }
    
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
    
    // ===== URL BUILDING =====
    
    buildUrl(endpoint, params = {}) {
        const url = new URL(endpoint, this.baseURL);
        
        // Add query parameters
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });
        
        return url.toString();
    }
    
    // ===== CONFIGURATION =====
    
    mergeConfig(options) {
        return {
            headers: { ...this.defaultHeaders, ...options.headers },
            ...options
        };
    }
    
    // ===== CSRF TOKEN =====
    
    addCsrfToken(config) {
        const csrfToken = this.getCsrfToken();
        if (csrfToken) {
            config.headers['X-CSRF-Token'] = csrfToken;
        }
    }
    
    getCsrfToken() {
        // Try to get from meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            return metaTag.getAttribute('content');
        }
        
        // Try to get from cookie
        const cookies = document.cookie.split(';');
        for (const cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'csrf_token') {
                return decodeURIComponent(value);
            }
        }
        
        return null;
    }
    
    // ===== REQUEST MAKING =====
    
    async makeRequest(url, config) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.timeout);
        
        try {
            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            return response;
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }
            
            throw error;
        }
    }
    
    // ===== RESPONSE HANDLING =====
    
    async handleResponse(response) {
        const contentType = response.headers.get('content-type');
        const isJson = contentType && contentType.includes('application/json');
        
        let data;
        try {
            data = isJson ? await response.json() : await response.text();
        } catch (error) {
            throw new Error('Invalid response format');
        }
        
        // Handle HTTP errors
        if (!response.ok) {
            const error = new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
            error.status = response.status;
            error.data = data;
            throw error;
        }
        
        // Handle API errors
        if (data.error || data.success === false) {
            const error = new Error(data.message || 'API request failed');
            error.code = data.code || 'API_ERROR';
            error.data = data;
            throw error;
        }
        
        return data;
    }
    
    // ===== ERROR HANDLING =====
    
    async handleError(error, endpoint, options) {
        console.error('API Error:', error);
        
        // Log error for debugging
        if (window.apsUtils) {
            window.apsUtils.error('API Error:', error.message, endpoint);
        }
        
        // Retry logic for network errors
        if (this.shouldRetry(error) && (!options.retryCount || options.retryCount < this.retryAttempts)) {
            options.retryCount = (options.retryCount || 0) + 1;
            
            await this.delay(this.retryDelay * options.retryCount);
            
            console.log(`Retrying request (${options.retryCount}/${this.retryAttempts}):`, endpoint);
            return this.request(endpoint, options);
        }
        
        // Handle different error types
        if (error.status === 401) {
            this.handleUnauthorized();
        } else if (error.status === 403) {
            this.handleForbidden();
        } else if (error.status === 404) {
            this.handleNotFound();
        } else if (error.status >= 500) {
            this.handleServerError(error);
        }
        
        // Show user-friendly error message
        this.showErrorMessage(error);
        
        // Re-throw error for further handling
        throw error;
    }
    
    shouldRetry(error) {
        // Retry on network errors and 5xx server errors
        return (
            error.name === 'TypeError' || // Network error
            error.name === 'AbortError' || // Timeout
            (error.status && error.status >= 500)
        );
    }
    
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    // ===== ERROR HANDLERS =====
    
    handleUnauthorized() {
        // Redirect to login or show login modal
        if (window.apsUtils) {
            window.apsUtils.showToast('Please log in to continue', 'warning');
        }
        
        // Store intended URL for redirect after login
        sessionStorage.setItem('intended_url', window.location.href);
        
        // Redirect to login page
        setTimeout(() => {
            window.location.href = `${this.baseURL}/login`;
        }, 2000);
    }
    
    handleForbidden() {
        if (window.apsUtils) {
            window.apsUtils.showToast('You do not have permission to perform this action', 'error');
        }
    }
    
    handleNotFound() {
        if (window.apsUtils) {
            window.apsUtils.showToast('The requested resource was not found', 'warning');
        }
    }
    
    handleServerError(error) {
        if (window.apsUtils) {
            window.apsUtils.showToast('Server error occurred. Please try again later', 'error');
        }
    }
    
    showErrorMessage(error) {
        const message = this.getUserFriendlyMessage(error);
        
        if (window.apsUtils) {
            window.apsUtils.showToast(message, 'error');
        } else {
            alert(message);
        }
    }
    
    getUserFriendlyMessage(error) {
        // Return user-friendly error messages
        if (error.name === 'AbortError') {
            return 'Request timed out. Please check your connection and try again.';
        }
        
        if (error.name === 'TypeError') {
            return 'Network error. Please check your internet connection.';
        }
        
        if (error.status === 429) {
            return 'Too many requests. Please wait a moment and try again.';
        }
        
        if (error.data && error.data.message) {
            return error.data.message;
        }
        
        return 'An error occurred. Please try again later.';
    }
}

// ===== PROPERTY API =====
class PropertyApi extends APSApiClient {
    async searchProperties(filters = {}) {
        return this.post('/api/properties/search', filters);
    }
    
    async getFeaturedProperties() {
        return this.get('/api/properties/featured');
    }
    
    async getPropertyDetails(propertyId) {
        return this.get(`/api/properties/${propertyId}`);
    }
    
    async getPropertyImages(propertyId) {
        return this.get(`/api/properties/${propertyId}/images`);
    }
    
    async saveProperty(propertyId) {
        return this.post('/api/properties/save', { property_id: propertyId });
    }
    
    async unsaveProperty(propertyId) {
        return this.delete(`/api/properties/${propertyId}/save`);
    }
    
    async getPropertyReviews(propertyId) {
        return this.get(`/api/properties/${propertyId}/reviews`);
    }
    
    async addPropertyReview(propertyId, review) {
        return this.post(`/api/properties/${propertyId}/reviews`, review);
    }
    
    async getPropertyStats(propertyId) {
        return this.get(`/api/properties/${propertyId}/stats`);
    }
}

// ===== CONTACT API =====
class ContactApi extends APSApiClient {
    async submitContactForm(formData) {
        return this.post('/api/contact/submit', formData);
    }
    
    async subscribeNewsletter(email) {
        return this.post('/api/newsletter/subscribe', { email });
    }
    
    async requestCallback(formData) {
        return this.post('/api/contact/callback', formData);
    }
    
    async scheduleVisit(formData) {
        return this.post('/api/contact/schedule-visit', formData);
    }
    
    async getContactInfo() {
        return this.get('/api/contact/info');
    }
}

// ===== USER API =====
class UserApi extends APSApiClient {
    async login(credentials) {
        return this.post('/api/auth/login', credentials);
    }
    
    async register(userData) {
        return this.post('/api/auth/register', userData);
    }
    
    async logout() {
        return this.post('/api/auth/logout');
    }
    
    async getProfile() {
        return this.get('/api/user/profile');
    }
    
    async updateProfile(userData) {
        return this.put('/api/user/profile', userData);
    }
    
    async changePassword(passwordData) {
        return this.post('/api/user/change-password', passwordData);
    }
    
    async getSavedProperties() {
        return this.get('/api/user/saved-properties');
    }
    
    async getUserActivity() {
        return this.get('/api/user/activity');
    }
    
    async forgotPassword(email) {
        return this.post('/api/auth/forgot-password', { email });
    }
    
    async resetPassword(resetData) {
        return this.post('/api/auth/reset-password', resetData);
    }
}

// ===== SEARCH API =====
class SearchApi extends APSApiClient {
    async quickSearch(query) {
        return this.get('/api/search/quick', { q: query });
    }
    
    async advancedSearch(filters) {
        return this.post('/api/search/advanced', filters);
    }
    
    async getSearchSuggestions(query) {
        return this.get('/api/search/suggestions', { q: query });
    }
    
    async getPopularSearches() {
        return this.get('/api/search/popular');
    }
    
    async saveSearch(searchData) {
        return this.post('/api/search/save', searchData);
    }
    
    async getSavedSearches() {
        return this.get('/api/search/saved');
    }
    
    async deleteSavedSearch(searchId) {
        return this.delete(`/api/search/saved/${searchId}`);
    }
}

// ===== CONTENT API =====
class ContentApi extends APSApiClient {
    async getPageContent(page) {
        return this.get(`/api/content/${page}`);
    }
    
    async getBlogPosts() {
        return this.get('/api/blog/posts');
    }
    
    async getBlogPost(slug) {
        return this.get(`/api/blog/posts/${slug}`);
    }
    
    async getTestimonials() {
        return this.get('/api/content/testimonials');
    }
    
    async getFAQs() {
        return this.get('/api/content/faqs');
    }
    
    async getLocations() {
        return this.get('/api/content/locations');
    }
    
    async getPropertyTypes() {
        return this.get('/api/content/property-types');
    }
    
    async getServices() {
        return this.get('/api/content/services');
    }
}

// ===== INITIALIZATION =====
const api = {
    properties: new PropertyApi(),
    contact: new ContactApi(),
    user: new UserApi(),
    search: new SearchApi(),
    content: new ContentApi()
};

// ===== EXPORT =====
window.APSApi = APSApiClient;
window.api = api;

// Add to APS module registry
if (window.APS) {
    window.APS.api = api;
    window.APS.modules.api = api;
}
