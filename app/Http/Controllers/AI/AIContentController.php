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

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Accept form-encoded or JSON bodies (views post JSON.stringify payloads),
     * unwrapping a nested {details: {...}} envelope when present.
     */
    private function readInput(): array
    {
        $input = $_POST;
        if (empty($input)) {
            $json = json_decode(file_get_contents('php://input') ?: '', true);
            if (is_array($json)) {
                $input = $json;
            }
        }
        if (isset($input['details']) && is_array($input['details'])) {
            $input = $input['details'] + $input;
        }
        return $input;
    }

    /**
     * POST /ai/content/description
     * Fields: name, location, price, area_sqft, colony, type, highlights (any subset)
     * Returns: { success, description }
     */
    public function generateDescription()
    {
        header('Content-Type: application/json');

        $input = $this->readInput();

        $property = [
            'name'        => trim($input['name'] ?? ($input['title'] ?? '')),
            'location'    => trim($input['location'] ?? ($input['city'] ?? '')),
            'price'       => trim($input['price'] ?? ($input['total_price'] ?? '')),
            'area_sqft'   => trim($input['area_sqft'] ?? ($input['area'] ?? '')),
            'colony'      => trim($input['colony'] ?? ''),
            'type'        => trim($input['type'] ?? ($input['property_type'] ?? 'plot')),
            'highlights'  => array_filter(array_map('trim', explode(',', $input['highlights'] ?? ''))),
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

        $input = $this->readInput();
        $topic = trim($input['topic'] ?? ($input['title'] ?? ''));
        $category = trim($input['category'] ?? '');

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

        $input = $this->readInput();
        $filename = trim($input['filename'] ?? '');

        $ctx = [
            'title'    => trim($input['title'] ?? ($input['name'] ?? '')),
            'type'     => trim($input['type'] ?? ($input['property_type'] ?? 'property')),
            'location' => trim($input['location'] ?? ($input['city'] ?? ($input['colony'] ?? ''))),
            'colony'   => trim($input['colony'] ?? ''),
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
