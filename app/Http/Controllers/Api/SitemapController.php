<?php
namespace App\Http\Controllers\Api;
use App\Core\Database\Database;

class SitemapController extends BaseApiController {
    
    public function generate() {
        header('Content-Type: application/xml; charset=utf-8');
        $base = rtrim(BASE_URL, '/');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Static pages
        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/properties', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => '/about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/contact', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/services', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/interior-design', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => '/construction-services', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => '/list-property', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/projects', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/plots', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/testimonials', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/blog', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => '/invest', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/financial-services', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => '/team', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/tools-hub', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => '/faqs', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/login', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => '/register', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => '/stamp-duty-calculator', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/plot-size-converter', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/home-loan-eligibility', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/property-valuation', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/rent-vs-buy', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => '/sip-vs-realestate', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => '/capital-gains-calculator', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => '/gst-calculator', 'priority' => '0.4', 'changefreq' => 'monthly'],
            ['loc' => '/support', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => '/careers', 'priority' => '0.5', 'changefreq' => 'weekly'],
            ['loc' => '/sitemap', 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];
        
        foreach ($staticPages as $page) {
            echo '<url>';
            echo '<loc>' . htmlspecialchars($base . $page['loc']) . '</loc>';
            echo '<priority>' . $page['priority'] . '</priority>';
            echo '<changefreq>' . $page['changefreq'] . '</changefreq>';
            echo '</url>' . "\n";
        }
        
        // Dynamic: colonies/projects from DB
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT id, slug, updated_at FROM colonies WHERE is_active = 1 ORDER BY name");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $slug = $row['slug'] ?? 'colony-' . $row['id'];
                $lastmod = !empty($row['updated_at']) ? date('Y-m-d', strtotime($row['updated_at'])) : '';
                echo '<url>';
                echo '<loc>' . htmlspecialchars($base . '/projects/' . $slug) . '</loc>';
                echo '<priority>0.7</priority>';
                echo '<changefreq>weekly</changefreq>';
                if ($lastmod) echo '<lastmod>' . $lastmod . '</lastmod>';
                echo '</url>' . "\n";
            }
            
            // Dynamic: properties from DB
            $stmt2 = $db->query("SELECT id, updated_at FROM user_properties WHERE status = 'approved' ORDER BY id DESC LIMIT 200");
            while ($row = $stmt2->fetch(\PDO::FETCH_ASSOC)) {
                $lastmod = $row['updated_at'] ? date('Y-m-d', strtotime($row['updated_at'])) : '';
                echo '<url>';
                echo '<loc>' . htmlspecialchars($base . '/properties/' . $row['id']) . '</loc>';
                echo '<priority>0.6</priority>';
                echo '<changefreq>weekly</changefreq>';
                if ($lastmod) echo '<lastmod>' . $lastmod . '</lastmod>';
                echo '</url>' . "\n";
            }
        } catch (\Throwable $e) {
            @error_log('SitemapController: ' . $e->getMessage() . "\n", 3, 'E:/backups/apsdreamhome/sitemap_err.log');
        }
        
        echo '</urlset>';
        exit;
    }
}
