<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Services\ReviewService;

/**
 * Public Testimonials page — wired to the live, moderated property_reviews table.
 * Never reads the legacy `testimonials` table (fabricated personas removed S678+).
 */
class TestimonialsController extends BaseController
{
    use TenantAwareTrait;

    private $service = null;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new ReviewService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    public function index()
    {
        $testimonials = [];
        $featured = [];
        $stats = ['avg_rating' => 0.0, 'approved_reviews' => 0];

        try {
            $tid = $this->tenantId();
            $tidSql = $tid > 1 ? " AND pr.tenant_id = ?" : "";
            $tidParams = $tid > 1 ? [$tid] : [];

            $rows = $this->db ? $this->db->fetchAll(
                "SELECT pr.id, pr.rating, pr.review_text, pr.anonymous, pr.created_at,
                        pr.helpful_count,
                        COALESCE(u.name, 'Verified Buyer') AS u_name,
                        COALESCE(p.title, 'APS Dream Home') AS property,
                        p.location AS property_location
                   FROM property_reviews pr
                   LEFT JOIN users u ON u.id = pr.customer_id
                   LEFT JOIN properties p ON p.id = pr.property_id
                  WHERE pr.status = 'approved'{$tidSql}
                  ORDER BY pr.created_at DESC
                  LIMIT 50",
                $tidParams
            ) : [];

            foreach ($rows as $r) {
                $name = !empty($r['anonymous']) ? 'Verified Buyer' : ($r['u_name'] ?: 'Verified Buyer');
                $testimonials[] = [
                    'customer_name' => $name,
                    'testimonial'   => (string)($r['review_text'] ?? ''),
                    'content'       => (string)($r['review_text'] ?? ''),
                    'rating'        => max(1, min(5, (int)$r['rating'])),
                    'project_name'  => (string)($r['property'] ?? 'APS Dream Home'),
                    'location'      => (string)($r['property_location'] ?? ''),
                    'is_featured'   => 0,
                    'created_at'    => $r['created_at'] ?? '',
                ];
            }

            $s = $this->db ? $this->db->fetchOne(
                "SELECT COUNT(*) AS total,
                        COALESCE(AVG(rating), 0) AS avg_rating
                   FROM property_reviews
                  WHERE status = 'approved'" . ($tid > 1 ? " AND tenant_id = ?" : ""),
                $tid > 1 ? [$tid] : []
            ) : null;

            $stats['approved_reviews'] = (int)($s['total'] ?? 0);
            $stats['avg_rating'] = $stats['approved_reviews'] > 0
                ? round((float)($s['avg_rating'] ?? 0), 1)
                : 0.0;

            // Featured = highest-rated approved, newest first, max 3
            $featured = array_slice(
                array_values(array_filter($testimonials, fn($t) => $t['rating'] >= 4)),
                0,
                3
            );
        } catch (\Throwable $e) {
            error_log('testimonials index error: ' . $e->getMessage());
        }

        return $this->render('pages.testimonials.index', [
            'page_title'   => 'Customer Reviews - APS Dream Home',
            'page_heading' => 'Customer Reviews',
            'testimonials' => $testimonials,
            'featured'     => $featured,
            'stats'        => $stats,
        ]);
    }

    public function showSubmit()
    {
        return $this->render('pages.testimonials.submit', [
            'page_title' => 'Share Your Review',
            'logged_in'  => !empty($_SESSION['user_id']),
            'properties' => $this->activeProperties(),
        ]);
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect(BASE_URL . '/testimonials');
        }

        // Public review form posts with a same-session CSRF token.
        $csrf = $_POST['csrf_token'] ?? '';
        $sess = $_SESSION['csrf_token'] ?? '';
        if ($sess === '' || !hash_equals((string)$sess, (string)$csrf)) {
            return $this->render('pages.testimonials.submit', [
                'page_title' => 'Share Your Review',
                'errors'     => ['Security token expired. Please reload and try again.'],
                'logged_in'  => !empty($_SESSION['user_id']),
                'properties' => $this->activeProperties(),
            ]);
        }

        $uid = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($uid <= 0) {
            return $this->render('pages.testimonials.submit', [
                'page_title' => 'Share Your Review',
                'errors'     => ['Please sign in before leaving a review.'],
                'logged_in'  => false,
                'properties' => $this->activeProperties(),
            ]);
        }

        $propertyId = (int)($_POST['property_id'] ?? 0);
        $rating     = (int)($_POST['rating'] ?? 5);
        $content    = trim(strip_tags($_POST['content'] ?? ''));
        $anon       = !empty($_POST['anonymous']) ? 1 : 0;
        $errors     = [];

        if ($propertyId <= 0) {
            $errors[] = 'Please choose the property or colony your review is about';
        } else {
            // property_id is user-supplied input — verify it is a real active listing.
            $ok = $this->db ? $this->db->fetchOne(
                "SELECT id FROM properties WHERE id = ? AND status = 'active' LIMIT 1",
                [$propertyId]
            ) : null;
            if (!$ok) {
                $errors[] = 'That property could not be found';
            }
        }
        if ($content === '') {
            $errors[] = 'Please write your review';
        }
        if (mb_strlen($content) > 2000) {
            $content = mb_substr($content, 0, 2000);
        }
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }

        if ($errors) {
            return $this->render('pages.testimonials.submit', [
                'page_title' => 'Share Your Review',
                'errors'     => $errors,
                'logged_in'  => true,
                'properties' => $this->activeProperties(),
            ]);
        }

        try {
            if (!$this->service) {
                throw new \RuntimeException('Review service unavailable');
            }
            $id = $this->service->createReview([
                'customer_id' => $uid,
                'property_id' => $propertyId,
                'rating'      => $rating,
                'review_text' => $content,
                'anonymous'   => $anon,
            ]);
            $this->setFlash('success', 'Thank you! Your review has been received and is pending moderation before publication.');
            return $this->redirect(BASE_URL . '/testimonials');
        } catch (\Throwable $e) {
            error_log('testimonials submit error: ' . $e->getMessage());
            return $this->render('pages.testimonials.submit', [
                'page_title' => 'Share Your Review',
                'errors'     => ['Submission failed. Please try again.'],
                'logged_in'  => true,
                'properties' => $this->activeProperties(),
            ]);
        }
    }

    /**
     * Live active listings for the review form's property selector.
     * Honest: no properties = no form to submit.
     */
    private function activeProperties(): array
    {
        try {
            if (!$this->db) {
                return [];
            }
            $rows = $this->db->fetchAll(
                "SELECT id, title, location, city FROM properties
                  WHERE status = 'active'
                  ORDER BY title ASC
                  LIMIT 100",
                []
            );
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('testimonials activeProperties error: ' . $e->getMessage());
            return [];
        }
    }
}
