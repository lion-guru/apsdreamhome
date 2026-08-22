<?php
/**
 * View Helper - APS Dream Home
 * 
 * Provides safe output methods with auto-escaping for XSS prevention.
 * Usage in views: <?= View::e($var) ?> or <?= e($var) ?>
 * 
 * Convention:
 * - View::e($var) - HTML escape (default, safe for all contexts)
 * - View::esc_url($url) - URL escape (safe for href/src)
 * - View::esc_attr($str) - Attribute escape (safe for HTML attributes)
 * - View::esc_html($str) - HTML escape (alias for e())
 * - View::raw($str) - Raw output (USE WITH CAUTION - only for trusted HTML)
 * - View::json($var) - JSON encode for inline scripts
 */

namespace App\Helpers;

class ViewHelper
{
    /**
     * HTML escape - safe for all HTML contexts
     */
    public static function e($string, $flags = ENT_QUOTES, $encoding = 'UTF-8', $doubleEncode = true)
    {
        if (is_array($string) || is_object($string)) {
            // Don't output arrays/objects directly - log and return empty
            error_log('ViewHelper::e() received array/object: ' . gettype($string));
            return '';
        }
        if ($string === null) {
            return '';
        }
        return htmlspecialchars((string)$string, $flags, $encoding, $doubleEncode);
    }

    /**
     * Alias for e() - HTML escape
     */
    public static function esc_html($string, $flags = ENT_QUOTES, $encoding = 'UTF-8', $doubleEncode = true)
    {
        return self::e($string, $flags, $encoding, $doubleEncode);
    }

    /**
     * URL escape - safe for href, src, action attributes
     */
    public static function esc_url($url, $protocols = ['http', 'https', 'mailto', 'tel'])
    {
        if (empty($url)) {
            return '';
        }
        $url = trim($url);
        
        // Check if URL has a valid protocol
        $parsed = parse_url($url);
        if ($parsed !== false && isset($parsed['scheme'])) {
            $scheme = strtolower($parsed['scheme']);
            if (!in_array($scheme, $protocols)) {
                // Invalid protocol - return empty to prevent javascript: etc.
                error_log('ViewHelper::esc_url() blocked invalid protocol: ' . $scheme);
                return '';
            }
        }
        
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Attribute escape - safe for HTML attributes
     */
    public static function esc_attr($string, $flags = ENT_QUOTES)
    {
        return self::e($string, $flags);
    }

    /**
     * Raw output - USE WITH EXTREME CAUTION
     * Only for trusted, sanitized HTML from known-safe sources
     */
    public static function raw($string)
    {
        if ($string === null) return '';
        if (is_array($string) || is_object($string)) {
            error_log('ViewHelper::raw() received array/object');
            return '';
        }
        return (string)$string;
    }

    /**
     * JSON encode for inline scripts/data attributes
     */
    public static function json($value, $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)
    {
        return json_encode($value, $flags);
    }

    /**
     * Safe output with context-aware escaping
     * 
     * @param mixed $value
     * @param string $context - 'html', 'url', 'attr', 'js', 'raw'
     * @return string
     */
    public static function out($value, $context = 'html')
    {
        switch ($context) {
            case 'url':
                return self::esc_url($value);
            case 'attr':
                return self::esc_attr($value);
            case 'js':
                return self::json($value);
            case 'raw':
                return self::raw($value);
            case 'html':
            default:
                return self::e($value);
        }
    }

    /**
     * Format number for display
     */
    public static function number($value, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        return number_format((float)$value, $decimals, $decPoint, $thousandsSep);
    }

    /**
     * Format currency (INR)
     */
    public static function currency($value, $symbol = '₹', $decimals = 0)
    {
        return $symbol . self::number($value, $decimals);
    }

    /**
     * Format date
     */
    public static function date($date, $format = 'd M Y')
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '—';
        }
        try {
            return date($format, strtotime($date));
        } catch (\Exception $e) {
            return '—';
        }
    }

    /**
     * Format datetime
     */
    public static function datetime($date, $format = 'd M Y, h:i A')
    {
        return self::date($date, $format);
    }

    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $suffix = '...')
    {
        $text = self::e($text);
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Convert newlines to <br>
     */
    public static function nl2br($text)
    {
        return nl2br(self::e($text));
    }

    /**
     * Get badge class for status
     */
    public static function badge($status)
    {
        $map = [
            'active'        => 'bg-success',
            'pending'       => 'bg-warning text-dark',
            'completed'     => 'bg-success',
            'approved'      => 'bg-success',
            'rejected'      => 'bg-danger',
            'cancelled'     => 'bg-secondary',
            'overdue'       => 'bg-danger',
            'draft'         => 'bg-info',
            'expired'       => 'bg-dark',
            'suspended'     => 'bg-warning text-dark',
        ];
        return $map[strtolower($status)] ?? 'bg-secondary';
    }
}

// Global convenience function (already in bootstrap.php but ensuring availability)
if (!function_exists('e')) {
    function e($string, $flags = ENT_QUOTES, $encoding = 'UTF-8', $doubleEncode = true)
    {
        return \App\Helpers\ViewHelper::e($string, $flags, $encoding, $doubleEncode);
    }
}

if (!function_exists('h')) {
    function h($string, $flags = ENT_QUOTES, $encoding = 'UTF-8', $doubleEncode = true)
    {
        return \App\Helpers\ViewHelper::e($string, $flags, $encoding, $doubleEncode);
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url)
    {
        return \App\Helpers\ViewHelper::esc_url($url);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($string)
    {
        return \App\Helpers\ViewHelper::esc_attr($string);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($string, $flags = ENT_QUOTES, $encoding = 'UTF-8', $doubleEncode = true)
    {
        return \App\Helpers\ViewHelper::esc_html($string, $flags, $encoding, $doubleEncode);
    }
}