<?php

namespace App\Services;

use App\Core\Database\Database;
use Exception;

class ProgressiveRegistrationService
{
    private $db;
    private $registrationSteps = [
        1 => [
            'name' => 'basic_info',
            'title' => 'Basic Information',
            'fields' => ['name', 'email', 'phone'],
            'required' => ['name', 'email'],
            'validation' => [
                'name' => 'required|min:3',
                'email' => 'required|email',
                'phone' => 'phone'
            ]
        ],
        2 => [
            'name' => 'address_info',
            'title' => 'Address Information',
            'fields' => ['address', 'city', 'state', 'pincode'],
            'required' => ['city', 'state'],
            'validation' => [
                'address' => 'max:500',
                'city' => 'required|min:2',
                'state' => 'required|min:2',
                'pincode' => 'numeric|digits:6'
            ]
        ],
        3 => [
            'name' => 'preferences',
            'title' => 'Preferences',
            'fields' => ['property_type', 'budget_range', 'preferred_locations', 'notification_preferences'],
            'required' => ['property_type'],
            'validation' => [
                'property_type' => 'required|in:apartment,villa,commercial,plot',
                'budget_range' => 'numeric|min:100000',
                'preferred_locations' => 'array',
                'notification_preferences' => 'array'
            ]
        ],
        4 => [
            'name' => 'verification',
            'title' => 'Account Verification',
            'fields' => ['email_verified', 'phone_verified', 'id_proof'],
            'required' => ['email_verified', 'phone_verified'],
            'validation' => [
                'email_verified' => 'required|boolean',
                'phone_verified' => 'required|boolean',
                'id_proof' => 'file|mimes:jpg,jpeg,png,pdf|max:2048'
            ]
        ],
        5 => [
            'name' => 'final_setup',
            'title' => 'Final Setup',
            'fields' => ['password', 'agree_terms', 'marketing_consent'],
            'required' => ['password', 'agree_terms'],
            'validation' => [
                'password' => 'required|min:8',
                'agree_terms' => 'required|accepted',
                'marketing_consent' => 'boolean'
            ]
        ]
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Start progressive registration
     */
    public function startRegistration($sessionId)
    {
        try {
            // Clean up any existing incomplete registration for this session
            $this->cleanupIncompleteRegistration($sessionId);

            // Create new progressive registration
            $query = "INSERT INTO progressive_registrations (session_id, current_step, completed_steps, data) VALUES (?, 1, '[]', '{}')";
            $this->db->execute($query, [$sessionId]);

            return [
                'success' => true,
                'registration_id' => $this->db->getLastInsertId(),
                'current_step' => 1,
                'total_steps' => count($this->registrationSteps),
                'step_info' => $this->registrationSteps[1]
            ];

        } catch (Exception $e) {
            error_log("Progressive registration start error: " . $e->getMessage());
            throw new Exception("Failed to start registration: " . $e->getMessage());
        }
    }

    /**
     * Get current registration step
     */
    public function getCurrentStep($sessionId)
    {
        try {
            $query = "SELECT * FROM progressive_registrations WHERE session_id = ? AND is_completed = 0 ORDER BY created_at DESC LIMIT 1";
            $registration = $this->db->fetch($query, [$sessionId]);

            if (!$registration) {
                return null;
            }

            $currentStep = $registration['current_step'];
            $collectedData = json_decode($registration['data'] ?? '{}', true);
            $completedSteps = json_decode($registration['completed_steps'] ?? '[]', true);

            return [
                'registration_id' => $registration['id'],
                'current_step' => $currentStep,
                'total_steps' => count($this->registrationSteps),
                'step_info' => $this->registrationSteps[$currentStep] ?? null,
                'collected_data' => $collectedData,
                'completed_steps' => $completedSteps,
                'progress' => $this->calculateProgress($currentStep, count($this->registrationSteps))
            ];

        } catch (Exception $e) {
            error_log("Get current step error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Save registration step data
     */
    public function saveStepData($sessionId, $stepData)
    {
        try {
            $registration = $this->getCurrentRegistration($sessionId);
            if (!$registration) {
                throw new Exception("No active registration found");
            }

            $currentStep = $registration['current_step'];
            $stepInfo = $this->registrationSteps[$currentStep] ?? null;

            if (!$stepInfo) {
                throw new Exception("Invalid step: $currentStep");
            }

            // Validate step data
            $validationResult = $this->validateStepData($stepData, $stepInfo);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }

            // Merge with existing data
            $existingData = json_decode($registration['data'] ?? '{}', true);
            $updatedData = array_merge($existingData, $stepData);

            // Update registration
            $query = "UPDATE progressive_registrations SET data = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, [json_encode($updatedData), $registration['id']]);

            return [
                'success' => true,
                'message' => 'Step data saved successfully'
            ];

        } catch (Exception $e) {
            error_log("Save step data error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to save step data'
            ];
        }
    }

    /**
     * Move to next step
     */
    public function moveToNextStep($sessionId)
    {
        try {
            $registration = $this->getCurrentRegistration($sessionId);
            if (!$registration) {
                throw new Exception("No active registration found");
            }

            $currentStep = $registration['current_step'];
            $totalSteps = count($this->registrationSteps);

            if ($currentStep >= $totalSteps) {
                throw new Exception("Already at final step");
            }

            // Mark current step as completed
            $completedSteps = json_decode($registration['completed_steps'] ?? '[]', true);
            if (!in_array($currentStep, $completedSteps)) {
                $completedSteps[] = $currentStep;
            }

            $nextStep = $currentStep + 1;

            // Update registration
            $query = "UPDATE progressive_registrations SET current_step = ?, completed_steps = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, [$nextStep, json_encode($completedSteps), $registration['id']]);

            return [
                'success' => true,
                'current_step' => $nextStep,
                'total_steps' => $totalSteps,
                'step_info' => $this->registrationSteps[$nextStep],
                'progress' => $this->calculateProgress($nextStep, $totalSteps)
            ];

        } catch (Exception $e) {
            error_log("Move to next step error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to move to next step'
            ];
        }
    }

    /**
     * Move to previous step
     */
    public function moveToPreviousStep($sessionId)
    {
        try {
            $registration = $this->getCurrentRegistration($sessionId);
            if (!$registration) {
                throw new Exception("No active registration found");
            }

            $currentStep = $registration['current_step'];

            if ($currentStep <= 1) {
                throw new Exception("Already at first step");
            }

            $previousStep = $currentStep - 1;

            // Update registration
            $query = "UPDATE progressive_registrations SET current_step = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, [$previousStep, $registration['id']]);

            return [
                'success' => true,
                'current_step' => $previousStep,
                'total_steps' => count($this->registrationSteps),
                'step_info' => $this->registrationSteps[$previousStep],
                'progress' => $this->calculateProgress($previousStep, count($this->registrationSteps))
            ];

        } catch (Exception $e) {
            error_log("Move to previous step error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to move to previous step'
            ];
        }
    }

    /**
     * Complete registration and create user
     */
    public function completeRegistration($sessionId)
    {
        try {
            $registration = $this->getCurrentRegistration($sessionId);
            if (!$registration) {
                throw new Exception("No active registration found");
            }

            $currentStep = $registration['current_step'];
            $totalSteps = count($this->registrationSteps);

            if ($currentStep < $totalSteps) {
                throw new Exception("Registration not complete. Current step: $currentStep");
            }

            // Get all collected data
            $collectedData = json_decode($registration['data'] ?? '{}', true);

            // Validate final data
            $validationResult = $this->validateFinalData($collectedData);
            if (!$validationResult['valid']) {
                return [
                    'success' => false,
                    'errors' => $validationResult['errors']
                ];
            }

            // Create user
            $userId = $this->createUserFromProgressiveData($collectedData);

            // Mark registration as completed
            $query = "UPDATE progressive_registrations SET user_id = ?, is_completed = 1, completed_at = NOW() WHERE id = ?";
            $this->db->execute($query, [$userId, $registration['id']]);

            // Create user preferences
            $this->createUserPreferences($userId, $collectedData);

            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Registration completed successfully'
            ];

        } catch (Exception $e) {
            error_log("Complete registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to complete registration'
            ];
        }
    }

    /**
     * Get current registration
     */
    private function getCurrentRegistration($sessionId)
    {
        $query = "SELECT * FROM progressive_registrations WHERE session_id = ? AND is_completed = 0 ORDER BY created_at DESC LIMIT 1";
        return $this->db->fetch($query, [$sessionId]);
    }

    /**
     * Validate step data
     */
    private function validateStepData($data, $stepInfo)
    {
        $errors = [];

        foreach ($stepInfo['fields'] as $field) {
            if (in_array($field, $stepInfo['required'] ?? []) && empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                continue;
            }

            if (!empty($data[$field])) {
                $fieldErrors = $this->validateField($field, $data[$field], $stepInfo['validation'][$field] ?? '');
                if (!empty($fieldErrors)) {
                    $errors[$field] = $fieldErrors;
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate individual field
     */
    private function validateField($field, $value, $rules)
    {
        $errors = [];
        $ruleList = explode('|', $rules);

        foreach ($ruleList as $rule) {
            if (empty($rule)) continue;

            $parts = explode(':', $rule);
            $ruleName = $parts[0];
            $ruleValue = $parts[1] ?? null;

            switch ($ruleName) {
                case 'required':
                    if (empty($value)) {
                        $errors[] = 'This field is required';
                    }
                    break;

                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Please enter a valid email address';
                    }
                    break;

                case 'phone':
                    if (!preg_match('/^[+]?[0-9]{10,15}$/', preg_replace('/[^0-9+]/', '', $value))) {
                        $errors[] = 'Please enter a valid phone number';
                    }
                    break;

                case 'min':
                    if (strlen($value) < $ruleValue) {
                        $errors[] = "Minimum length is $ruleValue characters";
                    }
                    break;

                case 'max':
                    if (strlen($value) > $ruleValue) {
                        $errors[] = "Maximum length is $ruleValue characters";
                    }
                    break;

                case 'numeric':
                    if (!is_numeric($value)) {
                        $errors[] = 'This field must be numeric';
                    }
                    break;

                case 'digits':
                    if (!preg_match('/^[0-9]{' . $ruleValue . '}$/', $value)) {
                        $errors[] = "This field must be exactly $ruleValue digits";
                    }
                    break;

                case 'in':
                    $allowedValues = explode(',', $ruleValue);
                    if (!in_array($value, $allowedValues)) {
                        $errors[] = 'Invalid value selected';
                    }
                    break;

                case 'accepted':
                    if ($value !== true && $value !== '1' && $value !== 'on') {
                        $errors[] = 'You must accept this term';
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * Validate final data
     */
    private function validateFinalData($data)
    {
        $errors = [];

        // Check if all required steps are completed
        $requiredFields = ['name', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        // Check email uniqueness
        if (!empty($data['email'])) {
            $query = "SELECT id FROM users WHERE email = ?";
            $existing = $this->db->fetch($query, [$data['email']]);
            if ($existing) {
                $errors['email'] = 'Email address already exists';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Create user from progressive data
     */
    private function createUserFromProgressiveData($data)
    {
        $query = "INSERT INTO users (name, email, phone, address, city, state, pincode, password, role, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'customer', 'active', NOW())";
        
        $this->db->execute($query, [
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['pincode'] ?? '',
            password_hash($data['password'] ?? '', PASSWORD_DEFAULT)
        ]);

        return $this->db->getLastInsertId();
    }

    /**
     * Create user preferences
     */
    private function createUserPreferences($userId, $data)
    {
        $preferences = [
            'notification_preferences' => [
                'email' => $data['email_notifications'] ?? true,
                'sms' => $data['sms_notifications'] ?? false,
                'push' => $data['push_notifications'] ?? true,
                'campaigns' => $data['marketing_consent'] ?? false,
                'updates' => true
            ],
            'ui_preferences' => [
                'theme' => 'light',
                'language' => 'en'
            ],
            'privacy_settings' => [
                'profile_visibility' => 'public',
                'contact_sharing' => $data['marketing_consent'] ?? false
            ]
        ];

        $query = "INSERT INTO user_preferences (user_id, notification_preferences, ui_preferences, privacy_settings) VALUES (?, ?, ?, ?)";
        $this->db->execute($query, [
            $userId,
            json_encode($preferences['notification_preferences']),
            json_encode($preferences['ui_preferences']),
            json_encode($preferences['privacy_settings'])
        ]);
    }

    /**
     * Calculate progress percentage
     */
    private function calculateProgress($currentStep, $totalSteps)
    {
        return round(($currentStep / $totalSteps) * 100);
    }

    /**
     * Clean up incomplete registration
     */
    private function cleanupIncompleteRegistration($sessionId)
    {
        $query = "DELETE FROM progressive_registrations WHERE session_id = ? AND is_completed = 0";
        $this->db->execute($query, [$sessionId]);
    }

    /**
     * Get registration summary
     */
    public function getRegistrationSummary($sessionId)
    {
        $registration = $this->getCurrentRegistration($sessionId);
        if (!$registration) {
            return null;
        }

        $collectedData = json_decode($registration['data'] ?? '{}', true);
        $completedSteps = json_decode($registration['completed_steps'] ?? '[]', true);

        return [
            'registration_id' => $registration['id'],
            'current_step' => $registration['current_step'],
            'completed_steps' => $completedSteps,
            'collected_data' => $collectedData,
            'progress' => $this->calculateProgress($registration['current_step'], count($this->registrationSteps)),
            'is_completed' => $registration['is_completed']
        ];
    }
}