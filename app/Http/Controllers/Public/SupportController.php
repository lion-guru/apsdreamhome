<?php
/**
 * Support Controller
 * Handles customer support inquiries and tickets
 */

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;
use PDO;
use Exception;

class SupportController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('csrf', ['only' => ['store']]);
    }

    /**
     * Display the support center page
     */
    public function index()
    {
        $this->data['page_title'] = 'Support Center - ' . APP_NAME;

        // Success/Error messages from session
        $this->data['success'] = $this->getFlash('success');
        $this->data['error'] = $this->getFlash('error');

        return $this->render('pages/support');
    }

    /**
     * Store a new support ticket
     */
    public function store()
    {
        // XSS Protection - Sanitize input
        $name = strip_tags($_POST['name'] ?? "");
        $email = filter_var($_POST['email'] ?? "", FILTER_SANITIZE_EMAIL);
        $phone = preg_replace("/[^0-9+\- ]/", "", $_POST['phone'] ?? "");
        $category = strip_tags($_POST['category'] ?? "");
        $subject = strip_tags($_POST['subject'] ?? "");
        $message = strip_tags($_POST['message'] ?? "");

        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $this->setFlash('error', 'Please fill in all required fields.');
            return $this->redirect('/support');
        }

        try {
            $inquiryModel = $this->model('Inquiry');
            if ($inquiryModel) {
                $full_message = "Category: " . $category . "\nSubject: " . $subject . "\n\n" . $message;

                $inquiry = $inquiryModel::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'message' => $full_message,
                    'type' => 'support',
                    'status' => 'pending',
                    'priority' => 'medium'
                ]);

                if ($inquiry) {
                    // Invalidate dashboard cache
                    if (function_exists('getPerformanceManager')) {
                        getPerformanceManager()->clearCache('query_');
                    }

                    $this->setFlash('success', 'Your support ticket has been submitted successfully. We will get back to you soon.');
                } else {
                    $this->setFlash('error', 'Failed to submit support ticket. Please try again.');
                }
            } else {
                $this->setFlash('error', 'System error. Please try again later.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'An error occurred while submitting your ticket: ' . $e->getMessage());
        }

        return $this->redirect('/support');
    }
}
