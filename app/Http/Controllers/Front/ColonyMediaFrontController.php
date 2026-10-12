<?php

namespace App\Http\Controllers\Front;

use App\Core\Database\Database;

/**
 * Public colony media gallery (drone/site/3D/embeds) + WhatsApp share.
 * Route: GET /colony/{id}/media-hub
 */
class ColonyMediaFrontController extends PageController
{
    public function show($id)
    {
        $id = (int)$id;
        try {
            $db = Database::getInstance();
            $colony = $db->fetchOne(
                "SELECT id, name FROM colonies WHERE id = ? AND is_active = 1 LIMIT 1",
                [$id]
            );
            $rows = $colony ? ($db->fetchAll(
                "SELECT * FROM colony_media WHERE colony_id = ? ORDER BY is_featured DESC, id DESC",
                [$id]
            ) ?: []) : [];
        } catch (\Throwable $e) {
            error_log('[ColonyMediaFrontController::show] ' . $e->getMessage());
            $colony = null;
            $rows = [];
        }
        if (!$colony) {
            return $this->render('pages/404', ['page_title' => 'Colony Not Found']);
        }
        $this->render('pages/colony_media', [
            'page_title' => ($colony['name'] ?? '') . ' — Media Gallery',
            'colony' => $colony,
            'rows' => $rows,
        ]);
    }
}
