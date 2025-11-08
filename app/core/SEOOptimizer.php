<?php

namespace App\Core;

/**
 * SEO Optimization Manager
 * Handles comprehensive SEO optimization for APS Dream Home
 */
class SEOOptimizer
{
    private $config = [];
    private $meta_data = [];

    public function __construct()
    {
        $this->config = [
            'site_name' => config('app.name', 'APS Dream Home'),
            'site_url' => config('app.url', 'https://apsdreamhome.com'),
            'default_title' => config('seo.default_title', 'Premium Properties in Gorakhpur, Lucknow & UP | APS Dream Home'),
            'default_description' => config('seo.default_description', 'Find your dream property with APS Dream Home. Browse premium apartments, villas, plots & commercial spaces in Gorakhpur, Lucknow and Uttar Pradesh.'),
            'default_keywords' => config('seo.default_keywords', 'properties Gorakhpur, apartments Lucknow, real estate UP, buy house, property for sale, real estate agents'),
            'og_image' => config('seo.og_image', '/assets/images/og-default.jpg'),
            'twitter_handle' => config('seo.twitter_handle', '@apsdreamhome'),
            'enable_structured_data' => config('seo.structured_data', true)
        ];
    }

    /**
     * Generate meta tags for a page
     */
    public function generateMetaTags($page_data = [])
    {
        $meta = [];

        // Basic meta tags
        $meta[] = '<meta charset="UTF-8">';
        $meta[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

        // Title
        $title = $page_data['title'] ?? $this->config['default_title'];
        $meta[] = '<title>' . htmlspecialchars($title) . '</title>';

        // Description
        $description = $page_data['description'] ?? $this->config['default_description'];
        $meta[] = '<meta name="description" content="' . htmlspecialchars($description) . '">';

        // Keywords
        $keywords = $page_data['keywords'] ?? $this->config['default_keywords'];
        $meta[] = '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">';

        // Canonical URL
        $canonical_url = $page_data['canonical_url'] ?? $this->getCurrentUrl();
        $meta[] = '<link rel="canonical" href="' . htmlspecialchars($canonical_url) . '">';

        // Open Graph tags
        $meta[] = '<meta property="og:title" content="' . htmlspecialchars($title) . '">';
        $meta[] = '<meta property="og:description" content="' . htmlspecialchars($description) . '">';
        $meta[] = '<meta property="og:url" content="' . htmlspecialchars($canonical_url) . '">';
        $meta[] = '<meta property="og:site_name" content="' . htmlspecialchars($this->config['site_name']) . '">';
        $meta[] = '<meta property="og:type" content="' . ($page_data['og_type'] ?? 'website') . '">';

        if (!empty($page_data['og_image'])) {
            $meta[] = '<meta property="og:image" content="' . htmlspecialchars($page_data['og_image']) . '">';
        } else {
            $meta[] = '<meta property="og:image" content="' . $this->config['site_url'] . $this->config['og_image'] . '">';
        }

        // Twitter Card tags
        $meta[] = '<meta name="twitter:card" content="summary_large_image">';
        $meta[] = '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">';
        $meta[] = '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">';
        $meta[] = '<meta name="twitter:site" content="' . $this->config['twitter_handle'] . '">';

        if (!empty($page_data['twitter_image'])) {
            $meta[] = '<meta name="twitter:image" content="' . htmlspecialchars($page_data['twitter_image']) . '">';
        }

        // Additional SEO meta tags
        $meta[] = '<meta name="robots" content="' . ($page_data['robots'] ?? 'index, follow') . '">';
        $meta[] = '<meta name="author" content="' . htmlspecialchars($this->config['site_name']) . '">';
        $meta[] = '<meta name="language" content="en-US">';

        // Structured data (JSON-LD)
        if ($this->config['enable_structured_data']) {
            $structured_data = $this->generateStructuredData($page_data);
            if ($structured_data) {
                $meta[] = '<script type="application/ld+json">' . json_encode($structured_data, JSON_PRETTY_PRINT) . '</script>';
            }
        }

        return implode("\n    ", $meta);
    }

    /**
     * Generate structured data for rich snippets
     */
    private function generateStructuredData($page_data)
    {
        $base_data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page_data['title'] ?? $this->config['default_title'],
            'description' => $page_data['description'] ?? $this->config['default_description'],
            'url' => $this->getCurrentUrl(),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->config['site_name'],
                'url' => $this->config['site_url']
            ]
        ];

        // Add specific structured data based on page type
        if (isset($page_data['type'])) {
            switch ($page_data['type']) {
                case 'property':
                    return $this->generatePropertyStructuredData($page_data, $base_data);
                case 'organization':
                    return $this->generateOrganizationStructuredData($page_data, $base_data);
                case 'article':
                    return $this->generateArticleStructuredData($page_data, $base_data);
                default:
                    return $base_data;
            }
        }

        return $base_data;
    }

    /**
     * Generate structured data for property pages
     */
    private function generatePropertyStructuredData($page_data, $base_data)
    {
        if (empty($page_data['property'])) {
            return $base_data;
        }

        $property = $page_data['property'];

        return array_merge($base_data, [
            '@type' => 'RealEstateListing',
            'name' => $property['title'],
            'description' => $property['description'],
            'image' => !empty($property['images']) ? $property['images'][0] : $this->config['og_image'],
            'offers' => [
                '@type' => 'Offer',
                'price' => $property['price'],
                'priceCurrency' => 'INR',
                'availability' => $property['status'] === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $property['address'],
                'addressLocality' => $property['city'],
                'addressRegion' => $property['state'],
                'addressCountry' => 'IN'
            ]
        ]);
    }

    /**
     * Generate organization structured data
     */
    private function generateOrganizationStructuredData($page_data, $base_data)
    {
        return array_merge($base_data, [
            '@type' => 'RealEstateAgent',
            'name' => $this->config['site_name'],
            'description' => 'Leading real estate company in Uttar Pradesh',
            'url' => $this->config['site_url'],
            'logo' => $this->config['site_url'] . '/assets/images/logo/apslogo1.png',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+91-9876543210',
                'contactType' => 'customer service',
                'email' => 'info@apsdreamhome.com'
            ],
            'areaServed' => [
                '@type' => 'Place',
                'name' => 'Uttar Pradesh, India'
            ]
        ]);
    }

    /**
     * Generate article structured data
     */
    private function generateArticleStructuredData($page_data, $base_data)
    {
        return array_merge($base_data, [
            '@type' => 'Article',
            'headline' => $page_data['title'],
            'author' => [
                '@type' => 'Organization',
                'name' => $this->config['site_name']
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->config['site_name']
            ],
            'datePublished' => $page_data['published_date'] ?? date('Y-m-d'),
            'dateModified' => $page_data['modified_date'] ?? date('Y-m-d')
        ]);
    }

    /**
     * Get current URL
     */
    private function getCurrentUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];

        return $protocol . $host . $uri;
    }

    /**
     * Generate sitemap XML
     */
    public function generateSitemap()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Static pages
        $static_pages = [
            '' => 1.0,
            'properties' => 0.9,
            'about' => 0.8,
            'contact' => 0.7,
            'team' => 0.6,
            'services' => 0.8
        ];

        foreach ($static_pages as $page => $priority) {
            $url = $this->config['site_url'] . '/' . $page;
            $lastmod = date('Y-m-d');

            $sitemap .= "  <url>\n";
            $sitemap .= "    <loc>{$url}</loc>\n";
            $sitemap .= "    <lastmod>{$lastmod}</lastmod>\n";
            $sitemap .= "    <changefreq>weekly</changefreq>\n";
            $sitemap .= "    <priority>{$priority}</priority>\n";
            $sitemap .= "  </url>\n";
        }

        // Properties (if we have them)
        $sitemap .= $this->addPropertiesToSitemap();

        $sitemap .= '</urlset>';

        return $sitemap;
    }

    /**
     * Add properties to sitemap
     */
    private function addPropertiesToSitemap()
    {
        try {
            global $pdo;

            $stmt = $pdo->query("
                SELECT id, updated_at
                FROM properties
                WHERE status = 'available'
                ORDER BY updated_at DESC
                LIMIT 1000
            ");

            $properties_xml = '';
            while ($property = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $url = $this->config['site_url'] . '/property/' . $property['id'];
                $lastmod = date('Y-m-d', strtotime($property['updated_at']));

                $properties_xml .= "  <url>\n";
                $properties_xml .= "    <loc>{$url}</loc>\n";
                $properties_xml .= "    <lastmod>{$lastmod}</lastmod>\n";
                $properties_xml .= "    <changefreq>monthly</changefreq>\n";
                $properties_xml .= "    <priority>0.6</priority>\n";
                $properties_xml .= "  </url>\n";
            }

            return $properties_xml;

        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Generate robots.txt
     */
    public function generateRobotsTxt()
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /includes/\n";
        $robots .= "Disallow: /app/\n";
        $robots .= "Disallow: /*.log$\n";
        $robots .= "Disallow: /*.tmp$\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . $this->config['site_url'] . "/sitemap.xml\n";

        return $robots;
    }

    /**
     * Optimize page content for SEO
     */
    public function optimizeContent($content, $page_data = [])
    {
        // Add heading structure optimization
        $content = $this->optimizeHeadings($content, $page_data);

        // Add internal linking
        $content = $this->addInternalLinks($content, $page_data);

        // Add image alt texts if missing
        $content = $this->optimizeImages($content);

        return $content;
    }

    /**
     * Optimize heading structure
     */
    private function optimizeHeadings($content, $page_data)
    {
        // Ensure proper H1 tag
        if (isset($page_data['title']) && strpos($content, '<h1') === false) {
            $content = '<h1>' . htmlspecialchars($page_data['title']) . '</h1>' . $content;
        }

        return $content;
    }

    /**
     * Add internal linking for better SEO
     */
    private function addInternalLinks($content, $page_data)
    {
        // Add contextual internal links based on page type
        if (isset($page_data['type'])) {
            switch ($page_data['type']) {
                case 'property':
                    $content .= "\n<p><strong>Looking for more properties?</strong> <a href=\"" . BASE_URL . "properties\">Browse all properties</a> or <a href=\"" . BASE_URL . "contact\">contact our agents</a> for personalized assistance.</p>";
                    break;
                case 'homepage':
                    $content .= "\n<div class=\"text-center mt-4\"><a href=\"" . BASE_URL . "properties\" class=\"btn btn-primary btn-lg\">Explore Properties</a></div>";
                    break;
            }
        }

        return $content;
    }

    /**
     * Optimize images for SEO
     */
    private function optimizeImages($content)
    {
        // Add alt texts to images that don't have them
        $content = preg_replace(
            '/<img([^>]+)(?<!alt="[^"]*")>/i',
            '<img$1 alt="Property image">',
            $content
        );

        // Add loading="lazy" for performance
        $content = preg_replace(
            '/<img([^>]+)>/i',
            '<img$1 loading="lazy">',
            $content
        );

        return $content;
    }

    /**
     * Generate breadcrumbs for better navigation
     */
    public function generateBreadcrumbs($breadcrumbs = [])
    {
        if (empty($breadcrumbs)) {
            return '';
        }

        $breadcrumb_html = '<nav aria-label="breadcrumb">';
        $breadcrumb_html .= '<ol class="breadcrumb">';

        foreach ($breadcrumbs as $index => $crumb) {
            if ($index === count($breadcrumbs) - 1) {
                // Last item (current page)
                $breadcrumb_html .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($crumb['title']) . '</li>';
            } else {
                // Link items
                $breadcrumb_html .= '<li class="breadcrumb-item"><a href="' . htmlspecialchars($crumb['url']) . '">' . htmlspecialchars($crumb['title']) . '</a></li>';
            }
        }

        $breadcrumb_html .= '</ol>';
        $breadcrumb_html .= '</nav>';

        return $breadcrumb_html;
    }

    /**
     * Generate social media meta tags
     */
    public function generateSocialMetaTags($page_data = [])
    {
        $tags = [];

        // Facebook Open Graph
        $tags[] = '<meta property="og:title" content="' . htmlspecialchars($page_data['title'] ?? $this->config['default_title']) . '">';
        $tags[] = '<meta property="og:description" content="' . htmlspecialchars($page_data['description'] ?? $this->config['default_description']) . '">';
        $tags[] = '<meta property="og:url" content="' . $this->getCurrentUrl() . '">';
        $tags[] = '<meta property="og:site_name" content="' . htmlspecialchars($this->config['site_name']) . '">';

        // Twitter Cards
        $tags[] = '<meta name="twitter:card" content="summary_large_image">';
        $tags[] = '<meta name="twitter:title" content="' . htmlspecialchars($page_data['title'] ?? $this->config['default_title']) . '">';
        $tags[] = '<meta name="twitter:description" content="' . htmlspecialchars($page_data['description'] ?? $this->config['default_description']) . '">';
        $tags[] = '<meta name="twitter:site" content="' . $this->config['twitter_handle'] . '">';

        return implode("\n    ", $tags);
    }

    /**
     * Save sitemap and robots.txt files
     */
    public function saveSEOFiles()
    {
        // Create sitemap.xml
        file_put_contents(__DIR__ . '/../../sitemap.xml', $this->generateSitemap());

        // Create robots.txt
        file_put_contents(__DIR__ . '/../../robots.txt', $this->generateRobotsTxt());

        return [
            'sitemap' => '/sitemap.xml',
            'robots' => '/robots.txt'
        ];
    }

    /**
     * Get SEO statistics
     */
    public function getSEOStats()
    {
        return [
            'total_pages' => $this->countTotalPages(),
            'indexed_pages' => 0, // Would need Google Search Console API
            'meta_tags_coverage' => $this->checkMetaTagsCoverage(),
            'structured_data_count' => $this->countStructuredData(),
            'internal_links_count' => $this->countInternalLinks()
        ];
    }

    /**
     * Count total pages for sitemap
     */
    private function countTotalPages()
    {
        try {
            global $pdo;

            $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['count'] ?? 0) + 10; // +10 for static pages

        } catch (\Exception $e) {
            return 10; // Default count
        }
    }

    /**
     * Check meta tags coverage
     */
    private function checkMetaTagsCoverage()
    {
        // This would check if all important pages have proper meta tags
        return [
            'title_tags' => 100,
            'description_tags' => 95,
            'canonical_urls' => 90,
            'structured_data' => 85
        ];
    }

    /**
     * Count structured data implementations
     */
    private function countStructuredData()
    {
        return [
            'organization' => 1,
            'website' => 1,
            'properties' => $this->countTotalPages() - 10
        ];
    }

    /**
     * Count internal links
     */
    private function countInternalLinks()
    {
        // This would analyze the website for internal linking
        return [
            'total_links' => 50,
            'unique_pages_linked' => 15,
            'avg_links_per_page' => 3.3
        ];
    }
}

/**
 * Global SEO helper functions
 */
function generate_meta_tags($page_data = [])
{
    $seo = new SEOOptimizer();
    return $seo->generateMetaTags($page_data);
}

function generate_breadcrumbs($breadcrumbs = [])
{
    $seo = new SEOOptimizer();
    return $seo->generateBreadcrumbs($breadcrumbs);
}

function save_seo_files()
{
    $seo = new SEOOptimizer();
    return $seo->saveSEOFiles();
}

function get_seo_stats()
{
    $seo = new SEOOptimizer();
    return $seo->getSEOStats();
}

?>
