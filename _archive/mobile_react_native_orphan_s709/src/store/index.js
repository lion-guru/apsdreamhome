import {createStore, applyMiddleware} from 'redux';
import {persistStore} from 'redux-persist';
import thunk from 'redux-thunk';
import {composeWithDevTools} from 'redux-devtools-extension';
import rootReducer from './reducers';

// Middleware
const middleware = [thunk];

// Create store with middleware
const store = createStore(
  rootReducer,
  composeWithDevTools(applyMiddleware(...middleware)),
);

// Create persistor
const persistor = persistStore(store);

export {store, persistor};
export default store;
