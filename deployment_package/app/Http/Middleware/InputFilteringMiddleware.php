<?php
/**
 * APS Dream Home - Input Filtering Middleware
 */

namespace App\Http\Middleware;

use App\Security\InputValidator;
use App\Security\InputSanitizer;

class InputFilteringMiddleware
{
    private $validator;
    private $sanitizer;

    public function __construct()
    {
        $this->validator = InputValidator::getInstance();
        $this->sanitizer = InputSanitizer::getInstance();
    }

    public function handle($request, $next)
    {
        // Get input data
        $inputData = $request->getAllInput();

        // Sanitize input data
        $sanitizedData = $this->sanitizer->sanitize($inputData);

        // Validate input data
        $isValid = $this->validator->validate($sanitizedData);

        if (!$isValid) {
            $errors = $this->validator->getErrors();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 400);
        }

        // Update request with sanitized data
        $request->merge($sanitizedData);

        return $next($request);
    }
}
