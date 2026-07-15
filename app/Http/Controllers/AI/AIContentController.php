<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\BaseController;
use App\Services\AI\MarketingContentGenerator;
use App\Services\AI\AIImageTagger;

/**
 * AIContentController — AJAX endpoints that generate marketing/CMS copy with AI.
 *
 * - generateDescription(): property listing description (used by list-property + admin forms)
 * - generateBlogDraft():  Hindi + English blog draft (used by blog create form)
 * - generateImageTags():  SEO alt text + tags for an uploaded image (filename + context)
 *
 * Uses AIGateway (Ollama local → Gemini cloud → template fallback).
 */
class AIContentController extends BaseController
{
    private $generator;

    public function __construct()
    {
        parent::__construct();
        $this->generator = new MarketingContentGenerator();
    }

    /**
     * POST /ai/content/description
     * Fields: name, location, price, area_sqft, colony, type, highlights (any subset)
     * Returns: { success, description }
     */
    public function generateDescription()
    {
        header('Content-Type: application/json');

        $property = [
            'name'        => trim($_POST['name'] ?? ($_POST['title'] ?? '')),
            'location'    => trim($_POST['location'] ?? ($_POST['city'] ?? '')),
            'price'       => trim($_POST['price'] ?? ($_POST['total_price'] ?? '')),
            'area_sqft'   => trim($_POST['area_sqft'] ?? ($_POST['area'] ?? '')),
            'colony'      => trim($_POST['colony'] ?? ''),
            'type'        => trim($_POST['type'] ?? ($_POST['property_type'] ?? 'plot')),
            'highlights'  => array_filter(array_map('trim', explode(',', $_POST['highlights'] ?? ''))),
        ];

        try {
            $description = $this->generator->generateListingDescription($property);
            echo json_encode(['success' => true, 'description' => $description]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'AI generation failed', 'description' => '']);
        }
        exit;
    }

    /**
     * POST /ai/content/blog-draft
     * Fields: topic, category
     * Returns: { success, hindi, english, excerpt, tags }
     */
    public function generateBlogDraft()
    {
        header('Content-Type: application/json');

        $topic = trim($_POST['topic'] ?? ($_POST['title'] ?? ''));
        $category = trim($_POST['category'] ?? '');

        if (empty($topic)) {
            echo json_encode(['success' => false, 'error' => 'Topic required']);
            exit;
        }

        try {
            $draft = $this->generator->generateBlogDraft($topic, $category);
            echo json_encode([
                'success'   => true,
                'hindi'     => $draft['hindi'],
                'english'   => $draft['english'],
                'excerpt'   => $draft['excerpt'],
                'tags'      => $draft['tags'],
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'AI generation failed']);
        }
        exit;
    }

    /**
     * POST /ai/content/image-tags
     * Fields: title, type, location, colony, filename
     * Returns: { success, alt_text, tags }
     */
    public function generateImageTags()
    {
        header('Content-Type: application/json');

        $filename = trim($_POST['filename'] ?? '');

        $ctx = [
            'title'    => trim($_POST['title'] ?? ($_POST['name'] ?? '')),
            'type'     => trim($_POST['type'] ?? ($_POST['property_type'] ?? 'property')),
            'location' => trim($_POST['location'] ?? ($_POST['city'] ?? ($_POST['colony'] ?? ''))),
            'colony'   => trim($_POST['colony'] ?? ''),
        ];

        try {
            $tagger = new AIImageTagger();
            $result = $tagger->generateAltText($ctx, $filename);
            echo json_encode([
                'success'  => true,
                'alt_text' => $result['alt_text'],
                'tags'     => $result['tags'],
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'AI tagging failed', 'alt_text' => '', 'tags' => []]);
        }
        exit;
    }
}
