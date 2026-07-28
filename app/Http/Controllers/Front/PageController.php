<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;
use PDO;

class PageController extends BaseController
{
    use TenantAwareTrait;
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
            error_log('PageController loadPageContent: ' . $e->getMessage());
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

        // Get featured projects from database (hot-path cached, 15 min TTL)
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

        // Get rank slabs for MLM benefits table
        $rank_slabs = [];
        try {
            $stmt = $this->db->query("SELECT rank_name, min_gbv, commission_rate, reward_name, reward_value FROM mlm_rank_slabs WHERE is_active = 1 ORDER BY min_gbv ASC");
            $rank_slabs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Home rank_slabs error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'APS Dream Home - Premium Real Estate in UP',
            'page_description' => 'Discover premium residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh',
            'hero_stats' => $hero_stats,
            'featured_properties' => $featured_properties,
            'all_projects' => $all_projects,
            'rank_slabs' => $rank_slabs,
        ];

        $this->render('pages/home', $data);
    }

    public function about()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('about');

        // Load dynamic content from DB (fallback to empty)
        $siteContent = [];
        try {
            $siteContent = \App\Services\SiteContentService::getInstance()->getSection('about');
        } catch (\Exception $e) {
            // fallback: empty, view will use __() lang keys
        }

        $data = [
            'page_title' => ($cmsTitle ?: 'About Us') . ' - APS Dream Home',
            'page_description' => 'Learn more about APS Dream Home',
            'pageContent' => $pageContent,
            'siteContent' => $siteContent,
        ];
        $this->render('pages/about', $data);
    }

    public function testimonials()
    {
        $testimonials = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY display_order ASC, created_at DESC LIMIT 20");
            $stmt->execute();
            $testimonials = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Testimonials error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Testimonials - APS Dream Home',
            'page_description' => 'What our customers say about us',
            'testimonials' => $testimonials,
        ];
        $this->render('pages/testimonials', $data);
    }

    public function team()
    {
        $team = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC");
            $stmt->execute();
            $team = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Team error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Our Team - APS Dream Home',
            'page_description' => 'Meet the team behind APS Dream Home',
            'team' => $team,
        ];
        $this->render('pages/team', $data);
    }

    public function careers()
    {
        $jobs = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM career_jobs WHERE status = 'open' ORDER BY created_at DESC");
            $stmt->execute();
            $jobs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Careers error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Careers - APS Dream Home',
            'page_description' => 'Join our team',
            'jobs' => $jobs,
        ];
        $this->render('pages/careers', $data);
    }

    public function faqs()
    {
        $faqs = [];
        try {
            $stmt = $this->db->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY display_order ASC");
            $faqs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("FAQs error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'FAQs - APS Dream Home',
            'page_description' => 'Frequently asked questions',
            'faqs' => $faqs,
        ];
        $this->render('pages/faqs', $data);
    }

    public function downloads()
    {
        $downloads = [];
        try {
            $stmt = $this->db->query("SELECT * FROM downloads WHERE status = 'active' ORDER BY created_at DESC");
            $downloads = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Downloads error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Downloads - APS Dream Home',
            'page_description' => 'Download brochures and documents',
            'downloads' => $downloads,
        ];
        $this->render('pages/downloads', $data);
    }

    public function reviews()
    {
        $data = [
            'page_title' => 'Customer Reviews - APS Dream Home',
            'page_description' => 'Read customer reviews',
        ];
        $this->render('pages/customer_reviews', $data);
    }

    public function comingSoon()
    {
        $data = [
            'page_title' => 'Coming Soon - APS Dream Home',
            'page_description' => 'This page is under construction',
        ];
        $this->render('pages/coming_soon', $data);
    }

    public function thankYou()
    {
        $data = [
            'page_title' => 'Thank You - APS Dream Home',
            'page_description' => 'Thank you for your submission',
        ];
        $this->render('pages/thank_you', $data);
    }

    public function gallery()
    {
        $images = [];
        try {
            $stmt = $this->db->query("SELECT * FROM gallery_images WHERE status = 'active' ORDER BY sort_order ASC");
            $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Gallery error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Gallery - APS Dream Home',
            'page_description' => 'View our project gallery',
            'images' => $images,
        ];
        $this->render('pages/gallery', $data);
    }

    public function galleryProject($projectId = null)
    {
        $images = [];
        try {
            $sql = "SELECT * FROM gallery_images WHERE status = 'active'";
            $params = [];
            if ($projectId) {
                $sql .= " AND project_id = ?";
                $params[] = $projectId;
            }
            $sql .= " ORDER BY sort_order ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Gallery project error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Project Gallery - APS Dream Home',
            'page_description' => 'View project gallery',
            'images' => $images,
        ];
        $this->render('pages/gallery_project', $data);
    }

    public function blogPost($slug = null)
    {
        if (!$slug) {
            $this->redirect('/blog');
            return;
        }

        $post = null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Blog post error: " . $e->getMessage());
        }

        if (!$post) {
            $this->redirect('/blog');
            return;
        }

        $data = [
            'page_title' => ($post['title'] ?? 'Blog Post') . ' - APS Dream Home',
            'page_description' => $post['excerpt'] ?? '',
            'post' => $post,
        ];
        $this->render('pages/blog_article_detail', $data);
    }

    public function careerApply()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('career-apply');
        $data = [
            'page_title' => ($cmsTitle ?: 'Apply Now') . ' - APS Dream Home',
            'page_description' => 'Apply for a career at APS Dream Home',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/career_apply', $data);
    }

    public function submitCareerApplication()
    {
        header('Content-Type: application/json');
        // Handle form submission
        echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
    }

    public function careerJobs()
    {
        $this->careers(); // Reuse careers method
    }

    public function careerJobDetails($id = null)
    {
        $job = null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM career_jobs WHERE id = ? AND status = 'open'");
            $stmt->execute([$id]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Job details error: " . $e->getMessage());
        }

        if (!$job) {
            $this->redirect('/careers');
            return;
        }

        $data = [
            'page_title' => ($job['title'] ?? 'Job Details') . ' - APS Dream Home',
            'page_description' => $job['description'] ?? '',
            'job' => $job,
        ];
        $this->render('pages/career_job_detail', $data);
    }

    public function builderRegistration()
    {
        $data = [
            'page_title' => 'Builder Registration - APS Dream Home',
            'page_description' => 'Register as a builder',
        ];
        $this->render('pages/builder_registration', $data);
    }

    public function setLanguage(string $lang)
    {
        $allowed = ['en', 'hi'];
        $lang = in_array($lang, $allowed) ? $lang : 'en';
        $_SESSION['user_language'] = $lang;
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';
        $this->redirect($referer);
    }

    public function opportunity()
    {
        $data = [
            'page_title' => 'Career Opportunity - APS Dream Home',
            'page_description' => 'Explore career opportunities',
        ];
        $this->render('pages/opportunity', $data);
    }

public function navigation()
    {
        $data = [
            'page_title' => 'Navigation - APS Dream Home',
        ];
        $this->render('pages/navigation', $data);
    }

    public function services()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('services');
        $data = [
            'page_title' => ($cmsTitle ?: 'Our Services') . ' - APS Dream Home',
            'page_description' => 'Our property services',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/services', $data);
    }

    public function legalServices()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('legal-services');
        $data = [
            'page_title' => ($cmsTitle ?: 'Legal Services') . ' - APS Dream Home',
            'page_description' => 'Legal services for property',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/legal/services', $data);
    }

    public function documents()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('legal-documents');
        $data = [
            'page_title' => ($cmsTitle ?: 'Legal Documents') . ' - APS Dream Home',
            'page_description' => 'Legal documents and templates',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/legal/documents', $data);
    }

    public function index()
    {
        $data = [
            'page_title' => 'Legal - APS Dream Home',
            'page_description' => 'Legal information and documents',
        ];
        $this->render('pages/legal/legal', $data);
    }

    public function insurance()
    {
        $data = [
            'page_title' => 'Property Insurance - APS Dream Home',
            'page_description' => 'Property insurance options',
        ];
        $this->render('pages/insurance', $data);
    }

    public function nachMandate()
    {
        $data = [
            'page_title' => 'NACH Mandate - APS Dream Home',
            'page_description' => 'NACH/e-Mandate setup',
        ];
        $this->render('pages/nach_mandate', $data);
    }

    public function agreements()
    {
        $data = [
            'page_title' => 'Agreements & E-Sign - APS Dream Home',
            'page_description' => 'Legal agreements and e-signature',
        ];
        $this->render('pages/agreements', $data);
    }

    public function reraLookup()
    {
        $data = [
            'page_title' => 'RERA Lookup - APS Dream Home',
            'page_description' => 'Check RERA registration',
        ];
        $this->render('pages/rera_lookup', $data);
    }

    public function titleProtection()
    {
        $data = [
            'page_title' => 'Title Protection - APS Dream Home',
            'page_description' => 'Title protection services',
        ];
        $this->render('pages/title_protection', $data);
    }

    public function propertyVerification()
    {
        $data = [
            'page_title' => 'Property Verification - APS Dream Home',
            'page_description' => 'Property verification badge',
        ];
        $this->render('pages/property_verification', $data);
    }

    public function howItWorks()
    {
        $data = [
            'page_title' => 'How It Works - APS Dream Home',
            'page_description' => 'Step by step guide',
        ];
        $this->render('pages/how_it_works', $data);
    }

    public function disclaimer()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('disclaimer');
        $data = [
            'page_title' => ($cmsTitle ?: 'Disclaimer') . ' - APS Dream Home',
            'page_description' => 'Disclaimer',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/disclaimer', $data);
    }

    public function cancellationPolicy()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('cancellation-policy');
        $data = [
            'page_title' => ($cmsTitle ?: 'Cancellation Policy') . ' - APS Dream Home',
            'page_description' => 'Cancellation policy',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/cancellation_policy', $data);
    }

    public function refundPolicy()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('refund-policy');
        $data = [
            'page_title' => ($cmsTitle ?: 'Refund Policy') . ' - APS Dream Home',
            'page_description' => 'Refund policy',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/refund_policy', $data);
    }

    public function financialServices()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('financial-services');
        $data = [
            'page_title' => ($cmsTitle ?: 'Financial Services') . ' - APS Dream Home',
            'page_description' => 'Financial services for property buyers',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/financial_services', $data);
    }

    public function financialContact()
    {
        header('Content-Type: application/json');
        // Handle financial services contact form
        echo json_encode(['success' => true, 'message' => 'Request submitted']);
    }

    public function bank()
    {
        $data = [
            'page_title' => 'Bank Partners - APS Dream Home',
            'page_description' => 'Our banking partners for home loans',
        ];
        $this->render('pages/bank', $data);
    }

    public function interiorDesign()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('interior-design');
        $data = [
            'page_title' => ($cmsTitle ?: 'Interior Design') . ' - APS Dream Home',
            'page_description' => 'Interior design services',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/interior_design', $data);
    }

    public function constructionServices()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('construction-services');
        $data = [
            'page_title' => ($cmsTitle ?: 'Construction Services') . ' - APS Dream Home',
            'page_description' => 'Construction services',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/construction_services', $data);
    }

    public function resell()
    {
        $data = [
            'page_title' => 'Resell Property - APS Dream Home',
            'page_description' => 'Resell your property with us',
        ];
        $this->render('pages/resell', $data);
    }

    public function documentGallery()
    {
        $data = [
            'page_title' => 'Document Gallery - APS Dream Home',
            'page_description' => 'Legal documents and templates',
        ];
        $this->render('pages/document_gallery', $data);
    }

    public function downloadDocument($id)
    {
        // Handle document download
        header('Content-Type: application/pdf');
        echo 'Document download - ID: ' . $id;
    }

    public function terms()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('terms');
        $data = [
            'page_title' => ($cmsTitle ?: 'Terms & Conditions') . ' - APS Dream Home',
            'page_description' => 'Terms and conditions of use',
            'pageContent' => $pageContent,
        ];
        $this->render('pages/terms', $data);
    }

    public function sitemap()
    {
        header('Content-Type: application/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        // Add sitemap URLs
        echo '</urlset>';
    }







public function properties()
    {
        $page = (int)($_GET['page'] ?? 1);
        $type = $_GET['type'] ?? '';
        $listingType = $_GET['listing'] ?? '';
        $location = $_GET['location'] ?? '';
        $minPrice = (int)($_GET['min_price'] ?? 0);
        $maxPrice = (int)($_GET['max_price'] ?? 0);
        $bedrooms = (int)($_GET['bedrooms'] ?? 0);
        $bathrooms = (int)($_GET['bathrooms'] ?? 0);
        $furnished = $_GET['furnished'] ?? '';
        $yearBuilt = (int)($_GET['year_built'] ?? 0);
        $areaMin = (int)($_GET['area_min'] ?? 0);
        $areaMax = (int)($_GET['area_max'] ?? 0);
        $keyword = trim($_GET['q'] ?? '');
        $sortBy = $_GET['sort'] ?? 'newest';
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        // ╬ô├╢├ç╬ô├╢├ç Hot-path cache: property listings (5 min TTL) ╬ô├╢├ç╬ô├╢├ç
        $filterHash = [
            'q' => $keyword,
            'type' => $type,
            'listing' => $listingType,
            'location' => $location,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'furnished' => $furnished,
            'year_built' => $yearBuilt,
            'area_min' => $areaMin,
            'area_max' => $areaMax,
        ];
        $cached = \App\Services\Cache\HotPathCacheService::getPropertyList(
            $filterHash,
            $page,
            $perPage,
            $sortBy,
            function () use ($filterHash, $keyword, $type, $listingType, $location, $minPrice, $maxPrice, $bedrooms, $bathrooms, $furnished, $yearBuilt, $areaMin, $areaMax, $sortBy, $perPage, $offset) {
                return $this->runPropertyListQuery(
                    $keyword,
                    $type,
                    $listingType,
                    $location,
                    $minPrice,
                    $maxPrice,
                    $bedrooms,
                    $bathrooms,
                    $furnished,
                    $yearBuilt,
                    $areaMin,
                    $areaMax,
                    $sortBy,
                    $perPage,
                    $offset
                );
            }
        );
        $properties = $cached['properties'] ?? [];
        $total = (int) ($cached['total'] ?? 0);

        $totalPages = max(1, (int)ceil($total / $perPage));

        // Fetch saved searches for logged-in users
        $savedSearches = [];
        if (!empty($_SESSION['user_id'])) {
            try {
                $svc = new \App\Services\SavedSearchService($this->db);
                $savedSearches = $svc->getUserSearches((int)$_SESSION['user_id'], 'user_properties');
            } catch (\Throwable $e) {
                error_log("PageController::properties - savedSearches: " . $e->getMessage());
            }
        }

        $data = [
            'properties' => $properties,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'type' => $type,
            'listingType' => $listingType,
            'location' => $location,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'bedrooms' => $bedrooms,
            'bathrooms' => $bathrooms,
            'furnished' => $furnished,
            'yearBuilt' => $yearBuilt,
            'areaMin' => $areaMin,
            'areaMax' => $areaMax,
            'keyword' => $keyword,
            'sortBy' => $sortBy,
            'savedSearches' => $savedSearches,
            'property_types' => ['plot', 'house', 'flat', 'shop', 'farmhouse', 'land'],
            'locations' => ['Gorakhpur', 'Lucknow', 'Kushinagar', 'Varanasi'],
            'price_ranges' => ['Under 5 Lakhs', '5-10 Lakhs', '10-20 Lakhs', '20-50 Lakhs', '50+ Lakhs'],
            'page_title' => 'Properties - APS Dream Home',
            'page_description' => 'Browse properties for sale and rent'
        ];

        $this->render('pages/properties', $data);
    }

public function plotsAvailability()
    {
        $data = [
            'page_title' => 'Plots Availability - APS Dream Home',
            'page_description' => 'Check available plots across our projects'
        ];
        $this->render('pages/plots-availability', $data);
    }

public function plot()
    {
        $data = [
            'page_title' => 'Plot Details - APS Dream Home',
            'page_description' => 'View detailed plot information'
        ];
        $this->render('pages/plot', $data);
    }

public function propertyDetails($id = null)
    {
        $property = null;
        $property_images = [];
        $related_properties = [];
        $reviews = [];
        $source = 'properties';

        if ($id) {
            try {
                // First check user_properties (user-submitted listings)
                $stmt = $this->db->prepare("SELECT *, 'user_properties' as source FROM user_properties WHERE id = ? AND status IN ('approved','verified') LIMIT 1");
                $stmt->execute([$id]);
                $property = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$property) {
                    // Fallback to admin properties table
                    $stmt = $this->db->prepare("SELECT *, 'properties' as source FROM properties WHERE id = ? AND status = 'available' LIMIT 1");
                    $stmt->execute([$id]);
                    $property = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $source = 'properties';
                } else {
                    $source = 'user_properties';
                }

                if ($property) {
                    if ($source === 'user_properties') {
                        // Use image column directly from user_properties
                        if (!empty($property['image'])) {
                            $property_images[] = ['image_path' => $property['image'], 'is_primary' => 1, 'caption' => ''];
                        }
                        // Related from user_properties
                        list($tSql, $tParams) = $this->tenantWhere();
                        $relStmt = $this->db->prepare("SELECT * FROM user_properties WHERE id != ? AND status IN ('approved','verified'){$tSql} ORDER BY RAND() LIMIT 3");
                        $relStmt->execute(array_merge([$id], $tParams));
                        $related_properties = $relStmt->fetchAll(\PDO::FETCH_ASSOC);
                    } else {
                        // Admin properties - use property_images table
                        $imgStmt = $this->db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC LIMIT 5");
                        $imgStmt->execute([$id]);
                        $property_images = $imgStmt->fetchAll(\PDO::FETCH_ASSOC);

                        list($tSql, $tParams) = $this->tenantWhere();
                        $relStmt = $this->db->prepare("SELECT * FROM properties WHERE id != ? AND status = 'available'{$tSql} ORDER BY RAND() LIMIT 3");
                        $relStmt->execute(array_merge([$id], $tParams));
                        $related_properties = $relStmt->fetchAll(\PDO::FETCH_ASSOC);

                        // Reviews for admin properties
                        $revStmt = $this->db->prepare("SELECT r.*, COALESCE(u.name, 'Anonymous') as user_name FROM property_reviews r LEFT JOIN users u ON r.customer_id = u.id WHERE r.property_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
                        $revStmt->execute([$id]);
                        $reviews = $revStmt->fetchAll(\PDO::FETCH_ASSOC);
                    }
                }
            } catch (\Exception $e) {
                error_log("Property fetch error: " . $e->getMessage());
            }
        }

        $data = [
            'page_title' => $property ? ($property['title'] ?? $property['name'] ?? 'Property') . ' - APS Dream Home' : 'Property Not Found',
            'page_description' => 'View property details',
            'property' => $property,
            'property_images' => $property_images,
            'related_properties' => $related_properties,
            'reviews' => $reviews,
            'property_source' => $source
        ];
        $this->render('properties/detail', $data);
    }

public function plotSizeConverter()
    {
        $data = [
            'page_title' => 'Plot Size Converter - APS Dream Home',
            'page_description' => 'Convert between square feet, square meters, acres, bigha, gaj and more'
        ];
        $this->render('pages/tools/plot_converter', $data);
    }

public function plotConverter()
    {
        $data = [
            'page_title' => 'Plot Area Converter - APS Dream Home',
            'page_description' => 'Convert between sqft, sqm, acre, bigha, gaj, katha, marla and more'
        ];
        $this->render('pages/tools/plot_converter', $data);
    }

public function getFeaturedProperties()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                ['id' => 1, 'title' => 'Suyoday Colony', 'location' => 'Gorakhpur', 'price' => '750000'],
                ['id' => 2, 'title' => 'Raghunat Nagri', 'location' => 'Gorakhpur', 'price' => '850000'],
            ]
        ]);
        exit;
    }

public function buyProperty()
    {
        $this->render('pages/buy');
    }

public function sellProperty()
    {
        $this->render('pages/sell');
    }

public function rentProperty()
    {
        $this->render('pages/rent');
    }

public function investProperty()
    {
        $this->render('pages/invest');
    }

public function listProperty()
    {
        $this->render('pages/list_property');
    }

public function handlePropertyListing()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $propertyType = trim($_POST['property_type'] ?? '');
            $listingType = trim($_POST['listing_type'] ?? 'sell');
            $price = (float)str_replace([',', ' '], '', $_POST['price'] ?? 0);
            $location = trim($_POST['location'] ?? '');
            $area = (int)str_replace([',', ' '], '', $_POST['area'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $stateId = (int)($_POST['selected_state_id'] ?? 0);
            $districtId = (int)($_POST['selected_district_id'] ?? 0);
            $cityName = trim($_POST['selected_city_name'] ?? $_POST['city'] ?? '');

            if (empty($name) || empty($phone) || empty($propertyType)) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                $this->redirect('/list-property');
                return;
            }

            if (!$this->tenantEnforce('create_property')) {
                $_SESSION['error'] = $_SESSION['error'] ?? 'Property limit reached for your plan.';
                $this->redirect('/list-property');
                return;
            }

            try {
                // Handle image upload
                $imagePath = null;
                if (!empty($_FILES['property_image']['name']) && $_FILES['property_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../../../assets/images/properties/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $v = \UploadValidator::validate($_FILES['property_image'], ['types' => 'images', 'max_size' => 5]);
                    if ($v['valid']) {
                        $safeName = \UploadValidator::safeFilename($_FILES['property_image']['name']);
                        $newName = 'prop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($safeName, PATHINFO_EXTENSION);
                        $targetPath = $uploadDir . $newName;
                        if (move_uploaded_file($_FILES['property_image']['tmp_name'], $targetPath)) {
                            \App\Core\ImageOptimizer::optimizeStatic($targetPath);
                            $imagePath = 'properties/' . $newName;
                            // Mirror to StorageManager (S3 or local fallback).
                            try {
                                \App\Services\Storage\StorageManager::getInstance()->put(
                                    'assets/images/' . $imagePath,
                                    file_get_contents($targetPath),
                                    [
                                        'ContentType'   => mime_content_type($targetPath) ?: 'image/jpeg',
                                        'Cache-Control' => 'public, max-age=31536000, immutable',
                                    ]
                                );
                            } catch (\Throwable $e) {
                                error_log('PageController::listPropertySubmit storage mirror: ' . $e->getMessage());
                            }
                        }
                    }
                }

                // Track WHO posted this property (associate/customer/agent)
                $postedBy = null;
                $postedByType = null;
                $userId = null;

                @session_start();

                if (isset($_SESSION['associate_id'])) {
                    $postedBy = $_SESSION['associate_id'];
                    $postedByType = 'associate';
                    $userId = $_SESSION['associate_id'];
                } elseif (isset($_SESSION['user_id'])) {
                    $postedBy = $_SESSION['user_id'];
                    $postedByType = 'customer';
                    $userId = $_SESSION['user_id'];
                } elseif (isset($_SESSION['agent_id'])) {
                    $postedBy = $_SESSION['agent_id'];
                    $postedByType = 'agent';
                    $userId = $_SESSION['agent_id'];
                }

                // Try to save to user_properties table
                $savedToUserProperties = false;
                try {
                    $this->db->query("SELECT 1 FROM user_properties LIMIT 1");

                    $insCols = "user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, state_id, district_id, city_name, status, created_at";
                    $insVals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()";
                    $insParams = [$userId, $postedBy, $postedByType, $name, $phone, $email, $propertyType, $listingType, $location, $area, $price, $listingType === 'rent' ? 'month' : 'lakh', $description, $imagePath, $stateId ?: null, $districtId ?: null, $cityName ?: null];
                    $insExtra = $this->tenantInsertData();
                    if (!empty($insExtra)) { $insCols .= ", tenant_id"; $insVals .= ", ?"; $insParams[] = $insExtra['tenant_id']; }
                    $stmt = $this->db->prepare("INSERT INTO user_properties ($insCols) VALUES ($insVals)");
                    $stmt->execute($insParams);
                    $propertyId = $this->db->lastInsertId();
                    $savedToUserProperties = true;
                } catch (\Exception $e1) {
                    // Table might not exist, create it
                    if (strpos($e1->getMessage(), "doesn't exist") !== false) {
                        $this->createUserPropertiesTable();
                        $insCols2 = "user_id, posted_by, posted_by_type, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, image, state_id, district_id, city_name, status, created_at";
                        $insVals2 = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW()";
                        $insParams2 = [$userId, $postedBy, $postedByType, $name, $phone, $email, $propertyType, $listingType, $location, $area, $price, $listingType === 'rent' ? 'month' : 'lakh', $description, $imagePath, $stateId ?: null, $districtId ?: null, $cityName ?: null];
                        $insExtra2 = $this->tenantInsertData();
                        if (!empty($insExtra2)) { $insCols2 .= ", tenant_id"; $insVals2 .= ", ?"; $insParams2[] = $insExtra2['tenant_id']; }
                        $stmt = $this->db->prepare("INSERT INTO user_properties ($insCols2) VALUES ($insVals2)");
                        $stmt->execute($insParams2);
                        $propertyId = $this->db->lastInsertId();
                        $savedToUserProperties = true;
                        error_log("PageController.php: " . $e1->getMessage());
                    }
                }

                if ($savedToUserProperties) {
                    $this->tenantTrackUsage('properties');
                }

                // Also save to inquiries for CRM tracking
                $message = "Property Type: " . ucfirst($propertyType) . "\n";
                $message .= "Listing Type: " . ucfirst($listingType) . "\n";
                $message .= "Price: " . $price . "\n";
                $message .= "Area: " . $area . " sq ft\n";
                $message .= "Location: " . $location . "\n";
                $message .= "Description: " . $description;

                try {
                    $inqStmt = $this->db->prepare("
                        INSERT INTO inquiries (name, email, phone, message, type, status, priority, user_id, created_at)
                        VALUES (?, ?, ?, ?, 'property', 'pending', 'medium', ?, NOW())
                    ");
                    $inqStmt->execute([$name, $email, $phone, $message, $postedBy]);
                } catch (\Exception $e2) {
                    error_log("Inquiry save error: " . $e2->getMessage());
                }

                // Auto-wire to CRM lead
                try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$message,'type'=>'property','created_by'=>$postedBy]); } catch (\Exception $e3) {}

                // Success message with user-specific redirect
                $_SESSION['success'] = 'Thank you! Your property listing request has been submitted. Our team will contact you within 24 hours to verify the details.';

                // Redirect based on user type
                if ($postedByType === 'associate') {
                    $this->redirect('/associate/properties');
                    return;
                } elseif ($postedByType === 'customer') {
                    $this->redirect('/user/properties');
                    return;
                }
            } catch (\Exception $e) {
                error_log("Property listing error: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to submit. Please try again or call us directly.';
            }
        }
        $this->redirect('/list-property');
    }

public function userPropertyDetail($id = null)
    {
        if (!$id) {
            $this->redirect('/properties');
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT up.*, s.name as state_name, d.name as district_name FROM user_properties up LEFT JOIN states s ON up.state_id = s.id LEFT JOIN districts d ON up.district_id = d.id WHERE up.id = ? AND up.status = 'approved' LIMIT 1");
            $stmt->execute([$id]);
            $property = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $property = null;
        }

        if (!$property) {
            $this->notFound();
            return;
        }

        // Increment view count
        try {
            $this->db->query("UPDATE user_properties SET views = views + 1 WHERE id = ?", [$id]);
        } catch (\Exception $e) {
            error_log("PageController.php: " . $e->getMessage());
        }

        // Track property view for lead generation
        try {
            $this->trackPropertyView($id);
        } catch (\Exception $e) {
            error_log("PageController: trackPropertyView error: " . $e->getMessage());
        }

        $data = [
            'page_title' => ($property['name'] ?? 'Property') . ' - APS Dream Home',
            'page_description' => 'View property details',
            'property' => $property
        ];
        $this->render('properties/user_detail', $data);
    }

public function propertyInterest()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $budget = trim($_POST['budget'] ?? '');
        $source = trim($_POST['source'] ?? 'property_interest');

        if (!$propertyId || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Phone number is required']);
            return;
        }

        // Validate phone (basic Indian format)
        $phoneClean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phoneClean) < 10) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number']);
            return;
        }

        // Get property name
        $propName = '';
        try {
            $prop = $this->db->fetch("SELECT name FROM user_properties WHERE id = ?", [$propertyId]);
            $propName = $prop['name'] ?? '';
        } catch (\Exception $e) { /* skip */ }

        // Check if lead already exists for this phone
        $existingLead = null;
        try {
            list($tSql, $tParams) = $this->tenantWhere();
            $existingLead = $this->db->fetch("SELECT id FROM leads WHERE phone = ? AND deleted_at IS NULL{$tSql} ORDER BY id DESC LIMIT 1", array_merge([$phoneClean], $tParams));
        } catch (\Exception $e) { /* skip */ }

        $userId = $_SESSION['user_id'] ?? 0;

        try {
            if ($existingLead) {
                // Update existing lead
                $this->db->query(
                    "UPDATE leads SET property_interest = CONCAT(COALESCE(property_interest, ''), ?), notes = CONCAT(COALESCE(notes, ''), ?), lead_score = LEAST(100, lead_score + 10), updated_at = NOW() WHERE id = ?",
                    [
                        ($propName ? ", $propName" : ''),
                        "\n" . date('Y-m-d H:i') . " ╬ô├ç├╢ Expressed interest in: $propName (Budget: $budget)",
                        $existingLead['id']
                    ]
                );
                $leadId = $existingLead['id'];
            } else {
                // Create new lead
                $insColsLead = "name, phone, email, source, status, priority, property_interest, budget_range, notes, created_by, lead_score, created_at";
                $insValsLead = "?, ?, '', ?, 'new', 'high', ?, ?, ?, 0, 50, NOW()";
                $insParamsLead = [$name ?: 'Unknown', $phoneClean, $source, $propName, $budget, "Expressed interest in: $propName" . ($budget ? " (Budget: $budget)" : '')];
                $insExtraLead = $this->tenantInsertData();
                if (!empty($insExtraLead)) { $insColsLead .= ", tenant_id"; $insValsLead .= ", ?"; $insParamsLead[] = $insExtraLead['tenant_id']; }
                $this->db->query("INSERT INTO leads ($insColsLead) VALUES ($insValsLead)", $insParamsLead);
                $leadId = $this->db->lastInsertId();
            }

            // Also save to inquiries table for backward compatibility
            try {
                $this->db->query(
                    "INSERT INTO inquiries (property_id, name, email, phone, message, type, property_type, status, priority, lead_id, created_at) VALUES (?, ?, '', ?, ?, 'property_inquiry', 'user_property', 'new', 'high', ?, NOW())",
                    [
                        $propertyId,
                        $name ?: 'Unknown',
                        $phoneClean,
                        "Interested in: $propName" . ($budget ? " | Budget: $budget" : ''),
                        $leadId
                    ]
                );
            } catch (\Exception $e) { /* skip */ }

            // Notify property owner
            try {
                $prop = $this->db->fetch("SELECT * FROM user_properties WHERE id = ?", [$propertyId]);
                if ($prop && !empty($prop['email'])) {
                    $ownerMsg = "New interest in your property '{$prop['name']}'!\n\nFrom: " . ($name ?: 'Unknown') . " ($phoneClean)\nBudget: $budget\n\nAPS Dream Home";
                    @mail($prop['email'], "Interest in your property: {$prop['name']}", $ownerMsg, "From: info@apsdreamhome.com\r\nReply-To: $phoneClean@aps.local");
                }
            } catch (\Exception $e) { /* skip */ }

            echo json_encode(['success' => true, 'message' => 'Interest recorded! Our team will contact you shortly.', 'lead_id' => $leadId]);
        } catch (\Exception $e) {
            error_log("Property interest error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
        }
    }

public function propertyInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/properties');
            return;
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$propertyId || empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            $this->redirect('/listing/' . $propertyId);
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO inquiries (property_id, name, email, phone, message, type, property_type, status, priority, created_at) VALUES (?, ?, ?, ?, ?, 'property_inquiry', 'user_property', 'new', 'high', NOW())");
            $stmt->execute([$propertyId, $name, $email, $phone, $message]);

            // Auto-wire to CRM lead
            try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$message,'type'=>'property_inquiry','property_id'=>$propertyId]); } catch (\Exception $e3) {}

            // Also notify property owner
            try {
                $prop = $this->db->fetch("SELECT * FROM user_properties WHERE id = ?", [$propertyId]);
                if ($prop && !empty($prop['email'])) {
                    $ownerMsg = "New inquiry for your property '{$prop['name']}'!\n\nFrom: $name ($phone, $email)\nMessage: $message\n\nAPS Dream Home";
                    @mail($prop['email'], "Inquiry for your property: {$prop['name']}", $ownerMsg, "From: info@apsdreamhome.com\r\nReply-To: $email");
                }
            } catch (\Exception $e) {
                error_log("Property owner notification error: " . $e->getMessage());
            }

            $_SESSION['success'] = 'Your inquiry has been sent. The property owner will contact you soon!';
        } catch (\Exception $e) {
            error_log("Property inquiry error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to send inquiry. Please call us at +91 92771 21112.';
        }

        $this->redirect('/listing/' . $propertyId);
    }

public function plotMap()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($requestUri, '/admin/') !== false || strpos($requestUri, 'admin') !== false) {
            $this->requireAdmin();
        }

        $colonies = [];
        $allPlots = [];
        $colonyStats = [];
        $totalStats = ['available' => 0, 'booked' => 0, 'sold' => 0, 'blocked' => 0, 'total' => 0];

        try {
            $colStmt = $this->db->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY id");
            $colonies = $colStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('PageController::plotMap colonies: ' . $e->getMessage());
        }

        foreach ($colonies as &$colony) {
            $colony['plots'] = [];
            $colony['stats'] = ['available' => 0, 'booked' => 0, 'sold' => 0, 'blocked' => 0, 'total' => 0];
            try {
                list($tSql, $tParams) = $this->tenantWhere();
                $pStmt = $this->db->prepare(
                    "SELECT p.id, p.colony_id, p.plot_number, p.block, p.area_sqft, p.width_ft, p.length_ft,
                            p.total_price, p.status, p.facing, p.corner_plot, p.park_facing,
                            c.name AS colony_name,
                            (SELECT psh.changed_at FROM plot_status_history psh WHERE psh.plot_id = p.id ORDER BY psh.changed_at DESC LIMIT 1) AS last_status_change
                     FROM plots p
                     JOIN colonies c ON c.id = p.colony_id
                     WHERE p.colony_id = ? AND p.is_active = 1{$tSql}
                     ORDER BY p.block, p.plot_number"
                );
                $pStmt->execute(array_merge([$colony['id']], $tParams));
                $plots = $pStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                foreach ($plots as &$plot) {
                    $status = $plot['status'];
                    if ($status === 'available') {
                        $plot['display_status'] = 'Available';
                        $plot['color'] = '#10b981';
                        $colony['stats']['available']++;
                        $totalStats['available']++;
                    } elseif ($status === 'booked' || $status === 'reserved') {
                        $plot['display_status'] = ucfirst($status);
                        $plot['color'] = '#f59e0b';
                        $colony['stats']['booked']++;
                        $totalStats['booked']++;
                    } elseif ($status === 'sold') {
                        $plot['display_status'] = 'Sold';
                        $plot['color'] = '#ef4444';
                        $colony['stats']['sold']++;
                        $totalStats['sold']++;
                    } else {
                        $plot['display_status'] = ucfirst($status);
                        $plot['color'] = '#6b7280';
                        $colony['stats']['blocked']++;
                        $totalStats['blocked']++;
                    }
                    $plot['width_ft'] = $plot['width_ft'] ?? 30;
                    $plot['length_ft'] = $plot['length_ft'] ?? 40;
                    $colony['stats']['total']++;
                    $totalStats['total']++;
                }
                $colony['plots'] = $plots;
            } catch (\Exception $e) {
                error_log('PageController::plotMap plots colony ' . $colony['id'] . ': ' . $e->getMessage());
            }
            $colonyStats[$colony['id']] = $colony['stats'];
        }
        unset($plot, $colony);

        $this->render('pages/plot_layout', [
            'page_title' => 'Plot Layout Map',
            'colonies' => $colonies,
            'colony_stats' => $colonyStats,
            'total_stats' => $totalStats,
        ]);
    }

public function contact()
    {
        $success = false;
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? 'Contact Form Submission');
            $message = trim($_POST['message'] ?? '');

            if (empty($name) || empty($email) || empty($message)) {
                $error = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                try {
                    $stmt = $this->db->prepare("INSERT INTO contacts (name, email, phone, subject, message, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $stmt->execute([$name, $email, $phone, $subject, $message, $ip]);
                    $success = true;
                    $_POST = [];

                    // Also save to inquiries table for CRM
                    try {
                        $inqStmt = $this->db->prepare("INSERT INTO inquiries (name, email, phone, message, type, status, priority, created_at) VALUES (?, ?, ?, ?, ?, 'new', 'medium', NOW())");
                        $inqStmt->execute([$name, $email, $phone, $subject . ': ' . $message, 'contact']);
                    } catch (\Exception $e2) {
                        error_log("Inquiry save error: " . $e2->getMessage());
                    }

                    // Auto-wire to CRM lead
                    try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$subject.': '.$message,'type'=>'contact']); } catch (\Exception $e3) {}

                    // Trigger WhatsApp notification for new inquiry
                    try {
                        $waService = new \App\Services\Communication\WhatsAppService();
                        $waService->sendTemplateMessage($phone, 'inquiry_received', [
                            'customer_name' => $name ?: 'Valued Customer',
                            'property_type' => 'property',
                        ]);
                    } catch (\Exception $e) {
                        // WhatsApp notification is best-effort
                        error_log("PageController.php: " . $e->getMessage());
                    }
                } catch (\Exception $e) {
                    $error = 'Failed to submit. Please try again or call us directly.';
                    error_log("Contact form error: " . $e->getMessage());
                }
            }

            if (
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false)
            ) {
                header('Content-Type: application/json');
                if ($success) {
                    echo json_encode(['success' => true, 'message' => __('contact_success', null, 'Thank you! Your message has been sent successfully.')]);
                } else {
                    echo json_encode(['success' => false, 'message' => $error ?: 'Validation failed. Please check your inputs.']);
                }
                exit;
            }
        }

        [$cmsTitle, $pageContent] = $this->loadPageContent('contact');
        $data = [
            'page_title' => ($cmsTitle ?: 'Contact Us') . ' - APS Dream Home',
            'page_description' => 'Get in touch with APS Dream Home',
            'contact_success' => $success,
            'contact_error' => $error,
            'pageContent' => $pageContent
        ];
        $this->render('pages/contact', $data);
    }

public function serviceInterest()
    {
        header('Content-Type: application/json');

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $serviceType = trim($_POST['service_type'] ?? '');
        $propertyId = (int)($_POST['property_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($phone) || empty($serviceType)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            return;
        }

        try {
            // Check if service_interests table exists
            $this->db->query("SELECT 1 FROM service_interests LIMIT 1");

            $stmt = $this->db->prepare("
                INSERT INTO service_interests (service_type, customer_name, customer_phone, customer_email, status, notes, created_at) 
                VALUES (?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([$serviceType, $name, $phone, $email, $message]);
            $serviceId = $this->db->lastInsertId();

            // Create lead
            $leadInsCols = "name, email, phone, source, status, created_at";
            $leadInsVals = "?, ?, ?, 'website', 'new', NOW()";
            $leadInsParams = [$name, $email, $phone];
            $leadInsExtra = $this->tenantInsertData();
            if (!empty($leadInsExtra)) { $leadInsCols .= ", tenant_id"; $leadInsVals .= ", ?"; $leadInsParams[] = $leadInsExtra['tenant_id']; }
            $leadStmt = $this->db->prepare("INSERT INTO leads ($leadInsCols) VALUES ($leadInsVals)");
            $leadStmt->execute($leadInsParams);
            $leadId = $this->db->lastInsertId();

            // Link lead to service
            $this->db->prepare("UPDATE service_interests SET lead_id = ? WHERE id = ?")
                ->execute([$leadId, $serviceId]);

            echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you shortly.']);
        } catch (\Exception $e) {
            // Table might not exist, create it
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->createServiceInterestsTable();
                // Retry
                $stmt = $this->db->prepare("
                    INSERT INTO service_interests (service_type, property_id, status, notes, created_at) 
                    VALUES (?, ?, 'new', ?, NOW())
                ");
                $stmt->execute([$serviceType, $propertyId, $message]);
                $serviceId = $this->db->lastInsertId();

                $leadInsCols2 = "name, email, phone, source, status, created_at";
                $leadInsVals2 = "?, ?, ?, 'website', 'new', NOW()";
                $leadInsParams2 = [$name, $email, $phone];
                $leadInsExtra2 = $this->tenantInsertData();
                if (!empty($leadInsExtra2)) { $leadInsCols2 .= ", tenant_id"; $leadInsVals2 .= ", ?"; $leadInsParams2[] = $leadInsExtra2['tenant_id']; }
                $leadStmt = $this->db->prepare("INSERT INTO leads ($leadInsCols2) VALUES ($leadInsVals2)");
                $leadStmt->execute($leadInsParams2);
                $leadId = $this->db->lastInsertId();

                $this->db->prepare("UPDATE service_interests SET lead_id = ? WHERE id = ?")
                    ->execute([$leadId, $serviceId]);

                echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you shortly.']);
                error_log("PageController.php: " . $e->getMessage());
            } else {
                error_log("Service interest error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
            }
        }
    }

public function blog()
    {
        $blog_posts = [
            [
                'id' => 1,
                'title' => 'The Future of Real Estate: Trends to Watch in 2025',
                'excerpt' => 'As we look ahead, the real estate market is poised for significant transformation. From sustainable housing to the integration of AI, here are the key trends that will shape the industry in 2025 and beyond.',
                'featured_image' => 'assets/images/blog/blog-1.jpg',
                'category' => 'Market Trends',
                'read_time' => 5,
                'created_at' => '2024-03-15'
            ],
            [
                'id' => 2,
                'title' => 'A Step-by-Step Guide to Buying Your First Home',
                'excerpt' => 'Buying your first home is a major milestone. This comprehensive guide will walk you through every step of the process, from getting pre-approved for a mortgage to closing the deal.',
                'featured_image' => 'assets/images/blog/blog-2.jpg',
                'category' => 'Buying Guide',
                'read_time' => 8,
                'created_at' => '2024-03-10'
            ],
            [
                'id' => 3,
                'title' => 'Top 5 Interior Design Tips to Increase Your Home\'s Value',
                'excerpt' => 'A well-designed home not only looks great but can also significantly increase its market value. Discover our top 5 interior design tips to make your home more appealing to potential buyers.',
                'featured_image' => 'assets/images/blog/blog-3.jpg',
                'category' => 'Interior Design',
                'read_time' => 4,
                'created_at' => '2024-03-05'
            ],
        ];

        $categories = [
            ['category' => 'Market Trends'],
            ['category' => 'Buying Guide'],
            ['category' => 'Interior Design'],
            ['category' => 'Investment'],
        ];

        $data = [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Latest news and articles from our blog',
            'blog_posts' => $blog_posts,
            'categories' => $categories
        ];
        $this->render('pages/blog', $data);
    }

public function privacy()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('privacy');
        $data = [
            'page_title' => ($cmsTitle ?: 'Privacy Policy') . ' - APS Dream Home',
            'page_description' => 'Our privacy policy',
            'pageContent' => $pageContent
        ];
        $this->render('pages/privacy', $data);
    }

public function news()
    {
        try {
            $news_items = $this->db->fetchAll(
                "SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC"
            );
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $news_items = [];
        }

        $categories = ['Project Launch', 'Company News', 'Market Updates'];

        $data = [
            'page_title' => 'News - APS Dream Home',
            'page_description' => 'Latest news and updates from APS Dream Home',
            'news_items' => $news_items,
            'categories' => $categories,
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => '/'],
                ['title' => 'News', 'url' => '']
            ]
        ];
        $this->render('pages/news', $data);
    }

public function newsView($id = null)
    {
        $data = [
            'page_title' => 'News - APS Dream Home',
            'page_description' => 'View news article',
            'news_id' => $id
        ];
        $this->render('pages/news', $data);
    }

public function mlmDashboard()
    {
        $data = [
            'page_title' => 'MLM Dashboard - APS Dream Home',
            'page_description' => 'Manage your MLM network and earnings'
        ];
        $this->render('pages/mlm-dashboard', $data);
    }

public function featuredProperties()
    {
        $data = [
            'page_title' => 'Featured Properties - APS Dream Home',
            'page_description' => 'Handpicked premium properties by APS Dream Home'
        ];
        $this->render('pages/featured_properties', $data);
    }

public function customerReviews()
    {
        $data = [
            'page_title' => 'Customer Reviews - APS Dream Home',
            'page_description' => 'Read reviews from our satisfied users'
        ];
        $this->render('pages/customer_reviews', $data);
    }

public function createMobileApp()
    {
        $data = [
            'page_title' => 'Mobile App - APS Dream Home',
            'page_description' => 'Download APS Dream Home mobile application'
        ];
        $this->render('pages/mobile_app', $data);
    }

public function constructionInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/construction-services');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $project_type = trim($_POST['project_type'] ?? '');
        $budget = floatval($_POST['budget'] ?? 0);
        $location = trim($_POST['location'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Name and phone are required';
            header('Location: ' . BASE_URL . '/construction-services#contact-form');
            exit;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO inquiries (name, email, phone, message, type, status, priority, created_at) VALUES (?, ?, ?, ?, 'project', 'pending', 'medium', NOW())");
            $stmt->execute([$name, $email, $phone, "Construction Inquiry - {$project_type}" . ($budget > 0 ? " | Budget: ╬ô├⌐Γòú{$budget}" : '') . ($location ? " | Location: {$location}" : '') . ($message ? " | Details: {$message}" : '')]);

            // Auto-wire to CRM lead
            try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>"Construction: {$project_type}",'type'=>'project']); } catch (\Exception $e3) {}

            // Also save to service_interests if table exists
            try {
                $sStmt = $this->db->prepare("INSERT INTO service_interests (lead_id, service_type, status, notes, created_at) VALUES (?, 'construction', 'pending', ?, NOW())");
                $sStmt->execute([$this->db->lastInsertId(), "Budget: ╬ô├⌐Γòú{$budget}, Location: {$location}, Type: {$project_type}"]);
            } catch (\Exception $e) {
                error_log('PageController constructionInquiry service interests: ' . $e->getMessage());
            }

            $_SESSION['success'] = 'Thank you! We will contact you shortly regarding your construction project.';
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $_SESSION['error'] = 'Something went wrong. Please try again.';
        }

        header('Location: ' . BASE_URL . '/construction-services#contact-form');
        exit;
    }

public function emailSystem()
    {
        $data = [
            'page_title' => 'Email System - APS Dream Home',
            'page_description' => 'Send emails to APS Dream Home team'
        ];
        $this->render('pages/email_system', $data);
    }

public function legalTermsConditions()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('terms-conditions');
        $data = [
            'page_title' => ($cmsTitle ?: 'Terms & Conditions') . ' - APS Dream Home',
            'page_description' => 'Detailed terms and conditions of APS Dream Home',
            'pageContent' => $pageContent
        ];
        $this->render('pages/legal/terms_conditions', $data);
    }

public function legalDocuments()
    {
        $data = [
            'page_title' => 'Legal Documents - APS Dream Home',
            'page_description' => 'Access legal documents and agreements'
        ];
        $this->render('pages/legal/documents', $data);
    }

public function systemLogSecurityEvent()
    {
        $data = [
            'page_title' => 'Security Log - APS Dream Home',
            'page_description' => 'System security event logging'
        ];
        $this->render('pages/system/log_security_event', $data);
    }

public function systemLaunchSystem()
    {
        $data = [
            'page_title' => 'Launch System - APS Dream Home',
            'page_description' => 'System launch and deployment interface'
        ];
        $this->render('pages/system/launch_system', $data);
    }

public function systemKycUpload()
    {
        $this->requireLogin();
        $data = [
            'page_title' => 'KYC Upload - APS Dream Home',
            'page_description' => 'Know Your Customer verification system'
        ];
        $this->render('pages/system/kyc-upload', $data);
    }

public function whatsappTemplates()
    {
        $data = [
            'page_title' => 'WhatsApp Templates - APS Dream Home',
            'page_description' => 'WhatsApp message templates for marketing'
        ];
        $this->render('pages/whatsapp-templates', $data);
    }

public function faq()
    {
        return $this->faqs();
    }

public function reviewSubmit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /properties');
            exit;
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['review_text'] ?? '');

        if (!$propertyId || !$name || !$email || !$rating || !$reviewText) {
            $_SESSION['error'] = 'All fields are required.';
            header('Location: /properties/' . $propertyId);
            exit;
        }

        if ($rating < 1 || $rating > 5) {
            $_SESSION['error'] = 'Rating must be between 1 and 5.';
            header('Location: /properties/' . $propertyId);
            exit;
        }

        try {
            // Find or create a user record for this reviewer
            $customerId = null;
            $userStmt = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $userStmt->execute([$email]);
            $existingUser = $userStmt->fetch(\PDO::FETCH_ASSOC);
            if ($existingUser) {
                $customerId = $existingUser['id'];
            } else {
                $createStmt = $this->db->prepare("INSERT INTO users (name, email, role, created_at) VALUES (?, ?, 'customer', NOW())");
                $createStmt->execute([$name, $email]);
                $customerId = $this->db->lastInsertId();
            }

            $stmt = $this->db->prepare("INSERT INTO property_reviews (customer_id, property_id, rating, review_text, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$customerId, $propertyId, $rating, $reviewText]);

            $_SESSION['success'] = 'Thank you! Your review has been submitted and is pending approval.';
        } catch (\Exception $e) {
            error_log("Review submit error: " . $e->getMessage());
            $_SESSION['error'] = 'Something went wrong. Please try again.';
        }

        header('Location: /properties/' . $propertyId);
        exit;
    }

public function projects()
    {
        try {
            // Get sites by state for grouping
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE status IN ('active', 'under_development') ORDER BY state, city, site_name");
            $stmt->execute();
            $projects = $stmt->fetchAll(\PDO::FETCH_OBJ);

            // Group by state
            $grouped = [];
            // Group by state > district
            $grouped = [];
            foreach ($projects as $project) {
                $state = $project->state ?? 'Other';
                $district = $project->district ?? 'Other';
                if (!isset($grouped[$state])) {
                    $grouped[$state] = [];
                }
                if (!isset($grouped[$state][$district])) {
                    $grouped[$state][$district] = [];
                }
                $grouped[$state][$district][] = $project;
            }
        } catch (\Exception $e) {
            $projects = [];
            $grouped = [];
            error_log("Projects error: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'Our Projects - APS Dream Home',
            'page_description' => 'Explore our residential and commercial projects',
            'projects' => $projects,
            'grouped_projects' => $grouped
        ];
        $this->render('pages/company_projects', $data);
    }

public function projectDetails($slug = null)
    {
        $project = null;
        $plots = [];

        if ($slug) {
            try {
                // Convert slug to site name format
                $searchName = str_replace('-', ' ', $slug);
                $searchName = preg_replace('/\s+/', ' ', trim($searchName));

                // Try exact match on site_name
                $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name = ? LIMIT 1");
                $stmt->execute([ucwords($searchName)]);
                $project = $stmt->fetch(\PDO::FETCH_OBJ);

                // Try case-insensitive match
                if (!$project) {
                    $stmt = $this->db->prepare("SELECT * FROM sites WHERE LOWER(site_name) = LOWER(?) LIMIT 1");
                    $stmt->execute([$searchName]);
                    $project = $stmt->fetch(\PDO::FETCH_OBJ);
                }

                // Try LIKE match
                if (!$project) {
                    $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE ? LIMIT 1");
                    $stmt->execute(['%' . $searchName . '%']);
                    $project = $stmt->fetch(\PDO::FETCH_OBJ);
                }

                // Get any active project as final fallback
                if (!$project) {
                    $stmt = $this->db->query("SELECT * FROM sites WHERE status = 'active' LIMIT 1");
                    $project = $stmt->fetch(\PDO::FETCH_OBJ);
                }

                // Get plots for this site
                if ($project) {
                    try {
                        $plotStmt = $this->db->prepare("SELECT * FROM plots WHERE site_id = ? AND status IN ('available', 'open') LIMIT 20");
                        $plotStmt->execute([$project->id]);
                        $plots = $plotStmt->fetchAll(\PDO::FETCH_OBJ);
                    } catch (\Exception $e) {
                        error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

                        $plots = [];
                    }

                    // Get related projects (same district, excluding current)
                    try {
                        $relatedStmt = $this->db->prepare("SELECT * FROM sites WHERE district = ? AND id != ? AND status IN ('active', 'completed') ORDER BY site_name LIMIT 4");
                        $relatedStmt->execute([$project->district, $project->id]);
                        $related_projects = $relatedStmt->fetchAll(\PDO::FETCH_OBJ);
                    } catch (\Exception $e) {
                        error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

                        $related_projects = [];
                    }
                }
            } catch (\Exception $e) {
                error_log("Project details error: " . $e->getMessage());
            }
        }

        $data = [
            'page_title' => $project ? ($project->site_name ?? 'Project') . ' - APS Dream Home' : 'Project Not Found',
            'page_description' => $project ? 'View details of ' . ($project->site_name ?? 'our project') : 'Project details',
            'project' => $project,
            'plots' => $plots ?? [],
            'related_projects' => $related_projects ?? []
        ];
        $this->render('pages/project_detail', $data);
    }

public function suyodayColony()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Suryoday%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $project = null;
        }

        $data = [
            'page_title' => 'Suyoday Colony - APS Dream Home',
            'page_description' => 'Premium residential plots in Suyoday Colony, Gorakhpur',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

public function raghunatNagri()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Raghunath%' OR site_name LIKE '%Raghunat%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $project = null;
        }

        $data = [
            'page_title' => 'Raghunat Nagri - APS Dream Home',
            'page_description' => 'Premium residential plots in Raghunat Nagri, Gorakhpur',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

public function brajRadhaNagri()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Braj Radha%' OR site_name LIKE '%Braj%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $project = null;
        }

        $data = [
            'page_title' => 'Braj Radha Nagri - APS Dream Home',
            'page_description' => 'Affordable residential plots in Braj Radha Nagri',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

public function budhBiharColony()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Budh Bihar%' OR site_name LIKE '%Budh%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $project = null;
        }

        $data = [
            'page_title' => 'Budh Bihar Colony - APS Dream Home',
            'page_description' => 'Integrated township at Budh Bihar Colony, Kushinagar',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

public function awadhpuri()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sites WHERE site_name LIKE '%Awadhpuri%' LIMIT 1");
            $stmt->execute();
            $project = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $project = null;
        }

        $data = [
            'page_title' => 'Awadhpuri - APS Dream Home',
            'page_description' => 'Premium project at Awadhpuri, Lucknow',
            'project' => $project
        ];
        $this->render('pages/project_detail', $data);
    }

public function whatsappChat()
    {
        $data = [
            'page_title' => 'WhatsApp Chat - APS Dream Home',
            'page_description' => 'Connect with us on WhatsApp'
        ];
        $this->render('pages/whatsapp_chat', $data);
    }

public function virtualTour()
    {
        $data = [
            'page_title' => 'Virtual Tour - APS Dream Home',
            'page_description' => 'Take a virtual tour of our properties'
        ];
        $this->render('pages/virtual_tour', $data);
    }

public function userAiSuggestions()
    {
        $data = [
            'page_title' => 'AI Suggestions - APS Dream Home',
            'page_description' => 'Personalized property suggestions powered by AI'
        ];
        $this->render('pages/user_ai_suggestions', $data);
    }

public function support()
    {
        $data = [
            'page_title' => 'Support - APS Dream Home',
            'page_description' => 'Get support from APS Dream Home team'
        ];
        $this->render('pages/support', $data);
    }

public function aiValuation()
    {
        $data = [
            'page_title' => 'AI Property Valuation - APS Dream Home',
            'page_description' => 'Get AI-powered property valuation'
        ];
        $this->render('pages/ai-valuation', $data);
    }

public function userSavedSearches()
    {
        $data = [
            'page_title' => 'Saved Searches - APS Dream Home',
            'page_description' => 'Your saved property searches'
        ];
        $this->render('pages/user/saved_searches', $data);
    }

public function userNotifications()
    {
        $data = [
            'page_title' => 'Notifications - APS Dream Home',
            'page_description' => 'Your notifications'
        ];
        $this->render('pages/user/notifications', $data);
    }

public function userInvestments()
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? 0;

        $investments = [];
        try {
            list($tSql, $tParams) = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT p.*, s.site_name, s.district as site_location 
                FROM plots p LEFT JOIN sites s ON p.colony_id = s.id 
                WHERE p.customer_id = ? AND p.is_active = 1{$tSql} ORDER BY p.updated_at DESC LIMIT 20");
            $stmt->execute(array_merge([$userId], $tParams));
            $investments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Investments fetch error: ' . $e->getMessage());
        }

        $data = [
            'page_title' => 'My Investments - APS Dream Home',
            'page_description' => 'Track your property investments',
            'investments' => $investments
        ];
        $this->render('pages/user/investments', $data);
    }

public function userEditProfile()
    {
        $this->requireLogin();
        $data = [
            'page_title' => 'Edit Profile - APS Dream Home',
            'page_description' => 'Update your profile information'
        ];
        $this->render('pages/user/edit_profile', $data);
    }

public function stampDutyCalculator()
    {
        $data = [
            'page_title' => 'Stamp Duty & Registration Calculator - APS Dream Home',
            'page_description' => 'Calculate stamp duty, registration fees and total cost for property purchase'
        ];
        $this->render('pages/tools/stamp_duty_calculator', $data);
    }

public function valuationCalculator()
    {
        $districts = [];
        try {
            $stmt = $this->db->query("SELECT id, name FROM districts ORDER BY name");
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('valuationCalculator districts error: ' . $e->getMessage());
        }
        $data = [
            'page_title' => 'Property Valuation Calculator - APS Dream Home',
            'page_description' => 'Estimate your property value based on location, type and area',
            'districts' => $districts,
        ];
        $this->render('pages/tools/valuation_calculator', $data);
    }

public function homeLoanEligibility()
    {
        $data = [
            'page_title' => 'Home Loan Eligibility Calculator - APS Dream Home',
            'page_description' => 'Check your home loan eligibility based on income and existing obligations'
        ];
        $this->render('pages/tools/loan_eligibility', $data);
    }

public function propertyValuation()
    {
        $districts = [];
        try {
            $stmt = $this->db->query("SELECT id, name FROM districts ORDER BY name");
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('propertyValuation districts error: ' . $e->getMessage());
        }
        $data = [
            'page_title' => 'Property Valuation - APS Dream Home',
            'page_description' => 'Get approximate market value of your property',
            'districts' => $districts,
        ];
        $this->render('pages/tools/property_valuation', $data);
    }

public function underConstruction()
    {
        $data = [
            'page_title' => 'Under Construction - APS Dream Home',
            'page_description' => 'This page is under construction'
        ];
        $this->render('pages/under_construction', $data);
    }

public function propertySubmit()
    {
        $data = [
            'page_title' => 'Submit Property - APS Dream Home',
            'page_description' => 'Submit your property for listing'
        ];
        $this->render('pages/properties/submit', $data);
    }

public function scheduleMeeting()
    {
        $data = [
            'page_title' => 'Schedule a Meeting - APS Dream Home',
            'page_description' => 'Book an appointment with our users'
        ];
        $this->render('pages/schedule_meeting', $data);
    }

public function handleScheduleMeeting()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form data here
            $this->setFlash('success', 'Meeting scheduled successfully! We will contact you soon.');
            $this->redirect('/');
        }
    }

public function projectsByLocation($location = null)
    {
        $projects = [];
        if ($location) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM sites WHERE LOWER(district) = LOWER(?) AND status IN ('active', 'completed') ORDER BY site_name");
                $stmt->execute([$location]);
                $projects = $stmt->fetchAll(\PDO::FETCH_OBJ);
            } catch (\Exception $e) {
                error_log("Projects by location error: " . $e->getMessage());
            }
        }
        $data = [
            'page_title' => ucfirst($location) . ' Projects - APS Dream Home',
            'page_description' => 'Explore our projects in ' . ucfirst($location),
            'projects' => $projects,
            'location' => $location
        ];
        $this->render('pages/projects_by_location', $data);
    }

public function handleQuickInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $requirement = trim($_POST['requirement'] ?? '');
            $budget = trim($_POST['budget'] ?? '');
            $location = trim($_POST['location'] ?? '');
            $timeline = trim($_POST['timeline'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $formType = trim($_POST['form_type'] ?? 'quick_inquiry');

            if (empty($name) || empty($phone)) {
                $_SESSION['error'] = 'Please fill in name and phone number.';
                $this->redirect('/');
                return;
            }

            try {
                // Save to inquiries table
                $fullMessage = "Requirement: " . ucfirst(str_replace('_', ' ', $requirement)) . "\n";
                $fullMessage .= "Budget: " . ucfirst(str_replace('_', ' ', $budget)) . "\n";
                $fullMessage .= "Location: " . ucfirst($location) . "\n";
                $fullMessage .= "Timeline: " . ucfirst(str_replace('_', ' ', $timeline)) . "\n";
                if ($message) {
                    $fullMessage .= "Message: " . $message;
                }

                $stmt = $this->db->prepare("INSERT INTO inquiries (name, email, phone, message, type, status, priority, created_at) VALUES (?, ?, ?, ?, ?, 'new', 'high', NOW())");
                $stmt->execute([$name, $email, $phone, $fullMessage, $formType]);
                $inquiryId = $this->db->lastInsertId();

                // Auto-wire to CRM lead
                try { \App\Services\InquiryToLeadService::wireFromInquiry(['name'=>$name,'phone'=>$phone,'email'=>$email,'message'=>$fullMessage,'type'=>$formType]); } catch (\Exception $e3) {}

                // Also save to contacts table
                try {
                    $contactStmt = $this->db->prepare("INSERT INTO contacts (name, email, phone, subject, message, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $contactStmt->execute([$name, $email, $phone, 'Quick Inquiry - ' . ucfirst(str_replace('_', ' ', $requirement)), $fullMessage, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
                } catch (\Exception $e2) {
                    error_log('PageController handleQuickInquiry contacts: ' . $e2->getMessage());
                }

                // Track service interests based on requirement
                $this->trackServiceInterests($name, $phone, $email, $requirement, $inquiryId);

                $_SESSION['success'] = 'Thank you! Your inquiry has been submitted. We will contact you shortly.';
            } catch (\Exception $e) {
                error_log("Quick inquiry error: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to submit. Please call us directly at +91 92771 21112.';
            }
        }
        $this->redirect('/');
    }

public function legal()
    {
        $legal_docs = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM legal_documents ORDER BY category, title");
            $stmt->execute();
            $legal_docs = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $legal_docs = [];
        }

        $data = [
            'page_title' => 'Legal Documents - APS Dream Home',
            'page_description' => 'View our official certifications and legal papers',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Legal', 'url' => '']
            ],
            'legal_docs' => $legal_docs
        ];
        $this->render('pages/legal/legal', $data);
    }

public function legalPrivacy()
    {
        $data = [
            'page_title' => 'Privacy Policy - APS Dream Home',
            'page_description' => 'How we collect, use, and protect your data',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Privacy Policy', 'url' => '']
            ]
        ];
        $this->render('pages/legal/privacy', $data);
    }

public function legalTermsPage()
    {
        [$cmsTitle, $pageContent] = $this->loadPageContent('terms');
        $data = [
            'page_title' => ($cmsTitle ?: 'Terms of Service') . ' - APS Dream Home',
            'page_description' => 'Please read our terms carefully before using our services',
            'pageContent' => $pageContent,
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Terms of Service', 'url' => '']
            ]
        ];
        $this->render('pages/legal/terms', $data);
    }

public function budhaCity()
    {
        $data = [
            'page_title' => 'Budha City - APS Dream Home',
            'page_description' => 'Integrated Township at Premwaliya, Kushinagar Highway',
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => BASE_URL],
                ['title' => 'Budha City', 'url' => '']
            ]
        ];
        $this->render('pages/budhacity', $data);
    }

public function suyodayColonyPage()
    {
        $data = [
            'page_title' => 'Suyoday Colony - APS Dream Home',
            'page_description' => 'Premium residential plots in Gorakhpur with modern infrastructure'
        ];
        $this->render('pages/suyoday_colony', $data);
    }

public function toolsHub()
    {
        $pageTitle = 'Tools Hub';
        $metaDescription = 'All property calculators in one place - EMI, Stamp Duty, Plot Converter, Loan Eligibility, Valuation and more';
        return $this->render('pages/tools/hub', compact('pageTitle', 'metaDescription'));
    }

public function rentVsBuy()
    {
        $pageTitle = 'Rent vs Buy Calculator';
        $metaDescription = 'Compare renting vs buying property over 20 years with comprehensive financial analysis';
        return $this->render('pages/tools/rent_vs_buy', compact('pageTitle', 'metaDescription'));
    }

public function sipVsRealestate()
    {
        $pageTitle = 'SIP vs Real Estate Calculator';
        $metaDescription = 'Compare SIP mutual fund investments vs real estate property investments over 20 years';
        return $this->render('pages/tools/sip_vs_realestate', compact('pageTitle', 'metaDescription'));
    }

public function capitalGains()
    {
        $pageTitle = 'Capital Gains Calculator';
        $metaDescription = 'Calculate LTCG and STCG tax on property sales with CII indexation for Indian real estate';
        return $this->render('pages/tools/capital_gains', compact('pageTitle', 'metaDescription'));
    }

public function gstCalculator()
    {
        $pageTitle = 'GST Calculator for Property';
        $metaDescription = 'Calculate GST on under-construction and ready-to-move properties in India';
        return $this->render('pages/tools/gst_calculator', compact('pageTitle', 'metaDescription'));
    }

public function constructionCostEstimator()
    {
        $pageTitle = 'Construction Cost Estimator';
        $metaDescription = 'Estimate building construction cost based on plot area, floors, and quality';
        return $this->render('pages/tools/construction_cost', compact('pageTitle', 'metaDescription'));
    }

public function rentalYieldCalculator()
    {
        $pageTitle = 'Rental Yield Calculator';
        $metaDescription = 'Calculate rental income, yield, and payback period for investment property';
        return $this->render('pages/tools/rental_yield', compact('pageTitle', 'metaDescription'));
    }

public function propertyTaxCalculator()
    {
        $pageTitle = 'Property Tax Calculator';
        $metaDescription = 'Estimate annual property tax with breakdown by type, city, and occupancy';
        return $this->render('pages/tools/property_tax', compact('pageTitle', 'metaDescription'));
    }

public function colonyDetail($slug = null)
    {
        if (!$slug) {
            $this->redirect('/projects');
            return;
        }
        $colony = $this->db->fetch("SELECT c.*, d.name as district_name, s.name as state_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE c.slug = ? AND c.is_active = 1", [$slug]);
        if (!$colony) {
            $this->notFound();
            return;
        }
        list($tSql, $tParams) = $this->tenantWhere();
        $availablePlots = $this->db->fetchAll("SELECT id, plot_number, block, area_sqft, width_ft, length_ft, total_price, status, price_per_sqft, corner_plot, park_facing FROM plots WHERE colony_id = ? AND status = 'available'{$tSql} ORDER BY plot_number LIMIT 20", array_merge([$colony['id']], $tParams));
        $this->render('pages/colony_detail', [
            'page_title'       => $colony['meta_title'] ?: $colony['name'] . ' - APS Dream Home',
            'page_description' => $colony['meta_description'] ?: $colony['name'] . ' - Premium residential plots',
            'colony'           => $colony,
            'availablePlots'   => $availablePlots,
        ]);
    }

public function colonyPlots($slug = null)
    {
        if (!$slug) {
            $this->redirect('/projects');
            return;
        }
        $colony = $this->db->fetch("SELECT * FROM colonies WHERE slug = ? AND is_active = 1", [$slug]);
        if (!$colony) {
            $this->notFound();
            return;
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 24;
        $offset = ($page - 1) * $perPage;
        list($tSql, $tParams) = $this->tenantWhere();
        $totalPlots = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM plots WHERE colony_id = ? AND status = 'available'{$tSql}", array_merge([$colony['id']], $tParams));
        $plots = $this->db->fetchAll("SELECT * FROM plots WHERE colony_id = ? AND status = 'available'{$tSql} ORDER BY plot_number LIMIT $perPage OFFSET $offset", array_merge([$colony['id']], $tParams));
        $this->render('pages/colony_plots', [
            'page_title' => 'Available Plots - ' . $colony['name'],
            'colony' => $colony,
            'plots' => $plots,
            'totalPlots' => $totalPlots,
            'current_page' => $page,
            'total_pages' => max(1, ceil($totalPlots / $perPage)),
        ]);
    }

public function aiChatbotPage()
    {
        $this->render('pages/ai/chatbot', ['page_title' => 'AI Property Assistant']);
    }

public function colonies()
    {
        try {
            $rows = $this->db->fetchAll("SELECT c.*, d.name as district_name, s.name as state_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id LEFT JOIN states s ON d.state_id = s.id WHERE c.is_active = 1 ORDER BY c.name");
            $totalPlots = $this->db->fetch("SELECT COUNT(*) as total FROM plots WHERE colony_id IN (SELECT id FROM colonies WHERE is_active = 1) AND status = 'available'")['total'] ?? 0;
            $colonies = [];
            foreach ($rows as $row) {
                $amenities = [];
                if (!empty($row['amenities'])) {
                    $amenitiesList = json_decode($row['amenities'], true);
                    if (is_array($amenitiesList)) {
                        foreach ($amenitiesList as $amenity) {
                            $amenities[] = is_string($amenity) ? $amenity : ($amenity['name'] ?? '');
                        }
                    }
                }
                $highlights = [];
                if (!empty($row['highlights'])) {
                    $highlightsList = json_decode($row['highlights'], true);
                    if (is_array($highlightsList)) {
                        foreach ($highlightsList as $highlight) {
                            $highlights[] = is_string($highlight) ? $highlight : ($highlight['text'] ?? '');
                        }
                    }
                }
                $totalPlotsColony = intval($row['total_plots'] ?? 0);
                $availablePlotsColony = intval($row['available_plots'] ?? 0);
                $soldPlots = $totalPlotsColony - $availablePlotsColony;
                if ($totalPlotsColony > 0 && $soldPlots === $totalPlotsColony) {
                    $completionStatus = 'Sold Out';
                } elseif ($soldPlots > 0) {
                    $completionStatus = 'Selling Fast';
                } else {
                    $completionStatus = 'New Launch';
                }
                $districtName = $row['district_name'] ?? '';
                $stateName = $row['state_name'] ?? '';
                $location = trim($districtName . ', ' . $stateName, ', ');
                if (empty($location)) {
                    $location = 'Uttar Pradesh';
                }
                $colonies[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'] ?? '',
                    'image' => $row['image_path'] ?? '',
                    'location' => $location,
                    'district_name' => $districtName,
                    'state_name' => $stateName,
                    'total_area' => ($totalPlotsColony * 1200) . ' sqft',
                    'available_plots' => $availablePlotsColony,
                    'starting_price' => !empty($row['starting_price']) ? '₹' . number_format($row['starting_price']) : 'Contact Us',
                    'price_per_sqft' => $row['price_per_sqft'] ?? 0,
                    'description' => $row['description'] ?? '',
                    'completion_status' => $completionStatus,
                    'amenities' => $amenities,
                    'highlights' => $highlights,
                ];
            }
            $colonyStats = [
                'total_colonies' => count($colonies),
                'total_area' => array_sum(array_map(function ($c) {
                    return intval($c['available_plots']) * 1200;
                }, $colonies)) . ' sqft',
                'total_plots' => $totalPlots,
                'cities_covered' => count(array_unique(array_column($colonies, 'district_name')))
            ];
        } catch (\Exception $e) {
            error_log("[PageController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $colonies = [];
            $colonyStats = ['total_colonies' => 0, 'total_area' => '0', 'total_plots' => 0, 'cities_covered' => 0];
        }
        $this->render('pages/colonies', ['colonies' => $colonies, 'colony_stats' => $colonyStats]);
    }

public function analytics()
    {
        $this->render('pages/analytics', ['page_title' => 'Analytics']);
    }

public function calc()
    {
        $this->render('pages/calc', ['page_title' => 'Calculator']);
    }

public function inquiry()
    {
        $this->render('pages/inquiry', ['page_title' => 'Contact Us / Inquiry']);
    }

public function becomeAssociate()
    {
        $isLoggedIn = !empty($_SESSION['user_id']);
        $loggedInReferralCode = $isLoggedIn ? ($_SESSION['referral_code'] ?? '') : '';
        $userName = $isLoggedIn ? ($_SESSION['user_name'] ?? '') : '';
        $base = BASE_URL;
        $role = $_SESSION['role'] ?? '';

        // Default company referral code
        $referral_code = $loggedInReferralCode ?: 'APS-COMPANY';
        $referral_link = $base . '/associate/register?ref=' . urlencode($referral_code);

        // If logged in, use portal layout; otherwise standalone page
        if ($isLoggedIn && in_array($role, ['associate', 'customer', 'user', 'agent'])) {
            $layoutMap = [
                'associate' => 'layouts/associate',
                'agent' => 'layouts/associate',
            ];
            $this->layout = $layoutMap[$role] ?? 'layouts/customer';
            $this->render('pages/become_associate_embed', [
                'page_title' => 'Promote & Earn',
                'page_description' => 'Share your referral code and earn rewards',
                'current_page' => 'list-property',
                'isLoggedIn' => true,
                'loggedInReferralCode' => $loggedInReferralCode,
                'referral_code' => $referral_code,
                'referral_link' => $referral_link,
                'userName' => $userName,
                'base' => $base,
            ], $this->layout);
        } else {
            // Standalone public page
            $this->render('pages/become_associate', [
                'page_title' => 'Become an Associate',
                'isLoggedIn' => false,
                'loggedInReferralCode' => '',
                'referral_code' => $referral_code,
                'referral_link' => $referral_link,
                'userName' => '',
                'base' => $base
            ]);
        }
    }

public function location($slug = null)
    {
        $allowed = [
            'gorakhpur-bohisawagar',
            'gorakhpur-raghunath-nagri',
            'gorakhpur-suryoday-colony',
            'kushinagar-budha-city',
            'lucknow-ram-nagri',
            'varanasi-ganga-nagri'
        ];
        if (!in_array($slug, $allowed)) {
            header('HTTP/1.0 404 Not Found');
            $this->render('errors/404', ['page_title' => 'Page Not Found']);
            return;
        }
        $this->render('locations/' . $slug, ['page_title' => ucwords(str_replace(['-', '/'], [' ', ' '], $slug))]);
    }

    /**
     * Run property list query with filters
     */
    private function runPropertyListQuery(
        ?string $keyword,
        ?string $type,
        ?string $listingType,
        ?string $location,
        ?float $minPrice,
        ?float $maxPrice,
        ?int $bedrooms,
        ?int $bathrooms,
        ?string $furnished,
        ?int $yearBuilt,
        ?int $areaMin,
        ?int $areaMax,
        ?string $sortBy,
        int $perPage,
        int $offset
    ): array {
        // DB has status: 'approved', 'pending' (not 'active')
        // Column names: property_type, listing_type, city_name (not type, listing_type, city)
        $where = ["p.status IN ('approved', 'verified')"];
        $params = [];
        list($tSql, $tParams) = $this->tenantWhere();
        if ($tSql) { $where[] = "p.tenant_id = ?"; $params[] = $tParams[0]; }

        if ($keyword) {
            $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.address LIKE ?)';
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }
        if ($type) {
            $where[] = 'p.property_type = ?';
            $params[] = $type;
        }
        if ($listingType) {
            $where[] = 'p.listing_type = ?';
            $params[] = $listingType;
        }
        if ($location) {
            $where[] = '(p.city_name LIKE ? OR p.address LIKE ?)';
            $loc = '%' . $location . '%';
            $params[] = $loc;
            $params[] = $loc;
        }
        if ($minPrice) {
            $where[] = 'p.price >= ?';
            $params[] = (float)$minPrice;
        }
        if ($maxPrice) {
            $where[] = 'p.price <= ?';
            $params[] = (float)$maxPrice;
        }
        if ($bedrooms) {
            $where[] = 'p.bedrooms >= ?';
            $params[] = (int)$bedrooms;
        }
        if ($bathrooms) {
            $where[] = 'p.bathrooms >= ?';
            $params[] = (int)$bathrooms;
        }
        if ($furnished) {
            $where[] = 'p.furnished = ?';
            $params[] = $furnished;
        }
        if ($yearBuilt) {
            $where[] = 'p.year_built >= ?';
            $params[] = (int)$yearBuilt;
        }
        if ($areaMin) {
            $where[] = 'p.area_sqft >= ?';
            $params[] = (int)$areaMin;
        }
        if ($areaMax) {
            $where[] = 'p.area_sqft <= ?';
            $params[] = (int)$areaMax;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Get total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM user_properties p $whereClause");
        $countStmt->execute($params);
        $total = (int)($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

        // Order by
        $orderBy = match ($sortBy) {
            'price_low' => 'p.price ASC',
            'price_high' => 'p.price DESC',
            'area_large' => 'p.area_sqft DESC',
            'area_small' => 'p.area_sqft ASC',
            default => 'p.created_at DESC',
        };

        // Get paginated results
        $params[] = $perPage;
        $params[] = $offset;
        $sql = "SELECT p.*, u.name as user_name, u.phone as user_phone, u.email as user_email
                FROM user_properties p
                LEFT JOIN users u ON u.id = p.user_id
                $whereClause
                ORDER BY $orderBy
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return ['properties' => $properties, 'total' => $total];
    }

    /**
     * Create user_properties table if it doesn't exist
     */
    private function createUserPropertiesTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS user_properties (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                posted_by INT NOT NULL,
                posted_by_type VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                email VARCHAR(255),
                property_type VARCHAR(50) NOT NULL,
                listing_type VARCHAR(20) NOT NULL,
                address TEXT,
                area_sqft INT,
                width_ft INT,
                length_ft INT,
                price DECIMAL(15,2),
                price_type VARCHAR(20) DEFAULT 'lakh',
                description TEXT,
                image VARCHAR(500),
                facing VARCHAR(50),
                corner_plot TINYINT(1) DEFAULT 0,
                park_facing TINYINT(1) DEFAULT 0,
                state_id INT,
                district_id INT,
                city_name VARCHAR(100),
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_status (status),
                INDEX idx_type (property_type),
                INDEX idx_listing_type (listing_type),
                INDEX idx_city (city_name),
                INDEX idx_price (price),
                INDEX idx_area (area_sqft)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $this->db->exec($sql);
    }

    /**
     * Track property view
     */
    private function trackPropertyView(int $propertyId): void
    {
        try {
            $this->db->exec("
                INSERT INTO property_views (property_id, viewed_at, ip_address, user_agent)
                VALUES ($propertyId, NOW(), '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['HTTP_USER_AGENT']}')
                ON DUPLICATE KEY UPDATE view_count = view_count + 1, viewed_at = NOW()
            ");
        } catch (\Throwable $e) {
            error_log('trackPropertyView error: ' . $e->getMessage());
        }
    }

    /**
     * Create service_interests table if it doesn't exist
     */
    private function createServiceInterestsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS service_interests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                service_category VARCHAR(50) NOT NULL,
                service_type VARCHAR(50),
                budget_min INT,
                budget_max INT,
                location VARCHAR(100),
                description TEXT,
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_category (service_category),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $this->db->exec($sql);
    }

    /**
     * Track service interest
     */
    private function trackServiceInterests(string $name, string $phone, string $email, string $requirement, int $inquiryId): void
    {
        try {
            // Map requirement to category
            $categoryMap = [
                'financial' => 'financial_services',
                'legal' => 'legal_services',
                'interior' => 'interior_design',
                'construction' => 'construction_services',
                'documents' => 'documents',
                'resell' => 'resell',
                'other' => 'other'
            ];
            $category = $categoryMap[$requirement] ?? 'other';

            // Try to get user_id from session
            $userId = $_SESSION['user_id'] ?? 0;

            $stmt = $this->db->prepare("
                INSERT INTO service_interests (user_id, service_category, service_type, budget_min, budget_max, location, description, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $userId,
                $category,
                'quick_inquiry',
                null,
                null,
                null,
                "Quick inquiry: {$requirement}. Name: {$name}, Phone: {$phone}, Email: {$email}, Inquiry ID: {$inquiryId}",
                'pending',
                (int)date('YmdHis')
            ]);
        } catch (\Throwable $e) {
            error_log('trackServiceInterests error: ' . $e->getMessage());
        }
    }

    // ── SaaS Pricing & Tenant Signup ────────────────────────

    /**
     * Public pricing page — /pricing
     */
    public function pricing()
    {
        $plans = [];
        try {
            $plans = \App\Services\TenantService::getInstance()->getPlans();
        } catch (\Throwable $e) {
            error_log('pricing() error: ' . $e->getMessage());
        }

        $this->render('pages/pricing', [
            'page_title' => 'Pricing Plans — APS Dream Home SaaS',
            'plans'      => $plans,
        ]);
    }

    /**
     * SaaS product landing page — public homepage for the SaaS product.
     */
    public function saasHome()
    {
        $plans = [];
        try {
            $plans = \App\Services\TenantService::getInstance()->getPlans();
        } catch (\Throwable $e) {
            error_log('saasHome() error: ' . $e->getMessage());
        }

        $this->render('pages/saas_home', [
            'page_title' => 'APS CRM — All-in-One Business Platform',
            'plans'      => $plans,
        ]);
    }

    /**
     * Tenant self-service signup — GET shows form, POST creates tenant.
     */
    public function tenantSignup()
    {
        $tenantService = \App\Services\TenantService::getInstance();
        $plans = $tenantService->getPlans();
        $selectedPlan = $_GET['plan'] ?? 'free';
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
                $error = 'Invalid form submission. Please try again.';
            } else {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['contact_email'] ?? '');
                $contactName = trim($_POST['contact_name'] ?? '');
                $phone = trim($_POST['contact_phone'] ?? '');
                $password = $_POST['password'] ?? '';
                $planSlug = $_POST['plan_slug'] ?? 'free';

                // Validation
                if (strlen($name) < 2) {
                    $error = 'Company name must be at least 2 characters.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Please enter a valid email address.';
                } elseif (strlen($contactName) < 2) {
                    $error = 'Please enter your name.';
                } elseif (strlen($password) < 8) {
                    $error = 'Password must be at least 8 characters.';
                } else {
                    // Check for duplicate email
                    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
                    $stmt = $pdo->prepare("SELECT id FROM tenants WHERE contact_email = ? AND deleted_at IS NULL LIMIT 1");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'An account with this email already exists.';
                    } else {
                        // Find plan by slug
                        $plan = null;
                        foreach ($plans as $p) {
                            if (($p['slug'] ?? '') === $planSlug) {
                                $plan = $p;
                                break;
                            }
                        }
                        if (!$plan) {
                            $error = 'Invalid plan selected.';
                        } else {
                            // Create tenant
                            try {
                                $tenantId = $tenantService->create([
                                    'name'           => $name,
                                    'contact_name'   => $contactName,
                                    'contact_email'  => $email,
                                    'contact_phone'  => $phone,
                                    'plan_id'        => (int)$plan['id'],
                                    'status'         => 'trial',
                                ]);

                                // Create admin user for the tenant
                                $hashedPw = password_hash($password, PASSWORD_DEFAULT);
                                $stmt = $pdo->prepare("
                                    INSERT INTO users (name, email, password, role, status, tenant_id, created_at)
                                    VALUES (?, ?, ?, 'admin', 'active', ?, NOW())
                                ");
                                $stmt->execute([$contactName, $email, $hashedPw, $tenantId]);
                                $userId = (int)$pdo->lastInsertId();

                                // Link user to tenant
                                $stmt = $pdo->prepare("
                                    INSERT INTO tenant_users (tenant_id, user_id, role, is_primary, created_at)
                                    VALUES (?, ?, 'admin', 1, NOW())
                                ");
                                $stmt->execute([$tenantId, $userId]);

                                // Create subscription record
                                $trialEnds = date('Y-m-d H:i:s', strtotime('+14 days'));
                                $stmt = $pdo->prepare("
                                    INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, billing_cycle, amount, current_period_start, current_period_end, created_at)
                                    VALUES (?, ?, 'trial', 'monthly', 0, NOW(), ?, NOW())
                                ");
                                $stmt->execute([$tenantId, (int)$plan['id'], $trialEnds]);

                                // Log activity
                                $tenantService->logActivity($tenantId, 'tenant_self_signup', "Self-service signup by {$email}");

                                // Redirect to success page
                                $_SESSION['flash_success'] = "Account created! Welcome to {$name}. Your 14-day free trial has started.";
                                header("Location: /admin/login");
                                exit;
                            } catch (\Throwable $e) {
                                error_log('tenantSignup create error: ' . $e->getMessage());
                                $error = 'Failed to create account. Please try again.';
                            }
                        }
                    }
                }
            }
        }

        $this->render('pages/tenant_signup', [
            'page_title'  => 'Create Your Account — APS Dream Home SaaS',
            'plans'       => $plans,
            'selectedPlan'=> $selectedPlan,
            'error'       => $error,
        ]);
    }
}
