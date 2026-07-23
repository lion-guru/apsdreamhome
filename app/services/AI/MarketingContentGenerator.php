<?php

namespace App\Services\AI;

/**
 * MarketingContentGenerator — AI-powered ad copy and marketing content
 *
 * Generates:
 * - Property advertisement copy (WhatsApp, Facebook, Instagram)
 * - Listing descriptions
 * - Social media posts
 * - Email subject lines
 * - Lead nurture messages
 *
 * Uses AIGateway for AI generation (falls back to templates).
 */

class MarketingContentGenerator
{
    private $aiGateway;

    public function __construct()
    {
        $this->aiGateway = AIGateway::getInstance();
    }

    /**
     * Generate ad copy for a property listing
     *
     * @param array $property  Keys: name, location, price, area_sqft, bedrooms, colony, type, highlights
     * @param string $platform whatsapp|facebook|instagram|all
     * @return array ['ad_copy' => string, 'platform' => string, 'hashtags' => array]
     */
    public function generatePropertyAd(array $property, string $platform = 'all'): array
    {
        $prompt = $this->buildPropertyAdPrompt($property, $platform);
        $result = $this->aiGateway->process($prompt, [], ['type' => 'ad_copy']);

        $adCopy = $result['response'] ?? $this->generateTemplateAd($property, $platform);

        // Add hashtags for social platforms
        $hashtags = $this->generateHashtags($property);

        return [
            'ad_copy' => $adCopy,
            'platform' => $platform,
            'hashtags' => $hashtags,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate a listing description
     */
    public function generateListingDescription(array $property): string
    {
        $prompt = "Write a compelling property listing description for: " . json_encode($property);
        $result = $this->aiGateway->process($prompt, [], ['type' => 'ad_copy']);

        return $result['response'] ?? $this->templateListingDescription($property);
    }

    /**
     * Generate social media post for a colony/project
     */
    public function generateColonyPost(array $colony): array
    {
        $prompt = "Generate a social media post promoting colony {$colony['name']} in {$colony['location']}. " .
                  "Starting price: {$colony['starting_price']}. " .
                  "Available plots: {$colony['available_plots']}. " .
                  "Key features: " . implode(', ', $colony['features'] ?? []);

        $result = $this->aiGateway->process($prompt, [], ['type' => 'ad_copy']);
        $hashtags = ['#RealEstate', '#PlotsForSale', '#Gorakhpur', '#DreamHome', '#Investment'];

        return [
            'post' => $result['response'] ?? $this->templateColonyPost($colony),
            'hashtags' => $hashtags,
        ];
    }

    /**
     * Generate email subject lines for a campaign
     */
    public function generateEmailSubjects(string $topic, int $count = 5): array
    {
        $prompt = "Generate $count catchy email subject lines for a real estate marketing email about: $topic. " .
                  "Keep each under 60 characters. Return as JSON array.";

        $result = $this->aiGateway->process($prompt, [], ['type' => 'ad_copy']);

        $subjects = json_decode($result['response'] ?? '', true);
        if (!is_array($subjects)) {
            $subjects = [
                "Discover Your Dream Home in Gorakhpur",
                "Premium Plots at Unbeatable Prices",
                "Your Investment Starts Here — Limited Plots Available",
                "Why Smart Investors Are Choosing Gorakhpur",
                "Book Your Site Visit Today — Zero Brokerage",
            ];
        }

        return array_slice($subjects, 0, $count);
    }

    /**
     * Generate a blog post draft in both Hindi and English for a given topic.
     *
     * @return array ['hindi' => string, 'english' => string, 'excerpt' => string, 'tags' => array]
     */
    public function generateBlogDraft(string $topic, string $category = ''): array
    {
        $ctx = $category ? "Category: $category. " : '';
        $enPrompt = $ctx . "Write a 350-450 word English blog post about: $topic. "
            . "Audience: real estate buyers/investors in Gorakhpur, UP, India. "
            . "Professional but friendly tone. Include a short heading, 3-4 paragraphs, and a concluding CTA to contact APS Dream Home. Return only the article body.";
        $hiPrompt = $ctx . "Hindi (Devanagari) mein lagbhag 350-450 shabdon ka blog post likho vishay: $topic. "
            . "Audience: Gorakhpur, UP ke real estate kharidtaa/investor. Professional lekin friendly tone. "
            . "Ek chota heading, 3-4 paragraphs, aur ant mein APS Dream Home se sampark ka CTA. Sirf article body do.";

        $en = $this->aiGateway->process($enPrompt, [], ['type' => 'blog'])['response']
            ?? $this->templateBlog($topic, 'en');
        $hi = $this->aiGateway->process($hiPrompt, [], ['type' => 'blog'])['response']
            ?? $this->templateBlog($topic, 'hi');

        $excerpt = $this->makeExcerpt($en);
        $tags = ['#RealEstate', '#Gorakhpur', '#Property', '#Investment', '#DreamHome'];

        return [
            'hindi'   => $hi,
            'english' => $en,
            'excerpt' => $excerpt,
            'tags'    => $tags,
        ];
    }

    private function makeExcerpt(string $text, int $len = 160): string
    {
        $text = preg_replace('/\s+/', ' ', strip_tags($text));
        return mb_substr(trim($text), 0, $len) . (mb_strlen($text) > $len ? '…' : '');
    }

    private function templateBlog(string $topic, string $lang): string
    {
        if ($lang === 'hi') {
            return "आज हम बात करेंगे: {$topic}। गोरखपुर में रियल एस्टेट निवेश का सही समय है। "
                . "स्पष्ट टाइटल, बेहतरीन लोकेशन और आसान भुगतान विकल्प APS Dream Home के साथ उपलब्ध हैं। "
                . "अधिक जानकारी के लिए हमसे संपर्क करें।";
        }
        return "Today we explore: $topic. Gorakhpur's real estate market offers clear titles, "
            . "prime locations, and easy payment plans with APS Dream Home. Contact us to learn more.";
    }

    /**
     * Generate WhatsApp broadcast message for new inventory
     */
    public function generateBroadcastMessage(array $colony, string $messageType = 'new_inventory'): string
    {
        $prompts = [
            'new_inventory' => "Write a short WhatsApp broadcast message (under 150 words, Hindi+English mix) " .
                "announcing new plot availability in {$colony['name']}, {$colony['location']}. " .
                "Price starting from {$colony['starting_price']}. Create urgency.",
            'price_update' => "Write a short WhatsApp broadcast about price update for {$colony['name']}. " .
                "Announce that prices will increase from next month. Create urgency.",
            'site_visit' => "Write a short WhatsApp broadcast inviting people for a free site visit to {$colony['name']}. " .
                "Mention free transport and refreshments.",
        ];

        $prompt = $prompts[$messageType] ?? $prompts['new_inventory'];
        $result = $this->aiGateway->process($prompt, [], ['type' => 'ad_copy']);

        return $result['response'] ?? $this->templateBroadcastMessage($colony, $messageType);
    }

    // ─── Private helpers ──────────────────────────────────────────

    private function buildPropertyAdPrompt(array $property, string $platform): string
    {
        $pName = $property['name'] ?? 'Premium Plot';
        $pLoc = $property['location'] ?? 'Gorakhpur';
        $pPrice = $property['price'] ?? 'Contact for price';

        $desc = "Property: {$pName}";
        $desc .= "\nLocation: {$pLoc}";
        $desc .= "\nPrice: {$pPrice}";
        if (!empty($property['area_sqft'])) $desc .= "\nArea: {$property['area_sqft']} sq ft";
        if (!empty($property['colony'])) $desc .= "\nColony: {$property['colony']}";
        if (!empty($property['highlights'])) $desc .= "\nHighlights: " . implode(', ', $property['highlights']);

        $platformGuide = '';
        if ($platform === 'whatsapp') {
            $platformGuide = 'Write in Hinglish (Hindi+English mix). Keep under 100 words. Use emojis. End with CTA.';
        } elseif ($platform === 'facebook') {
            $platformGuide = 'Write in English. Keep under 200 words. Professional tone. Include location details.';
        } elseif ($platform === 'instagram') {
            $platformGuide = 'Write in English. Short punchy text. Use line breaks. 10-15 relevant hashtags.';
        } else {
            $platformGuide = 'Write in Hinglish. Under 120 words. Use emojis. Include CTA and contact info.';
        }

        return "Generate a real estate ad copy.\n$desc\n\n$platformGuide";
    }

    private function generateHashtags(array $property): array
    {
        $tags = ['#RealEstate', '#PlotsForSale', '#Investment'];

        if (!empty($property['colony'])) {
            $tags[] = '#' . preg_replace('/\s+/', '', $property['colony']);
        }
        if (!empty($property['location'])) {
            $tags[] = '#' . preg_replace('/\s+/', '', $property['location']);
        }
        $tags[] = '#DreamHome';
        $tags[] = '#Gorakhpur';

        return array_unique($tags);
    }

    private function generateTemplateAd(array $property, string $platform): string
    {
        $name = $property['name'] ?? 'Premium Plot';
        $location = $property['location'] ?? 'Gorakhpur';
        $price = $property['price'] ?? 'Call for price';
        $colony = $property['colony'] ?? '';
        $colonyText = $colony ? " ($colony)" : "";

        return "🏠 {$name}\n📍 {$location}{$colonyText}\n💰 Starting {$price}\n\n" .
               "✅ Clear title, ready for construction\n✅ Gated community with amenities\n✅ Easy payment plans\n\n" .
               "📞 Call now: 7007444842\n🌐 apsdreamhome.com\n\n#RealEstate #PlotsForSale #Gorakhpur";
    }

    private function templateListingDescription(array $property): string
    {
        $name = $property['name'] ?? 'Premium Plot';
        $location = $property['location'] ?? 'Gorakhpur';
        $price = $property['price'] ?? 'Contact for pricing';

        return "Discover your dream plot at $name, located in the heart of $location. " .
               "Priced at $price, this property offers excellent investment potential with clear title and ready construction. " .
               "Surrounded by schools, hospitals, and markets, it's the perfect place to build your future. " .
               "Contact APS Dream Home at 7007444842 for a free site visit.";
    }

    private function templateColonyPost(array $colony): string
    {
        return "🏗️ {$colony['name']} — Where Your Dream Home Begins!\n\n" .
               "📍 {$colony['location']}\n💰 Starting from {$colony['starting_price']}\n" .
               "📊 Available plots: {$colony['available_plots']}\n\n" .
               "✅ Prime location\n✅ Clear titles\n✅ Modern amenities\n✅ Easy EMI options\n\n" .
               "📞 Book your free site visit: 7007444842";
    }

    private function templateBroadcastMessage(array $colony, string $type): string
    {
        return "📢 New Plot Alert!\n\n" .
               "{$colony['name']} — Limited plots now available!\n" .
               "Starting from {$colony['starting_price']}\n\n" .
               "⚡ Hurry — these won't last long!\n📞 Call: 7007444842\n🌐 apsdreamhome.com";
    }
}
