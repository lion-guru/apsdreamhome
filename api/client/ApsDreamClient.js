// APS Dream Home API Client (JavaScript)
// Save this as ApsDreamClient.js

class ApsDreamClient {
  /**
   * Initialize the API client
   * @param {string} baseUrl - Base URL of the API (e.g., 'http://localhost/apsdreamhome/api/v1')
   * @param {string} apiKey - API key for authentication (optional, can be set later)
   * @param {Object} options - Additional options
   * @param {boolean} options.debug - Enable debug mode to log requests/responses
   * @param {Object} options.fetchOptions - Additional fetch options
   */
  constructor(baseUrl, apiKey = null, options = {}) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
    this.apiKey = apiKey;
    this.debug = options.debug || false;
    this.fetchOptions = options.fetchOptions || {};
    this.authToken = null;
  }

  /**
   * Set the API key
   * @param {string} apiKey - The API key
   */
  setApiKey(apiKey) {
    this.apiKey = apiKey;
    return this;
  }

  /**
   * Set the authentication token
   * @param {string} token - The authentication token
   */
  setAuthToken(token) {
    this.authToken = token;
    return this;
  }

  /**
   * Make an HTTP request to the API
   * @private
   */
  async request(method, endpoint, data = null, requiresAuth = true) {
    const url = `${this.baseUrl}/${endpoint.replace(/^\/+/, '')}`;
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (requiresAuth) {
      if (this.authToken) {
        headers['Authorization'] = `Bearer ${this.authToken}`;
      } else if (this.apiKey) {
        headers['Authorization'] = `Bearer ${this.apiKey}`;
      } else {
        throw new Error('Authentication required but no API key or token provided');
      }
    }

    const options = {
      method: method.toUpperCase(),
      headers,
      ...this.fetchOptions,
    };

    if (data && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase())) {
      options.body = JSON.stringify(data);
    }

    if (this.debug) {
      console.log(`[${method}] ${url}`, { options, data });
    }

    try {
      const response = await fetch(url, options);
      let responseData;

      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        responseData = await response.json();
      } else {
        responseData = await response.text();
      }

      if (!response.ok) {
        const error = new Error(responseData.message || responseData.error || 'Request failed');
        error.status = response.status;
        error.data = responseData;
        throw error;
      }

      if (this.debug) {
        console.log(`[${method} ${response.status}] ${url}`, responseData);
      }

      return responseData;
    } catch (error) {
      if (this.debug) {
        console.error(`[${method} ERROR] ${url}`, error);
      }
      throw error;
    }
  }

  // ===== AUTHENTICATION =====

  
  /**
   * Login with email and password
   * @param {string} email - User email
   * @param {string} password - User password
   * @returns {Promise<Object>} Response data with user and token
   */
  async login(email, password) {
    const data = await this.request('POST', '/auth/login', { email, password }, false);
    if (data.token) {
      this.authToken = data.token;
    }
    return data;
  }

  /**
   * Logout (revoke current token)
   * @returns {Promise<Object>} Response data
   */
  async logout() {
    if (!this.authToken && !this.apiKey) {
      throw new Error('Not authenticated');
    }
    const data = await this.request('POST', '/auth/logout');
    this.authToken = null;
    return data;
  }

  // ===== PROFILE =====

  
  /**
   * Get current user profile
   * @returns {Promise<Object>} User profile data
   */
  async getProfile() {
    return this.request('GET', '/profile');
  }

  /**
   * Update current user profile
   * @param {Object} profileData - Profile data to update
   * @returns {Promise<Object>} Updated profile data
   */
  async updateProfile(profileData) {
    return this.request('PUT', '/profile', profileData);
  }

  // ===== PROPERTIES =====
  
  /**
   * List properties with optional filters
   * @param {Object} [filters={}] - Filter criteria
   * @returns {Promise<Array>} List of properties
   */
  async getProperties(filters = {}) {
    const query = new URLSearchParams(filters).toString();
    const endpoint = query ? `/properties?${query}` : '/properties';
    return this.request('GET', endpoint, null, false);
  }

  /**
   * Get a single property by ID
   * @param {string|number} id - Property ID
   * @returns {Promise<Object>} Property data
   */
  async getProperty(id) {
    return this.request('GET', `/properties/${id}`, null, false);
  }

  /**
   * Create a new property
   * @param {Object} propertyData - Property data
   * @returns {Promise<Object>} Created property data
   */
  async createProperty(propertyData) {
    return this.request('POST', '/properties', propertyData);
  }

  /**
   * Update a property
   * @param {string|number} id - Property ID
   * @param {Object} propertyData - Property data to update
   * @returns {Promise<Object>} Updated property data
   */
  async updateProperty(id, propertyData) {
    return this.request('PUT', `/properties/${id}`, propertyData);
  }

  /**
   * Delete a property
   * @param {string|number} id - Property ID
   * @returns {Promise<Object>} Deletion result
   */
  async deleteProperty(id) {
    return this.request('DELETE', `/properties/${id}`);
  }

  // ===== USERS (Admin only) =====
  
  /**
   * List users (admin only)
   * @param {Object} [filters={}] - Filter criteria
   * @returns {Promise<Array>} List of users
   */
  async getUsers(filters = {}) {
    const query = new URLSearchParams(filters).toString();
    const endpoint = query ? `/users?${query}` : '/users';
    return this.request('GET', endpoint);
  }

  /**
   * Get a single user by ID (admin only)
   * @param {string|number} id - User ID
   * @returns {Promise<Object>} User data
   */
  async getUser(id) {
    return this.request('GET', `/users/${id}`);
  }

  /**
   * Create a new user (admin only)
   * @param {Object} userData - User data
   * @returns {Promise<Object>} Created user data
   */
  async createUser(userData) {
    return this.request('POST', '/users', userData);
  }

  /**
   * Update a user (admin only)
   * @param {string|number} id - User ID
   * @param {Object} userData - User data to update
   * @returns {Promise<Object>} Updated user data
   */
  async updateUser(id, userData) {
    return this.request('PUT', `/users/${id}`, userData);
  }

  /**
   * Delete a user (admin only)
   * @param {string|number} id - User ID
   * @returns {Promise<Object>} Deletion result
   */
  async deleteUser(id) {
    return this.request('DELETE', `/users/${id}`);
  }
}

// For Node.js/CommonJS
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ApsDreamClient;
}

// For ES Modules
if (typeof window !== 'undefined') {
  window.ApsDreamClient = ApsDreamClient;
}
