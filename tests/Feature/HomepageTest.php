<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use PDO;

class HomepageTest extends TestCase
{
    private PDO $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    
    public function test_homepage_loads_successfully()
    {
        // Test if home.php file exists and is accessible
        $homeFile = __DIR__ . '/../../home.php';
        $this->assertTrue(file_exists($homeFile), 'Home page file should exist');
        
        // Check if the file is readable
        $this->assertTrue(is_readable($homeFile), 'Home page file should be readable');
        
        // Verify the file contains expected content
        $content = file_get_contents($homeFile);
        $this->assertNotEmpty($content, 'Home page should not be empty');
        $this->assertStringContains('APS Dream Home', $content, 'Home page should contain company name');
    }
    
    public function test_homepage_has_required_elements()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for essential HTML structure
        $this->assertStringContains('<!DOCTYPE', $content, 'Should have DOCTYPE declaration');
        $this->assertStringContains('<html', $content, 'Should have HTML tag');
        $this->assertStringContains('<head>', $content, 'Should have head section');
        $this->assertStringContains('<body>', $content, 'Should have body section');
        
        // Check for meta tags
        $this->assertStringContains('<meta charset=', $content, 'Should have charset meta tag');
        $this->assertStringContains('<meta name="viewport"', $content, 'Should have viewport meta tag');
        
        // Check for title
        $this->assertStringContains('<title>', $content, 'Should have title tag');
    }
    
    public function test_homepage_has_navigation()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for navigation elements
        $this->assertStringContains('nav', $content, 'Should have navigation');
        $this->assertStringContains('menu', $content, 'Should have menu');
        
        // Check for common navigation links
        $navLinks = ['home', 'properties', 'projects', 'about', 'contact'];
        foreach ($navLinks as $link) {
            $this->assertStringContains($link, $content, "Should have {$link} navigation link");
        }
    }
    
    public function test_homepage_has_hero_section()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for hero section
        $this->assertStringContains('hero', $content, 'Should have hero section');
        $this->assertStringContains('banner', $content, 'Should have banner section');
        
        // Check for call-to-action elements
        $this->assertStringContains('button', $content, 'Should have buttons');
        $this->assertStringContains('cta', $content, 'Should have call-to-action');
    }
    
    public function test_homepage_displays_featured_properties()
    {
        // Check if there are properties to display
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE featured = 1");
        $stmt->execute();
        $featuredCount = $stmt->fetch()['count'];
        
        if ($featuredCount > 0) {
            $homeFile = __DIR__ . '/../../home.php';
            $content = file_get_contents($homeFile);
            
            // Check for property display elements
            $this->assertStringContains('properties', $content, 'Should display properties');
            $this->assertStringContains('featured', $content, 'Should display featured properties');
        }
    }
    
    public function test_homepage_has_search_functionality()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for search form
        $this->assertStringContains('search', $content, 'Should have search functionality');
        $this->assertStringContains('form', $content, 'Should have search form');
        
        // Check for search inputs
        $searchInputs = ['location', 'type', 'price'];
        foreach ($searchInputs as $input) {
            $this->assertStringContains($input, $content, "Should have {$input} search input");
        }
    }
    
    public function test_homepage_has_contact_information()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for contact information
        $this->assertStringContains('contact', $content, 'Should have contact information');
        $this->assertStringContains('phone', $content, 'Should have phone number');
        $this->assertStringContains('email', $content, 'Should have email address');
        
        // Check for social media links
        $this->assertStringContains('facebook', $content, 'Should have Facebook link');
        $this->assertStringContains('twitter', $content, 'Should have Twitter link');
        $this->assertStringContains('instagram', $content, 'Should have Instagram link');
    }
    
    public function test_homepage_has_footer()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for footer
        $this->assertStringContains('footer', $content, 'Should have footer');
        
        // Check for footer links
        $footerLinks = ['about', 'privacy', 'terms', 'sitemap'];
        foreach ($footerLinks as $link) {
            $this->assertStringContains($link, $content, "Should have {$link} footer link");
        }
    }
    
    public function test_homepage_is_responsive()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for responsive design elements
        $this->assertStringContains('bootstrap', $content, 'Should use Bootstrap for responsiveness');
        $this->assertStringContains('responsive', $content, 'Should have responsive design');
        $this->assertStringContains('mobile', $content, 'Should be mobile-friendly');
    }
    
    public function test_homepage_has_seo_elements()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for SEO meta tags
        $this->assertStringContains('description', $content, 'Should have meta description');
        $this->assertStringContains('keywords', $content, 'Should have meta keywords');
        
        // Check for structured data
        $this->assertStringContains('schema.org', $content, 'Should have structured data');
        $this->assertStringContains('RealEstate', $content, 'Should have real estate schema');
    }
    
    public function test_homepage_has_analytics()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for analytics integration
        $this->assertStringContains('analytics', $content, 'Should have analytics integration');
        $this->assertStringContains('google', $content, 'Should have Google Analytics');
    }
    
    public function test_homepage_has_security_headers()
    {
        $homeFile = __DIR__ . '/../../home.php';
        $content = file_get_contents($homeFile);
        
        // Check for security headers
        $this->assertStringContains('csrf', $content, 'Should have CSRF protection');
        $this->assertStringContains('security', $content, 'Should have security measures');
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }
}
