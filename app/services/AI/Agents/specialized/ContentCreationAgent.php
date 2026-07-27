<?php

namespace App\Services\AI\Agents\specialized;

use App\Services\AI\Agents\BaseAgent;

/**
 * ContentCreationAgent - Generates SEO-optimized real estate content.
 * Creates blog posts, property descriptions, marketing copy, and social media content.
 */
class ContentCreationAgent extends BaseAgent
{
    private static $templates = [
        'blog' => [
            'structure' => ['intro', 'body_sections', 'conclusion', 'cta'],
            'min_words' => 400,
            'max_words' => 1200,
        ],
        'listing' => [
            'structure' => ['headline', 'highlights', 'description', 'features', 'cta'],
            'min_words' => 100,
            'max_words' => 300,
        ],
        'social' => [
            'structure' => ['hook', 'value', 'cta'],
            'min_words' => 20,
            'max_words' => 100,
        ],
        'email' => [
            'structure' => ['subject', 'greeting', 'body', 'cta', 'signature'],
            'min_words' => 50,
            'max_words' => 400,
        ],
        'whatsapp' => [
            'structure' => ['greeting', 'message', 'link', 'cta'],
            'min_words' => 10,
            'max_words' => 50,
        ],
    ];

    public function __construct()
    {
        parent::__construct('CONTENT_GEN_001', 'Content Creation & SEO Agent');
    }

    public function process($input, $context = []): array
    {
        $topic   = $input['topic'] ?? 'Real Estate';
        $format  = $input['format'] ?? 'blog';
        $keywords = $input['keywords'] ?? ['property', 'investment', 'home'];
        $data    = $input['data'] ?? [];

        $this->logActivity("CONTENT_GENERATION", "Topic: $topic, Format: $format");

        try {
            switch ($format) {
                case 'blog':
                    $content = $this->generateBlogPost($topic, $keywords, $data);
                    break;
                case 'listing':
                    $content = $this->generatePropertyListing($topic, $data);
                    break;
                case 'social':
                    $content = $this->generateSocialPost($topic, $keywords, $data);
                    break;
                case 'email':
                    $content = $this->generateEmailCampaign($topic, $keywords, $data);
                    break;
                case 'whatsapp':
                    $content = $this->generateWhatsAppMessage($topic, $keywords, $data);
                    break;
                default:
                    $content = $this->generateBlogPost($topic, $keywords, $data);
            }

            $seoScore = $this->calculateSeoScore($content['body'] ?? '', $keywords);

            $this->logActivity("CONTENT_GENERATED", "Format: $format, SEO: $seoScore");

            return [
                'success'       => true,
                'content'       => $content,
                'seo_score'     => $seoScore,
                'keywords_used' => $keywords,
                'format'        => $format,
                'word_count'    => str_word_count($content['body'] ?? $content['message'] ?? ''),
            ];
        } catch (\Throwable $e) {
            $this->logActivity("CONTENT_ERROR", $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function generateBlogPost(string $topic, array $keywords, array $data): array
    {
        $kw = implode(', ', array_slice($keywords, 0, 5));
        $location = $data['location'] ?? 'Gorakhpur';
        $price = $data['price'] ?? '';

        $title = "Complete Guide to {$topic} in {$location} — APS Dream Home";
        $metaDesc = "Explore {$topic} opportunities in {$location}. {$kw}. Expert advice from APS Dream Home — your trusted real estate partner.";

        $intro = "Looking for the perfect {$topic} in {$location}? "
               . "APS Dream Home brings you a comprehensive guide to help you make an informed decision. "
               . "Whether you're a first-time buyer or a seasoned investor, understanding the local market is crucial.";

        $bodySections = [];
        $bodySections[] = "## Why {$location} for {$topic}?\n\n"
            . "{$location} has emerged as one of the fastest-growing real estate markets in Uttar Pradesh. "
            . "With excellent connectivity, developing infrastructure, and competitive prices, "
            . "it offers unmatched value for homebuyers and investors alike.";

        $bodySections[] = "## Key Factors to Consider\n\n"
            . "When exploring {$topic} options, keep these factors in mind:\n"
            . "- **Location & Connectivity** — Proximity to schools, hospitals, markets\n"
            . "- **Legal Documentation** — RERA approval, clear title, NOCs\n"
            . "- **Payment Plans** — EMI options, construction-linked, down payment\n"
            . "- **Developer Reputation** — Track record, delivery timeline, quality";

        if (!empty($price)) {
            $bodySections[] = "## Pricing Details\n\n"
                . "Starting at just {$price}, our properties offer exceptional value. "
                . "Flexible payment plans and home loan assistance make it easy to own your dream property.";
        }

        $bodySections[] = "## About APS Dream Home\n\n"
            . "APS Dream Home is a trusted name in Gorakhpur's real estate market. "
            . "With multiple successful colonies and hundreds of satisfied families, "
            . "we are committed to delivering quality and transparency.";

        $conclusion = "Don't miss out on these opportunities. Contact APS Dream Home today for a site visit "
                    . "and take the first step toward your dream {$topic}.";

        $body = $intro . "\n\n" . implode("\n\n", $bodySections) . "\n\n" . $conclusion;

        return [
            'title'          => $title,
            'meta_desc'      => $metaDesc,
            'body'           => $body,
            'section_count'  => count($bodySections) + 2,
        ];
    }

    private function generatePropertyListing(string $topic, array $data): array
    {
        $title = $data['title'] ?? "Premium {$topic} in " . ($data['location'] ?? 'Gorakhpur');
        $price = $data['price'] ?? 'Contact for price';
        $area = $data['area'] ?? '';
        $location = $data['location'] ?? 'Gorakhpur';
        $features = $data['features'] ?? ['RERA Approved', 'Gated Community', '24/7 Security', 'Vastu Compliant'];

        $headline = "🏠 {$title} — Starting at {$price}";
        $highlights = "📍 {$location}" . ($area ? " | 📐 {$area}" : "");

        $description = "Explore this premium {$topic} in {$location}. "
            . "Offering world-class amenities and excellent connectivity. "
            . "RERA approved with clear documentation. "
            . "Perfect for families looking for their dream home.";

        $featureList = implode(' | ', array_slice($features, 0, 6));

        return [
            'headline'     => $headline,
            'highlights'   => $highlights,
            'description'  => $description,
            'features'     => $featureList,
            'body'         => $headline . "\n" . $highlights . "\n\n" . $description,
        ];
    }

    private function generateSocialPost(string $topic, array $keywords, array $data): array
    {
        $location = $data['location'] ?? 'Gorakhpur';
        $price = $data['price'] ?? '';

        $hooks = [
            "Your dream {$topic} in {$location} is just a call away! 🏡",
            "Invest in {$location}'s booming real estate market! 📈",
            "Premium {$topic}s available at APS Dream Home — Limited stock! 🔥",
        ];

        $hook = $hooks[array_rand($hooks)];
        $value = "✅ RERA Approved\n✅ Flexible Payment Plans\n✅ Prime Location\n✅ Modern Amenities";
        $cta = "📞 Call: 7007444842\n🌐 Visit: apsdreamhome.com";

        $message = "{$hook}\n\n{$value}\n\n{$cta}";

        return [
            'message'   => $message,
            'hook'      => $hook,
            'body'      => $message,
            'platforms' => ['facebook', 'instagram', 'linkedin'],
        ];
    }

    private function generateEmailCampaign(string $topic, array $keywords, array $data): array
    {
        $topic = $data['topic'] ?? $topic;
        $name = $data['name'] ?? 'Customer';

        $subject = "Exclusive {$topic} Opportunities — APS Dream Home";
        $greeting = "Dear {$name},";
        $body = "We're excited to share some amazing {$topic} opportunities with you.\n\n"
              . "APS Dream Home has launched new projects in prime locations across Gorakhpur. "
              . "With flexible payment plans, RERA-approved projects, and world-class amenities, "
              . "now is the perfect time to invest.\n\n"
              . "Key Highlights:\n"
              . "- Premium locations with excellent connectivity\n"
              . "- 2 & 3 BHK options starting from competitive prices\n"
              . "- Construction-linked payment plans\n"
              . "- Home loan assistance available\n\n";
        $cta = "📞 Schedule a Free Site Visit: 7007444842";
        $signature = "Team APS Dream Home\nwww.apsdreamhome.com";

        return [
            'subject'    => $subject,
            'greeting'   => $greeting,
            'body'       => $body,
            'cta'        => $cta,
            'signature'  => $signature,
            'body_full'  => "{$greeting}\n\n{$body}{$cta}\n\n{$signature}",
        ];
    }

    private function generateWhatsAppMessage(string $topic, array $keywords, array $data): array
    {
        $location = $data['location'] ?? 'Gorakhpur';
        $price = $data['price'] ?? '';

        $greeting = "Namaste! 🙏";
        $message = "APS Dream Home presents premium {$topic}s in {$location}."
            . ($price ? " Starting at {$price}." : "")
            . " RERA approved | Flexible EMI | Prime location.";
        $link = "🌐 apsdreamhome.com";
        $cta = "📞 Reply or Call: 7007444842";

        return [
            'greeting'  => $greeting,
            'message'   => $message,
            'link'      => $link,
            'cta'       => $cta,
            'body'      => "{$greeting}\n\n{$message}\n\n{$link}\n{$cta}",
        ];
    }

    private function calculateSeoScore(string $content, array $keywords): int
    {
        $score = 50;
        $lower = mb_strtolower($content, 'UTF-8');

        foreach ($keywords as $kw) {
            $count = mb_substr_count($lower, mb_strtolower($kw, 'UTF-8'), 'UTF-8');
            if ($count >= 1) $score += 5;
            if ($count >= 3) $score += 5;
        }

        $wordCount = str_word_count($content);
        if ($wordCount >= 300) $score += 10;
        if ($wordCount >= 600) $score += 5;

        if (preg_match('/^#+\s/m', $content)) $score += 5;

        return min($score, 98);
    }
}
