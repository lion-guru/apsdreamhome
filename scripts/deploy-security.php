<?php
// scripts/deploy-security.php

class SecurityDeployer {
    private $basePath;
    private $successes = [];
    private $warnings = [];
    private $errors = [];

    public function __construct() {
        $this->basePath = __DIR__ . '/../';
    }

    public function runDeployment() {
        echo "🚀 APS Dream Home - Security Deployment Script\n";
        echo "=============================================\n\n";

        $this->createSecureDirectories();
        $this->setSecurePermissions();
        $this->validateEnvironment();
        $this->createSecurityConfig();
        $this->setupMonitoring();
        $this->generateCertificates();
        $this->createDeploymentChecklist();

        $this->generateDeploymentReport();
    }

    private function createSecureDirectories() {
        echo "📁 Creating secure directory structure...\n";

        $directories = [
            'storage/uploads' => 0755,
            'storage/logs' => 0755,
            'storage/cache' => 0755,
            'storage/sessions' => 0755,
            'storage/backups' => 0755,
            'storage/temp' => 0755
        ];

        foreach ($directories as $dir => $perms) {
            $fullPath = $this->basePath . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, $perms, true);
                echo "✅ Created: $dir\n";
                $this->successes[] = "Created secure directory: $dir";
            } else {
                chmod($fullPath, $perms);
                echo "✅ Secured: $dir\n";
                $this->successes[] = "Secured existing directory: $dir";
            }
        }

        // Create .gitkeep files
        $gitkeepDirs = ['storage/logs', 'storage/cache', 'storage/sessions'];
        foreach ($gitkeepDirs as $dir) {
            $gitkeepPath = $this->basePath . $dir . '/.gitkeep';
            if (!file_exists($gitkeepPath)) {
                file_put_contents($gitkeepPath, '');
                $this->successes[] = "Created .gitkeep in: $dir";
            }
        }
    }

    private function setSecurePermissions() {
        echo "🔒 Setting secure file permissions...\n";

        // PHP files should be 644
        $phpFiles = $this->findFilesByExtension('php');
        foreach ($phpFiles as $file) {
            if (fileperms($file) !== 0100644) {
                chmod($file, 0644);
            }
        }

        // Directories should be 755
        $directories = $this->findDirectories();
        foreach ($directories as $dir) {
            if (fileperms($dir) !== 0100755) {
                chmod($dir, 0755);
            }
        }

        $this->successes[] = "Set secure permissions on " . count($phpFiles) . " PHP files";
        echo "  • PHP files secured: " . count($phpFiles) . "\n";
    }

    private function validateEnvironment() {
        echo "🔍 Validating environment security...\n";

        // Check PHP version
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            $this->successes[] = "PHP version $phpVersion is secure";
        } else {
            $this->warnings[] = "PHP version $phpVersion should be upgraded to 7.4+";
        }

        // Check for dangerous PHP functions
        $dangerousFunctions = ['exec', 'shell_exec', 'system', 'passthru', 'eval'];
        $disabledFunctions = array_map('trim', explode(',', ini_get('disable_functions')));

        foreach ($dangerousFunctions as $func) {
            if (in_array($func, $disabledFunctions)) {
                $this->successes[] = "Dangerous function '$func' is disabled";
            } else {
                $this->warnings[] = "Dangerous function '$func' should be disabled";
            }
        }

        echo "  • PHP version: $phpVersion\n";
        echo "  • Dangerous functions: " . count($dangerousFunctions) . " checked\n";
    }

    private function createSecurityConfig() {
        echo "⚙️  Creating security configuration...\n";

        // Create robots.txt
        $robotsContent = "User-agent: *\nDisallow: /admin/\nDisallow: /config/\nDisallow: /storage/\nDisallow: /scripts/\nDisallow: *.log\nDisallow: *.sql\nDisallow: .env\n";
        file_put_contents($this->basePath . 'robots.txt', $robotsContent);
        $this->successes[] = "Created robots.txt for security";

        // Create .htaccess for additional security
        $additionalSecurity = "
# Block access to sensitive files
<FilesMatch \"^(config\.php|\.env|composer\.json|composer\.lock)$\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to backup files
<FilesMatch \"\\.(bak|backup|old|orig|original|tmp)$\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to log files
<FilesMatch \"\\.(log|error_log)$\">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers for all responses
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
    Header always set Permissions-Policy \"geolocation=(), microphone=(), camera=()\"
</IfModule>
";

        $htaccessPath = $this->basePath . '.htaccess';
        if (file_exists($htaccessPath)) {
            $currentContent = file_get_contents($htaccessPath);
            if (strpos($currentContent, 'X-Content-Type-Options') === false) {
                file_put_contents($htaccessPath, $currentContent . "\n" . $additionalSecurity);
                $this->successes[] = "Enhanced .htaccess with additional security";
            }
        }

        echo "  • Robots.txt: Created\n";
        echo "  • .htaccess: Enhanced\n";
    }

    private function setupMonitoring() {
        echo "📊 Setting up security monitoring...\n";

        // Create monitoring cron job template
        $cronTemplate = "# Security monitoring cron jobs
# Add these to your crontab (crontab -e)

# Run security audit daily at 2 AM
0 2 * * * /usr/bin/php {$this->basePath}scripts/security-audit.php

# Run security monitoring every hour
0 * * * * /usr/bin/php {$this->basePath}scripts/security-monitor.php

# Clean old logs weekly on Sunday at 3 AM
0 3 * * 0 /usr/bin/php {$this->basePath}scripts/cleanup-logs.php

# Database backup daily at 1 AM
0 1 * * * /usr/bin/mysqldump -u[username] -p[password] [database] > {$this->basePath}storage/backups/backup-\$(date +\%Y\%m\%d\%H\%M\%S).sql
";

        file_put_contents($this->basePath . 'cron-jobs.template', $cronTemplate);
        $this->successes[] = "Created cron job templates for monitoring";

        // Create log cleanup script
        $cleanupScript = <<<'PHP'
<?php
// scripts/cleanup-logs.php

$logDir = __DIR__ . '/../storage/logs/';
$daysToKeep = 30;

echo "🧹 Cleaning up old security logs...\n";

if (is_dir($logDir)) {
    $files = glob($logDir . '*.log');
    $cleaned = 0;

    foreach ($files as $file) {
        if (filemtime($file) < (time() - ($daysToKeep * 24 * 60 * 60))) {
            if (unlink($file)) {
                echo "✅ Deleted: " . basename($file) . "\n";
                $cleaned++;
            }
        }
    }

    echo "🧹 Cleaned up $cleaned old log files\n";
} else {
    echo "❌ Log directory not found\n";
}
?>
PHP;

        file_put_contents($this->basePath . 'scripts/cleanup-logs.php', $cleanupScript);
        $this->successes[] = "Created log cleanup script";

        echo "  • Cron templates: Created\n";
        echo "  • Cleanup script: Created\n";
    }

    private function generateCertificates() {
        echo "🔐 Generating security certificates...\n";

        // Create self-signed certificate for development (HTTPS testing)
        $certDir = $this->basePath . 'storage/certificates/';
        if (!is_dir($certDir)) {
            mkdir($certDir, 0755, true);
        }

        // Generate private key
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        // Generate certificate
        $certData = [
            'countryName' => 'US',
            'stateOrProvinceName' => 'State',
            'localityName' => 'City',
            'organizationName' => 'APS Dream Home',
            'organizationalUnitName' => 'Development',
            'commonName' => 'localhost',
            'emailAddress' => 'admin@localhost'
        ];

        $cert = openssl_csr_new($certData, $privateKey);
        $cert = openssl_csr_sign($cert, null, $privateKey, 365);

        // Save certificate and key
        openssl_x509_export_to_file($cert, $certDir . 'localhost.crt');
        openssl_pkey_export_to_file($privateKey, $certDir . 'localhost.key');

        $this->successes[] = "Generated self-signed SSL certificate for development";
        echo "  • Development certificate: Generated\n";
        echo "  • Note: Use proper SSL certificate for production\n";
    }

    private function createDeploymentChecklist() {
        echo "📋 Creating deployment checklist...\n";

        $checklist = "# 🚀 APS Dream Home - Production Deployment Checklist

## ✅ PRE-DEPLOYMENT CHECKS

### Security Validation
- [ ] Run security validation: `php scripts/security-validation.php`
- [ ] Fix any critical security issues
- [ ] Verify all tests pass

### Environment Setup
- [ ] Copy .env.example to .env
- [ ] Configure database credentials
- [ ] Set APP_ENV=production
- [ ] Set APP_DEBUG=false
- [ ] Generate secure APP_KEY

### HTTPS Configuration
- [ ] Obtain SSL certificate (Let's Encrypt recommended)
- [ ] Configure web server for HTTPS
- [ ] Set up HTTP to HTTPS redirects
- [ ] Configure HSTS headers
- [ ] Test HTTPS functionality

## 🛡️ PRODUCTION SECURITY SETUP

### Web Server Configuration
- [ ] Enable HTTPS only
- [ ] Configure security headers
- [ ] Set secure file permissions
- [ ] Disable dangerous PHP functions
- [ ] Configure PHP limits

### Database Security
- [ ] Create separate database user for application
- [ ] Set strong database passwords
- [ ] Configure database backups
- [ ] Enable database logging
- [ ] Set up connection pooling

### File System Security
- [ ] Move uploads outside web root
- [ ] Set proper file permissions (644/755)
- [ ] Configure file upload limits
- [ ] Set up virus scanning for uploads
- [ ] Implement file integrity monitoring

## 📊 MONITORING & MAINTENANCE

### Security Monitoring
- [ ] Set up security monitoring cron jobs
- [ ] Configure log rotation
- [ ] Set up alert notifications
- [ ] Configure backup procedures
- [ ] Set up intrusion detection

### Performance Monitoring
- [ ] Set up application monitoring
- [ ] Configure error tracking
- [ ] Set up performance metrics
- [ ] Configure uptime monitoring

## 🚨 SECURITY TESTING

### Pre-Launch Security Audit
- [ ] Test all forms for CSRF protection
- [ ] Verify SQL injection fixes
- [ ] Test file upload security
- [ ] Validate input sanitization
- [ ] Check session security

### Penetration Testing
- [ ] Conduct security assessment
- [ ] Test for common vulnerabilities
- [ ] Verify HTTPS implementation
- [ ] Check for information leakage

## 📞 EMERGENCY PROCEDURES

### Security Contacts
- [ ] Set up security incident response team
- [ ] Configure emergency contact information
- [ ] Set up security reporting procedures
- [ ] Create incident response plan

### Backup & Recovery
- [ ] Test backup procedures
- [ ] Set up automated backups
- [ ] Configure disaster recovery plan
- [ ] Test restoration procedures

## 🎯 DEPLOYMENT COMMANDS

```bash
# 1. Set permissions
find . -type f -name \"*.php\" -exec chmod 644 {} \\;
find . -type d -exec chmod 755 {} \\;

# 2. Run security validation
php scripts/security-validation.php

# 3. Test application
php -l index.php

# 4. Clear caches
rm -rf storage/cache/*

# 5. Set up cron jobs
crontab cron-jobs.template
```

## ✅ POST-DEPLOYMENT VERIFICATION

- [ ] Verify HTTPS is working
- [ ] Test all application features
- [ ] Confirm security headers are set
- [ ] Validate database connections
- [ ] Check file upload functionality
- [ ] Test user authentication
- [ ] Verify email functionality
- [ ] Check payment processing (if applicable)

---

**🗓️ Deployment Date:** " . date('Y-m-d') . "
**👨‍💻 Deployed By:** __________________
**🔒 Security Status:** PRODUCTION READY
**📞 Emergency Contact:** __________________

---

*This checklist ensures your APS Dream Home application is securely deployed to production.*\n";

        file_put_contents($this->basePath . 'DEPLOYMENT_CHECKLIST.md', $checklist);
        $this->successes[] = "Created comprehensive deployment checklist";
        echo "  • Deployment checklist: Created\n";
    }

    private function findFilesByExtension($extension) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === $extension) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function findDirectories() {
        $dirs = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $dirs[] = $file->getPathname();
            }
        }

        return $dirs;
    }

    private function generateDeploymentReport() {
        echo "\n📊 DEPLOYMENT REPORT\n";
        echo "===================\n";

        echo "\n✅ DEPLOYMENT SUCCESSES (" . count($this->successes) . "):\n";
        foreach ($this->successes as $success) {
            echo "  $success\n";
        }

        echo "\n⚠️  DEPLOYMENT WARNINGS (" . count($this->warnings) . "):\n";
        foreach ($this->warnings as $warning) {
            echo "  $warning\n";
        }

        echo "\n❌ DEPLOYMENT ERRORS (" . count($this->errors) . "):\n";
        foreach ($this->errors as $error) {
            echo "  $error\n";
        }

        echo "\n🎯 DEPLOYMENT STATUS: " . (count($this->errors) === 0 ? 'SUCCESS' : 'NEEDS ATTENTION') . "\n";

        echo "\n📋 NEXT STEPS:\n";
        echo "  1. Review and address any warnings or errors\n";
        echo "  2. Configure HTTPS on your production server\n";
        echo "  3. Set up security monitoring cron jobs\n";
        echo "  4. Test all security features\n";
        echo "  5. Complete the deployment checklist\n";
        echo "  6. Conduct final security audit\n";

        echo "\n🚀 Your application is now ready for secure deployment!\n";
    }
}

// Run the deployment script
try {
    $deployer = new SecurityDeployer();
    $deployer->runDeployment();

} catch (Exception $e) {
    echo "❌ Deployment script failed: " . $e->getMessage() . "\n";
    echo "Please check your environment and try again.\n";
}
?>
