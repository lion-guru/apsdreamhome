/**
 * Utility functions for API integrations
 * - Pincode API (Postalpincode.in)
 * - IFSC API (Razorpay)
 */

/**
 * Fetch details from Pincode
 * @param {string} pincode - 6 digit pincode
 * @returns {Promise<Object>} - { district, state, city, country } or { error }
 */
async function fetchPincodeDetails(pincode) {
    if (!pincode || pincode.length !== 6) {
        return { error: "Invalid Pincode" };
    }
    try {
        const response = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
        const data = await response.json();
        
        if (data && data[0].Status === "Success") {
            const details = data[0].PostOffice[0];
            return {
                district: details.District,
                state: details.State,
                city: details.Block, // Often block is used as city/locality
                country: details.Country
            };
        } else {
            return { error: "Pincode not found" };
        }
    } catch (e) {
        console.error("Pincode API Error:", e);
        return { error: "API Error" };
    }
}

/**
 * Fetch details from IFSC
 * @param {string} ifsc - 11 character IFSC code
 * @returns {Promise<Object>} - { bank, branch, address, city, state } or { error }
 */
async function fetchIfscDetails(ifsc) {
    if (!ifsc || ifsc.length !== 11) {
        return { error: "Invalid IFSC Code" };
    }
    try {
        const response = await fetch(`https://ifsc.razorpay.com/${ifsc}`);
        if (!response.ok) throw new Error("IFSC not found");
        const data = await response.json();
        return {
            bank: data.BANK,
            branch: data.BRANCH,
            address: data.ADDRESS,
            city: data.CITY,
            state: data.STATE
        };
    } catch (e) {
        console.error("IFSC API Error:", e);
        return { error: "IFSC not found" };
    }
}

// Export for module usage if needed, otherwise global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { fetchPincodeDetails, fetchIfscDetails };
}
