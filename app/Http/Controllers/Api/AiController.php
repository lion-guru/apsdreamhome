<?php

namespace App\Http\Controllers\Api;

use \Exception;

class AIController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('role:associate', ['only' => ['generateDescription']]);
    }

    /**
     * Handle chatbot messages
     */
    public function chatbot()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $message = \trim($this->request()->input('message', ''));

            if (!$message) {
                return $this->jsonError('No message provided.', 400);
            }

            $aiService = new \App\Services\AIService();
            $reply = $aiService->getChatCompletion($message);

            if ($reply) {
                return $this->jsonSuccess(['reply' => $reply]);
            } else {
                return $this->jsonError('No response from AI.');
            }

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Generate property description
     */
    public function generateDescription()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $details = $this->request()->input('details', '');

            if (!$details) {
                return $this->jsonError('No property details provided.', 400);
            }

            $prompt = "Generate a professional and attractive real estate description for a property with the following details: " . $details;
            $systemPrompt = "You are an expert real estate copywriter for APS Dream Homes.";

            $aiService = new \App\Services\AIService();
            $description = $aiService->getChatCompletion($prompt, $systemPrompt);

            if ($description) {
                return $this->jsonSuccess(['description' => $description]);
            } else {
                return $this->jsonError('Failed to generate description.');
            }

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get AI property suggestions
     */
    public function getSuggestions()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $propertyType = $this->request()->input('property_type', '');
            $budget = $this->request()->input('budget', '');
            $location = $this->request()->input('location', '');

            if (!$propertyType || !$budget || !$location) {
                return $this->jsonError('Please provide property type, budget, and location.', 400);
            }

            $prompt = "As an AI real estate assistant, suggest 3-5 properties for a user looking for a $propertyType in $location with a budget of ₹$budget. Provide helpful advice on what to look for in these areas.";
            $systemPrompt = "You are a local real estate expert for APS Dream Homes in India.";

            $aiService = new \App\Services\AIService();
            $suggestions = $aiService->getChatCompletion($prompt, $systemPrompt);

            if ($suggestions) {
                return $this->jsonSuccess(['suggestions' => $suggestions]);
            } else {
                return $this->jsonError('Failed to get suggestions.');
            }

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
