<?php

namespace App\Http\Controllers\Api;

class SeoController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get SEO metadata for a URL
     */
    public function getMetadata()
    {
        try {
            $url = $_GET['url'] ?? '/';
            $pageName = trim($_GET['page'] ?? 'home');

            $db = \App\Core\Database\Database::getInstance();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM seo_metadata WHERE page_name = ? LIMIT 1");
            $stmt->execute([$pageName]);
            $metadata = $stmt->fetch(\PDO::FETCH_ASSOC);

            $data = [
                'title' => $metadata['meta_title'] ?? 'APS Dream Home - Premium Real Estate',
                'description' => $metadata['meta_description'] ?? 'Find your dream home in Lucknow and Gorakhpur with APS Dream Home.',
                'og_title' => $metadata['og_title'] ?? $metadata['meta_title'] ?? 'APS Dream Home',
                'og_description' => $metadata['og_description'] ?? $metadata['meta_description'] ?? '',
                'og_image' => $metadata['og_image'] ?? 'https://apsdreamhome.com/images/logo/aps.png',
                'twitter_card' => 'summary_large_image',
                'canonical' => $metadata['canonical_url'] ?? $url,
                'robots' => $metadata['robots'] ?? 'index, follow'
            ];

            return $this->jsonSuccess($data);
        } catch (\Throwable $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Update SEO metadata (Admin only)
     */
    public function update()
    {
        $this->requireAdmin();

        try {
            $pageName = $_POST['page_name'] ?? '';
            if (empty($pageName)) {
                return $this->jsonError('Page name is required', 400);
            }

            $db = \App\Core\Database\Database::getInstance();
            $conn = $db->getConnection();

            $stmt = $conn->prepare("SELECT id FROM seo_metadata WHERE page_name = ? LIMIT 1");
            $stmt->execute([$pageName]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

            $data = [
                'meta_title' => strip_tags($_POST['meta_title'] ?? ''),
                'meta_description' => strip_tags($_POST['meta_description'] ?? ''),
                'meta_keywords' => \App\Core\Security::sanitize($_POST['meta_keywords'] ?? ''),
                'og_title' => strip_tags($_POST['og_title'] ?? ''),
                'og_description' => strip_tags($_POST['og_description'] ?? ''),
                'og_image' => filter_var($_POST['og_image'] ?? '', FILTER_SANITIZE_URL),
                'canonical_url' => filter_var($_POST['canonical_url'] ?? '', FILTER_SANITIZE_URL),
                'robots' => in_array(($_POST['robots'] ?? ''), ['index, follow', 'noindex, nofollow', 'index, nofollow', 'noindex, follow']) ? $_POST['robots'] : 'index, follow'
            ];

            if ($existing) {
                $sets = [];
                $params = [];
                foreach ($data as $col => $val) {
                    $sets[] = "$col = ?";
                    $params[] = $val;
                }
                $params[] = $existing['id'];
                try {
                    $conn->prepare("UPDATE seo_metadata SET " . implode(', ', $sets) . " WHERE id = ?")->execute($params);
                } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
                error_log($e->getMessage());
                }
            } else {
                $cols = array_keys($data);
                $placeholders = rtrim(str_repeat('?,', count($cols)), ',');
                $vals = array_values($data);
                $conn->prepare("INSERT INTO seo_metadata (page_name, " . implode(',', $cols) . ") VALUES (?, $placeholders)")->execute(array_merge([$pageName], $vals));
            }

            return $this->jsonSuccess(null, 'SEO metadata updated successfully');
        } catch (\Throwable $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
