<?php
/**
 * APS Dream Home - Google Analytics Integration
 * Complete analytics setup with tracking, events, and reporting
 */

namespace App\Services\Legacy\Analytics;

// Google Analytics configuration
if (!defined('GA_TRACKING_ID')) define('GA_TRACKING_ID', 'GA_MEASUREMENT_ID');
if (!defined('GA_API_SECRET')) define('GA_API_SECRET', 'YOUR_GA_API_SECRET');
if (!defined('GA_PROPERTY_ID')) define('GA_PROPERTY_ID', 'YOUR_PROPERTY_ID');

// Enhanced Google Analytics class
class GoogleAnalytics {

    private $tracking_id;
    private $api_secret;
    private $property_id;
    private $debug_mode;

    public function __construct($tracking_id = GA_TRACKING_ID, $debug_mode = false) {
        $this->tracking_id = $tracking_id;
        $this->debug_mode = $debug_mode;
        $this->api_secret = GA_API_SECRET;
        $this->property_id = GA_PROPERTY_ID;
    }

    // Generate Google Analytics script tag
    public function get_ga_script() {
        if (empty($this->tracking_id) || $this->tracking_id === 'GA_MEASUREMENT_ID') {
            return '<!-- Google Analytics not configured -->';
        }

        return "
<!-- Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id={$this->tracking_id}\"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  // Basic configuration
  gtag('config', '{$this->tracking_id}', {
    'anonymize_ip': true,
    'allow_ad_features': false,
    'allow_ad_personalization_signals': false,
    'custom_map': {
      'dimension1': 'user_type',
      'dimension2': 'property_type',
      'dimension3': 'user_role'
    }
  });

  // Enhanced ecommerce tracking (if enabled)
  gtag('config', '{$this->tracking_id}', {
    'custom_map': {'dimension4': 'property_id'}
  });
</script>";
    }

    // Track page view
    public function track_page_view($page_title = '', $page_location = '') {
        if (empty($this->tracking_id)) return;

        $title = !empty($page_title) ? $page_title : (function_exists('get_page_title') ? get_page_title() : 'APS Dream Home');
        $location = !empty($page_location) ? $page_location : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');

        echo "
<script>
  gtag('config', '{$this->tracking_id}', {
    'page_title': '{$title}',
    'page_location': '{$location}'
  });
</script>";
    }

    // Track custom events
    public function track_event($event_category, $event_action, $event_label = '', $value = '') {
        if (empty($this->tracking_id)) return;

        $parameters = [
            'event_category' => $event_category,
            'event_action' => $event_action
        ];

        if (!empty($event_label)) $parameters['event_label'] = $event_label;
        if (!empty($value)) $parameters['value'] = $value;

        echo "
<script>
  gtag('event', '{$event_action}', " . json_encode($parameters) . ");
</script>";
    }

    // Track property view
    public function track_property_view($property_id, $property_name, $property_type, $property_price) {
        $this->track_event('property', 'view', $property_name, $property_price);
    }
}
