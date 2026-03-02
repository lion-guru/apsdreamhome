<?php

namespace App\Http\Controllers\Api;

class NewsletterController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('csrf', ['only' => ['subscribe']]);
    }

    /**
     * Handle newsletter subscription
     */
    public function subscribe()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Invalid request method.', 405);
        }

        $email = \trim($this->request()->input('email', ''));

        if (!$email || !\filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError('Invalid email address.');
        }

        try {
            $subscriberModel = $this->model('NewsletterSubscriber');
            $subscriber = new \App\Models\NewsletterSubscriber(['email' => $email]);
            
            if ($subscriber->save()) {
                return $this->jsonSuccess(['message' => 'Thank you for subscribing!']);
            } else {
                return $this->jsonError('Subscription failed. Please try again.');
            }
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
