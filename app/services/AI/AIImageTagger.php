<?php

namespace App\Services\AI;

/**
 * AIImageTagger — generates SEO-friendly alt text + tags for property/CMS images.
 *
 * NOTE: The self-hosted Ollama model (llama3.2:3b) is text-only, so this uses
 * the image filename + the property/listing context (type, location, title) to
 * produce a meaningful description rather than true computer-vision analysis.
 * When a vision model is later available it can be swapped in here.
 */
class AIImageTagger
{
    private $engines;

    public function __construct()
    {
        $this->engines = FreeAIEngines::getInstance();
    }

    /**
     * @param array  $ctx      Keys: title, type, location, colony (any subset)
     * @param string $filename Original uploaded filename (e.g. plot_front.jpg)
     * @return array ['alt_text' => string, 'tags' => array]
     */
    public function generateAltText(array $ctx, string $filename = ''): array
    {
        $title    = $ctx['title'] ?? ($ctx['name'] ?? '');
        $type     = $ctx['type'] ?? ($ctx['property_type'] ?? 'property');
        $location = $ctx['location'] ?? ($ctx['city'] ?? ($ctx['colony'] ?? 'Gorakhpur'));
        $hint     = $this->inferView($filename);

        $prompt = "Generate a concise, SEO-friendly image alt text (max 120 chars) for a real estate photo. "
            . "Context: type=$type, location=$location, title=" . ($title ?: 'property')
            . ($hint ? ", likely view: $hint" : "")
            . ". Also return 4 comma-separated SEO tags. Respond JSON: "
            . "{\"alt_text\":\"...\",\"tags\":[\"...\",\"...\",\"...\",\"...\"]}. English only.";

        try {
            $res = $this->engines->generate($prompt, ['temperature' => 0.4, 'max_tokens' => 250], 'seo');
            $text = $res['text'] ?? '';
            $start = strpos($text, '{');
            $end = strrpos($text, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $jsonText = substr($text, $start, $end - $start + 1);
                $jsonText = preg_replace('/([{,]\s*)([A-Za-z_][A-Za-z0-9_]*)\s*:/', '$1"$2":', $jsonText);
                $json = json_decode($jsonText, true);
                if (is_array($json) && !empty($json['alt_text'])) {
                    $tags = $json['tags'] ?? [];
                    if (is_string($tags)) {
                        $tags = array_map('trim', explode(',', $tags));
                    }
                    $tags = array_filter(array_map('trim', (array)$tags));
                    if (empty($tags)) $tags = ['real estate', $type, $location, 'APS Dream Home'];
                    return ['alt_text' => $json['alt_text'], 'tags' => array_slice($tags, 0, 4)];
                }
            }
        } catch (\Throwable $e) {
            // fall through to template
        }

        $alt = ucfirst($type) . ' in ' . $location . ($title ? ' — ' . $title : '');
        if ($hint) $alt .= ' (' . $hint . ')';
        return [
            'alt_text' => $alt,
            'tags'    => ['real estate', $type, $location, 'APS Dream Home'],
        ];
    }

    private function inferView(string $filename): string
    {
        $f = strtolower($filename);
        $map = [
            'front' => 'front/exterior view', 'gate' => 'main gate',
            'plot' => 'plot layout', 'map' => 'location map',
            'interior' => 'interior', 'kitchen' => 'kitchen',
            'bedroom' => 'bedroom', 'hall' => 'living hall',
            'parking' => 'parking', 'garden' => 'garden/landscape',
            'amenity' => 'amenity', 'construction' => 'construction progress',
        ];
        foreach ($map as $k => $v) {
            if (strpos($f, $k) !== false) return $v;
        }
        return '';
    }
}
