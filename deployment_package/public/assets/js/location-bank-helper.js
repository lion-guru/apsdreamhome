/**
 * Location and Bank Helper
 * Handles Pincode and IFSC API lookups via the Associate Controller
 * Uses Fetch API (No jQuery dependency)
 */

const LocationBankHelper = {
  /**
   * Lookup Pincode details
   * @param {string} pincode - 6 digit pincode
   * @param {function} successCallback - Callback with data object
   * @param {function} errorCallback - Callback with error message
   */
  lookupPincode: function (pincode, successCallback, errorCallback) {
    if (!pincode || pincode.length !== 6) {
      if (errorCallback) errorCallback("Invalid Pincode length");
      return;
    }

    fetch("/associate/api/pincode?pincode=" + encodeURIComponent(pincode), {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.status === "success") {
          if (successCallback) successCallback(data);
        } else {
          if (errorCallback) errorCallback(data.message || "Pincode not found");
        }
      })
      .catch((error) => {
        console.error("Error fetching pincode:", error);
        if (errorCallback) errorCallback("Error fetching pincode details");
      });
  },

  /**
   * Lookup IFSC details
   * @param {string} ifsc - 11 character IFSC code
   * @param {function} successCallback - Callback with data object
   * @param {function} errorCallback - Callback with error message
   */
  lookupIFSC: function (ifsc, successCallback, errorCallback) {
    if (!ifsc || ifsc.length !== 11) {
      if (errorCallback) errorCallback("Invalid IFSC length");
      return;
    }

    fetch("/associate/api/ifsc?ifsc=" + encodeURIComponent(ifsc), {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.status === "success") {
          if (successCallback) successCallback(data);
        } else {
          if (errorCallback)
            errorCallback(data.message || "IFSC Code not found");
        }
      })
      .catch((error) => {
        console.error("Error fetching IFSC:", error);
        if (errorCallback) errorCallback("Error fetching bank details");
      });
  },

  /**
   * Get Current Geolocation
   * @param {function} successCallback - Callback with position object
   * @param {function} errorCallback - Callback with error message
   */
  getCurrentLocation: function (successCallback, errorCallback) {
    if (!navigator.geolocation) {
      if (errorCallback)
        errorCallback("Geolocation is not supported by your browser");
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (position) {
        if (successCallback) {
          successCallback({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
          });
        }
      },
      function (error) {
        let errorMsg = "Unable to retrieve your location";
        switch (error.code) {
          case error.PERMISSION_DENIED:
            errorMsg = "User denied the request for Geolocation.";
            break;
          case error.POSITION_UNAVAILABLE:
            errorMsg = "Location information is unavailable.";
            break;
          case error.TIMEOUT:
            errorMsg = "The request to get user location timed out.";
            break;
          case error.UNKNOWN_ERROR:
            errorMsg = "An unknown error occurred.";
            break;
        }
        if (errorCallback) errorCallback(errorMsg);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      }
    );
  },
};
