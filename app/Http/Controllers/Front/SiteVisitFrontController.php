<?php

namespace App\Http\Controllers\Front;

use App\Core\Database\Database;

/**
 * Field GPS geo-checkin for site visits (anti-fake-visit proof).
 * GET  /site-visit/checkin/{id}        — open check-in page (public, like live-layout)
 * POST /site-visit/checkin/{id}/submit — needs a logged-in session + CSRF
 */
class SiteVisitFrontController extends PageController
{
    private static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        return 2 * $r * asin(min(1, sqrt($a)));
    }

    private function loadVisit(int $id): ?array
    {
        try {
            $db = Database::getInstance();
            $row = $db->fetchOne(
                "SELECT v.*, c.name AS colony_name, c.latitude AS colony_lat, c.longitude AS colony_lng
                 FROM site_visits v LEFT JOIN colonies c ON c.id = v.colony_id
                 WHERE v.id = ? LIMIT 1",
                [$id]
            );
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[SiteVisitFrontController::loadVisit] ' . $e->getMessage());
            return null;
        }
    }

    public function showCheckin($id)
    {
        $visit = $this->loadVisit((int)$id);
        if (!$visit) {
            return $this->render('pages/404', ['page_title' => 'Visit Not Found']);
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->render('pages/site_checkin', [
            'page_title' => 'Site Visit Check-in',
            'visit' => $visit,
        ]);
    }

    public function submitCheckin($id)
    {
        @session_start();
        if (empty($_SESSION['user_id']) && empty($_SESSION['admin_id'])) {
            $_SESSION['flash_error'] = 'Please log in to submit a check-in.';
            $this->redirect('/site-visit/checkin/' . (int)$id);
            return;
        }
        $id = (int)$id;
        $visit = $this->loadVisit($id);
        if (!$visit) {
            $this->redirect('/site-visit/checkin/' . $id);
            return;
        }
        $lat = filter_var($_POST['lat'] ?? null, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($_POST['lng'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $_SESSION['flash_error'] = 'Valid GPS coordinates are required (allow location access).';
            $this->redirect('/site-visit/checkin/' . $id);
            return;
        }
        $colonyLat = (float)($visit['colony_lat'] ?? 0);
        $colonyLng = (float)($visit['colony_lng'] ?? 0);
        $distKm = ($colonyLat != 0.0 && $colonyLng != 0.0)
            ? self::haversineKm($lat, $lng, $colonyLat, $colonyLng) : null;
        $verified = ($distKm !== null && $distKm <= 1.0);

        $photoPath = null;
        if (isset($_FILES['gate_photo']) && ($_FILES['gate_photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $v = \UploadValidator::validate($_FILES['gate_photo'], ['types' => 'images', 'max_size' => 5]);
            if (empty($v['valid'])) {
                $_SESSION['flash_error'] = 'Gate photo: ' . ($v['error'] ?? 'invalid file');
                $this->redirect('/site-visit/checkin/' . $id);
                return;
            }
            $dir = dirname(__DIR__, 4) . '/assets/uploads/checkins/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $safe = \UploadValidator::safeFilename($_FILES['gate_photo']['name']);
            $ext = pathinfo($safe, PATHINFO_EXTENSION);
            $filename = 'checkin_' . $id . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['gate_photo']['tmp_name'], $dir . $filename)) {
                $_SESSION['flash_error'] = 'Could not store gate photo.';
                $this->redirect('/site-visit/checkin/' . $id);
                return;
            }
            $photoPath = 'assets/uploads/checkins/' . $filename;
        }

        try {
            $db = Database::getInstance();
            $db->execute(
                "UPDATE site_visits SET checkin_lat = ?, checkin_lng = ?, checkin_time = NOW(), checkin_photo = COALESCE(?, checkin_photo)"
                . ($verified ? ", status = 'completed'" : "") . " WHERE id = ? LIMIT 1",
                [$lat, $lng, $photoPath, $id]
            );
            if ($verified) {
                // Best-effort: close the day's scheduled fleet trip for this colony.
                try {
                    $db->execute(
                        "UPDATE fleet_trips SET status = 'completed'
                         WHERE colony_id = ? AND trip_date = ? AND status = 'scheduled'",
                        [(int)($visit['colony_id'] ?? 0), (string)($visit['visit_date'] ?? '')]
                    );
                } catch (\Throwable $e) {
                    error_log('[SiteVisitFrontController fleet] ' . $e->getMessage());
                }
            }
            $_SESSION['flash_success'] = $verified
                ? sprintf('Verified visit — %.2f km from site. Marked completed.', $distKm)
                : 'Coordinates recorded, but outside the 1 km site radius — NOT verified.';
        } catch (\Throwable $e) {
            error_log('[SiteVisitFrontController::submitCheckin] ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Check-in failed. Try again.';
        }
        $this->redirect('/site-visit/checkin/' . $id);
    }
}
