<?php

namespace App\Services;

use App\DTO\ProjectMicrositeDTO;
use App\Models\Project;

class MicrositeAssembler
{
    private Project $projectModel;

    public function __construct(?Project $projectModel = null)
    {
        $this->projectModel = $projectModel ?? new Project();
    }

    public function assembleByCode(string $projectCode): ?ProjectMicrositeDTO
    {
        $project = $this->projectModel->getProjectByCode($projectCode);

        if (!$project) {
            return null;
        }

        $decodedProject = $this->decodeProject($project);

        $dtoPayload = [
            'project' => $this->mapProject($decodedProject),
            'location' => $this->mapLocation($decodedProject),
            'developer' => $this->mapDeveloper($decodedProject),
            'media' => $this->mapMedia($decodedProject),
            'highlights' => $this->mapHighlights($decodedProject),
            'cta' => $this->mapCta($decodedProject),
            'meta' => $this->mapMeta($decodedProject),
            'related' => $this->mapRelated($decodedProject),
            'theme' => $this->mapTheme($decodedProject),
        ];

        return new ProjectMicrositeDTO($dtoPayload);
    }

    private function decodeProject(array $project): array
    {
        $project['amenities'] = $this->decodeJsonArray($project['amenities'] ?? null);
        $project['highlights'] = $this->decodeJsonArray($project['highlights'] ?? null);
        $project['gallery_images'] = $this->decodeJsonArray($project['gallery_images'] ?? null);

        return $project;
    }

    private function decodeJsonArray(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $decoded = \json_decode($value, true);

        return \is_array($decoded) ? $decoded : [];
    }

    private function mapProject(array $project): array
    {
        return [
            'id' => $project['project_id'] ?? null,
            'code' => $project['project_code'] ?? null,
            'name' => $project['project_name'] ?? '',
            'type' => $project['project_type'] ?? '',
            'status' => $project['project_status'] ?? '',
            'possession_date' => $project['possession_date'] ?? null,
            'rera_number' => $project['rera_number'] ?? null,
            'total_area' => $project['total_area'] ?? null,
            'total_plots' => $project['total_plots'] ?? null,
            'available_plots' => $project['available_plots'] ?? null,
            'price_per_sqft' => $project['price_per_sqft'] ?? null,
            'base_price' => $project['base_price'] ?? null,
            'booking_amount' => $project['booking_amount'] ?? null,
            'emi_available' => (bool)($project['emi_available'] ?? false),
            'summary' => $project['short_description'] ?? '',
            'description' => $project['description'] ?? '',
            'is_featured' => (bool)($project['is_featured'] ?? false),
        ];
    }

    private function mapLocation(array $project): array
    {
        return [
            'address' => $project['address'] ?? '',
            'city' => $project['city'] ?? '',
            'state' => $project['state'] ?? '',
            'pincode' => $project['pincode'] ?? '',
            'latitude' => $project['latitude'] ?? null,
            'longitude' => $project['longitude'] ?? null,
            'landmarks' => $project['location_landmarks'] ?? [],
        ];
    }

    private function mapDeveloper(array $project): array
    {
        return [
            'name' => $project['developer_name'] ?? 'APS Dream Homes Pvt Ltd',
            'contact' => $project['developer_contact'] ?? $project['contact_number'] ?? '',
            'email' => $project['developer_email'] ?? $project['contact_email'] ?? '',
            'project_head' => $project['project_head'] ?? '',
            'project_manager' => $project['project_manager'] ?? '',
            'sales_manager' => $project['sales_manager'] ?? '',
            'website' => $project['website'] ?? '',
        ];
    }

    private function mapMedia(array $project): array
    {
        $gallery = $project['gallery_images'] ?? [];
        $heroImage = $project['hero_image'] ?? ($gallery[0]['url'] ?? $gallery[0] ?? null);

        $baseUrl = \defined('BASE_URL') ? \rtrim(BASE_URL, '/') : '';

        return [
            'hero' => $heroImage ? $this->absoluteAsset($heroImage) : $baseUrl . '/public/assets/images/projects/placeholder-hero.jpg',
            'gallery' => \array_map([$this, 'normalizeGalleryItem'], $gallery),
            'virtual_tour' => $project['virtual_tour'] ?? null,
            'layout_map' => $this->absoluteAsset($project['layout_map'] ?? null),
            'brochure' => $this->absoluteAsset($project['brochure'] ?? null),
            'video' => $project['microsite_video'] ?? null,
        ];
    }

    private function mapHighlights(array $project): array
    {
        return [
            'amenities' => $project['amenities'] ?? [],
            'highlights' => $project['highlights'] ?? [],
            'awards' => $project['project_awards'] ?? [],
            'usp' => $project['unique_selling_points'] ?? [],
        ];
    }

    private function mapCta(array $project): array
    {
        $baseUrl = \defined('BASE_URL') ? \rtrim(BASE_URL, '/') : '';
        $projectCode = $project['project_code'] ?? '';

        return [
            'enquiry' => [
                'endpoint' => $baseUrl . '/api/enquiry',
                'default_subject' => 'Project enquiry: ' . ($project['project_name'] ?? ''),
                'project_code' => $projectCode,
            ],
            'book_visit' => [
                'endpoint' => $baseUrl . '/api/visit-booking',
                'project_code' => $projectCode,
            ],
            'download_brochure' => $this->absoluteAsset($project['brochure'] ?? null),
            'whatsapp' => $project['whatsapp_link'] ?? null,
            'phone' => $project['contact_number'] ?? null,
            'email' => $project['contact_email'] ?? null,
        ];
    }

    private function mapMeta(array $project): array
    {
        $title = $project['seo_title'] ?? ($project['project_name'] ?? '') . ' | APS Dream Home';
        $description = $project['seo_description'] ?? \substr(\strip_tags($project['short_description'] ?? $project['description'] ?? ''), 0, 160);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $project['seo_keywords'] ?? '',
            'image' => $this->absoluteAsset($project['meta_image'] ?? null) ?: ($project['hero_image'] ?? null),
            'breadcrumbs' => [
                ['label' => 'Projects', 'url' => '/projects'],
                ['label' => $project['project_name'] ?? 'Project'],
            ],
        ];
    }

    private function mapRelated(array $project): array
    {
        $city = $project['city'] ?? null;
        $projectId = $project['project_id'] ?? null;

        if (!$city) {
            return [];
        }

        $related = $this->projectModel->getProjectsByCity($city);

        return \array_values(\array_filter($related, static function ($item) use ($projectId) {
            return ($item['project_id'] ?? null) !== $projectId;
        }));
    }

    private function mapTheme(array $project): array
    {
        $type = \strtolower($project['project_type'] ?? '');

        $palette = match ($type) {
            'villa' => ['primary' => '#4e73df', 'accent' => '#f6c23e'],
            'apartment' => ['primary' => '#1cc88a', 'accent' => '#36b9cc'],
            'commercial' => ['primary' => '#2e59d9', 'accent' => '#17a673'],
            default => ['primary' => '#4e73df', 'accent' => '#1cc88a'],
        };

        return [
            'palette' => $palette,
            'hero_variant' => $project['microsite_hero_variant'] ?? 'default',
        ];
    }

    private function normalizeGalleryItem($item): array
    {
        if (is_array($item)) {
            return [
                'url' => $this->absoluteAsset($item['url'] ?? null),
                'alt' => $item['alt'] ?? ($item['caption'] ?? ''),
                'caption' => $item['caption'] ?? '',
            ];
        }

        $url = $this->absoluteAsset($item);

        return [
            'url' => $url,
            'alt' => '',
            'caption' => '',
        ];
    }

    private function absoluteAsset(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0) {
            return $path;
        }

        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $normalized = '/' . ltrim($path, '/');

        return $baseUrl . $normalized;
    }
}
