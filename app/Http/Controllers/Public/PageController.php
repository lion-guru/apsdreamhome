<?php
/**
 * Page Controller
 * Handles static pages like About, Contact, etc.
 */

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;

class PageController extends BaseController {
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display About page
     */
    public function about() {
        // Set page data
        $this->data['page_title'] = 'About Us - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'About', 'url' => BASE_URL . 'about']
        ];

        // Render the about page
        $this->render('pages/about');
    }

    /**
     * Display Contact page
     */
    public function contact() {
        // Set page data
        $this->data['page_title'] = 'Contact Us - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Contact', 'url' => BASE_URL . 'contact']
        ];

        // Contact information
        $this->data['contact_info'] = [
            'phone' => '+91-1234567890',
            'email' => 'info@apsdreamhome.com',
            'address' => '123 Main Street, Gorakhpur, Uttar Pradesh - 273001',
            'hours' => 'Mon - Sat: 9:00 AM - 8:00 PM, Sun: 10:00 AM - 6:00 PM'
        ];

        // Render the contact page
        $this->render('pages/contact');
    }

    /**
     * Display Services page
     */
    public function services() {
        // Set page data
        $this->data['page_title'] = 'Our Services - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Services', 'url' => BASE_URL . 'services']
        ];

        // Render the services page
        $this->render('pages/services');
    }

    /**
     * Display Team page
     */
    public function team() {
        // Set page data
        $this->data['page_title'] = 'Our Team - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'Team', 'url' => BASE_URL . 'team']
        ];

        // Render the team page
        $this->render('pages/team');
    }

    /**
     * Display Gallery page
     */
    public function gallery() {
        $this->data['page_title'] = 'Gallery - ' . APP_NAME;
        $this->render('pages/gallery');
    }

    /**
     * Display Resell page
     */
    public function resell() {
        $this->data['page_title'] = 'Resell Properties - ' . APP_NAME;
        $this->render('pages/resell');
    }

    /**
     * Display Careers page
     */
    public function careers() {
        $this->data['page_title'] = 'Careers - ' . APP_NAME;
        $this->render('pages/careers');
    }

    /**
     * Display Testimonials page
     */
    public function testimonials() {
        $this->data['page_title'] = 'Testimonials - ' . APP_NAME;
        $this->render('pages/testimonials');
    }

    /**
     * Display Blog landing page
     */
    public function blog() {
        $this->data['page_title'] = 'Blog - ' . APP_NAME;
        $this->render('pages/blog');
    }

    /**
     * Display individual Blog post
     */
    public function blogShow($slug = null) {
        $this->data['page_title'] = 'Blog Article - ' . APP_NAME;
        $this->data['slug'] = $slug;
        $this->render('pages/blog_detail');
    }

    /**
     * Display FAQ page
     */
    public function faq() {
        $this->data['page_title'] = 'FAQs - ' . APP_NAME;
        $this->render('pages/faq');
    }

    /**
     * Display Downloads page
     */
    public function downloads() {
        $this->data['page_title'] = 'Downloads - ' . APP_NAME;
        $this->render('pages/downloads');
    }

    /**
     * Display Sitemap page
     */
    public function sitemap() {
        $this->data['page_title'] = 'Sitemap - ' . APP_NAME;
        $this->render('pages/sitemap');
    }

    /**
     * Display Privacy Policy
     */
    public function privacy() {
        $this->data['page_title'] = 'Privacy Policy - ' . APP_NAME;
        $this->render('pages/privacy_policy');
    }

    /**
     * Display Terms of Service
     */
    public function terms() {
        $this->data['page_title'] = 'Terms of Service - ' . APP_NAME;
        $this->render('pages/terms_of_service');
    }

}
