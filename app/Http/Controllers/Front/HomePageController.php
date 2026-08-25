<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use PDO;

/**
 * HomePageController
 * Core landing pages (home, about, testimonials, team, gallery, 3D tour, thank you, coming soon, sitemap)
 */
class HomePageController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    protected function loadPageContent(string $slug): array
    {
        $pageTitle = '';
        $pageContent = '';
        try {
            $stmt = $this->db->prepare("SELECT title, content FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $pageTitle = $row['title'];
                $pageContent = $row['content'];
            }
        } catch (\Exception $e) {
            error_log('HomePageController loadPageContent: ' . $e->getMessage());
        }
        return [$pageTitle, $pageContent];
    }

    // Core Pages
    public function home()
    {
        // Get hero statistics from DB settings with fallback
        $scStats = \App\Services\SiteContentService::getInstance()->getSection('settings');
        $hero_stats = [
            'years_experience' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_experience_value'] ?? '15') ?: 15,
            'projects_completed' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_projects_value'] ?? '50') ?: 50,
            'happy_customers' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_families_value'] ?? '1000') ?: 1000,
            'awards_won' => (int) preg_replace('/[^0-9]/', '', $scStats['stat_properties_value'] ?? '25') ?: 25,
        ];

        // Get featured properties from database (hot-path cached, 15 min TTL)
        $featured_properties = [];
        $all_projects = [];
        try {
            $cachedHome = \App\Services\Cache\HotPathCacheService::getHomeFeaturedProperties(
                function () {
                    $stmt = $this->db->prepare("SELECT * FROM sites WHERE status IN ('active', 'completed') ORDER BY site_name LIMIT 6");
                    $stmt->execute();
                    return $stmt->fetchAll(\PDO::FETCH_OBJ);
                }
            );
            $all_projects = is_array($cachedHome) ? $cachedHome : [];

            // Map to featured format
            foreach ($all_projects as $project) {
                if (!is_object($project)) {
                    continue;
                }
                $siteName = $project->site_name ?? '';
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $siteName));

                // Get starting price from plots table
                $priceText = 'Price on Request';
                try {
                    $priceStmt = $this->db->prepare("SELECT MIN(price_per_sqft) as min_price FROM plots WHERE colony_id = ? AND status != 'sold'");
                    $priceStmt->execute([(int)$project->id]);
                    $priceRow = $priceStmt->fetch(\PDO::FETCH_ASSOC);
                    if ($priceRow && !empty($priceRow['min_price'])) {
                        $minPrice = (float)$priceRow['min_price'];
                        $priceText = 'Starting from ' . "\xE2\x82\xB9" . number_format($minPrice) . '/sqft';
                    }
                } catch (\Exception $e) {
                    // fallback to generic text
                    error_log($e->getMessage());
                }

                $featured_properties[] = [
                    'id' => $project->id ?? null,
                    'title' => $siteName,
                    'location' => ($project->district ?? '') . ', ' . ($project->state ?? ''),
                    'city' => $project->district ?? '',
                    'price' => $priceText,
                    'slug' => $slug,
                    'type' => ucfirst($project->site_type ?? 'Residential'),
                    'status' => ($project->status === 'active') ? 'Available' : 'Completed',
                    'total_area' => $project->total_area ?? null,
                    'description' => $project->description ?? null
                ];
            }
        } catch (\Exception $e) {
            error_log("Home projects error: " . $e->getMessage());
        }

        // Get latest blog posts for homepage
        $latest_blog_posts = [];
        try {
            $stmt = $this->db->prepare("SELECT id, title, slug, excerpt, featured_image, category, created_at FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
            $stmt->execute();
            $latest_blog_posts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($latest_blog_posts as &$post) {
                if (!empty($post['featured_image']) && strpos($post['featured_image'], 'http') !== 0) {
                    $post['featured_image'] = BASE_URL . '/' . ltrim($post['featured_image'], '/');
                }
            }
        } catch (\Exception $e) {
            error_log("Home blog posts error: " . $e->getMessage());
        }

        // Get hero properties from cache (hot-path)
        $hero_properties = \App\Services\Cache\HotPathCacheService::getHomeHeroProperties(
            function () {
                $stmt = $this->db->prepare("SELECT id, title, price, city, location, type, area_sqft, bedrooms, featured, image_path FROM properties WHERE status = 'active' AND featured = 1 AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 3");
                $stmt->execute();
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        );

        $this->render('pages/home', [
            'page_title' => 'APS Dream Home - Find Your Dream Property',
            'page_description' => 'Discover premium residential and commercial properties across India. Verified listings, transparent pricing, and expert guidance.',
            'hero_stats' => $hero_stats,
            'featured_properties' => $featured_properties,
            'latest_blog_posts' => $latest_blog_posts,
            'hero_properties' => $hero_properties,
        ]);
    }

    public function threeDTour()
    {
        $this->render('pages/3d_tour', [
            'page_title' => '3D Virtual Tour - APS Dream Home',
            'page_description' => 'Explore our properties with immersive 3D virtual tours.',
        ]);
    }

    public function about()
    {
        $siteContent = \App\Services\SiteContentService::getInstance()->getSection('about');
        $leaders = [
            [
                'name' => $siteContent['leader_1_name'] ?? 'Abhaay Singh',
                'role' => $siteContent['leader_1_role'] ?? 'Founder & Director',
                'bio' => $siteContent['leader_1_bio'] ?? 'Visionary leader with 20+ years in real estate development.',
                'photo' => $siteContent['leader_1_photo'] ?? 'leader-1.jpg',
                'linkedin' => $siteContent['leader_1_linkedin'] ?? '#',
                'twitter' => $siteContent['leader_1_twitter'] ?? '#',
            ],
            [
                'name' => $siteContent['leader_2_name'] ?? 'Praveen Prabhat',
                'role' => $siteContent['leader_2_role'] ?? 'Senior Property Advisor',
                'bio' => $siteContent['leader_2_bio'] ?? 'Government servant with passion for real estate advisory.',
                'photo' => $siteContent['leader_2_photo'] ?? 'leader-2.jpg',
                'linkedin' => $siteContent['leader_2_linkedin'] ?? '#',
                'twitter' => $siteContent['leader_2_twitter'] ?? '#',
            ],
            [
                'name' => $siteContent['leader_3_name'] ?? 'Vijay Verma',
                'role' => $siteContent['leader_3_role'] ?? 'Chief Technology Officer',
                'bio' => $siteContent['leader_3_bio'] ?? 'Tech leader driving digital transformation in real estate.',
                'photo' => $siteContent['leader_3_photo'] ?? 'leader-3.jpg',
                'linkedin' => $siteContent['leader_3_linkedin'] ?? '#',
                'twitter' => $siteContent['leader_3_twitter'] ?? '#',
            ],
            [
                'name' => $siteContent['leader_4_name'] ?? 'Shushant Srivastava',
                'role' => $siteContent['leader_4_role'] ?? 'Legal Head',
                'bio' => $siteContent['leader_4_bio'] ?? 'Legal expert ensuring compliance and transparency.',
                'photo' => $siteContent['leader_4_photo'] ?? 'leader-4.jpg',
                'linkedin' => $siteContent['leader_4_linkedin'] ?? '#',
                'twitter' => $siteContent['leader_4_twitter'] ?? '#',
            ],
            [
                'name' => $siteContent['leader_5_name'] ?? 'Anuj Srivastava',
                'role' => $siteContent['leader_5_role'] ?? 'Finance Head',
                'bio' => $siteContent['leader_5_bio'] ?? 'Financial strategist with expertise in real estate financing.',
                'photo' => $siteContent['leader_5_photo'] ?? 'leader-5.jpg',
                'linkedin' => $siteContent['leader_5_linkedin'] ?? '#',
                'twitter' => $siteContent['leader_5_twitter'] ?? '#',
            ],
            [
                'name' => $siteContent['leader_6_name'] ?? 'Pramod Sharma',
                'role' => $siteContent['leader_6_role'] ?? 'Marketing Head',
                'bio' => $siteContent['leader_6_bio'] ?? 'Marketing expert with focus on customer acquisition.',
                'photo' => $siteContent['leader_6_photo'] ?? 'leader-6.jpg',
                'linkedin' => $siteContent['leader_6_linkedin'] ?? '#',
                'twitter' => $siteContent['leader_6_twitter'] ?? '#',
            ],
        ];

        $this->render('pages/about', [
            'page_title' => 'About Us - APS Dream Home',
            'page_description' => 'Learn about APS Dream Home - your trusted real estate partner.',
            'leaders' => $leaders,
        ]);
    }

    public function testimonials()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 10");
            $stmt->execute();
            $testimonials = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('Testimonials error: ' . $e->getMessage());
            $testimonials = [];
        }

        $this->render('pages/testimonials', [
            'page_title' => 'Customer Testimonials - APS Dream Home',
            'page_description' => 'Read what our customers say about us.',
            'testimonials' => $testimonials,
        ]);
    }

    public function team()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC");
            $stmt->execute();
            $teamMembers = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('Team page error: ' . $e->getMessage());
            $teamMembers = [];
        }

        $this->render('pages/team', [
            'page_title' => 'Our Team - APS Dream Home',
            'page_description' => 'Meet the team behind APS Dream Home.',
            'team_members' => $teamMembers,
        ]);
    }

    public function gallery()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM gallery_albums WHERE status = 'active' ORDER BY sort_order ASC");
            $stmt->execute();
            $albums = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('Gallery error: ' . $e->getMessage());
            $albums = [];
        }

        $this->render('pages/gallery', [
            'page_title' => 'Gallery - APS Dream Home',
            'page_description' => 'Explore our project gallery.',
            'albums' => $albums,
        ]);
    }

    public function thankYou()
    {
        $this->render('pages/thank_you', [
            'page_title' => 'Thank You - APS Dream Home',
            'page_description' => 'Thank you for your submission.',
        ]);
    }

    public function comingSoon()
    {
        $this->render('pages/coming_soon', [
            'page_title' => 'Coming Soon - APS Dream Home',
            'page_description' => 'Exciting features coming soon!',
        ]);
    }

    public function sitemap()
    {
        $this->render('pages/sitemap', [
            'page_title' => 'Sitemap - APS Dream Home',
            'page_description' => 'Site map for APS Dream Home.',
        ]);
    }
}