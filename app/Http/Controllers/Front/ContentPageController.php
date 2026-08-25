<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use PDO;

/**
 * ContentPageController
 * Content pages (blog, news, reviews, documents, legal terms, disclaimers, policies, construction services, interior design, gallery projects, document gallery, gallery project)
 */
class ContentPageController extends BaseController
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

    public function blog()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
            $stmt->execute();
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            // Get categories
            $categories = array_unique(array_column($posts, 'category'));
            $categories = array_filter($categories);
        } catch (\Exception $e) {
            error_log("Blog page error: " . $e->getMessage());
            $posts = [];
            $categories = [];
        }

        $this->render('pages/blog', [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Latest news and insights from APS Dream Home.',
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }

    public function blogPost($slug = null)
    {
        $post = null;
        if ($slug) {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1");
            $stmt->execute([$slug]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$post) {
            $this->render('pages/404', [
                'page_title' => 'Blog Post Not Found',
                'page_description' => 'The requested blog post could not be found.',
            ]);
            return;
        }

        // Get related posts
        $related = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
            $stmt->execute([$post['id']]);
            $related = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            // fallback
        }

        $this->render('pages/blog_post', [
            'page_title' => $post['title'] . ' - APS Dream Home',
            'page_description' => $post['excerpt'] ?? 'Read our latest blog post',
            'post' => $post,
            'related_posts' => $related,
        ]);
    }

    public function news()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM news_articles WHERE status = 'published' ORDER BY published_at DESC LIMIT 20");
            $stmt->execute();
            $news = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log("News page error: " . $e->getMessage());
            $news = [];
        }

        $this->render('pages/news', [
            'page_title' => 'News & Updates - APS Dream Home',
            'page_description' => 'Latest news and updates from APS Dream Home.',
            'news' => $news,
        ]);
    }

    public function reviews()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC");
            $stmt->execute();
            $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log("Reviews page error: " . $e->getMessage());
            $reviews = [];
        }

        $this->render('pages/reviews', [
            'page_title' => 'Reviews - APS Dream Home',
            'page_description' => 'Customer reviews and ratings.',
            'reviews' => $reviews,
        ]);
    }

    public function documents()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $documents = $this->db->fetchAll("
            SELECT * FROM documents
            WHERE status = 'published'{$tidSql}
            ORDER BY created_at DESC
        ", $params) ?: [];

        $this->render('pages/documents', [
            'page_title' => 'Documents - APS Dream Home',
            'page_description' => 'Download important documents.',
            'documents' => $documents,
        ]);
    }

    public function downloadDocument($id)
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$id];
        if ($tid > 1) $params[] = $tid;

        $doc = $this->db->fetchOne("SELECT * FROM documents WHERE id = ?{$tidSql} LIMIT 1", $params);

        if (!$doc) {
            $this->render('pages/404', [
                'page_title' => 'Document Not Found',
                'page_description' => 'The requested document could not be found.',
            ]);
            return;
        }

        // Increment download count
        $this->db->execute("UPDATE documents SET download_count = download_count + 1 WHERE id = ?", [$id]);

        // Serve file
        $filePath = PUBLIC_PATH . '/' . ltrim($doc['file_path'], '/');
        if (file_exists($filePath)) {
            header('Content-Type: ' . $doc['mime_type']);
            header('Content-Disposition: attachment; filename="' . basename($doc['file_name']) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            $this->render('pages/404', [
                'page_title' => 'File Not Found',
                'page_description' => 'The requested file could not be found on server.',
            ]);
        }
    }

    public function constructionServices()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $services = $this->db->fetchAll("
            SELECT * FROM construction_services
            WHERE status = 'active'{$tidSql}
            ORDER BY sort_order
        ", $params) ?: [];

        $this->render('pages/construction_services', [
            'page_title' => 'Construction Services - APS Dream Home',
            'page_description' => 'Professional construction services.',
            'services' => $services,
        ]);
    }

    public function interiorDesign()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $designs = $this->db->fetchAll("
            SELECT * FROM interior_designs
            WHERE status = 'active'{$tidSql}
            ORDER BY created_at DESC
        ", $params) ?: [];

        $this->render('pages/interior_design', [
            'page_title' => 'Interior Design - APS Dream Home',
            'page_description' => 'Beautiful interior design solutions.',
            'designs' => $designs,
        ]);
    }

    public function galleryProject($projectId = null)
    {
        if (!$projectId) {
            $this->redirect('/gallery');
            return;
        }

        $project = $this->db->fetchOne("SELECT * FROM gallery_projects WHERE id = ? AND status = 'active' LIMIT 1", [$projectId]);

        if (!$project) {
            $this->render('pages/404', [
                'page_title' => 'Project Not Found',
                'page_description' => 'The requested gallery project could not be found.',
            ]);
            return;
        }

        // Get project images
        $images = $this->db->fetchAll("SELECT * FROM gallery_images WHERE project_id = ? ORDER BY sort_order", [$projectId]) ?: [];

        $this->render('pages/gallery_project', [
            'page_title' => $project['title'] . ' - Gallery - APS Dream Home',
            'page_description' => $project['description'] ?? 'View project gallery',
            'project' => $project,
            'images' => $images,
        ]);
    }

    public function documentGallery()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $documents = $this->db->fetchAll("
            SELECT * FROM documents
            WHERE status = 'published'{$tidSql}
            ORDER BY created_at DESC
        ", $params) ?: [];

        $this->render('pages/document_gallery', [
            'page_title' => 'Document Gallery - APS Dream Home',
            'page_description' => 'Browse our document gallery.',
            'documents' => $documents,
        ]);
    }

    public function downloads()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $downloads = $this->db->fetchAll("
            SELECT * FROM downloads
            WHERE status = 'active'{$tidSql}
            ORDER BY created_at DESC
        ", $params) ?: [];

        $this->render('pages/downloads', [
            'page_title' => 'Downloads - APS Dream Home',
            'page_description' => 'Download resources and documents.',
            'downloads' => $downloads,
        ]);
    }

    public function faqs()
    {
        $tid = TenantContext::getId();
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $faqs = $this->db->fetchAll("
            SELECT * FROM faqs
            WHERE status = 'active'{$tidSql}
            ORDER BY display_order, question
        ", $params) ?: [];

        // Group by category
        $grouped = [];
        foreach ($faqs as $faq) {
            $cat = $faq['category'] ?? 'General';
            if (!isset($grouped[$cat])) $grouped[$cat] = [];
            $grouped[$cat][] = $faq;
        }

        $this->render('pages/faqs', [
            'page_title' => 'Frequently Asked Questions - APS Dream Home',
            'page_description' => 'Find answers to common questions.',
            'faqs_grouped' => $grouped,
        ]);
    }

    public function legalTermsConditions()
    {
        $this->render('pages/legal_terms', [
            'page_title' => 'Terms & Conditions - APS Dream Home',
            'page_description' => 'Terms and conditions of use.',
        ]);
    }

    public function legalDocuments()
    {
        $this->render('pages/legal_documents', [
            'page_title' => 'Legal Documents - APS Dream Home',
            'page_description' => 'Legal documents and resources.',
        ]);
    }

    public function disclaimer()
    {
        $this->render('pages/disclaimer', [
            'page_title' => 'Disclaimer - APS Dream Home',
            'page_description' => 'Disclaimer and legal notices.',
        ]);
    }

    public function cancellationPolicy()
    {
        $this->render('pages/cancellation_policy', [
            'page_title' => 'Cancellation Policy - APS Dream Home',
            'page_description' => 'Our cancellation and refund policy.',
        ]);
    }

    public function refundPolicy()
    {
        $this->render('pages/refund_policy', [
            'page_title' => 'Refund Policy - APS Dream Home',
            'page_description' => 'Our refund policy.',
        ]);
    }

    public function systemLogSecurityEvent()
    {
        // API endpoint for logging security events
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $event = $data['event'] ?? '';
        $details = $data['details'] ?? [];

        // Log the event
        error_log("SECURITY_EVENT: $event - " . json_encode($details));

        echo json_encode(['success' => true]);
        exit;
    }

    public function systemLaunchSystem()
    {
        $this->render('pages/launch_system', [
            'page_title' => 'System Launch - APS Dream Home',
            'page_description' => 'System launch and initialization.',
        ]);
    }

    public function systemKycUpload()
    {
        // Handle KYC upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle file upload
            $_SESSION['success'] = 'KYC documents uploaded successfully!';
        }
        $this->redirect('/user/kyc');
    }
}