<?php

namespace App\Services\SEO;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * SEO Management Service
 * Automated SEO optimization, sitemap generation, meta tags
 */
class SEOManagementService
{
    use ServiceTenantTrait;

    private $database;
    private $baseUrl;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->baseUrl = $_ENV['APP_URL'] ?? 'https://apsdreamhome.com';
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // SEO meta tags table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // URL redirects
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Broken links
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // SEO analytics
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Generate XML sitemap
     */
    public function generateSitemap(): string
    {
        $urls = [];
        
        // Static pages
        $staticPages = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/about', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => '/properties', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/services', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];
        
        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $this->baseUrl . $page['url'],
                'lastmod' => date('Y-m-d'),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority']
            ];
        }
        
        // Property pages
        $propSql = "SELECT id, slug, updated_at FROM properties WHERE status = 'available'" . $this->tenantSql() . " ORDER BY updated_at DESC";
        $properties = $this->database->query($propSql)->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($properties as $property) {
            $urls[] = [
                'loc' => $this->baseUrl . '/property/' . ($property['slug'] ?? $property['id']),
                'lastmod' => date('Y-m-d', strtotime($property['updated_at'])),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }
        
        // Project pages
        $projSql = "SELECT id, slug, updated_at FROM projects WHERE status = 'active'" . $this->tenantSql() . " ORDER BY updated_at DESC";
        $projects = $this->database->query($projSql)->fetchAll(\PDO::FETCH_ASSOC);
        $projects = $this->database->query($projSql)->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($projects as $project) {
            $urls[] = [
                'loc' => $this->baseUrl . '/project/' . ($project['slug'] ?? $project['id']),
                'lastmod' => date('Y-m-d', strtotime($project['updated_at'])),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        }
        
        // Location pages
        $locSql = "SELECT DISTINCT city FROM properties WHERE city IS NOT NULL AND status = 'available'" . $this->tenantSql();
        $cities = $this->database->query($locSql)->fetchAll(\PDO::FETCH_COLUMN);
        
        foreach ($cities as $city) {
            $urls[] = [
                'loc' => $this->baseUrl . '/properties/' . urlencode(strtolower($city)),
                'lastmod' => date('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.7'
            ];
        }
        
        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        
        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }
        
        $xml .= '</urlset>';
        
        // Save to file
        $sitemapPath = PUBLIC_PATH . '/sitemap.xml';
        file_put_contents($sitemapPath, $xml);
        
        return $xml;
    }
    
    /**
     * Generate robots.txt
     */
    public function generateRobotsTxt(): string
    {
        $content = "User-agent: *\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /user/dashboard\n";
        $content .= "Allow: /\n";
        $content .= "\n";
        $content .= "Sitemap: {$this->baseUrl}/sitemap.xml\n";
        $content .= "\n";
        $content .= "# Crawl-delay: 10\n";
        $content .= "Host: {$this->baseUrl}\n";
        
        $robotsPath = PUBLIC_PATH . '/robots.txt';
        file_put_contents($robotsPath, $content);
        
        return $content;
    }
    
    /**
     * Auto-generate meta tags for property
     */
    public function generatePropertyMeta(int $propertyId): array
    {
        $sql = "SELECT p.*, pi.image_path, c.name as city_name, s.name as state_name
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            LEFT JOIN cities c ON p.city_id = c.id
            LEFT JOIN states s ON p.state_id = s.id
            WHERE p.id = ?" . $this->tenantSql();
        
        $stmt = $this->database->prepare($sql);
        $params = [$propertyId];
        if ($this->isTenantScoped()) $params[] = $this->tenantId();
        $stmt->execute($params);
        $property = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$property) {
            return [];
        }
        
        $title = $this->truncate($property['title'], 60);
        $description = $this->truncate(
            $property['description'] ?? "Buy {$property['type']} in {$property['city']} - {$property['area']} sqft at ₹" . number_format($property['price']),
            155
        );
        
        $keywords = implode(', ', [
            $property['type'] . ' for sale',
            $property['city'] . ' property',
            $property['locality'] ?? '',
            $property['bedrooms'] ? $property['bedrooms'] . ' bhk' : '',
            'real estate',
            'aps dream home'
        ]);
        
        $meta = [
            'entity_type' => 'property',
            'entity_id' => $propertyId,
            'title' => $title . ' | APS Dream Home',
            'description' => $description,
            'keywords' => $keywords,
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $property['image_path'] ? $this->baseUrl . '/uploads/' . $property['image_path'] : null,
            'canonical_url' => $this->baseUrl . '/property/' . $propertyId,
            'priority' => 0.8,
            'change_frequency' => 'weekly',
            'schema_markup' => $this->generatePropertySchema($property)
        ];
        
        $this->saveMetaTags($meta);
        
        return $meta;
    }
    
    /**
     * Generate Schema.org markup for property
     */
    private function generatePropertySchema(array $property): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateListing',
            'name' => $property['title'],
            'description' => $property['description'],
            'url' => $this->baseUrl . '/property/' . $property['id'],
            'image' => $property['image_path'] ? $this->baseUrl . '/uploads/' . $property['image_path'] : null,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $property['locality'] ?? $property['city'],
                'addressRegion' => $property['state'],
                'addressCountry' => 'IN'
            ],
            'floorSize' => [
                '@type' => 'QuantitativeValue',
                'value' => $property['area'],
                'unitCode' => 'SQF'
            ],
            'numberOfRooms' => $property['bedrooms'] ?? null,
            'price' => $property['price'],
            'priceCurrency' => 'INR',
            'offers' => [
                '@type' => 'Offer',
                'price' => $property['price'],
                'priceCurrency' => 'INR',
                'availability' => $property['status'] === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'
            ]
        ];
    }
    
    /**
     * Save meta tags
     */
    private function saveMetaTags(array $meta): void
    {
        $sql = "INSERT INTO seo_meta_tags 
            (entity_type, entity_id, title, description, keywords, og_title, og_description, 
             og_image, canonical_url, priority, change_frequency, schema_markup" . 
            implode(',', array_keys($this->tenantInsertData())) . ") 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?" . 
            implode(',', array_fill(0, count($this->tenantInsertData()), '?')) . ")
            ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            description = VALUES(description),
            keywords = VALUES(keywords),
            og_title = VALUES(og_title),
            og_description = VALUES(og_description),
            og_image = VALUES(og_image),
            canonical_url = VALUES(canonical_url),
            priority = VALUES(priority),
            schema_markup = VALUES(schema_markup),
            updated_at = NOW()";
        
        $stmt = $this->database->prepare($sql);
        $executeParams = array_merge([
            $meta['entity_type'],
            $meta['entity_id'],
            $meta['title'],
            $meta['description'],
            $meta['keywords'],
            $meta['og_title'],
            $meta['og_description'],
            $meta['og_image'],
            $meta['canonical_url'],
            $meta['priority'],
            $meta['change_frequency'],
            json_encode($meta['schema_markup'])
        ], array_values($this->tenantInsertData()));
        $stmt->execute($executeParams);
    }
    
    /**
     * Get meta tags for entity
     */
    public function getMetaTags(string $entityType, int $entityId): ?array
    {
        $sql = "SELECT * FROM seo_meta_tags WHERE entity_type = ? AND entity_id = ?" . $this->tenantSql();
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$entityType, $entityId]);
        $meta = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($meta && $meta['schema_markup']) {
            $meta['schema_markup'] = json_decode($meta['schema_markup'], true);
        }
        
        return $meta ?: null;
    }
    
    /**
     * Add URL redirect
     */
    public function addRedirect(string $oldUrl, string $newUrl, string $type = '301'): array
    {
        try {
            $sql = "INSERT INTO url_redirects (old_url, new_url, redirect_type" . 
            (empty($this->tenantInsertData()) ? '' : ', tenant_id') . ") 
            VALUES (?, ?, ?" . 
            (empty($this->tenantInsertData()) ? '' : ', ?') . ")
            ON DUPLICATE KEY UPDATE
            new_url = VALUES(new_url),
            redirect_type = VALUES(redirect_type)";
            
        $stmt = $this->database->prepare($sql);
        $execParams = [$oldUrl, $newUrl, $type];
        if (!empty($this->tenantInsertData())) $execParams = array_merge($execParams, array_values($this->tenantInsertData()));
        $stmt->execute($execParams);
            
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get redirect for URL
     */
    public function getRedirect(string $url): ?array
    {
        $sql = "SELECT * FROM url_redirects WHERE old_url = ? AND is_active = 1" . $this->tenantSql();
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$url]);
        
        $redirect = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($redirect) {
            // Update hit count
            $updateSql = "UPDATE url_redirects SET hit_count = hit_count + 1, last_accessed = NOW() WHERE id = ?" . $this->tenantSql();
            $updateStmt = $this->database->prepare($updateSql);
            $updateStmt->execute([$redirect['id']]);
        }
        
        return $redirect ?: null;
    }
    
    /**
     * Log broken link
     */
    public function logBrokenLink(string $url, ?string $sourcePage = null, ?int $statusCode = null, ?string $error = null): void
    {
$sql = "INSERT INTO broken_links (url, source_page, status_code, error_message" . 
                (empty($this->tenantInsertData()) ? '' : ', tenant_id') . ") 
                VALUES (?, ?, ?, ?" . 
                (empty($this->tenantInsertData()) ? '' : ', ?') . ")
                ON DUPLICATE KEY UPDATE
                found_at = NOW(),
                is_fixed = 0";
            
            $stmt = $this->database->prepare($sql);
            $execParams = [$url, $sourcePage, $statusCode, $error];
            if (!empty($this->tenantInsertData())) $execParams = array_merge($execParams, array_values($this->tenantInsertData()));
            $stmt->execute($execParams);
    }
    
    /**
     * Get SEO analytics
     */
    public function getAnalytics(string $dateFrom, string $dateTo): array
    {
        $sql = "SELECT 
            SUM(page_views) as total_views,
            SUM(unique_visitors) as total_visitors,
            AVG(avg_time_on_page) as avg_time,
            AVG(bounce_rate) as avg_bounce_rate,
            SUM(search_impressions) as total_impressions,
            SUM(search_clicks) as total_clicks,
            AVG(search_position) as avg_position
            FROM seo_analytics 
            WHERE date_recorded BETWEEN ? AND ?" . $this->tenantSql();
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top pages
     */
    public function getTopPages(int $limit = 10): array
    {
        $sql = "SELECT page_url, SUM(page_views) as total_views,
            AVG(search_position) as avg_position
            FROM seo_analytics" . 
            $this->tenantSql() . "
            GROUP BY page_url
            ORDER BY total_views DESC
            LIMIT ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Truncate string
     */
    private function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length - 3) . '...';
    }
}
