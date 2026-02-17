<?php
/**
 * APS Dream Home - Google Analytics Integration
 * Complete analytics setup with tracking, events, and reporting
 */

// Google Analytics configuration
define('GA_TRACKING_ID', 'GA_MEASUREMENT_ID'); // Replace with your actual GA ID
define('GA_API_SECRET', 'YOUR_GA_API_SECRET'); // Replace with your GA API Secret
define('GA_PROPERTY_ID', 'YOUR_PROPERTY_ID'); // Replace with your GA Property ID

// Enhanced Google Analytics class
class APS_Analytics {

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

        $title = !empty($page_title) ? $page_title : get_page_title();
        $location = !empty($page_location) ? $page_location : get_current_url();

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

        // Enhanced ecommerce tracking
        echo "
<script>
  gtag('event', 'view_item', {
    'event_category': 'ecommerce',
    'event_label': '{$property_name}',
    'items': [{
      'item_id': '{$property_id}',
      'item_name': '{$property_name}',
      'category': '{$property_type}',
      'price': '{$property_price}',
      'currency': 'INR'
    }]
  });
</script>";
    }

    // Track inquiry submission
    public function track_inquiry($inquiry_type, $property_id = '', $source = 'website') {
        $this->track_event('inquiry', 'submit', $inquiry_type);

        echo "
<script>
  gtag('event', 'generate_lead', {
    'event_category': 'engagement',
    'event_label': '{$inquiry_type}',
    'value': '{$property_id}',
    'custom_map': {'dimension1': '{$source}'}
  });
</script>";
    }

    // Track user registration
    public function track_registration($user_type = 'customer') {
        $this->track_event('user', 'register', $user_type);

        echo "
<script>
  gtag('event', 'sign_up', {
    'event_category': 'engagement',
    'event_label': '{$user_type}',
    'method': 'website'
  });
</script>";
    }

    // Track search queries
    public function track_search($search_query, $results_count = 0) {
        $this->track_event('search', 'query', $search_query, $results_count);
    }

    // Track contact form interactions
    public function track_contact_interaction($action, $form_type = 'general') {
        $this->track_event('contact', $action, $form_type);
    }

    // Send data to Google Analytics API (server-side)
    public function send_to_ga_api($events) {
        if (empty($this->api_secret) || empty($this->property_id)) return;

        $url = "https://www.google-analytics.com/mp/collect?measurement_id={$this->property_id}&api_secret={$this->api_secret}";

        $payload = [
            'client_id' => $this->get_client_id(),
            'events' => $events
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($payload)
            ]
        ]);

        $result = @file_get_contents($url, false, $context);

        if ($this->debug_mode) {
            error_log('GA API Response: ' . ($result ? 'Success' : 'Failed'));
        }

        return $result !== false;
    }

    // Generate unique client ID
    private function get_client_id() {
        if (isset($_COOKIE['ga_client_id'])) {
            return $_COOKIE['ga_client_id'];
        }

        $client_id = uniqid('client_', true);

        setcookie('ga_client_id', $client_id, [
            'expires' => time() + (86400 * 365 * 2), // 2 years
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        return $client_id;
    }

    // Track user engagement
    public function track_engagement($engagement_type, $target = '', $time_spent = 0) {
        $this->track_event('engagement', $engagement_type, $target, $time_spent);
    }

    // Track conversion funnel
    public function track_conversion($step, $property_id = '', $value = '') {
        echo "
<script>
  gtag('event', 'conversion_funnel', {
    'event_category': 'conversion',
    'event_label': '{$step}',
    'value': '{$value}',
    'custom_map': {'dimension4': '{$property_id}'}
  });
</script>";
    }

    // Track error events
    public function track_error($error_type, $error_message, $error_file = '') {
        $this->track_event('error', $error_type, $error_message);

        if ($this->debug_mode) {
            error_log("GA Error Tracking: $error_type - $error_message in $error_file");
        }
    }
}

// Utility functions for analytics
function get_page_title() {
    $page_titles = [
        '/' => 'Home - APS Dream Homes',
        '/properties.php' => 'Properties - APS Dream Homes',
        '/about.php' => 'About Us - APS Dream Homes',
        '/contact.php' => 'Contact Us - APS Dream Homes',
        '/projects.php' => 'Projects - APS Dream Homes'
    ];

    $current_page = $_SERVER['REQUEST_URI'] ?? '/';
    return $page_titles[$current_page] ?? 'APS Dream Homes';
}

function get_current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return $protocol . $host . $uri;
}

// Initialize analytics
$analytics = new APS_Analytics();

// Advanced tracking functions for common events
function track_property_inquiry($property_id, $property_name) {
    global $analytics;
    $analytics->track_inquiry('property_inquiry', $property_id);
    $analytics->track_property_view($property_id, $property_name, 'view', 0);
}

function track_user_login($user_type) {
    global $analytics;
    $analytics->track_event('user', 'login', $user_type);
}

function track_newsletter_signup($email) {
    global $analytics;
    $analytics->track_event('newsletter', 'signup', 'subscription');
}

function track_phone_click() {
    global $analytics;
    $analytics->track_event('contact', 'phone_click', 'phone_number');
}

function track_social_click($platform) {
    global $analytics;
    $analytics->track_event('social', 'click', $platform);
}

// Performance tracking
function track_page_load_time() {
    echo "
<script>
  window.addEventListener('load', function() {
    setTimeout(function() {
      const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
      gtag('event', 'page_load_time', {
        'event_category': 'performance',
        'value': Math.round(loadTime),
        'custom_map': {'metric1': Math.round(loadTime)}
      });
    }, 0);
  });
</script>";
}

echo "✅ Google Analytics integration setup completed!\n";
echo "📊 Features: Event tracking, conversion tracking, user behavior analysis\n";
echo "🔍 Tracking: Page views, property views, inquiries, user registration\n";
echo "📈 Advanced: Server-side tracking, custom dimensions, ecommerce tracking\n";
echo "🚀 Performance: Load time tracking, user engagement metrics\n";
echo "💳 Ecommerce: Property value tracking, conversion funnel analysis\n";

?>
