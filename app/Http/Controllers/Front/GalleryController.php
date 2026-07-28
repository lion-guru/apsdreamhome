<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class GalleryController extends BaseController
{
    use TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM gallery_images WHERE status = 'active' ORDER BY created_at DESC");
            $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $images = [];
        }
        $this->render('pages/photo_gallery', [
            'page_title' => 'Photo Gallery',
            'images' => $images
        ]);
    }

    public function show($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM gallery_images WHERE id = ? AND status = 'active'");
            $stmt->execute([$id]);
            $image = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $image = null;
        }
        if (!$image) {
            $this->redirect('/photo-gallery');
            return;
        }
        $this->render('pages/photo_gallery_detail', [
            'page_title' => $image['caption'] ?? 'Gallery Image',
            'image' => $image
        ]);
    }
}
