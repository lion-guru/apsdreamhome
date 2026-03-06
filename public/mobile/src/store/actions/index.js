import apiService from '../../services/ApiService';

// Action Types
export const AUTH_REQUEST = 'AUTH_REQUEST';
export const AUTH_SUCCESS = 'AUTH_SUCCESS';
export const AUTH_FAILURE = 'AUTH_FAILURE';
export const AUTH_LOGOUT = 'AUTH_LOGOUT';

export const PROPERTY_REQUEST = 'PROPERTY_REQUEST';
export const PROPERTY_SUCCESS = 'PROPERTY_SUCCESS';
export const PROPERTY_FAILURE = 'PROPERTY_FAILURE';
export const PROPERTY_ADD_FAVORITE = 'PROPERTY_ADD_FAVORITE';
export const PROPERTY_REMOVE_FAVORITE = 'PROPERTY_REMOVE_FAVORITE';

export const BOOKING_REQUEST = 'BOOKING_REQUEST';
export const BOOKING_SUCCESS = 'BOOKING_SUCCESS';
export const BOOKING_FAILURE = 'BOOKING_FAILURE';

export const USER_UPDATE_REQUEST = 'USER_UPDATE_REQUEST';
export const USER_UPDATE_SUCCESS = 'USER_UPDATE_SUCCESS';
export const USER_UPDATE_FAILURE = 'USER_UPDATE_FAILURE';

// Auth Actions
export const loginRequest = (email, password) => ({
  type: AUTH_REQUEST,
  payload: {email, password},
});

export const loginSuccess = (user, token) => ({
  type: AUTH_SUCCESS,
  payload: {user, token},
});

export const loginFailure = (error) => ({
  type: AUTH_FAILURE,
  payload: {error},
});

export const logout = () => ({
  type: AUTH_LOGOUT,
});

export const login = (email, password) => {
  return async (dispatch) => {
    dispatch(loginRequest(email, password));

    try {
      const response = await apiService.login(email, password);

      if (response.success) {
        dispatch(loginSuccess(response.data.user, response.data.token));
        return {success: true};
      } else {
        dispatch(loginFailure(response.error || 'Login failed'));
        return {success: false, error: response.error};
      }
    } catch (error) {
      dispatch(loginFailure(error.message));
      return {success: false, error: error.message};
    }
  };
};

export const register = (userData) => {
  return async (dispatch) => {
    dispatch(loginRequest());

    try {
      const response = await apiService.register(userData);

      if (response.success) {
        dispatch(loginSuccess(response.data, response.data.token));
        return {success: true};
      } else {
        dispatch(loginFailure(response.error || 'Registration failed'));
        return {success: false, error: response.error};
      }
    } catch (error) {
      dispatch(loginFailure(error.message));
      return {success: false, error: error.message};
    }
  };
};

export const logoutUser = () => {
  return async (dispatch) => {
    try {
      await apiService.logout();
    } catch (error) {
      console.error('Logout error:', error);
    }

    dispatch(logout());
  };
};

// Property Actions
export const fetchPropertiesRequest = (filters) => ({
  type: PROPERTY_REQUEST,
  payload: {filters},
});

export const fetchPropertiesSuccess = (properties) => ({
  type: PROPERTY_SUCCESS,
  payload: {properties},
});

export const fetchPropertiesFailure = (error) => ({
  type: PROPERTY_FAILURE,
  payload: {error},
});

export const addToFavorites = (propertyId) => ({
  type: PROPERTY_ADD_FAVORITE,
  payload: {propertyId},
});

export const removeFromFavorites = (propertyId) => ({
  type: PROPERTY_REMOVE_FAVORITE,
  payload: {propertyId},
});

export const fetchProperties = (filters = {}) => {
  return async (dispatch) => {
    dispatch(fetchPropertiesRequest(filters));

    try {
      const response = await apiService.getProperties(filters);

      if (response.success) {
        dispatch(fetchPropertiesSuccess(response.data));
        return {success: true};
      } else {
        dispatch(fetchPropertiesFailure(response.error || 'Failed to fetch properties'));
        return {success: false, error: response.error};
      }
    } catch (error) {
      dispatch(fetchPropertiesFailure(error.message));
      return {success: false, error: error.message};
    }
  };
};

export const searchProperties = (query, filters = {}) => {
  return async (dispatch) => {
    dispatch(fetchPropertiesRequest({...filters, search: query}));

    try {
      const response = await apiService.searchProperties(query, filters);

      if (response.success) {
        dispatch(fetchPropertiesSuccess(response.data));
        return {success: true};
      } else {
        dispatch(fetchPropertiesFailure(response.error || 'Search failed'));
        return {success: false, error: response.error};
      }
    } catch (error) {
      dispatch(fetchPropertiesFailure(error.message));
      return {success: false, error: error.message};
    }
  };
};

export const toggleFavorite = (propertyId, isFavorite) => {
  return async (dispatch) => {
    try {
      if (isFavorite) {
        await apiService.removeFromFavorites(propertyId);
        dispatch(removeFromFavorites(propertyId));
      } else {
        await apiService.addToFavorites(propertyId);
        dispatch(addToFavorites(propertyId));
      }
      return {success: true};
    } catch (error) {
      console.error('Toggle favorite error:', error);
      return {success: false, error: error.message};
    }
  };
};

// Booking Actions
export const createBookingRequest = (bookingData) => ({
  type: BOOKING_REQUEST,
  payload: {bookingData},
});

export const createBookingSuccess = (booking) => ({
  type: BOOKING_SUCCESS,
  payload: {booking},
});

export const createBookingFailure = (error) => ({
  type: BOOKING_FAILURE,
  payload: {error},
});

export const createBooking = (bookingData) => {
  return async (dispatch) => {
    dispatch(createBookingRequest(bookingData));

    try {
      const response = await apiService.createBooking(bookingData);

      if (response.success) {
        dispatch(createBookingSuccess(response.data));
        return {success: true, booking: response.data};
      } else {
        dispatch(createBookingFailure(response.error || 'Booking failed'));
        return {success: false, error: response.error};
      }
    } catch (error) {
      dispatch(createBookingFailure(error.message));
      return {success: false, error: error.message};
    }
  };
};

// User Actions
export const updateUserRequest = (userData) => ({
  type: USER_UPDATE_REQUEST,
  payload: {userData},
});

export const updateUserSuccess = (user) => ({
  type: USER_UPDATE_SUCCESS,
  payload: {user},
});

export const updateUserFailure = (error) => ({
  type: USER_UPDATE_FAILURE,
  payload: {error},
});

export const updateUserProfile = (userData) => {
  return async (dispatch) => {
    dispatch(updateUserRequest(userData));

    try {
      const response = await apiService.updateProfile(userData);

      if (response.success) {
        dispatch(updateUserSuccess(response.data));
        return {success: true, user: response.data};
      } else {
        dispatch(updateUserFailure(response.error || 'Update failed'));
        return {success: false, error: response.error};
      }
    } catch (error) {
      dispatch(updateUserFailure(error.message));
      return {success: false, error: error.message};
    }
  };
};
