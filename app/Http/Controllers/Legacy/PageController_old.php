<?php

namespace App\Controllers;

use App\Services\PropertyService;

class PageController extends Controller {
    private $propertyService;

    public function __construct() {
        parent::__construct();
        $this->propertyService = new PropertyService();
    }

    /**
     * Display about us page
     */
    public function about() {
        $this->view('pages/about', [
            'title' => 'About Us - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display contact page
     */
    public function contact() {
        $this->view('pages/contact', [
            'title' => 'Contact Us - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display services page
     */
    public function services() {
        $this->view('pages/services', [
            'title' => 'Our Services - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display team page
     */
    public function team() {
        $this->view('pages/team', [
            'title' => 'Our Team - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display careers page
     */
    public function careers() {
        $this->view('pages/careers', [
            'title' => 'Careers - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display gallery page
     */
    public function gallery() {
        $projects = $this->propertyService->getFeaturedProperties(12);

        $this->view('pages/gallery', [
            'title' => 'Project Gallery - APS Dream Homes Pvt Ltd',
            'projects' => $projects
        ]);
    }

    /**
     * Display testimonials page
     */
    public function testimonials() {
        $this->view('pages/testimonials', [
            'title' => 'Customer Testimonials - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display blog listing
     */
    public function blog() {
        $this->view('pages/blog', [
            'title' => 'Blog - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display single blog post
     */
    public function blogShow($slug) {
        $this->view('pages/blog-show', [
            'title' => 'Blog Post - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display privacy policy
     */
    public function privacy() {
        $this->view('pages/privacy', [
            'title' => 'Privacy Policy - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display terms of service
     */
    public function terms() {
        $this->view('pages/terms', [
            'title' => 'Terms of Service - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display FAQ page
     */
    public function faq() {
        $this->view('pages/faq', [
            'title' => 'Frequently Asked Questions - APS Dream Homes Pvt Ltd'
        ]);
    }

    /**
     * Display sitemap
     */
    public function sitemap() {
        $pages = [
            '/' => 'Home',
            '/about' => 'About Us',
            '/contact' => 'Contact',
            '/properties' => 'Properties',
            '/services' => 'Services',
            '/team' => 'Our Team',
            '/careers' => 'Careers',
            '/gallery' => 'Gallery',
            '/testimonials' => 'Testimonials',
            '/blog' => 'Blog',
            '/faq' => 'FAQ'
        ];

        $this->view('pages/sitemap', [
            'title' => 'Sitemap - APS Dream Homes Pvt Ltd',
            'pages' => $pages
        ]);
    }

    /**
     * Handle contact form submission
     */
    public function submitContact() {
        try {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';

            // Validate required fields
            if (empty($name) || empty($email) || empty($message)) {
                throw new \Exception('Please fill in all required fields');
            }

            // Here you would typically save to database and send email
            // For now, just redirect with success message

            $this->setFlash('success', 'Thank you for contacting us! We will get back to you soon.');
            $this->redirect('/contact');

        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $_SESSION['form_data'] = $_POST;
            $this->redirect('/contact');
        }
    }

    /**
     * Handle newsletter subscription
     */
    public function subscribeNewsletter() {
        try {
            $email = $_POST['email'] ?? '';

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Please provide a valid email address');
            }

            // Here you would typically save to database
            $this->setFlash('success', 'Thank you for subscribing to our newsletter!');

        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/');
    }
}
