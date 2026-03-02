<?php

namespace App\Http\Controllers\Api;

use \Exception;

class NotificationController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('csrf', ['only' => ['register', 'unregister', 'markAsRead']]);
    }

    /**
     * List recent notifications for the authenticated user
     */
    public function index()
    {
        try {
            $user = $this->auth->user();
            $limit = \min(100, (int)$this->request()->input('limit', 50));
            $offset = (int)$this->request()->input('offset', 0);

            $notificationModel = $this->model('Notification');
            $notifications = $notificationModel->getForUser($user->uid, $limit, $offset);

            return $this->jsonSuccess(['notifications' => $notifications]);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Register a device for push notifications
     */
    public function register()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();
            $token = strip_tags($this->request()->input('token'));
            $platform = strip_tags($this->request()->input('platform'));

            if (empty($token) || empty($platform)) {
                return $this->jsonError('Token and platform are required', 400);
            }

            $deviceModel = $this->model('MobileDevice');
            $existing = $deviceModel->findDevice($token, $user->uemail);

            if ($existing) {
                return $this->jsonSuccess(null, 'Device already registered');
            }

            $device = new \App\Models\MobileDevice([
                'device_user' => $user->uemail,
                'push_token' => $token,
                'platform' => $platform
            ]);

            if ($device->save()) {
                return $this->jsonSuccess(null, 'Device registered successfully', 201);
            }

            return $this->jsonError('Failed to register device');

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Unregister a device
     */
    public function unregister()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $user = $this->auth->user();
            $token = strip_tags($this->request()->input('token'));
            if (empty($token)) {
                return $this->jsonError('Token is required', 400);
            }

            $deviceModel = $this->model('MobileDevice');
            $deviceModel->unregisterDevice($token, $user->uemail);

            return $this->jsonSuccess(null, 'Device unregistered successfully');

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
