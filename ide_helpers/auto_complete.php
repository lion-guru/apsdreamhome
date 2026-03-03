<?php
/**
 * Auto-complete functionality for IDE enhancement
 */

class AutoComplete {
    private $suggestions = [];
    
    public function __construct() {
        $this->loadSuggestions();
    }
    
    private function loadSuggestions() {
        $this->suggestions = [
            'functions' => [
                'db()', 'request()', 'response()', 'view()', 'redirect()',
                'session()', 'auth()', 'validate()', 'sanitize()', 'hash()'
            ],
            'classes' => [
                'Controller', 'Model', 'View', 'Database', 'Auth', 'Session'
            ],
            'methods' => [
                'find()', 'findAll()', 'save()', 'delete()', 'validate()', 'render()'
            ]
        ];
    }
    
    public function getSuggestions($type) {
        return $this->suggestions[$type] ?? [];
    }
    
    public function addSuggestion($type, $suggestion) {
        if (!isset($this->suggestions[$type])) {
            $this->suggestions[$type] = [];
        }
        $this->suggestions[$type][] = $suggestion;
    }
}
