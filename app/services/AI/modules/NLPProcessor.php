<?php

namespace App\Services\AI\Modules;

/**
 * AI Module - NLPProcessor
 * Handles natural language processing, intent recognition, and sentiment analysis.
 */
class NLPProcessor {
    /**
     * Analyze text for intent, entities, and sentiment
     *
     * @param string $text
     * @param array $options
     * @return array
     */
    public function analyze($text, $options = []) {
        $text = \strtolower($text);
        
        // 1. Basic Intent Recognition
        $intent = $this->recognizeIntent($text);
        
        // 2. Simple Sentiment Analysis
        $sentiment = $this->analyzeSentiment($text);
        
        // 3. Entity Extraction (Simulated)
        $entities = $this->extractEntities($text);
        
        // 4. Complexity & Strategy flags (used by AIManager)
        $is_strategic = $this->isStrategic($text);
        $complexity = $this->calculateComplexity($text);

        return [
            'intent' => $intent,
            'sentiment' => $sentiment,
            'entities' => $entities,
            'is_strategic' => $is_strategic,
            'complexity' => $complexity,
            'raw_text' => $text
        ];
    }

    private function recognizeIntent($text) {
        $intents = [
            'enquiry' => ['price', 'cost', 'how much', 'details', 'info', 'brochure'],
            'investment' => ['invest', 'return', 'roi', 'profit', 'yield'],
            'appointment' => ['visit', 'meet', 'schedule', 'book', 'see'],
            'location' => ['where', 'place', 'area', 'locality', 'near'],
            'amenities' => ['facility', 'gym', 'pool', 'parking', 'security'],
            'greeting' => ['hi', 'hello', 'hey', 'good morning', 'good evening']
        ];

        foreach ($intents as $name => $keywords) {
            foreach ($keywords as $keyword) {
                if (\strpos($text, $keyword) !== false) {
                    return ['name' => $name, 'confidence' => 0.85];
                }
            }
        }

        return ['name' => 'other', 'confidence' => 0.5];
    }

    private function analyzeSentiment($text) {
        $positive = ['good', 'great', 'excellent', 'happy', 'interested', 'best', 'nice'];
        $negative = ['bad', 'poor', 'unhappy', 'not good', 'expensive', 'late', 'issue'];

        $posCount = 0;
        $negCount = 0;

        foreach ($positive as $word) {
            if (\strpos($text, $word) !== false) $posCount++;
        }
        foreach ($negative as $word) {
            if (\strpos($text, $word) !== false) $negCount++;
        }

        if ($posCount > $negCount) return ['label' => 'positive', 'score' => 0.8];
        if ($negCount > $posCount) return ['label' => 'negative', 'score' => 0.8];
        return ['label' => 'neutral', 'score' => 0.5];
    }

    private function extractEntities($text) {
        $entities = [
            'monetary' => [],
            'property_type' => [],
            'location' => []
        ];

        // Simulated extraction
        if (\preg_match('/(\d+)\s*(lakh|crore|cr|k)/i', $text, $matches)) {
            $entities['monetary'][] = $matches[0];
        }

        $types = ['flat', 'villa', 'plot', 'land', 'shop', 'office'];
        foreach ($types as $type) {
            if (\strpos($text, $type) !== false) $entities['property_type'][] = $type;
        }

        return $entities;
    }

    private function isStrategic($text) {
        $strategicKeywords = ['future', 'plan', 'expand', 'growth', 'strategy', 'roadmap'];
        foreach ($strategicKeywords as $word) {
            if (\strpos($text, $word) !== false) return true;
        }
        return false;
    }

    private function calculateComplexity($text) {
        $wordCount = \str_word_count($text);
        if ($wordCount > 20) return 'high';
        if ($wordCount > 10) return 'medium';
        return 'low';
    }
}
