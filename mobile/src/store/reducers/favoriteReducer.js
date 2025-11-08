const initialState = {
  favorites: [],
  loading: false,
  error: null,
};

const favoriteReducer = (state = initialState, action) => {
  switch (action.type) {
    case 'FAVORITE_ADD':
      return {
        ...state,
        favorites: [...state.favorites, action.payload.propertyId],
      };

    case 'FAVORITE_REMOVE':
      return {
        ...state,
        favorites: state.favorites.filter(id => id !== action.payload.propertyId),
      };

    case 'FAVORITES_LOAD':
      return {
        ...state,
        favorites: action.payload.favorites,
        loading: false,
      };

    case 'FAVORITES_LOADING':
      return {
        ...state,
        loading: true,
        error: null,
      };

    case 'FAVORITES_ERROR':
      return {
        ...state,
        loading: false,
        error: action.payload.error,
      };

    default:
      return state;
  }
};

export default favoriteReducer;
