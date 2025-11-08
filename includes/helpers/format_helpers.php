<?php
/**
 * Format Helpers
 * 
 * Contains various formatting functions used throughout the application
 */

if (!function_exists('format_currency')) {
    /**
     * Format a number as currency
     * 
     * @param float $amount The amount to format
     * @param string $currency The currency code (default: '₹' for Indian Rupees)
     * @param int $decimals Number of decimal places (default: 0)
     * @return string Formatted currency string
     */
    function format_currency($amount, $currency = '₹', $decimals = 0) {
        // Convert to float if it's a string
        $amount = (float)$amount;
        
        // Format the number with thousands separators
        $formatted = number_format($amount, $decimals);
        
        // Add currency symbol
        if ($currency === '₹') {
            // For Indian Rupees, add symbol before the number
            return $currency . ' ' . $formatted;
        } else {
            // For other currencies, use standard format
            return $currency . $formatted;
        }
    }
}

// Add more formatting functions as needed

// End of file format_helpers.php
