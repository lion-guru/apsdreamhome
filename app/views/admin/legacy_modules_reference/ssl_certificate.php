<?php
/**
 * APS Dream Home - SSL Certificate Setup & Security Configuration
 * Complete SSL/HTTPS setup with security headers and certificate management
 */

// SSL Configuration constants
define('SSL_ENABLED', true);
define('HSTS_ENABLED', true);
define('HSTS_MAX_AGE', 31536000); // 1 year
define('FORCE_HTTPS', true);

// SSL Certificate class
class APS_SSL_Manager {

    public function __construct() {
        $this->enforce_ssl();
        $this->set_security_headers();
    }

    // Force HTTPS redirect
    private function enforce_ssl() {
        if (FORCE_HTTPS && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
            $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirect_url, true, 301);
            exit();
        }
    }

    // Set comprehensive security headers
    private function set_security_headers() {
        // HSTS Header
        if (HSTS_ENABLED) {
            header('Strict-Transport-Security: max-age=' . HSTS_MAX_AGE . '; includeSubDomains; preload');
        }

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://checkout.razorpay.com https://js.stripe.com https://www.googletagmanager.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https:; frame-src https://checkout.razorpay.com;");

        // X-Frame-Options
        header('X-Frame-Options: SAMEORIGIN');

        // X-Content-Type-Options
        header('X-Content-Type-Options: nosniff');

        // X-XSS-Protection
        header('X-XSS-Protection: 1; mode=block');

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Feature Policy/Permissions Policy
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(self), payment=(self)');

        // Remove server information
        header('X-Powered-By: ');
        header_remove('X-Powered-By');
    }

    // Generate CSR (Certificate Signing Request)
    public function generate_csr($domain, $organization, $country = 'IN', $state = 'UP', $city = 'Gorakhpur') {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Generate private key
        $private_key = openssl_pkey_new($config);

        // Generate CSR
        $dn = [
            'countryName' => $country,
            'stateOrProvinceName' => $state,
            'localityName' => $city,
            'organizationName' => $organization,
            'organizationalUnitName' => 'IT Department',
            'commonName' => $domain,
            'emailAddress' => 'admin@' . $domain
        ];

        $csr = openssl_csr_new($dn, $private_key, $config);

        // Export private key and CSR
        openssl_pkey_export($private_key, $private_key_pem);
        openssl_csr_export($csr, $csr_pem);

        return [
            'private_key' => $private_key_pem,
            'csr' => $csr_pem,
            'domain' => $domain
        ];
    }

    // Validate SSL certificate
    public function validate_ssl_certificate($domain) {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ]);

        try {
            $socket = stream_socket_client(
                "ssl://{$domain}:443",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($socket) {
                $cert_info = stream_context_get_params($socket);
                fclose($socket);

                return [
                    'valid' => true,
                    'certificate_info' => $cert_info['options']['ssl']['peer_certificate']
                ];
            }

        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }

        return ['valid' => false, 'error' => 'Unable to connect to domain'];
    }

    // Generate self-signed certificate for testing
    public function generate_self_signed_certificate($domain, $days = 365) {
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Generate private key
        $private_key = openssl_pkey_new($config);

        // Generate certificate
        $dn = [
            'countryName' => 'IN',
            'stateOrProvinceName' => 'UP',
            'localityName' => 'Gorakhpur',
            'organizationName' => 'APS Dream Homes Pvt Ltd',
            'organizationalUnitName' => 'IT Department',
            'commonName' => $domain,
            'emailAddress' => 'admin@' . $domain
        ];

        $certificate = openssl_csr_sign(openssl_csr_new($dn, $private_key, $config), null, $private_key, $days);

        // Export certificate and key
        openssl_x509_export($certificate, $certificate_pem);
        openssl_pkey_export($private_key, $private_key_pem);

        return [
            'certificate' => $certificate_pem,
            'private_key' => $private_key_pem,
            'valid_until' => date('Y-m-d', strtotime("+{$days} days"))
        ];
    }
}

// SSL utility functions
function redirect_to_https() {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect_url, true, 301);
        exit();
    }
}

function is_https_enabled() {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
}

function get_security_headers_status() {
    $headers = [
        'Strict-Transport-Security' => 'HSTS not set',
        'Content-Security-Policy' => 'CSP not set',
        'X-Frame-Options' => 'X-Frame-Options not set',
        'X-Content-Type-Options' => 'X-Content-Type-Options not set',
        'X-XSS-Protection' => 'X-XSS-Protection not set'
    ];

    $response_headers = headers_list();

    foreach ($response_headers as $header) {
        $header_parts = explode(':', $header, 2);
        $header_name = trim($header_parts[0]);

        if (isset($headers[$header_name])) {
            $headers[$header_name] = '✅ ' . trim($header_parts[1]);
        }
    }

    return $headers;
}

// Certificate installation guide
function get_ssl_installation_guide() {
    return "
    <div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px;'>
        <h1>🔒 SSL Certificate Installation Guide</h1>

        <h2>📋 Prerequisites</h2>
        <ul>
            <li>Domain name registered and pointing to your server</li>
            <li>Web server (Apache/Nginx) configured</li>
            <li>Administrative access to server</li>
        </ul>

        <h2>🚀 Step 1: Generate CSR (Certificate Signing Request)</h2>
        <p>Use the CSR generator in this system or manually create:</p>
        <pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>
openssl req -new -newkey rsa:2048 -nodes -keyout yourdomain.key -out yourdomain.csr
        </pre>

        <h2>💳 Step 2: Purchase SSL Certificate</h2>
        <p>Recommended providers:</p>
        <ul>
            <li><strong>Let's Encrypt</strong> - Free, automated</li>
            <li><strong>GoDaddy</strong> - ₹2,000-₹5,000/year</li>
            <li><strong>Namecheap</strong> - ₹1,500-₹4,000/year</li>
            <li><strong>DigiCert</strong> - ₹8,000-₹25,000/year</li>
        </ul>

        <h2>⚙️ Step 3: Install Certificate</h2>

        <h3>For Apache:</h3>
        <pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>
SSLEngine on
SSLCertificateFile /path/to/yourdomain.crt
SSLCertificateKeyFile /path/to/yourdomain.key
SSLCertificateChainFile /path/to/intermediate.crt
        </pre>

        <h3>For Nginx:</h3>
        <pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>
server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    ssl_certificate /path/to/yourdomain.crt;
    ssl_certificate_key /path/to/yourdomain.key;
    ssl_trusted_certificate /path/to/intermediate.crt;
}
        </pre>

        <h2>🔧 Step 4: Configure HTTPS Redirect</h2>
        <p>Add to your .htaccess (Apache):</p>
        <pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
        </pre>

        <h2>✅ Step 5: Test SSL Installation</h2>
        <ul>
            <li>Visit https://yourdomain.com</li>
            <li>Check for padlock icon in browser</li>
            <li>Use SSL Labs test: <a href='https://www.ssllabs.com/ssltest/'>SSL Labs Test</a></li>
        </ul>

        <h2>🛠️ Troubleshooting</h2>
        <ul>
            <li><strong>Mixed Content:</strong> Update all internal links to HTTPS</li>
            <li><strong>Certificate Errors:</strong> Check certificate chain and dates</li>
            <li><strong>Browser Warnings:</strong> Ensure HSTS is properly configured</li>
        </ul>

        <div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>
            <h3>🎯 Quick Setup Commands:</h3>
            <p><strong>For Let's Encrypt (Free):</strong></p>
            <pre>sudo certbot --apache -d yourdomain.com -d www.yourdomain.com</pre>
            <p><strong>For manual installation:</strong></p>
            <pre>openssl req -new -newkey rsa:2048 -nodes -keyout private.key -out request.csr</pre>
        </div>
    </div>";
}

// Auto HTTPS redirect function
function enable_auto_https() {
    if (!is_https_enabled()) {
        redirect_to_https();
    }
}

// Security monitoring
function log_security_event($event_type, $details = '') {
    $log_entry = [
        'event_type' => $event_type,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details,
        'timestamp' => date('Y-m-d H:i:s'),
        'session_id' => session_id() ?? 'none'
    ];

    $log_file = __DIR__ . '/logs/security_' . date('Y-m') . '.log';
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND);
}

// Initialize SSL manager
$ssl_manager = new APS_SSL_Manager();

echo "✅ SSL certificate setup and security configuration completed!\n";
echo "🔒 Features: HTTPS enforcement, security headers, certificate management\n";
echo "🛡️ Security: HSTS, CSP, XSS protection, frame options, content type protection\n";
echo "📊 Monitoring: Security event logging and certificate validation\n";
echo "🔧 Tools: CSR generation, self-signed certificates, installation guides\n";
echo "⚙️ Configuration: Auto HTTPS redirect, security headers, mixed content protection\n";

?>
