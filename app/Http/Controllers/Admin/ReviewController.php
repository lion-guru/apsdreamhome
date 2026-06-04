<?php

namespace App\Http\Controllers\Admin;

use App\Services\ReviewService;
use App\Services\AuditService;

/**
 * Reviews & Testimonials Admin Controller
 * Moderate customer reviews and manage testimonials
 */
class ReviewController extends AdminController
{
    private $reviews;
    private $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->reviews = new ReviewService($this->db); } catch (\Throwable $e) { $this->reviews = null; }
        try { $this->audit = new AuditService($this->db); } catch (\Throwable $e) { $this->audit = null; }
    }

    private function getPdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }

    public function index()
    {
        $stats = $this->reviews ? $this->reviews->getStats() : [];
        $reviews = $this->reviews ? $this->reviews->getAllReviews('', 30) : [];
        $testimonials = $this->reviews ? $this->reviews->getAllTestimonials('', 20) : [];
        return $this->render('admin.reviews.index', [
            'page_title' => 'Reviews & Testimonials',
            'page_heading' => 'Reviews & Testimonials',
            'stats' => $stats,
            'reviews' => $reviews,
            'testimonials' => $testimonials
        ]);
    }

    public function approve()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($this->reviews && $id) {
            $this->reviews->approve($id);
            if ($this->audit) $this->audit->log('review.approve', $this->getUserId(), $this->getUserRole(), 'review', $id, "Approved review #$id");
            $this->setFlash('success', 'Review approved');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function reject()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($this->reviews && $id) {
            $this->reviews->reject($id);
            if ($this->audit) $this->audit->log('review.reject', $this->getUserId(), $this->getUserRole(), 'review', $id, "Rejected review #$id");
            $this->setFlash('success', 'Review rejected');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function respond()
    {
        $id = (int)($_POST['id'] ?? 0);
        $response = trim($_POST['response'] ?? '');
        if ($this->reviews && $id && $response) {
            $this->reviews->addAdminResponse($id, $response);
            if ($this->audit) $this->audit->log('review.respond', $this->getUserId(), $this->getUserRole(), 'review', $id, "Added response to review #$id");
            $this->setFlash('success', 'Response added');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($this->reviews && $id) {
            $this->reviews->delete($id);
            if ($this->audit) $this->audit->log('review.delete', $this->getUserId(), $this->getUserRole(), 'review', $id, "Deleted review #$id");
            $this->setFlash('success', 'Review deleted');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function featureTestimonial()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->reviews && $id) {
            $this->reviews->toggleFeaturedTestimonial($id);
            $this->setFlash('success', 'Featured status toggled');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function approveTestimonial()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->reviews && $id) {
            $this->reviews->approveTestimonial($id);
            $this->setFlash('success', 'Testimonial approved');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function rejectTestimonial()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->reviews && $id) {
            $this->reviews->rejectTestimonial($id);
            $this->setFlash('success', 'Testimonial rejected');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function deleteTestimonial()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->reviews && $id) {
            $this->reviews->deleteTestimonial($id);
            $this->setFlash('success', 'Testimonial deleted');
        }
        return $this->redirect(BASE_URL . '/admin/reviews');
    }
}
