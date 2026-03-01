<?php

namespace App\Http\Controllers\Api;

use App\Models\Property;
use \Exception;

class SeoController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['only' => ['update']]);
        $this->middleware('role:admin', ['only' => ['update']]);
        $this->middleware('csrf', ['only' => ['update']]);
    }

    /**
     * Get SEO metadata for a URL
     */
    public function getMetadata()
    {
        try {
            $url = $this->request()->input('url', '/');
            $pageName = \trim($this->request()->input('page', 'home'));

            $seoModel = $this->model('SeoMetadata');
            $metadata = $seoModel->getByPage($pageName);

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

            $propertyModel = $this->model('Property');

            // If it's a property page, fetch property details to override
            if (\preg_match('/property-detail\.php\?id=(\d+)/', $url, $matches) || \preg_match('/\/properties\/(\d+)/', $url, $matches)) {
                $id = $matches[1];
                $prop = $propertyModel->find($id);

                if ($prop) {
                    $data['title'] = $prop->title . ' | APS Dream Home';
                    $data['description'] = \substr(\strip_tags($prop->description), 0, 160);
                    $data['og_title'] = $prop->title;
                    $data['og_description'] = $data['description'];
                    // Use first property image for OG if available
                    $images = $this->db->fetchAll("SELECT image_path FROM property_images WHERE property_id = ? LIMIT 1", [$id]);
                    if (!empty($images)) {
                        $data['og_image'] = 'https://apsdreamhome.com/' . $images[0]['image_path'];
                    }
                }
            }

            return $this->jsonSuccess($data);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Update SEO metadata (Admin only)
     */
    public function update()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $pageName = $this->request()->input('page_name', '');
            if (empty($pageName)) {
                return $this->jsonError('Page name is required', 400);
            }

            $seoModel = $this->model('SeoMetadata');
            $existing = $seoModel->getByPage($pageName);

            $data = [
                'page_name' => $pageName,
                'meta_title' => $this->request()->input('meta_title'),
                'meta_description' => $this->request()->input('meta_description'),
                'meta_keywords' => $this->request()->input('meta_keywords'),
                'og_title' => $this->request()->input('og_title'),
                'og_description' => $this->request()->input('og_description'),
                'og_image' => $this->request()->input('og_image'),
                'canonical_url' => $this->request()->input('canonical_url'),
                'robots' => $this->request()->input('robots', 'index, follow')
            ];

            if ($existing) {
                $seo = new \App\Models\SeoMetadata($existing);
                $seo->fill($data);
            } else {
                $seo = new \App\Models\SeoMetadata($data);
            }

            if ($seo->save()) {
                return $this->jsonSuccess(null, 'SEO metadata updated successfully');
            }

            return $this->jsonError('Failed to update SEO metadata', 500);

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
