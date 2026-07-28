<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Services\ReviewService;

/**
 * Public Testimonials page
 */
class TestimonialsController extends BaseController
{
    use TenantAwareTrait;

    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new ReviewService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    public function index()
    {
        $testimonials = $this->service ? $this->service->getTestimonials(50) : [];
        $featured = $this->service ? $this->service->getTestimonials(6, true) : [];
        $stats = $this->service ? $this->service->getStats() : [];
        return $this->render('pages.testimonials.index', [
            'page_title' => 'Customer Testimonials & Reviews',
            'page_heading' => 'What Our Customers Say',
            'testimonials' => $testimonials,
            'featured' => $featured,
            'stats' => $stats
        ]);
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect(BASE_URL . '/testimonials');
        }
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $project = trim($_POST['project_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $content = trim($_POST['content'] ?? '');
        $errors = [];
        if (!$name) $errors[] = 'Name is required';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
        if (!$content) $errors[] = 'Testimonial content is required';
        if ($rating < 1 || $rating > 5) $rating = 5;
        if ($errors) {
            return $this->render('pages.testimonials.submit', [
                'page_title' => 'Share Your Testimonial',
                'errors' => $errors,
                'logged_in' => !empty($_SESSION['user_id'])
            ]);
        }
        try {
            $stmt = $this->db ? $this->db->prepare("INSERT INTO testimonials (customer_name, client_name, email, rating, content, testimonial, project_name, location, tenant_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')") : null;
            if ($stmt) {
                $stmt->execute([$name, $name, $email, $rating, $content, $content, $project, $location, $this->tenantId()]);
            }
            $this->setFlash('success', 'Thank you! Your testimonial has been submitted for review.');
            return $this->redirect(BASE_URL . '/testimonials');
        } catch (\Throwable $e) {
            return $this->render('pages.testimonials.submit', [
                'page_title' => 'Share Your Testimonial',
                'errors' => ['Submission failed. Please try again.'],
                'logged_in' => !empty($_SESSION['user_id'])
            ]);
        }
    }

    public function showSubmit()
    {
        return $this->render('pages.testimonials.submit', [
            'page_title' => 'Share Your Testimonial',
            'logged_in' => !empty($_SESSION['user_id'])
        ]);
    }
}
