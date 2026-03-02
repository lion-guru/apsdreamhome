<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;

class SupportController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    public function index()
    {
        $this->data['page_title'] = 'Help & Support - ' . APP_NAME;
        return $this->render('user/support');
    }

    public function store()
    {
        $this->middleware('csrf');

        $category = $this->input('category');
        $subject = $this->input('subject');
        $message = $this->input('message');

        if (empty($category) || empty($subject) || empty($message)) {
            $this->setFlash('error', 'All fields are required.');
            return $this->redirect('/support');
        }

        try {
            $inquiryModel = $this->model('Inquiry');
            $inquiry = $inquiryModel::create([
                'user_id' => $_SESSION['uid'],
                'name' => $_SESSION['uname'] ?? 'User',
                'email' => $_SESSION['uemail'] ?? '',
                'phone' => $_SESSION['uphone'] ?? '',
                'subject' => $subject,
                'message' => $message,
                'type' => 'support',
                'category' => $category,
                'status' => 'pending'
            ]);

            if ($inquiry) {
                $this->setFlash('success', 'Your support ticket has been submitted successfully.');
            } else {
                $this->setFlash('error', 'Failed to submit ticket. Please try again.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->redirect('/support');
    }
}
