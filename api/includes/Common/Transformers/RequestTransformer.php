<?php
namespace App\Common\Transformers;

class RequestTransformer {
    public static function transform(array $data, array $rules): array {
        $transformed = [];
        
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                if (strpos($rule['rules'] ?? '', 'required') === false) continue;
                $transformed[$field] = null;
                continue;
            }
            
            $value = $data[$field];
            $transformed[$field] = self::applyTransformations($value, (array)($rule['transform'] ?? []));
        }
        
        return $transformed;
    }
    
    private static function applyTransformations($value, array $transformations) {
        foreach ($transformations as $transform) {
            $value = match($transform) {
                'trim' => is_string($value) ? trim($value) : $value,
                'lowercase' => is_string($value) ? strtolower($value) : $value,
                'uppercase' => is_string($value) ? strtoupper($value) : $value,
                'int' => (int)$value,
                'float' => (float)$value,
                'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                'json_decode' => self::safeJsonDecode($value),
                default => is_callable($transform) ? $transform($value) : $value
            };
        }
        return $value;
    }
    
    private static function safeJsonDecode($value) {
        if (!is_string($value)) return $value;
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
