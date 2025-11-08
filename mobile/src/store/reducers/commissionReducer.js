const initialState = {
  commission: {
    total: 0,
    monthly: 0,
    transactions: [],
  },
  loading: false,
  error: null,
};

const commissionReducer = (state = initialState, action) => {
  switch (action.type) {
    case 'COMMISSION_REQUEST':
      return {
        ...state,
        loading: true,
        error: null,
      };

    case 'COMMISSION_SUCCESS':
      return {
        ...state,
        loading: false,
        commission: action.payload,
        error: null,
      };

    case 'COMMISSION_FAILURE':
      return {
        ...state,
        loading: false,
        error: action.payload,
      };

    default:
      return state;
  }
};

export default commissionReducer;
