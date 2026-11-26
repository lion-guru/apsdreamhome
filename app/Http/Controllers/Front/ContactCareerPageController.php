<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;
use PDO;

/**
 * ContactCareerPageController
 * Contact, service interest, property interest, property inquiry, careers, career apply, career jobs, career job details, service interest
 */
class ContactCareerPageController extends BaseController
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

    public function contact()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name    = trim($_POST['name'] ?? '');
            $email   = trim($_POST['email'] ?? '');
            $phone   = trim($_POST['phone'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $subject = trim($_POST['subject'] ?? '') ?: 'Website Contact Form';

            $errors = [];
            if ($name === '') $errors[] = 'Name is required';
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
            if ($phone === '') $errors[] = 'Phone is required';
            if ($message === '') $errors[] = 'Message is required';

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

            if (!empty($errors)) {
                $msg = implode(' ', $errors);
                if ($isAjax) {
                    $this->jsonResponse(['success' => false, 'message' => $msg], 422);
                    return;
                }
                $_SESSION['error'] = $msg;
                $this->redirect('/contact');
                return;
            }

            try {
                $db = Database::getInstance();
                $tid = (int)$this->tenantId();
                $stmt = $db->prepare(
                    "INSERT INTO inquiries (name, email, phone, subject, message, status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, 'new', ?, NOW())"
                );
                $stmt->execute([$name, $email, $phone, $subject, $message, $tid > 1 ? $tid : 1]);
                $id = (int)$db->lastInsertId();
                $msg = 'Thank you! Your message has been received. We will contact you within 24 hours.';

                // Notify admin/sales using the same path as the main inquiry form.
                try {
                    $notif = new \App\Services\NotificationService($db);
                    $notif->notify(
                        'inquiry',
                        "New website contact from {$name} ({$phone}): " . (strlen($message) > 80 ? substr($message, 0, 80) . '...' : $message),
                        null,
                        "/admin/inquiries/view/{$id}",
                        "New Website Contact: {$name}"
                    );
                } catch (\Throwable $e) {
                    error_log('Contact notification failed: ' . $e->getMessage());
                }

                if ($isAjax) {
                    $this->jsonResponse(['success' => true, 'message' => $msg, 'id' => $id]);
                    return;
                }
                $_SESSION['success'] = $msg;
                $this->redirect('/contact');
                return;
            } catch (\Throwable $e) {
                error_log('Contact form save failed: ' . $e->getMessage());
                $msg = 'Sorry, we could not save your message. Please try again or call us.';
                if ($isAjax) {
                    $this->jsonResponse(['success' => false, 'message' => $msg], 500);
                    return;
                }
                $_SESSION['error'] = $msg;
                $this->redirect('/contact');
                return;
            }
        }

        $this->render('pages/contact', [
            'page_title' => 'Contact Us - APS Dream Home',
            'page_description' => 'Get in touch with APS Dream Home.',
        ]);
    }

    public function serviceInterest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle service interest form
            $_SESSION['success'] = 'Thank you for your interest! We will contact you soon.';
        }
        $this->redirect('/contact');
    }

    public function propertyInterest()
    {
        $this->render('pages/property_inquire', [
            'page_title' => 'Property Interest - APS Dream Home',
            'page_description' => 'Express interest in a property.',
        ]);
    }

    public function propertyInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ═══ LEGAL COMPLIANCE: Terms consent must be accepted ═══
            $termsConsent = $_POST['terms_consent'] ?? '';
            if ($termsConsent !== 'on' && $termsConsent !== '1') {
                $_SESSION['error'] = 'You must accept the Cancellation Policy, Refund Policy, and Master Agreement Terms to proceed.';
                $this->redirect('/inquiry');
                return;
            }
            // Handle property inquiry
            $_SESSION['success'] = 'Thank you for your inquiry! We will contact you soon.';
        }
        $this->redirect('/contact');
    }

    public function careers()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE status = 'active' ORDER BY created_at DESC");
            $stmt->execute();
            $jobs = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Careers page error: " . $e->getMessage());
            $jobs = [];
        }

        $this->render('pages/careers', [
            'page_title' => 'Careers - APS Dream Home',
            'page_description' => 'Join our team at APS Dream Home.',
            'jobs' => $jobs,
        ]);
    }

    public function careerApply()
    {
        $jobId = $_GET['job_id'] ?? null;
        $job = null;
        if ($jobId) {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$jobId]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        $this->render('pages/career_apply', [
            'page_title' => 'Apply for Job - APS Dream Home',
            'page_description' => 'Apply for a career at APS Dream Home.',
            'job' => $job,
        ]);
    }

    public function submitCareerApplication()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/careers/apply');
            return;
        }

        // Handle application submission
        $_SESSION['success'] = 'Your application has been submitted successfully!';
        $this->redirect('/careers');
    }

    public function careerJobs()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE status = 'active' ORDER BY created_at DESC");
            $stmt->execute();
            $jobs = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Career jobs page error: " . $e->getMessage());
            $jobs = [];
        }

        $this->render('pages/careers', [
            'page_title' => 'Job Listings - APS Dream Home',
            'page_description' => 'Browse all job openings.',
            'jobs' => $jobs,
        ]);
    }

    public function careerJobDetails($id = null)
    {
        $job = null;
        if ($id) {
            $stmt = $this->db->prepare("SELECT * FROM careers WHERE id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$id]);
            $job = $stmt->fetch(\PDO::FETCH_ASSOC);
        }

        if (!$job) {
            $this->render('pages/404', [
                'page_title' => 'Job Not Found',
                'page_description' => 'The requested job could not be found.',
            ]);
            return;
        }

        $this->render('pages/career_job_detail', [
            'page_title' => $job['title'] . ' - Career - APS Dream Home',
            'page_description' => 'Job details for ' . $job['title'],
            'job' => $job,
        ]);
    }

    }