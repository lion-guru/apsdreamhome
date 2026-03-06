<?php
/**
 * Google Maps API Utility Functions
 * Provides centralized handling for Google Maps API integration
 */

class GoogleMapsUtil {
    
    /**
     * Get Google Maps API Key from environment configuration
     * 
     * @return string Google Maps API key
     */
    public static function getApiKey() {
        // Try to get from environment variable
        $apiKey = getenv('GOOGLE_MAPS_API_KEY');
        
        // Fallback to constant if environment variable not set
        if (empty($apiKey) && defined('GOOGLE_MAPS_API_KEY')) {
            $apiKey = GOOGLE_MAPS_API_KEY;
        }
        
        // Return placeholder if no key configured
        if (empty($apiKey) || $apiKey === 'YOUR_GOOGLE_MAPS_API_KEY') {
            return 'YOUR_GOOGLE_MAPS_API_KEY';
        }
        
        return $apiKey;
    }
    
    /**
     * Generate Google Maps API script tag with proper configuration
     * 
     * @param string $callback Function to call when API loads
     * @param array $libraries Additional libraries to load
     * @return string HTML script tag
     */
    public static function getScriptTag($callback = 'initMap', $libraries = []) {
        $apiKey = self::getApiKey();
        $librariesParam = '';
        
        if (!empty($libraries)) {
            $librariesParam = '&libraries=' . implode(',', $libraries);
        }
        
        $script = "<script>";
        $script .= "if (typeof google === 'undefined' || typeof google.maps === 'undefined') {";
        $script .= "const script = document.createElement('script');";
        $script .= "script.src = 'https://maps.googleapis.com/maps/api/js?key=" . $apiKey . "&callback=" . $callback . $librariesParam . "';";
        $script .= "script.async = true;";
        $script .= "script.defer = true;";
        $script .= "window." . $callback . " = " . $callback . ";";
        $script .= "document.head.appendChild(script);";
        $script .= "} else {";
        $script .= $callback . "();";
        $script .= "}";
        $script .= "</script>";
        
        return $script;
    }
    
    /**
     * Generate Google Places Autocomplete script
     * 
     * @param string $inputId ID of the input element
     * @param string $mapId ID of the map container (optional)
     * @return string JavaScript code for Places Autocomplete
     */
    public static function getPlacesAutocompleteScript($inputId, $mapId = null) {
        $script = "<script>";
        $script .= "function initPlacesAutocomplete() {";
        $script .= "const input = document.getElementById('" . $inputId . "');";
        $script .= "if (input && typeof google !== 'undefined' && google.maps && google.maps.places) {";
        $script .= "const autocomplete = new google.maps.places.Autocomplete(input, {";
        $script .= "types: ['geocode', 'establishment'],";
        $script .= "componentRestrictions: { country: 'in' }";
        $script .= "});";
        
        if ($mapId) {
            $script .= "autocomplete.addListener('place_changed', function() {";
            $script .= "const place = autocomplete.getPlace();";
            $script .= "if (!place.geometry) return;";
            $script .= "const map = new google.maps.Map(document.getElementById('" . $mapId . "'), {";
            $script .= "zoom: 15,";
            $script .= "center: place.geometry.location";
            $script .= "});";
            $script .= "new google.maps.Marker({";
            $script .= "map: map,";
            $script .= "position: place.geometry.location";
            $script .= "});";
            $script .= "});";
        }
        
        $script .= "}";
        $script .= "}";
        
        // Load Places library and initialize
        $script .= "if (typeof google !== 'undefined' && google.maps && google.maps.places) {";
        $script .= "initPlacesAutocomplete();";
        $script .= "} else {";
        $script .= "// Load Google Maps with Places library";
        $script .= "const script = document.createElement('script');";
        $script .= "script.src = 'https://maps.googleapis.com/maps/api/js?key=" . self::getApiKey() . "&libraries=places&callback=initPlacesAutocomplete';";
        $script .= "script.async = true;";
        $script .= "script.defer = true;";
        $script .= "window.initPlacesAutocomplete = initPlacesAutocomplete;";
        $script .= "document.head.appendChild(script);";
        $script .= "}";
        $script .= "</script>";
        
        return $script;
    }
    
    /**
     * Get latitude and longitude from address using Geocoding API
     * 
     * @param string $address Address to geocode
     * @return array|null Array with lat and lng, or null on failure
     */
    public static function geocodeAddress($address) {
        $apiKey = self::getApiKey();
        
        if ($apiKey === 'YOUR_GOOGLE_MAPS_API_KEY') {
            // Return default coordinates for demo purposes
            return ['lat' => 26.7606, 'lng' => 83.3732];
        }
        
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if ($data && $data['status'] === 'OK' && !empty($data['results'][0]['geometry']['location'])) {
            return $data['results'][0]['geometry']['location'];
        }
        
        return null;
    }
    
    /**
     * Get place details from place ID
     * 
     * @param string $placeId Google Places place ID
     * @return array|null Place details or null on failure
     */
    public static function getPlaceDetails($placeId) {
        $apiKey = self::getApiKey();
        
        if ($apiKey === 'YOUR_GOOGLE_MAPS_API_KEY') {
            return null;
        }
        
        $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=" . urlencode($placeId) . "&key=" . $apiKey;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if ($data && $data['status'] === 'OK') {
            return $data['result'];
        }
        
        return null;
    }
}

// Helper function for easy access
function google_maps_api_key() {
    return GoogleMapsUtil::getApiKey();
}

function google_maps_script($callback = 'initMap', $libraries = []) {
    return GoogleMapsUtil::getScriptTag($callback, $libraries);
}

function google_places_autocomplete($inputId, $mapId = null) {
    return GoogleMapsUtil::getPlacesAutocompleteScript($inputId, $mapId);
}
?>