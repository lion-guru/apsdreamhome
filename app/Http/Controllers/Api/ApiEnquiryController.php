<?php

namespace App\Http\Controllers\Api;

use \Exception;

class ApiEnquiryController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('csrf', ['only' => ['store']]);
    }

    public function store(): void
    {
        if ($this->request()->getMethod() !== 'POST') {
            $this->jsonError('Invalid request method.', 405);
        }

        $projectCode = $this->sanitize($this->request()->input('project_code', ''));
        $name = $this->sanitize($this->request()->input('name', ''));
        $email = $this->sanitize($this->request()->input('email', ''));
        $phone = $this->sanitize($this->request()->input('phone', ''));
        $message = $this->sanitize($this->request()->input('message', ''));

        $errors = [];

        if ($projectCode === '') {
            $errors['project_code'] = 'Missing project code.';
        }
        if ($name === '') {
            $errors['name'] = 'Name is required.';
        }
        if ($email === '' || !\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }
        if ($phone === '') {
            $errors['phone'] = 'Phone number is required.';
        }
        if ($message === '' || \mb_strlen($message) < 5) {
            $errors['message'] = 'Please share a brief message (min 5 characters).';
        }

        if (!empty($errors)) {
            $this->jsonError('Validation failed.', 422, ['errors' => $errors]);
        }

        $meta = [
            'referrer' => $this->request()->header('Referer'),
            'user_agent' => $this->request()->header('User-Agent'),
            'ip' => $this->request()->clientIp(),
        ];

        $enquiryData = [
            'project_code' => $projectCode,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'utm_source' => $this->sanitize($this->request()->input('utm_source', '')),
            'utm_medium' => $this->sanitize($this->request()->input('utm_medium', '')),
            'utm_campaign' => $this->sanitize($this->request()->input('utm_campaign', '')),
            'status' => 'new',
            'meta' => \array_filter($meta),
        ];

        try {
            $enquiryModel = $this->model('ProjectEnquiry');
            $enquiryId = $enquiryModel->createEnquiry($enquiryData);

            if (!$enquiryId) {
                throw new \Exception('Unable to save enquiry.');
            }

            $this->jsonSuccess([
                'enquiry_id' => $enquiryId,
            ], 'Enquiry submitted successfully.', 201);
        } catch (\Exception $e) {
            \error_log('ApiEnquiryController::store error - ' . $e->getMessage());
            $this->jsonError('Unable to submit enquiry at this time.', 500);
        }
    }

    private function sanitize(?string $value): string
    {
        return \trim(\filter_var($value ?? '', \FILTER_SANITIZE_SPECIAL_CHARS));
    }
}
