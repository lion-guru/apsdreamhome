import {
  BOOKING_REQUEST,
  BOOKING_SUCCESS,
  BOOKING_FAILURE,
} from '../actions';

const initialState = {
  bookings: [],
  currentBooking: null,
  loading: false,
  error: null,
};

const bookingReducer = (state = initialState, action) => {
  switch (action.type) {
    case BOOKING_REQUEST:
      return {
        ...state,
        loading: true,
        error: null,
      };

    case BOOKING_SUCCESS:
      return {
        ...state,
        loading: false,
        currentBooking: action.payload.booking,
        bookings: [...state.bookings, action.payload.booking],
        error: null,
      };

    case BOOKING_FAILURE:
      return {
        ...state,
        loading: false,
        error: action.payload.error,
      };

    default:
      return state;
  }
};

export default bookingReducer;
