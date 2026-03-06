import {
  USER_UPDATE_REQUEST,
  USER_UPDATE_SUCCESS,
  USER_UPDATE_FAILURE,
} from '../actions';

const initialState = {
  profile: null,
  loading: false,
  error: null,
};

const userReducer = (state = initialState, action) => {
  switch (action.type) {
    case USER_UPDATE_REQUEST:
      return {
        ...state,
        loading: true,
        error: null,
      };

    case USER_UPDATE_SUCCESS:
      return {
        ...state,
        loading: false,
        profile: action.payload.user,
        error: null,
      };

    case USER_UPDATE_FAILURE:
      return {
        ...state,
        loading: false,
        error: action.payload.error,
      };

    default:
      return state;
  }
};

export default userReducer;
