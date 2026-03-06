import {
  PROPERTY_REQUEST,
  PROPERTY_SUCCESS,
  PROPERTY_FAILURE,
  PROPERTY_ADD_FAVORITE,
  PROPERTY_REMOVE_FAVORITE,
} from '../actions';

const initialState = {
  properties: [],
  loading: false,
  error: null,
  favorites: [],
  lastFilters: {},
};

const propertyReducer = (state = initialState, action) => {
  switch (action.type) {
    case PROPERTY_REQUEST:
      return {
        ...state,
        loading: true,
        error: null,
        lastFilters: action.payload.filters || {},
      };

    case PROPERTY_SUCCESS:
      return {
        ...state,
        loading: false,
        properties: action.payload.properties || [],
        error: null,
      };

    case PROPERTY_FAILURE:
      return {
        ...state,
        loading: false,
        error: action.payload.error,
        properties: [],
      };

    case PROPERTY_ADD_FAVORITE:
      return {
        ...state,
        favorites: [...state.favorites, action.payload.propertyId],
      };

    case PROPERTY_REMOVE_FAVORITE:
      return {
        ...state,
        favorites: state.favorites.filter(id => id !== action.payload.propertyId),
      };

    default:
      return state;
  }
};

export default propertyReducer;
