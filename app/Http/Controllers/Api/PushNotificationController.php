<?php
namespace App\Http\Controllers\Api;

class PushNotificationController extends BaseApiController
{
    public function subscribe()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            $this->json(['success' => false, 'error' => 'Login required'], 401);
            return;
        }

        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $endpoint = $body['endpoint'] ?? $_POST['endpoint'] ?? '';
        $p256dh = $body['keys']['p256dh'] ?? $_POST['keys']['p256dh'] ?? $_POST['p256dh'] ?? '';
        $auth = $body['keys']['auth'] ?? $_POST['keys']['auth'] ?? $_POST['auth'] ?? '';

        if (!$endpoint) {
            $this->json(['success' => false, 'error' => 'Endpoint required'], 400);
            return;
        }

        $service = new \App\Services\PushNotificationService();
        $result = $service->subscribe($userId, $endpoint, $p256dh, $auth);
        $this->json($result, $result['success'] ? 200 : 500);
    }

    public function unsubscribe()
    {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $endpoint = $body['endpoint'] ?? $_POST['endpoint'] ?? '';
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if (!$endpoint) {
            $this->json(['success' => false, 'error' => 'Endpoint required'], 400);
            return;
        }

        $service = new \App\Services\PushNotificationService();
        $service->unsubscribe($userId, $endpoint);
        $this->json(['success' => true]);
    }

    public function vapidPublicKey()
    {
        $key = $_ENV['VAPID_PUBLIC_KEY'] ?? '';
        $this->json(['publicKey' => $key]);
    }
}
