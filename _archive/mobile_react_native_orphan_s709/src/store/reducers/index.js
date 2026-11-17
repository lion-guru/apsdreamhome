import {combineReducers} from 'redux';
import {persistReducer} from 'redux-persist';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Import reducers
import authReducer from './src/store/reducers/authReducer';
import propertyReducer from './src/store/reducers/propertyReducer';
import bookingReducer from './src/store/reducers/bookingReducer';
import userReducer from './src/store/reducers/userReducer';
import commissionReducer from './src/store/reducers/commissionReducer';
import favoriteReducer from './src/store/reducers/favoriteReducer';

// Persist configuration
const persistConfig = {
  key: 'root',
  storage: AsyncStorage,
  whitelist: ['auth', 'user', 'favorites'], // Only persist these reducers
  blacklist: ['loading', 'error'], // Don't persist loading and error states
};

// Combine all reducers
const rootReducer = combineReducers({
  auth: authReducer,
  properties: propertyReducer,
  bookings: bookingReducer,
  user: userReducer,
  commission: commissionReducer,
  favorites: favoriteReducer,
  loading: (state = {}, action) => {
    const {type} = action;
    const matches = /(.*)_(REQUEST|SUCCESS|FAILURE)/.exec(type);

    if (!matches) return state;

    const [, requestName, requestState] = matches;

    return {
      ...state,
      [requestName]: requestState === 'REQUEST',
    };
  },
  error: (state = null, action) => {
    const {type, error} = action;

    if (type.endsWith('_FAILURE')) {
      return error;
    }

    if (type.endsWith('_SUCCESS') || type.endsWith('_REQUEST')) {
      return null;
    }

    return state;
  },
});

// Create persisted reducer
const persistedReducer = persistReducer(persistConfig, rootReducer);

export default persistedReducer;
