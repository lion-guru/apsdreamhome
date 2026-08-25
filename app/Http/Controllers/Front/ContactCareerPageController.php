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
        $this->render('pages/property_interest', [
            'page_title' => 'Property Interest - APS Dream Home',
            'page_description' => 'Express interest in a property.',
        ]);
    }

    public function propertyInquiry()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        } catch (\Exception $e) {
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

        $this->render('pages/careers_apply', [
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
        } catch (\Exception $e) {
            error_log("Career jobs page error: " . $e->getMessage());
            $jobs = [];
        }

        $this->render('pages/career_jobs', [
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