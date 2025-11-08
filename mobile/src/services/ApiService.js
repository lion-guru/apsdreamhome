import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// API Configuration
const API_BASE_URL = __DEV__
  ? 'http://localhost/apsdreamhomefinal/api'
  : 'https://api.apsdreamhome.com/v1';

// Create axios instance
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor
apiClient.interceptors.request.use(
  async (config) => {
    // Add API key if available
    const apiKey = await AsyncStorage.getItem('api_key');
    if (apiKey) {
      config.headers['X-API-Key'] = apiKey;
    }

    // Add auth token if available
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

// Response interceptor
apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    const originalRequest = error.config;

    // Handle token refresh
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const refreshToken = await AsyncStorage.getItem('refresh_token');
        if (refreshToken) {
          const response = await axios.post(`${API_BASE_URL}/auth/refresh`, {
            refresh_token: refreshToken,
          });

          const {token} = response.data.data;
          await AsyncStorage.setItem('auth_token', token);

          originalRequest.headers['Authorization'] = `Bearer ${token}`;
          return apiClient(originalRequest);
        }
      } catch (refreshError) {
        // Refresh failed, logout user
        await AsyncStorage.multiRemove(['auth_token', 'refresh_token', 'user_data']);
        // Navigate to login screen
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  },
);

class ApiService {
  // Authentication
  async login(email, password) {
    const response = await apiClient.post('/auth/login', {email, password});
    return response.data;
  }

  async register(userData) {
    const response = await apiClient.post('/auth/register', userData);
    return response.data;
  }

  async logout() {
    const response = await apiClient.post('/auth/logout');
    await AsyncStorage.multiRemove(['auth_token', 'refresh_token', 'user_data']);
    return response.data;
  }

  async getProfile() {
    const response = await apiClient.get('/auth/profile');
    return response.data;
  }

  async updateProfile(userData) {
    const response = await apiClient.put('/auth/profile', userData);
    return response.data;
  }

  // Properties
  async getProperties(filters = {}) {
    const params = new URLSearchParams();

    Object.keys(filters).forEach(key => {
      if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
        params.append(key, filters[key]);
      }
    });

    const response = await apiClient.get(`/properties?${params.toString()}`);
    return response.data;
  }

  async getProperty(id) {
    const response = await apiClient.get(`/properties/${id}`);
    return response.data;
  }

  async searchProperties(query, filters = {}) {
    const searchData = {
      query,
      ...filters,
    };
    const response = await apiClient.post('/properties/search', searchData);
    return response.data;
  }

  // Favorites
  async getFavorites() {
    const response = await apiClient.get('/favorites');
    return response.data;
  }

  async addToFavorites(propertyId) {
    const response = await apiClient.post('/favorites', {property_id: propertyId});
    return response.data;
  }

  async removeFromFavorites(propertyId) {
    const response = await apiClient.delete(`/favorites/${propertyId}`);
    return response.data;
  }

  // Bookings
  async getBookings(filters = {}) {
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
      if (filters[key] !== null && filters[key] !== undefined) {
        params.append(key, filters[key]);
      }
    });

    const response = await apiClient.get(`/bookings?${params.toString()}`);
    return response.data;
  }

  async createBooking(bookingData) {
    const response = await apiClient.post('/bookings', bookingData);
    return response.data;
  }

  async updateBooking(bookingId, bookingData) {
    const response = await apiClient.put(`/bookings/${bookingId}`, bookingData);
    return response.data;
  }

  async cancelBooking(bookingId) {
    const response = await apiClient.delete(`/bookings/${bookingId}`);
    return response.data;
  }

  // Commission (for agents)
  async getCommission(filters = {}) {
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
      if (filters[key] !== null && filters[key] !== undefined) {
        params.append(key, filters[key]);
      }
    });

    const response = await apiClient.get(`/commission?${params.toString()}`);
    return response.data;
  }

  async getCommissionSummary() {
    const response = await apiClient.get('/commission/summary');
    return response.data;
  }

  // Agents
  async getAgents(filters = {}) {
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
      if (filters[key] !== null && filters[key] !== undefined) {
        params.append(key, filters[key]);
      }
    });

    const response = await apiClient.get(`/agents?${params.toString()}`);
    return response.data;
  }

  async getAgentProfile(agentId) {
    const response = await apiClient.get(`/agents/${agentId}`);
    return response.data;
  }

  // Contact/Inquiry
  async submitInquiry(inquiryData) {
    const response = await apiClient.post('/inquiry', inquiryData);
    return response.data;
  }

  async submitPropertyInquiry(propertyId, inquiryData) {
    const response = await apiClient.post(`/properties/${propertyId}/inquiry`, inquiryData);
    return response.data;
  }

  // WhatsApp Integration
  async sendWhatsAppMessage(phone, message) {
    const response = await apiClient.post('/whatsapp/send', {phone, message});
    return response.data;
  }

  // File Upload
  async uploadFile(fileData, type = 'image') {
    const formData = new FormData();
    formData.append('file', fileData);
    formData.append('type', type);

    const response = await apiClient.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  // Analytics (for agents/admin)
  async getAnalytics(filters = {}) {
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
      if (filters[key] !== null && filters[key] !== undefined) {
        params.append(key, filters[key]);
      }
    });

    const response = await apiClient.get(`/analytics?${params.toString()}`);
    return response.data;
  }

  async getPropertyAnalytics(propertyId, filters = {}) {
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
      if (filters[key] !== null && filters[key] !== undefined) {
        params.append(key, filters[key]);
      }
    });

    const response = await apiClient.get(`/analytics/properties/${propertyId}?${params.toString()}`);
    return response.data;
  }

  // Notifications
  async getNotifications() {
    const response = await apiClient.get('/notifications');
    return response.data;
  }

  async markNotificationAsRead(notificationId) {
    const response = await apiClient.put(`/notifications/${notificationId}/read`);
    return response.data;
  }

  // Location services
  async getNearbyProperties(latitude, longitude, radius = 5000) {
    const response = await apiClient.get('/properties/nearby', {
      params: {
        lat: latitude,
        lng: longitude,
        radius,
      },
    });
    return response.data;
  }

  async getCities() {
    const response = await apiClient.get('/locations/cities');
    return response.data;
  }

  async getStates() {
    const response = await apiClient.get('/locations/states');
    return response.data;
  }

  // Compare properties
  async compareProperties(propertyIds) {
    const response = await apiClient.post('/properties/compare', {
      property_ids: propertyIds,
    });
    return response.data;
  }

  // Testimonials
  async submitTestimonial(testimonialData) {
    const response = await apiClient.post('/testimonials', testimonialData);
    return response.data;
  }

  // Settings
  async getSettings() {
    const response = await apiClient.get('/settings');
    return response.data;
  }

  async updateSettings(settingsData) {
    const response = await apiClient.put('/settings', settingsData);
    return response.data;
  }
}

// Create and export API service instance
const apiService = new ApiService();
export default apiService;

// Export axios instance for custom requests
export {apiClient};
