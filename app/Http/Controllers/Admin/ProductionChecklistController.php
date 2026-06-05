<?php
/**
 * APS Dream Home - Production Checklist Controller
 *
 * Renders a single-page pre-launch checklist. Each item is a check the operator
 * should run before flipping DNS to production. The page is informational and
 * pure-HTML (no external services are called), so the operator can preview
 * the checks at any time.
 *
 * The "mark complete" action just records a per-item `completed` state in the
 * session, so the operator gets a sticky reminder during the same browser
 * session. There is no DB persistence — this is a launch-day aid, not an
 * audit log. For a real audit, run the checks themselves (curl, php -m, etc.)
 * and keep the output.
 *
 * @version 1.0.0
 * @date    2026-06-05
 */

namespace App\Http\Controllers\Admin;

use App\Helpers\SecurityHelper;

class ProductionChecklistController extends AdminController
{
    /** GET /admin/production-checklist */
    public function index()
    {
        $this->requireAdmin();

        $checks = $this->buildChecklist();

        $completed = $_SESSION['prod_checklist_done'] ?? [];
        if (!is_array($completed)) {
            $completed = [];
        }

        $pageTitle    = 'Production Checklist';
        $pageHeading  = 'Production Launch Checklist';
        $totalCount   = count($checks);
        $passedCount  = 0;
        $failedCount  = 0;
        $unknownCount = 0;

        foreach ($checks as $c) {
            $status = $this->detect($c);
            $c['status'] = $status;
            $c['done']   = !empty($completed[$c['key']]);
            if ($status === 'pass') {
                $passedCount++;
            } elseif ($status === 'fail') {
                $failedCount++;
            } else {
                $unknownCount++;
            }
        }

        $this->render('admin/production_checklist', [
            'pageTitle'    => $pageTitle,
            'pageHeading'  => $pageHeading,
            'checks'       => $checks,
            'completed'    => $completed,
            'totalCount'   => $totalCount,
            'passedCount'  => $passedCount,
            'failedCount'  => $failedCount,
            'unknownCount' => $unknownCount,
            'csrf_token'   => SecurityHelper::generateCsrfToken(),
        ]);
    }

    /** POST /admin/production-checklist/mark/{key} — toggle "I did this" sticky state */
    public function mark($key = null)
    {
        $this->requireAdmin();

        $key = (string)($key ?? $_POST['key'] ?? '');
        $key = preg_replace('/[^a-z0-9_\-]/i', '', $key);

        $allowed = array_column($this->buildChecklist(), 'key');
        if ($key === '' || !in_array($key, $allowed, true)) {
            $this->setFlash('error', 'Unknown checklist item: ' . htmlspecialchars($key));
            $this->redirect('/admin/production-checklist');
            return;
        }

        $done = $_SESSION['prod_checklist_done'] ?? [];
        if (!is_array($done)) {
            $done = [];
        }
        $done[$key] = empty($done[$key]) ? 1 : 0;
        $_SESSION['prod_checklist_done'] = $done;

        $this->setFlash('success', 'Checklist item updated.');
        $this->redirect('/admin/production-checklist');
    }

    /**
     * Build the canonical list of pre-launch checks.
     *
     * Each check is one of:
     *   - "env:<NAME>=<expected-substring>" — env var contains the expected value
     *   - "php-ext:<ext>"                   — PHP extension is loaded
     *   - "file:<path>"                     — file exists and is readable
     *   - "db:<table>"                      — table exists and has >= 1 row
     *   - "info"                            — informational, no auto-detect
     *
     * @return array<int, array{key:string,label:string,detail:string,command?:string,howto?:string}>
     */
    private function buildChecklist(): array
    {
        return [
            // ============================================================
            // 1. CRITICAL ENV
            // ============================================================
            [
                'key'    => 'app_env_production',
                'label'  => 'APP_ENV=production',
                'detail' => 'APP_ENV must be set to "production" (not "local" or "dev"). This disables debug error pages and verbose logging.',
                'command'=> "grep -E '^APP_ENV=' .env",
                'howto'  => 'Set APP_ENV=production in .env on the production server.',
            ],
            [
                'key'    => 'app_debug_off',
                'label'  => 'APP_DEBUG=false',
                'detail' => 'Debug must be off. Otherwise stack traces leak into error responses.',
                'command'=> "grep -E '^APP_DEBUG=' .env",
                'howto'  => 'Set APP_DEBUG=false in .env.',
            ],
            [
                'key'    => 'app_key_set',
                'label'  => 'APP_KEY set and >= 32 chars',
                'detail' => 'APP_KEY is the master key used for encryption. If it is missing or short, sessions and tokens are at risk.',
                'command'=> "grep -E '^APP_KEY=' .env",
                'howto'  => 'Run: php -r "echo bin2hex(random_bytes(32));" and paste into APP_KEY=.',
            ],
            [
                'key'    => 'db_host_set',
                'label'  => 'DB credentials point to production DB',
                'detail' => 'DB_HOST, DB_DATABASE, DB_USER, DB_PASSWORD must point to the real production database. Verify the host does not match localhost or 127.0.0.1 unless intentionally so.',
                'command'=> "grep -E '^DB_(HOST|DATABASE|USER|PASSWORD)=' .env",
            ],
            [
                'key'    => 'twilio_test_mode_off',
                'label'  => 'TWILIO_TEST_MODE=false',
                'detail' => 'Twilio must be in live mode for production SMS to actually send.',
                'command'=> "grep -E '^TWILIO_TEST_MODE=' .env",
                'howto'  => 'Set TWILIO_TEST_MODE=false once real Twilio credentials are loaded.',
            ],
            [
                'key'    => 'razorpay_test_mode_off',
                'label'  => 'RAZORPAY_TEST_MODE=false',
                'detail' => 'Razorpay must be in live mode for production payments to settle.',
                'command'=> "grep -E '^RAZORPAY_TEST_MODE=' .env",
            ],
            [
                'key'    => 'razorpay_webhook_secret_set',
                'label'  => 'RAZORPAY_WEBHOOK_SECRET is set and not a placeholder',
                'detail' => 'The webhook secret is used to verify signatures on Razorpay callbacks. Without it, the webhook is rejected.',
                'command'=> "grep -E '^RAZORPAY_WEBHOOK_SECRET=' .env",
                'howto'  => 'Get it from Razorpay dashboard -> Settings -> Webhooks -> Add new webhook -> copy "Secret".',
            ],
            [
                'key'    => 'storage_driver_s3',
                'label'  => 'STORAGE_DRIVER=s3 with valid AWS credentials',
                'detail' => 'Production uploads must go to S3 (or another remote store), not the local filesystem. AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY must be valid.',
                'command'=> "grep -E '^(STORAGE_DRIVER|AWS_ACCESS_KEY_ID|AWS_SECRET_ACCESS_KEY|AWS_DEFAULT_REGION|AWS_BUCKET)=' .env",
                'howto'  => 'Run scripts/setup_s3_cors.php to provision CORS after the bucket exists.',
            ],
            [
                'key'    => 'webhook_public_url',
                'label'  => 'WEBHOOK_PUBLIC_URL set (or BASE_URL is the public URL)',
                'detail' => 'Razorpay will POST to whatever URL you put in the dashboard. If you are behind a CDN, this must be the CDN URL, not BASE_URL.',
                'command'=> "grep -E '^(WEBHOOK_PUBLIC_URL|BASE_URL)=' .env",
                'howto'  => 'Set WEBHOOK_PUBLIC_URL=https://pay.yourdomain.com/webhook/razorpay in .env.',
            ],

            // ============================================================
            // 2. PHP EXTENSIONS
            // ============================================================
            [
                'key'    => 'php_pdo_mysql',
                'label'  => 'PHP extension: pdo_mysql',
                'detail' => 'Required for the MySQL database driver.',
                'command'=> "php -m | grep -i pdo_mysql",
            ],
            [
                'key'    => 'php_openssl',
                'label'  => 'PHP extension: openssl',
                'detail' => 'Required for Razorpay signature verification, password_hash, and HTTPS connections.',
                'command'=> "php -m | grep -i openssl",
            ],
            [
                'key'    => 'php_gd',
                'label'  => 'PHP extension: gd (with WebP support)',
                'detail' => 'Required by the ImageOptimizer for WebP and resize operations.',
                'command'=> "php -m | grep -i gd",
            ],
            [
                'key'    => 'php_curl',
                'label'  => 'PHP extension: curl',
                'detail' => 'Required by Twilio, Razorpay, AWS S3 adapters, and all external gateway clients.',
                'command'=> "php -m | grep -i curl",
            ],
            [
                'key'    => 'php_sockets',
                'label'  => 'PHP extension: sockets',
                'detail' => 'Required by the WebSocket server (Ratchet).',
                'command'=> "php -m | grep -i sockets",
            ],
            [
                'key'    => 'php_intl',
                'label'  => 'PHP extension: intl',
                'detail' => 'Required for locale-aware date/number formatting.',
                'command'=> "php -m | grep -i intl",
            ],

            // ============================================================
            // 3. FILESYSTEM
            // ============================================================
            [
                'key'    => 'htaccess_present',
                'label'  => '.htaccess present and Apache has mod_rewrite',
                'detail' => 'Apache needs mod_rewrite for the public/ document-root routing. .htaccess must be at the doc root.',
                'command'=> "ls -la .htaccess public/.htaccess 2>&1",
            ],
            [
                'key'    => 'storage_writable',
                'label'  => 'storage/ writable by the web user',
                'detail' => 'storage/logs, storage/cache, storage/uploads, storage/sessions must all be writable. Inside Docker this is www-data; on bare metal it is the Apache user.',
                'command'=> "ls -ld storage storage/logs storage/cache storage/uploads storage/sessions",
                'howto'  => 'chown -R www-data:www-data storage && chmod -R 775 storage',
            ],
            [
                'key'    => 'env_not_public',
                'label'  => '.env is NOT accessible from the web',
                'detail' => 'Verify that http://yourdomain/.env returns 404. If it returns the file contents, anyone can read your DB password.',
                'command'=> "curl -sI https://yourdomain.com/.env | head -1   # expect 404",
            ],
            [
                'key'    => 'public_assets_writable',
                'label'  => 'public/uploads and public/assets/uploads writable',
                'detail' => 'User-uploaded images land here if S3 is misconfigured (graceful fallback).',
                'command'=> "ls -ld public/uploads public/assets/uploads",
            ],

            // ============================================================
            // 4. SECURITY
            // ============================================================
            [
                'key'    => 'https_forced',
                'label'  => 'HTTPS forced (HSTS header or 301 redirect)',
                'detail' => 'All traffic must redirect to HTTPS. The HSTS header should be present (Strict-Transport-Security).',
                'command'=> "curl -sI http://yourdomain.com/ | grep -i 'strict-transport\\|301'",
                'howto'  => 'Enable the SSL block in docker/nginx/conf.d/ssl.conf and uncomment ssl-redirect.conf.',
            ],
            [
                'key'    => 'csp_set',
                'label'  => 'Content-Security-Policy header set',
                'detail' => 'CSP helps prevent XSS attacks by whitelisting allowed script/style sources.',
                'command'=> "curl -sI https://yourdomain.com/ | grep -i content-security-policy",
            ],
            [
                'key'    => 'xframe_deny',
                'label'  => 'X-Frame-Options: SAMEORIGIN',
                'detail' => 'Prevents clickjacking by disallowing iframes from other domains.',
                'command'=> "curl -sI https://yourdomain.com/ | grep -i x-frame-options",
            ],
            [
                'key'    => 'admin_password_rotated',
                'label'  => 'Default admin password rotated',
                'detail' => 'The seeded admin/admin credentials must be replaced with a strong, unique password. Bcrypt hash, 12+ chars.',
                'howto'  => 'Login as admin and change password, OR run: php scripts/rotate_admin_password.php newPasswordHere',
            ],
            [
                'key'    => 'test_login_disabled',
                'label'  => '?test_login=1 bypass disabled in production',
                'detail' => 'The AdminAuthController has a test-login bypass guarded by APP_ENV != production. Verify it is not active.',
                'howto'  => 'Ensure APP_ENV=production in .env. Then visit /admin/login?test_login=1 — it should redirect to login, not let you in.',
            ],
            [
                'key'    => 'session_secure_cookie',
                'label'  => 'Session cookie is Secure + HttpOnly + SameSite=Lax',
                'detail' => 'Prevents session hijacking via XSS or downgrade attacks.',
                'command'=> "curl -sI https://yourdomain.com/admin/login | grep -i set-cookie",
            ],

            // ============================================================
            // 5. PAYMENTS
            // ============================================================
            [
                'key'    => 'razorpay_keys_live',
                'label'  => 'Razorpay keys are LIVE (rzp_live_*)',
                'detail' => 'rzp_test_* keys will return fake orders and never settle real money. The dashboard must show "Configured" with live key prefix.',
                'command'=> "grep -E '^RAZORPAY_KEY_ID=' .env   # should be rzp_live_...",
            ],
            [
                'key'    => 'razorpay_webhook_registered',
                'label'  => 'Razorpay webhook is registered in the dashboard',
                'detail' => 'Set the URL from /admin/gateways (Razorpay Webhook URL card) and paste the WEBHOOK_SECRET. Subscribe to events: payment.captured, payment.failed, refund.processed, order.paid.',
                'howto'  => 'Visit https://dashboard.razorpay.com/app/webhooks -> Add new webhook. URL: copy from /admin/gateways. Events: at least the four above.',
            ],
            [
                'key'    => 'test_payment_smoke',
                'label'  => 'Razorpay test payment round-trip works end-to-end',
                'detail' => 'Create a booking, hit checkout, complete a real (small) test payment with a live card or UPI. Verify the booking moves to "paid" and the user gets a receipt.',
            ],
            [
                'key'    => 'webhook_signature_valid',
                'label'  => 'Razorpay webhook signature verification passes',
                'detail' => 'Trigger a real test event from the Razorpay dashboard. The /webhook/razorpay endpoint must return 200 and log signature_verified=1 in payment_webhook_logs.',
            ],

            // ============================================================
            // 6. STORAGE
            // ============================================================
            [
                'key'    => 's3_bucket_exists',
                'label'  => 'S3 bucket exists and is reachable',
                'detail' => 'The bucket referenced in AWS_BUCKET must exist in the AWS_DEFAULT_REGION. Verify with: aws s3 ls s3://$AWS_BUCKET.',
                'command'=> "aws s3 ls s3://apsdreamhome-prod-uploads/ --region ap-south-1",
            ],
            [
                'key'    => 's3_cors_configured',
                'label'  => 'S3 CORS configured for this domain',
                'detail' => 'Direct browser uploads to S3 (e.g. presigned URLs) need CORS. The current upload path is server-side, so this is a soft requirement, but you should still run setup_s3_cors.php for future flexibility.',
                'command'=> "php scripts/setup_s3_cors.php --check",
            ],
            [
                'key'    => 's3_iam_policy_minimal',
                'label'  => 'IAM user has minimal s3:* permissions on the bucket only',
                'detail' => 'Never give *:* on *. Limit to s3:GetObject, s3:PutObject, s3:DeleteObject, s3:ListBucket on the specific bucket ARN.',
            ],

            // ============================================================
            // 7. CACHE / QUEUE
            // ============================================================
            [
                'key'    => 'cache_driver_redis',
                'label'  => 'Cache driver = redis (or memcached)',
                'detail' => 'File cache works in dev but does not scale and has no atomic invalidation. Use Redis in production.',
                'command'=> "grep -E '^CACHE_DRIVER=' .env",
            ],
            [
                'key'    => 'session_driver_redis',
                'label'  => 'Session driver = redis (or DB)',
                'detail' => 'File-based sessions under load cause file-lock contention. Use Redis or the database.',
                'command'=> "grep -E '^SESSION_DRIVER=' .env",
            ],
            [
                'key'    => 'queue_connection_redis',
                'label'  => 'Queue connection = redis',
                'detail' => 'Database queue works but Redis is faster and more reliable for transient jobs.',
                'command'=> "grep -E '^QUEUE_CONNECTION=' .env",
            ],

            // ============================================================
            // 8. MONITORING / OBSERVABILITY
            // ============================================================
            [
                'key'    => 'health_endpoint',
                'label'  => '/health endpoint returns 200',
                'detail' => 'The /health URL is the liveness probe for Docker, Kubernetes, and uptime monitors. It should always return 200 if PHP and DB are reachable.',
                'command'=> "curl -sI https://yourdomain.com/health | head -1   # expect 200",
            ],
            [
                'key'    => 'log_destination',
                'label'  => 'Application logs going to a persistent volume',
                'detail' => 'In Docker, logs go to the app_logs named volume. In bare metal, make sure logs/ is on a persistent disk and rotated (logrotate or cron).',
                'command'=> "ls -la storage/logs/",
            ],
            [
                'key'    => 'error_alerts',
                'label'  => 'Error alerts wired to email/Slack',
                'detail' => 'The monitoring cron (scripts/monitoring_cron.php) should be set up to email the admin on PHP fatals. The cron should be scheduled every 5 minutes.',
            ],
            [
                'key'    => 'uptime_monitor',
                'label'  => 'Uptime monitor (UptimeRobot / Better Uptime / Pingdom) configured',
                'detail' => 'External uptime monitoring is the only way to detect a 500-loop that does not hit your error log.',
            ],

            // ============================================================
            // 9. BACKUPS
            // ============================================================
            [
                'key'    => 'db_backup_cron',
                'label'  => 'DB backup cron is scheduled (daily)',
                'detail' => 'scripts/backup_cron.php dumps the DB to db_backups/ and rotates weekly. Verify the cron is installed and the dump files are non-empty.',
                'command'=> "ls -la storage/backups/   # should have recent .sql.gz files",
            ],
            [
                'key'    => 'backup_offsite',
                'label'  => 'Backups mirrored offsite (S3 / B2 / Google Cloud Storage)',
                'detail' => 'If the server is compromised or the disk dies, offsite backups are the only way to recover.',
            ],
            [
                'key'    => 'backup_restore_drill',
                'label'  => 'Backup restore drill performed within the last 30 days',
                'detail' => 'A backup you have never restored is a backup you cannot trust. Restore to a staging DB at least once a month and spot-check the data.',
            ],

            // ============================================================
            // 10. SMOKE / DNS
            // ============================================================
            [
                'key'    => 'dns_a_record',
                'label'  => 'A record points to production server',
                'detail' => 'Verify with: dig +short yourdomain.com',
            ],
            [
                'key'    => 'ssl_cert_valid',
                'label'  => 'SSL certificate is valid and auto-renews',
                'detail' => 'Use https://www.ssllabs.com/ssltest/ to verify. The Let\'s Encrypt cert should auto-renew via the host cron (see docs/DEPLOYMENT.md).',
                'command'=> "echo | openssl s_client -servername yourdomain.com -connect yourdomain.com:443 2>/dev/null | openssl x509 -noout -dates",
            ],
            [
                'key'    => 'homepage_200',
                'label'  => 'Homepage returns 200 within 1s',
                'detail' => 'Open https://yourdomain.com/ in a browser. Hard refresh (Ctrl+Shift+R) and watch the network tab — first byte should be < 500ms, full page < 1.5s on broadband.',
            ],
            [
                'key'    => 'admin_login_200',
                'label'  => 'Admin login renders without errors',
                'detail' => 'Visit /admin/login. Should be a clean Bootstrap page with no PHP warnings in the browser console or in storage/logs/php_error_log.',
            ],
            [
                'key'    => 'sitemap_xml',
                'label'  => 'sitemap.xml and robots.txt return 200',
                'detail' => 'Search engines need both. /sitemap.xml lists all public URLs, /robots.txt controls crawler access.',
                'command'=> "curl -sI https://yourdomain.com/sitemap.xml | head -1   # expect 200\ncurl -sI https://yourdomain.com/robots.txt | head -1   # expect 200",
            ],
        ];
    }

    /**
     * Best-effort auto-detection. Returns 'pass', 'fail', or 'unknown'.
     * The output is informational only — the operator still has to run the actual command.
     */
    private function detect(array $check): string
    {
        $key = $check['key'] ?? '';
        switch ($key) {
            // ENV
            case 'app_env_production':
                $v = (string)($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: '');
                return $v === 'production' ? 'pass' : 'fail';
            case 'app_debug_off':
                $v = (string)($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: '');
                return in_array(strtolower($v), ['false', '0', 'no', 'off', ''], true) ? 'pass' : 'fail';
            case 'app_key_set':
                $v = (string)($_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: '');
                return strlen($v) >= 32 ? 'pass' : 'fail';
            case 'db_host_set':
                $h = (string)($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '');
                $d = (string)($_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: '');
                return ($h !== '' && $d !== '') ? 'pass' : 'fail';
            case 'twilio_test_mode_off':
                $v = (string)($_ENV['TWILIO_TEST_MODE'] ?? getenv('TWILIO_TEST_MODE') ?: '');
                return in_array(strtolower($v), ['false', '0', 'no', 'off', ''], true) ? 'pass' : 'fail';
            case 'razorpay_test_mode_off':
                $v = (string)($_ENV['RAZORPAY_TEST_MODE'] ?? getenv('RAZORPAY_TEST_MODE') ?: '');
                return in_array(strtolower($v), ['false', '0', 'no', 'off', ''], true) ? 'pass' : 'fail';
            case 'razorpay_webhook_secret_set':
                $v = (string)($_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? getenv('RAZORPAY_WEBHOOK_SECRET') ?: '');
                return ($v !== '' && strpos($v, 'xxxxx') === false) ? 'pass' : 'fail';
            case 'storage_driver_s3':
                $driver = (string)($_ENV['STORAGE_DRIVER'] ?? getenv('STORAGE_DRIVER') ?: 'local');
                $key    = (string)($_ENV['AWS_ACCESS_KEY_ID'] ?? getenv('AWS_ACCESS_KEY_ID') ?: '');
                $secret = (string)($_ENV['AWS_SECRET_ACCESS_KEY'] ?? getenv('AWS_SECRET_ACCESS_KEY') ?: '');
                $bucket = (string)($_ENV['AWS_BUCKET'] ?? getenv('AWS_BUCKET') ?: '');
                return ($driver === 's3' && $key !== '' && $secret !== '' && $bucket !== '') ? 'pass' : 'fail';
            case 'webhook_public_url':
                $u = (string)($_ENV['WEBHOOK_PUBLIC_URL'] ?? getenv('WEBHOOK_PUBLIC_URL') ?: '');
                $b = (string)(defined('BASE_URL') ? BASE_URL : '');
                if ($u !== '') {
                    return 'pass';
                }
                return ($b !== '' && strpos($b, 'localhost') === false) ? 'pass' : 'fail';
            // PHP EXT
            case 'php_pdo_mysql': return extension_loaded('pdo_mysql') ? 'pass' : 'fail';
            case 'php_openssl':  return extension_loaded('openssl')  ? 'pass' : 'fail';
            case 'php_gd':       return extension_loaded('gd')       ? 'pass' : 'fail';
            case 'php_curl':     return extension_loaded('curl')     ? 'pass' : 'fail';
            case 'php_sockets':  return extension_loaded('sockets')  ? 'pass' : 'fail';
            case 'php_intl':     return extension_loaded('intl')     ? 'pass' : 'fail';
            // FS
            case 'htaccess_present':
                $root = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
                $a = is_file($root . '/.htaccess');
                $b = is_file($root . '/public/.htaccess');
                return ($a || $b) ? 'pass' : 'fail';
            case 'storage_writable':
                $root = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
                return is_writable($root . '/storage/logs') ? 'pass' : 'fail';
            case 'env_not_public':
            case 'public_assets_writable':
            case 'https_forced':
            case 'csp_set':
            case 'xframe_deny':
            case 'session_secure_cookie':
            case 'admin_password_rotated':
            case 'test_login_disabled':
            case 'razorpay_keys_live':
            case 'razorpay_webhook_registered':
            case 's3_bucket_exists':
            case 's3_cors_configured':
            case 's3_iam_policy_minimal':
            case 'cache_driver_redis':
            case 'session_driver_redis':
            case 'queue_connection_redis':
            case 'health_endpoint':
            case 'log_destination':
            case 'error_alerts':
            case 'uptime_monitor':
            case 'db_backup_cron':
            case 'backup_offsite':
            case 'backup_restore_drill':
            case 'dns_a_record':
            case 'ssl_cert_valid':
            case 'homepage_200':
            case 'admin_login_200':
            case 'sitemap_xml':
            case 'test_payment_smoke':
            case 'webhook_signature_valid':
            default:
                return 'unknown';
        }
    }
}
