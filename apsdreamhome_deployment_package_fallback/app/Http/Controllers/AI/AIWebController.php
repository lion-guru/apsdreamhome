<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\BaseController;

class AIWebController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->data = [];

        // Register middlewares
        $this->middleware('auth', ['only' => ['descriptionGenerator', 'suggestions']]);
    }

    /**
     * Display AI Chatbot page
     */
    public function chatbot()
    {
        $this->data['page_title'] = 'AI Property Assistant - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'AI Assistant', 'url' => BASE_URL . 'ai/chatbot']
        ];

        $this->render('pages/ai/chatbot');
    }

    /**
     * Display Property Description Generator page
     */
    public function descriptionGenerator()
    {
        $this->data['page_title'] = 'Property Description Generator - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'AI Tools', 'url' => '#'],
            ['title' => 'Description Generator', 'url' => BASE_URL . 'ai/description-generator']
        ];

        // Get property types for the dropdown
        $this->data['property_types'] = $this->getPropertyTypes();

        $this->render('pages/ai/description-generator');
    }

    /**
     * Display AI Property Suggestions page
     */
    public function suggestions()
    {
        $this->data['page_title'] = 'AI Property Suggestions - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Home', 'url' => BASE_URL],
            ['title' => 'AI Tools', 'url' => '#'],
            ['title' => 'Property Suggestions', 'url' => BASE_URL . 'ai/suggestions']
        ];

        // Get property types for the dropdown
        $this->data['property_types'] = $this->getPropertyTypes();

        $this->render('pages/ai/suggestions');
    }

    /**
     * Get property types (helper method)
     */
    private function getPropertyTypes()
    {
        $query = "SELECT * FROM property_types ORDER BY type_name";
        return $this->db->fetchAll($query);
    }
}
