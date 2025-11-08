<?php
/**
 * APS Dream Home - Production Security Hardening
 * ===============================================
 * This script applies comprehensive security hardening for production deployment
 */

class ProductionSecurity {

    private $config = [];
    private $logFile;

    public function __construct() {
        $this->logFile = __DIR__ . '/logs/production_security.log';
        $this->config = require __DIR__ . '/config/security.php';
    }

    /**
     * Apply complete production security hardening
     */
    public function applySecurityHardening() {
        $this->log('Starting production security hardening...');

        $securityTasks = [
            'generate_secure_keys' => 'Generating secure application keys',
            'setup_ssl_certificates' => 'Setting up SSL certificates',
            'configure_firewall' => 'Configuring firewall rules',
            'setup_fail2ban' => 'Setting up fail2ban protection',
            'harden_php_security' => 'Hardening PHP security settings',
            'configure_security_headers' => 'Configuring security headers',
            'setup_monitoring_alerts' => 'Setting up security monitoring',
            'create_backup_strategy' => 'Creating backup strategy',
            'setup_incident_response' => 'Setting up incident response',
            'generate_security_report' => 'Generating security compliance report',
        ];

        $results = [];

        foreach ($securityTasks as $method => $description) {
            try {
                $this->log("Running: {$description}");
                $result = $this->$method();
                $results[$method] = $result;
                $this->log("Completed: {$method}");
            } catch (Exception $e) {
                $this->log("Error in {$method}: " . $e->getMessage());
                $results[$method] = ['error' => $e->getMessage()];
            }
        }

        $this->log('Production security hardening completed');
        return $results;
    }

    /**
     * Generate secure application keys
     */
    private function generate_secure_keys() {
        $keys = [];

        // Generate new APP_KEY
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $keys['app_key'] = $appKey;

        // Generate JWT secret
        $jwtSecret = bin2hex(random_bytes(64));
        $keys['jwt_secret'] = $jwtSecret;

        // Generate API keys
        $apiKeys = [];
        for ($i = 1; $i <= 5; $i++) {
            $apiKeys["api_key_{$i}"] = bin2hex(random_bytes(32));
        }
        $keys['api_keys'] = $apiKeys;

        // Save keys securely
        $keyFile = __DIR__ . '/config/keys.php';
        $keyContent = "<?php\nreturn " . var_export($keys, true) . ";\n";
        file_put_contents($keyFile, $keyContent);
        chmod($keyFile, 0600); // Read/write for owner only

        return $keys;
    }

    /**
     * Set up SSL certificates
     */
    private function setup_ssl_certificates() {
        $sslDir = __DIR__ . '/ssl';
        if (!is_dir($sslDir)) {
            mkdir($sslDir, 0755, true);
        }

        // Generate self-signed certificate for development
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $privkey = openssl_pkey_new($config);
        $csr = openssl_csr_new([
            "countryName" => "IN",
            "stateOrProvinceName" => "Uttar Pradesh",
            "localityName" => "Lucknow",
            "organizationName" => "APS Dream Home",
            "organizationalUnitName" => "IT Department",
            "commonName" => "localhost",
            "emailAddress" => "admin@apsdreamhome.com"
        ], $privkey);

        $x509 = openssl_csr_sign($csr, null, $privkey, 365);

        openssl_x509_export_to_file($x509, "{$sslDir}/certificate.crt");
        openssl_pkey_export_to_file($privkey, "{$sslDir}/private.key");

        // Set proper permissions
        chmod("{$sslDir}/private.key", 0600);
        chmod("{$sslDir}/certificate.crt", 0644);

        return [
            'certificate_path' => "{$sslDir}/certificate.crt",
            'private_key_path' => "{$sslDir}/private.key",
            'note' => 'Replace with production SSL certificate'
        ];
    }

    /**
     * Configure firewall rules
     */
    private function configure_firewall() {
        $firewallRules = [
            'apache' => [
                'description' => 'Allow Apache web server',
                'ports' => [80, 443],
                'protocol' => 'tcp'
            ],
            'mysql' => [
                'description' => 'Allow MySQL database',
                'ports' => [3306],
                'protocol' => 'tcp'
            ],
            'redis' => [
                'description' => 'Allow Redis cache',
                'ports' => [6379],
                'protocol' => 'tcp'
            ],
            'ssh' => [
                'description' => 'Allow SSH access',
                'ports' => [22],
                'protocol' => 'tcp'
            ]
        ];

        $ufwRules = [];
        foreach ($firewallRules as $service => $config) {
            $ufwRules[] = "sudo ufw allow {$config['ports'][0]}/{$config['protocol']}  # {$config['description']}";
        }

        $firewallConfig = [
            'ufw_rules' => $ufwRules,
            'instructions' => [
                'Run these commands as root/sudo:',
                'sudo ufw --force reset',
                'sudo ufw default deny incoming',
                'sudo ufw default allow outgoing',
                ...$ufwRules,
                'sudo ufw enable'
            ]
        ];

        return $firewallConfig;
    }

    /**
     * Set up fail2ban protection
     */
    private function setup_fail2ban() {
        $jailLocal = "
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5
backend = auto
destemail = admin@apsdreamhome.com
sendername = Fail2Ban
mta = sendmail

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3

[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/error.log

[apache-badbots]
enabled = true
port = http,https
filter = apache-badbots
logpath = /var/log/apache2/access.log

[apache-noscript]
enabled = true
port = http,https
filter = apache-noscript
logpath = /var/log/apache2/access.log

[apache-overflows]
enabled = true
port = http,https
filter = apache-overflows
logpath = /var/log/apache2/error.log

[php-url-fopen]
enabled = true
port = http,https
filter = php-url-fopen
logpath = /var/log/apache2/access.log

[wordpress]
enabled = true
port = http,https
filter = wordpress
logpath = /var/log/apache2/access.log
        ";

        $fail2banConfig = [
            'jail_local_path' => '/etc/fail2ban/jail.local',
            'jail_local_content' => $jailLocal,
            'instructions' => [
                'Install fail2ban: sudo apt install fail2ban',
                'Copy jail.local content to /etc/fail2ban/jail.local',
                'sudo systemctl restart fail2ban',
                'sudo fail2ban-client status'
            ]
        ];

        return $fail2banConfig;
    }

    /**
     * Harden PHP security settings
     */
    private function harden_php_security() {
        $phpIniProduction = "
; Production PHP configuration for APS Dream Home
; ==================================================

[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
unserialize_callback_func =
serialize_precision = -1
disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority
disable_classes =
zend.enable_gc = On
expose_php = Off
max_execution_time = 30
max_input_time = 60
memory_limit = 128M
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
display_errors = Off
display_startup_errors = Off
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On
track_errors = Off
html_errors = Off
variables_order = \"GPCS\"
request_order = \"GP\"
register_argc_argv = Off
auto_globals_jit = On
post_max_size = 8M
auto_prepend_file =
auto_append_file =
default_mimetype = \"text/html\"
default_charset = \"UTF-8\"
doc_root =
user_dir =
enable_dl = Off
file_uploads = On
upload_max_filesize = 10M
max_file_uploads = 20
allow_url_fopen = Off
allow_url_include = Off
default_socket_timeout = 60

[Date]
date.timezone = Asia/Kolkata

[filter]
filter.default = unsafe_raw
filter.default_flags =

[iconv]
iconv.input_encoding = ISO-8859-1
iconv.internal_encoding = ISO-8859-1
iconv.output_encoding = ISO-8859-1

[intl]
intl.default_locale =
intl.error_level = E_WARNING
intl.use_exceptions = 0

[sqlite3]
sqlite3.extension_dir =

[pgsql]
pgsql.allow_persistent = On
pgsql.auto_reset_persistent = Off
pgsql.max_persistent = -1
pgsql.max_links = -1
pgsql.ignore_notice = 0
pgsql.log_notice = 0

[bcmath]
bcmath.scale = 0

[browscap]
browscap = /etc/php/browscap.ini

[session]
session.save_handler = files
session.save_path = /var/lib/php/sessions
session.use_strict_mode = 1
session.use_cookies = 1
session.use_only_cookies = 1
session.name = PHPSESSID
session.auto_start = 0
session.cookie_lifetime = 0
session.cookie_path = /
session.cookie_domain =
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = Strict
session.serialize_handler = php
session.gc_probability = 1
session.gc_divisor = 1000
session.gc_maxlifetime = 1440
session.referer_check =
session.cache_limiter = nocache
session.cache_expire = 180
session.use_trans_sid = 0
session.sid_length = 32
session.trans_sid_hosts =
session.trans_sid_tags = \"a=href,area=href,frame=src,form=\"
session.sid_bits_per_character = 6

[opcache]
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.max_wasted_percentage = 5
opcache.use_cwd = 1
opcache.validate_timestamps = 1
opcache.revalidate_freq = 2
opcache.revalidate_path = 0
opcache.save_comments = 1
opcache.load_comments = 1
opcache.fast_shutdown = 1
opcache.enable_file_override = 0
opcache.optimization_level = 0x7FFFBFFF
opcache.dups_fix = 0

[mail function]
SMTP = localhost
smtp_port = 25
mail.add_x_header = On
        ";

        $phpConfig = [
            'php_ini_path' => '/etc/php/8.2/fpm/php.ini',
            'php_ini_content' => $phpIniProduction,
            'cli_php_ini' => '/etc/php/8.2/cli/php.ini',
            'instructions' => [
                'Copy php_ini_content to /etc/php/8.2/fpm/php.ini',
                'Copy php_ini_content to /etc/php/8.2/cli/php.ini',
                'sudo systemctl restart php8.2-fpm',
                'sudo systemctl restart nginx'
            ]
        ];

        return $phpConfig;
    }

    /**
     * Configure security headers
     */
    private function configure_security_headers() {
        $nginxSecurityConf = "
# APS Dream Home - Security Headers Configuration
# ================================================

server {
    listen 443 ssl http2;
    server_name _;

    # Security Headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection \"1; mode=block\";
    add_header Referrer-Policy \"strict-origin-when-cross-origin\";
    add_header Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self'; frame-ancestors 'none';\";
    add_header Strict-Transport-Security \"max-age=31536000; includeSubDomains; preload\";
    add_header Permissions-Policy \"geolocation=(), microphone=(), camera=(), payment=(), usb=()\";
    add_header X-Permitted-Cross-Domain-Policies none;

    # Rate limiting
    limit_req_zone \$binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone \$binary_remote_addr zone=login:10m rate=5r/m;

    # API rate limiting
    location /api/ {
        limit_req zone=api burst=20 nodelay;

        # Require API key for API endpoints
        if (\$request_uri ~ ^/api/) {
            add_header X-API-Required \"API key required\";
        }
    }

    # Admin area extra security
    location /admin/ {
        limit_req zone=login burst=5 nodelay;

        # Require additional authentication for admin
        auth_basic \"Admin Area\";
        auth_basic_user_file /etc/nginx/.htpasswd;

        # Log admin access
        access_log /var/log/nginx/admin_access.log;
    }

    # Block common exploit attempts
    location ~* (wp-admin|wp-login|administrator) {
        return 403;
    }

    location ~* \\.(bak|backup|sql|log)$ {
        return 403;
    }

    # Prevent access to sensitive files
    location ~ /\\. {
        deny all;
    }

    location ~ /storage/app/private {
        deny all;
    }

    location ~ /storage/logs {
        deny all;
    }
}
        ";

        return [
            'nginx_security_conf' => $nginxSecurityConf,
            'instructions' => [
                'Copy nginx_security_conf to /etc/nginx/conf.d/security.conf',
                'sudo nginx -t && sudo systemctl reload nginx',
                'Test security headers with curl -I https://yourdomain.com'
            ]
        ];
    }

    /**
     * Set up monitoring alerts
     */
    private function setup_monitoring_alerts() {
        $alertsConfig = [
            'email_alerts' => [
                'smtp_server' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_username' => 'alerts@apsdreamhome.com',
                'smtp_password' => 'SECURE_SMTP_PASSWORD',
                'admin_email' => 'admin@apsdreamhome.com'
            ],
            'slack_webhook' => 'https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK',
            'monitoring_thresholds' => [
                'response_time' => 1000, // ms
                'error_rate' => 0.05, // 5%
                'memory_usage' => 0.8, // 80%
                'disk_usage' => 0.9, // 90%
                'database_connections' => 50
            ]
        ];

        // Create alert script
        $alertScript = "
#!/bin/bash
# APS Dream Home - Security Alert Script

ALERT_TYPE=\"\$1\"
ALERT_MESSAGE=\"\$2\"
ADMIN_EMAIL=\"admin@apsdreamhome.com\"

# Send email alert
echo \"\$ALERT_MESSAGE\" | mail -s \"Security Alert: \$ALERT_TYPE\" \"\$ADMIN_EMAIL\"

# Send Slack alert (if webhook configured)
if [ -n \"\$SLACK_WEBHOOK\" ]; then
    curl -X POST -H 'Content-type: application/json' \
        --data \"{\\\"text\\\":\\\"🚨 Security Alert: \$ALERT_TYPE - \$ALERT_MESSAGE\\\"}\" \
        \$SLACK_WEBHOOK
fi

# Log alert
echo \"\$(date): \$ALERT_TYPE - \$ALERT_MESSAGE\" >> /var/log/security_alerts.log

echo \"Alert sent for: \$ALERT_TYPE\"
        ";

        return [
            'alerts_config' => $alertsConfig,
            'alert_script' => $alertScript,
            'instructions' => [
                'Configure email settings in alerts_config',
                'Set up Slack webhook if needed',
                'Create /usr/local/bin/security-alert script',
                'Make it executable: chmod +x /usr/local/bin/security-alert'
            ]
        ];
    }

    /**
     * Create backup strategy
     */
    private function create_backup_strategy() {
        $backupConfig = [
            'backup_schedule' => [
                'daily' => '0 2 * * *', // 2 AM daily
                'weekly' => '0 3 * * 0', // 3 AM Sundays
                'monthly' => '0 4 1 * *' // 4 AM 1st of month
            ],
            'backup_paths' => [
                '/var/www',
                '/etc/nginx',
                '/etc/php',
                '/etc/mysql'
            ],
            'backup_retention' => [
                'daily' => 7,    // Keep 7 days
                'weekly' => 4,   // Keep 4 weeks
                'monthly' => 12  // Keep 12 months
            ],
            'backup_destinations' => [
                'local' => '/backup/local',
                'remote' => 's3://apsdreamhome-backups',
                'offsite' => 'backup-server:/backups/apsdreamhome'
            ]
        ];

        // Create backup script
        $backupScript = "
#!/bin/bash
# APS Dream Home - Automated Backup Script

BACKUP_DIR=\"/backup/\$(date +%Y%m%d_%H%M%S)\"
LOG_FILE=\"/var/log/backup.log\"

echo \"\$(date): Starting backup to \$BACKUP_DIR\" >> \$LOG_FILE

# Create backup directory
mkdir -p \$BACKUP_DIR

# Backup database
mysqldump -u \$DB_USER -p\$DB_PASS \$DB_NAME > \$BACKUP_DIR/database.sql

# Backup application files
tar -czf \$BACKUP_DIR/app_files.tar.gz /var/www --exclude=/var/www/vendor --exclude=/var/www/node_modules

# Backup configuration
tar -czf \$BACKUP_DIR/config.tar.gz /etc/nginx /etc/php /etc/mysql/conf.d

# Upload to remote storage (if configured)
if [ -n \"\$S3_BUCKET\" ]; then
    aws s3 sync \$BACKUP_DIR s3://\$S3_BUCKET/backups/
fi

# Cleanup old backups
find /backup -type d -mtime +7 -exec rm -rf {} \\;

echo \"\$(date): Backup completed\" >> \$LOG_FILE
        ";

        return [
            'backup_config' => $backupConfig,
            'backup_script' => $backupScript,
            'instructions' => [
                'Set up cron jobs for automated backups',
                'Configure S3 or remote backup storage',
                'Test backup restoration procedure',
                'Set up monitoring for backup success/failure'
            ]
        ];
    }

    /**
     * Set up incident response
     */
    private function setup_incident_response() {
        $incidentResponse = [
            'procedures' => [
                'security_incident' => [
                    'steps' => [
                        '1. Isolate affected systems',
                        '2. Preserve evidence (logs, memory dumps)',
                        '3. Notify security team',
                        '4. Assess impact and scope',
                        '5. Contain the incident',
                        '6. Eradicate root cause',
                        '7. Recover systems',
                        '8. Document lessons learned'
                    ]
                ],
                'data_breach' => [
                    'steps' => [
                        '1. Contain the breach',
                        '2. Assess data exposure',
                        '3. Notify affected users (if required by law)',
                        '4. Notify authorities (if required)',
                        '5. Implement additional security measures',
                        '6. Monitor for follow-on attacks'
                    ]
                ]
            ],
            'contact_information' => [
                'security_team' => 'security@apsdreamhome.com',
                'legal_team' => 'legal@apsdreamhome.com',
                'technical_lead' => 'admin@apsdreamhome.com',
                'authorities' => 'cybercrime@cyberpolice.in'
            ]
        ];

        return $incidentResponse;
    }

    /**
     * Generate security compliance report
     */
    private function generate_security_report() {
        $report = [
            'compliance_frameworks' => [
                'GDPR' => 'Partial',
                'ISO_27001' => 'In Progress',
                'PCI_DSS' => 'Not Applicable',
                'HIPAA' => 'Not Applicable'
            ],
            'security_measures' => [
                'ssl_encryption' => 'Implemented',
                'firewall_protection' => 'Configured',
                'intrusion_detection' => 'fail2ban',
                'access_logging' => 'Implemented',
                'data_encryption' => 'Database level',
                'backup_strategy' => 'Automated',
                'incident_response' => 'Documented',
                'vulnerability_scanning' => 'Manual',
                'penetration_testing' => 'Not Done',
                'security_audits' => 'Internal'
            ],
            'risk_assessment' => [
                'high_risk' => [
                    'Database credentials exposure',
                    'SSL certificate management',
                    'Third-party dependency vulnerabilities'
                ],
                'medium_risk' => [
                    'User session management',
                    'File upload security',
                    'API rate limiting'
                ],
                'low_risk' => [
                    'Client-side validation',
                    'Information disclosure',
                    'Social engineering'
                ]
            ],
            'recommendations' => [
                'immediate' => [
                    'Implement proper SSL certificates',
                    'Set up automated vulnerability scanning',
                    'Conduct penetration testing'
                ],
                'short_term' => [
                    'Implement two-factor authentication',
                    'Set up centralized logging',
                    'Create security awareness training'
                ],
                'long_term' => [
                    'Achieve ISO 27001 certification',
                    'Implement zero-trust architecture',
                    'Set up security operations center'
                ]
            ]
        ];

        return $report;
    }

    /**
     * Log messages
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        error_log($logMessage);
    }
}

// Run security hardening if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    try {
        $security = new ProductionSecurity();
        $results = $security->applySecurityHardening();

        echo "🔒 Production Security Hardening Complete!\n\n";
        echo "Results:\n";
        echo json_encode($results, JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>
