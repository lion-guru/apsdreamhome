<?php
/**
 * Error detection for IDE enhancement
 */

class ErrorDetection {
    private $errors = [];
    private $errorPatterns = [];
    
    public function __construct() {
        $this->loadErrorPatterns();
    }
    
    private function loadErrorPatterns() {
        $this->errorPatterns = [
            'syntax' => [
                '/Parse error.*syntax error/i',
                '/Unexpected token/i',
                '/Unexpected \'/i'
            ],
            'undefined' => [
                '/Undefined variable/i',
                '/Undefined constant/i',
                '/Call to undefined function/i',
                '/Call to undefined method/i'
            ],
            'fatal' => [
                '/Fatal error/i',
                '/Uncaught Error/i',
                '/Call to a member function/i'
            ]
        ];
    }
    
    public function detectErrors($code) {
        $this->errors = [];
        
        foreach ($this->errorPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $code)) {
                    $this->errors[] = [
                        'type' => $type,
                        'pattern' => $pattern,
                        'message' => "Potential {$type} error detected"
                    ];
                }
            }
        }
        
        return $this->errors;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
}
