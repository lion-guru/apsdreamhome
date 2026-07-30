<?php

/**
 * ConversationEngine — Multi-turn state machine for intelligent chatbot
 * Tracks user actions across messages, collects data step-by-step,
 * validates inputs, and executes actions via ActionHandlers.
 */

namespace App\Services\AI;

use App\Core\Database\Database;

class ConversationEngine
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $sessionId;
    private $userId;
    private $userRole;
    private $userName;

    /** Which roles can perform which actions */
    private static $rolePermissions = [
        'post_property'    => ['admin', 'manager', 'associate', 'agent', 'customer', 'employee'],
        'add_lead'         => ['admin', 'manager', 'associate', 'agent', 'employee', 'telecaller'],
        'book_site_visit'  => ['admin', 'manager', 'associate', 'agent', 'customer', 'employee', 'guest'],
        'search_property'  => ['admin', 'manager', 'associate', 'agent', 'customer', 'employee', 'guest'],
        'register'         => ['guest', 'customer'],
        'file_complaint'   => ['admin', 'manager', 'associate', 'agent', 'customer', 'employee', 'guest'],
        'check_booking'    => ['admin', 'manager', 'associate', 'agent', 'customer', 'employee'],
    ];

    /** Action flow definitions: field => validation rule */
    private static $flows = [
        'post_property' => [
            'greeting' => "Chaliye! Property post karte hain. Pehle bataiye property kis type ki hai?",
            'steps' => [
                ['field' => 'property_type', 'prompt' => "Property ka type kya hai?", 'type' => 'choice', 'options' => ['Plot', 'House', 'Flat', 'Shop', 'Land', 'Farmhouse'], 'required' => true],
                ['field' => 'name', 'prompt' => "Property ka naam / title kya rakhein?", 'type' => 'text', 'required' => true],
                ['field' => 'price', 'prompt' => "Price kitna hai? (lakh mein, jaise 25)", 'type' => 'number', 'required' => true],
                ['field' => 'location', 'prompt' => "Location kahan hai? (city + area)", 'type' => 'text', 'required' => true],
                ['field' => 'phone', 'prompt' => "Aapka phone number?", 'type' => 'phone', 'required' => true],
                ['field' => 'description', 'prompt' => "Property ke baare mein kuch aur batana ho? (optional, skip bhi kar sakte hain)", 'type' => 'text', 'required' => false],
            ],
            'confirm_message' => "✅ Property details confirm karein:",
            'execute_label' => "Submit Property",
            'success_message' => "🎉 Property successfully submit ho gayi! Hamari team jald hi aapse contact karegi.",
            'cancel_message' => "👍 Property post cancel kar diya. Koi aur madad chahiye?",
        ],

        'add_lead' => [
            'greeting' => "Lead create karte hain! Customer ka naam bataiye.",
            'steps' => [
                ['field' => 'name', 'prompt' => "Customer ka naam?", 'type' => 'text', 'required' => true],
                ['field' => 'phone', 'prompt' => "Phone number?", 'type' => 'phone', 'required' => true],
                ['field' => 'email', 'prompt' => "Email hai koi? (optional, skip kar sakte hain)", 'type' => 'email', 'required' => false],
                ['field' => 'budget', 'prompt' => "Budget kitna hai approximately? (lakh mein)", 'type' => 'number', 'required' => false],
                ['field' => 'location', 'prompt' => "Kis area mein interested hai?", 'type' => 'text', 'required' => false],
                ['field' => 'interest', 'prompt' => "Property type — Plot, House, Flat, ya kuch aur?", 'type' => 'text', 'required' => false],
            ],
            'confirm_message' => "✅ Lead details confirm karein:",
            'execute_label' => "Create Lead",
            'success_message' => "🎉 Lead successfully create ho gayi! Ab isse follow-up kar sakte hain.",
            'cancel_message' => "👍 Lead creation cancel. Koi aur madad?",
        ],

        'book_site_visit' => [
            'greeting' => "Site visit book karte hain! Pehle bataiye kaunsi property dekhni hai.",
            'steps' => [
                ['field' => 'property', 'prompt' => "Kaunsi property / colony dekhni hai?", 'type' => 'text', 'required' => true],
                ['field' => 'name', 'prompt' => "Aapka naam?", 'type' => 'text', 'required' => true],
                ['field' => 'phone', 'prompt' => "Phone number?", 'type' => 'phone', 'required' => true],
                ['field' => 'date', 'prompt' => "Visit kab karna hai? (date bataiye, jaise 'kal', ' Monday', '15 July')", 'type' => 'date', 'required' => true],
            ],
            'confirm_message' => "✅ Site visit confirm karein:",
            'execute_label' => "Book Visit",
            'success_message' => "🎉 Site visit book ho gayi! Aapko confirmation milega.",
            'cancel_message' => "👍 Visit cancel. Koi aur madad?",
        ],

        'search_property' => [
            'greeting' => "Property dhundhte hain! Kya chahiye aapko?",
            'steps' => [
                ['field' => 'location', 'prompt' => "Kis area / city mein property chahiye?", 'type' => 'text', 'required' => true],
                ['field' => 'budget', 'prompt' => "Budget kitna hai? (lakh mein)", 'type' => 'number', 'required' => false],
                ['field' => 'type', 'prompt' => "Property type — Plot, House, Flat, Shop?", 'type' => 'choice', 'options' => ['Any', 'Plot', 'House', 'Flat', 'Shop'], 'required' => false],
            ],
            'confirm_message' => "🔍 Property search:",
            'execute_label' => "Search",
            'success_message' => "Yeh rahe aapki search ke results:",
            'cancel_message' => "👍 Search cancel. Koi aur madad?",
        ],

        'register' => [
            'greeting' => "Naya account banate hain! Pehle bataiye aap kaun hain — customer, associate, ya agent?",
            'steps' => [
                ['field' => 'role', 'prompt' => "Aap kaun hain?", 'type' => 'choice', 'options' => ['Customer', 'Associate', 'Agent'], 'required' => true],
                ['field' => 'name', 'prompt' => "Aapka pura naam?", 'type' => 'text', 'required' => true],
                ['field' => 'email', 'prompt' => "Email address?", 'type' => 'email', 'required' => true],
                ['field' => 'phone', 'prompt' => "Phone number?", 'type' => 'phone', 'required' => true],
                ['field' => 'password', 'prompt' => "Password set karein (kam se kam 6 characters)?", 'type' => 'password', 'required' => true],
            ],
            'confirm_message' => "✅ Registration details:",
            'execute_label' => "Register",
            'success_message' => "🎉 Account ban gaya! Ab aap login kar sakte hain.",
            'cancel_message' => "👍 Registration cancel. Koi aur madad?",
        ],

        'file_complaint' => [
            'greeting' => "Complaint register karte hain. Pehle bataiye kya problem hai?",
            'steps' => [
                ['field' => 'type', 'prompt' => "Complaint ka type kya hai?", 'type' => 'choice', 'options' => ['Payment Issue', 'Property Issue', 'Service Delay', 'Staff Behavior', 'Legal Issue', 'Other'], 'required' => true],
                ['field' => 'name', 'prompt' => "Aapka naam?", 'type' => 'text', 'required' => true],
                ['field' => 'phone', 'prompt' => "Phone number?", 'type' => 'phone', 'required' => true],
                ['field' => 'description', 'prompt' => "Problem ka detail mein description?", 'type' => 'text', 'required' => true],
            ],
            'confirm_message' => "✅ Complaint details:",
            'execute_label' => "Submit Complaint",
            'success_message' => "🎉 Complaint register ho gayi! Hamari team 24 ghante mein contact karegi.",
            'cancel_message' => "👍 Complaint cancel. Koi aur madad?",
        ],

        'check_booking' => [
            'greeting' => "Booking status dekhte hain!",
            'steps' => [
                ['field' => 'identifier', 'prompt' => "Booking number ya apna phone number bataiye?", 'type' => 'text', 'required' => true],
            ],
            'confirm_message' => "📋 Booking search:",
            'execute_label' => "Check Status",
            'success_message' => "",
            'cancel_message' => "👍 Koi aur madad?",
        ],
    ];

    private $analytics;

    public function __construct($sessionId, $userId = null, $userRole = 'guest', $userName = 'Guest', $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->sessionId = $sessionId;
        $this->userId = $userId;
        $this->userRole = $userRole;
        $this->userName = $userName;
        $this->analytics = new ChatAnalytics($this->db);
    }

    /**
     * Main entry point — process user message through state machine.
     * Returns: ['handled' => bool, 'response' => string, 'action_data' => array]
     */
    public function processMessage($message): array
    {
        $msg = trim($message);
        $lower = strtolower($msg);

        // Check for cancel intent at any stage
        if ($this->isCancelIntent($lower)) {
            $analytics = new ChatAnalytics($this->db);
            $active = $this->getActiveConversation();
            if ($active) {
                $analytics->logCancelled($active['current_action'], $this->sessionId);
            }
            return $this->cancelConversation();
        }

        // Check for restart/new action intent
        if ($this->isNewActionIntent($lower)) {
            $this->clearConversation();
        }

        // Load active conversation if exists (ONLY 'active' — 'confirm' is handled by controller)
        try {
            $active = $this->db->fetch(
                "SELECT * FROM ai_chat_conversations WHERE session_id = ?{$this->tenantSql()} AND status = 'active' ORDER BY id DESC LIMIT 1",
                array_merge([$this->sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
        } catch (\Exception $e) {
            $active = null;
        }

        if ($active && !empty($active['current_action'])) {
            return $this->continueFlow($active, $msg);
        }

        // No active conversation — detect intent for new action
        $action = $this->detectActionIntent($lower);

        if ($action) {
            return $this->startFlow($action);
        }

        // Not an action — return not handled (let 7-layer AI handle it)
        return ['handled' => false, 'response' => '', 'action_data' => []];
    }

    /**
     * Detect which action the user wants to start
     */
    private function detectActionIntent($lower): ?string
    {
        $candidates = [];

        // Property posting
        if (preg_match('/(property\s*post|post\s*property|sell\s*property|property\s*bech|list\s*property|property\s*list|meri?\s*property|property\s*dal|property\s*add)/i', $lower)) {
            $candidates[] = 'post_property';
        }

        // Lead creation
        if (preg_match('/(lead\s*add|add\s*lead|new\s*lead|lead\s*ban|lead\s*create|lead\s*submit|customer\s*add)/i', $lower)) {
            $candidates[] = 'add_lead';
        }

        // Site visit
        if (preg_match('/(site\s*visit|visit\s*book|book\s*visit|visit\s*karni|visit\s*chahti|ghumna\s*hai|dekhna\s*hai|visit\s*schedule)/i', $lower)) {
            $candidates[] = 'book_site_visit';
        }

        // Property search
        if (preg_match('/(property\s*dhundh|property\s*chahiye|plot\s*chahiye|ghar\s*chahiye|buy\s*karna|kharidna|property\s*search|search\s*property|kya\s*hai\s*available)/i', $lower)) {
            $candidates[] = 'search_property';
        }

        // Registration
        if (preg_match('/(register|sign\s*up|account\s*ban|naya\s*account|join\s*karna|member\s*ban)/i', $lower)) {
            $candidates[] = 'register';
        }

        // Complaint
        if (preg_match('/(complaint|shikayat|problem|issue|grievance|complain)/i', $lower)) {
            $candidates[] = 'file_complaint';
        }

        // Check booking
        if (preg_match('/(booking\s*status|booking\s*check|meri\s*booking|booking\s*kahan|payment\s*status|emi\s*status)/i', $lower)) {
            $candidates[] = 'check_booking';
        }

        // Return first match that the user's role is allowed to perform
        foreach ($candidates as $action) {
            if ($this->canPerformAction($action)) {
                return $action;
            }
        }

        // If no permitted action found but candidates existed, return the first for messaging
        return !empty($candidates) ? $candidates[0] : null;
    }

    /**
     * Check if current user role can perform an action
     */
    public function canPerformAction(string $action): bool
    {
        $allowed = self::$rolePermissions[$action] ?? [];
        return in_array($this->userRole, $allowed);
    }

    /**
     * Get actions available for current role
     */
    public function getAvailableActions(): array
    {
        $available = [];
        foreach (self::$rolePermissions as $action => $roles) {
            if (in_array($this->userRole, $roles)) {
                $available[] = $action;
            }
        }
        return $available;
    }

    /**
     * Start a new conversation flow
     */
    private function startFlow(string $action): array
    {
        $flow = self::$flows[$action] ?? null;
        if (!$flow) {
            return ['handled' => false, 'response' => '', 'action_data' => []];
        }

        // Role-based permission check
        if (!$this->canPerformAction($action)) {
            $roleLabels = [
                'guest' => 'Guest', 'customer' => 'Customer', 'associate' => 'Associate',
                'agent' => 'Agent', 'admin' => 'Admin', 'manager' => 'Manager',
                'employee' => 'Employee', 'telecaller' => 'Telecaller',
            ];
            $roleName = $roleLabels[$this->userRole] ?? ucfirst($this->userRole);
            $actionLabels = [
                'post_property' => 'Property post karna',
                'add_lead' => 'Lead add karna',
                'book_site_visit' => 'Site visit book karna',
                'search_property' => 'Property search karna',
                'register' => 'Account register karna',
                'file_complaint' => 'Complaint file karna',
                'check_booking' => 'Booking status check karna',
            ];
            $actionName = $actionLabels[$action] ?? $action;
            $allowedRoles = self::$rolePermissions[$action] ?? [];
            $allowedLabels = array_map(function ($r) { return ucfirst($r); }, $allowedRoles);

            return [
                'handled' => true,
                'response' => "🔒 Aapka role ({$roleName}) se ye action nahi ho sakta.\n\n❌ {$actionName} — sirf ye roles kar sakte hain:\n" . implode(', ', $allowedLabels) . "\n\n📞 Agar zaroorat hai toh admin se baat karein: +91 92771 21112",
                'action_data' => [
                    'action' => null,
                    'step' => null,
                    'suggestions' => $this->getRoleBasedSuggestions(),
                ]
            ];
        }

        $stepCount = count($flow['steps']);

        // Track analytics
        $this->analytics->logStarted($action, $this->sessionId, $this->userId);

        // Save conversation state
        $this->saveConversation($action, [], 0, $stepCount);

        // Return first prompt
        $greeting = $flow['greeting'] . "\n\n" . $flow['steps'][0]['prompt'];
        $suggestions = $this->getStepSuggestions($flow['steps'][0]);

        return [
            'handled' => true,
            'response' => $greeting,
            'action_data' => [
                'action' => $action,
                'step' => 0,
                'step_count' => $stepCount,
                'suggestions' => $suggestions,
                'progress' => $this->makeProgress(0, $stepCount),
            ]
        ];
    }

    /**
     * Continue an existing flow — collect field and move to next step
     */
    private function continueFlow(array $active, string $message): array
    {
        $action = $active['current_action'];
        $flow = self::$flows[$action];
        $step = (int)$active['current_step'];
        $collected = json_decode($active['collected_data'] ?? '{}', true) ?: [];
        $steps = $flow['steps'];

        // Validate current step's input
        $currentField = $steps[$step];
        $validation = $this->validateInput($currentField, $message);

        if (!$validation['valid']) {
            // Re-prompt with error
            return [
                'handled' => true,
                'response' => "⚠️ " . $validation['error'] . "\n\n" . $currentField['prompt'],
                'action_data' => [
                    'action' => $action,
                    'step' => $step,
                    'step_count' => count($steps),
                    'suggestions' => $this->getStepSuggestions($currentField),
                    'progress' => $this->makeProgress($step, count($steps)),
                    'error' => true,
                ]
            ];
        }

        // Store the value
        $collected[$currentField['field']] = $validation['value'];

        // Move to next step
        $nextStep = $step + 1;

        // Track step progress
        $this->analytics->logStep($action, $this->sessionId, $nextStep, count($steps));

        // If skip requested and field not required
        if (strtolower($message) === 'skip' && !$currentField['required']) {
            // Don't advance, just skip this field
        }

        // Check if all required fields collected
        if ($nextStep >= count($steps)) {
            // All data collected — show confirmation
            $this->saveConversation($action, $collected, $step, count($steps), 'confirm');
            return $this->showConfirmation($action, $collected);
        }

        // Move to next step
        $this->saveConversation($action, $collected, $nextStep, count($steps));

        $nextField = $steps[$nextStep];
        $response = $nextField['prompt'];
        $suggestions = $this->getStepSuggestions($nextField);

        // Add progress indicator
        $progress = $this->makeProgress($nextStep, count($steps));

        return [
            'handled' => true,
            'response' => $progress . "\n" . $response,
            'action_data' => [
                'action' => $action,
                'step' => $nextStep,
                'step_count' => count($steps),
                'suggestions' => $suggestions,
                'progress' => $progress,
            ]
        ];
    }

    /**
     * Show confirmation before executing action
     */
    private function showConfirmation(string $action, array $data): array
    {
        $flow = self::$flows[$action];

        // Build confirmation text
        $lines = [$flow['confirm_message'], ''];
        foreach ($flow['steps'] as $step) {
            $value = $data[$step['field']] ?? '(skip)';
            $label = $this->getFieldLabel($step['field']);
            $lines[] = $this->formatField($label, $value, $step['type']);
        }

        $response = implode("\n", $lines);

        return [
            'handled' => true,
            'response' => $response,
            'action_data' => [
                'action' => $action,
                'step' => 'confirm',
                'collected' => $data,
                'suggestions' => ['✅ Confirm & Submit', '✏️ Edit', '❌ Cancel'],
                'progress' => $this->makeProgress(count($flow['steps']), count($flow['steps'])),
            ]
        ];
    }

    /**
     * Handle confirmation response (yes/confirm → execute, edit → go back, cancel → abort)
     */
    public function handleConfirmation(array $active, string $message): array
    {
        $lower = strtolower(trim($message));
        $action = $active['current_action'];
        $collected = json_decode($active['collected_data'] ?? '{}', true) ?: [];
        $flow = self::$flows[$action];

        // Confirm intent
        if (preg_match('/(confirm|yes|haan|ha|submit|confirm\s*&\s*submit|theek\s*hai|sahi\s*hai|ok|done)/i', $lower)) {
            $this->updateStatus($active['id'], 'completed');

            // Execute the action
            $handler = new ActionHandlers($this->db);
            $result = $handler->execute($action, $collected, $this->userId, $this->userRole);

            // Track completion
            $success = isset($result['success']) ? $result['success'] : true;
            $this->analytics->logCompleted($action, $this->sessionId, $success, $this->userId);

            return [
                'handled' => true,
                'response' => $result['message'] ?? $flow['success_message'],
                'action_data' => [
                    'action' => $action,
                    'step' => 'done',
                    'result' => $result,
                    'suggestions' => $this->getActionFollowUps($action),
                ]
            ];
        }

        // Edit intent — show fields to pick which one to edit
        if (preg_match('/(edit|badal|change|sudhar|dubara)/i', $lower)) {
            $lines = ["✏️ Kaunsa field edit karna hai?", ''];
            foreach ($flow['steps'] as $i => $step) {
                $value = $collected[$step['field']] ?? '(skip)';
                $label = $this->getFieldLabel($step['field']);
                $lines[] = "{$i}. {$label}: {$value}";
            }
            $lines[] = '';
            $lines[] = "📝 Field number bataiye (jaise '0' ya 'name'), ya 'sab' sab edit karein.";

            $suggestions = [];
            foreach ($flow['steps'] as $i => $step) {
                $suggestions[] = "{$i}. " . $this->getFieldLabel($step['field']);
            }
            $suggestions[] = 'Sab';

            $this->saveConversation($action, $collected, $step, count($flow['steps']), 'active');

            return [
                'handled' => true,
                'response' => implode("\n", $lines),
                'action_data' => [
                    'action' => $action,
                    'step' => 'edit_pick',
                    'collected' => $collected,
                    'suggestions' => $suggestions,
                    'progress' => $this->makeProgress(count($flow['steps']), count($flow['steps'])),
                ]
            ];
        }

        // Handle field-specific edit (user picks a field number or name)
        if (preg_match('/^(\d+)$/', $lower, $m) || preg_match('/^(name|phone|email|budget|location|interest|property_type|price|description|date|property|role|password|type|identifier)$/i', $lower)) {
            $fieldIndex = null;
            if (isset($m[1])) {
                $fieldIndex = (int)$m[1];
            } else {
                // Match by field name
                foreach ($flow['steps'] as $i => $step) {
                    if (strtolower($step['field']) === $lower) {
                        $fieldIndex = $i;
                        break;
                    }
                }
            }

            if ($fieldIndex !== null && isset($flow['steps'][$fieldIndex])) {
                $targetStep = $flow['steps'][$fieldIndex];
                // Go to this specific step
                $this->saveConversation($action, $collected, $fieldIndex, count($flow['steps']), 'active');
                return [
                    'handled' => true,
                    'response' => "✏️ {$this->getFieldLabel($targetStep['field'])} update karein.\n\n" . $targetStep['prompt'],
                    'action_data' => [
                        'action' => $action,
                        'step' => $fieldIndex,
                        'step_count' => count($flow['steps']),
                        'suggestions' => $this->getStepSuggestions($targetStep),
                        'progress' => $this->makeProgress($fieldIndex, count($flow['steps'])),
                    ]
                ];
            }

            // "sab" — restart from step 0
            if ($lower === 'sab') {
                $this->saveConversation($action, $collected, 0, count($flow['steps']), 'active');
                $firstStep = $flow['steps'][0];
                return [
                    'handled' => true,
                    'response' => "✏️ Chaliye sab details dobara bharte hain.\n\n" . $firstStep['prompt'],
                    'action_data' => [
                        'action' => $action,
                        'step' => 0,
                        'step_count' => count($flow['steps']),
                        'suggestions' => $this->getStepSuggestions($firstStep),
                        'progress' => $this->makeProgress(0, count($flow['steps'])),
                    ]
                ];
            }
        }

        // Cancel intent
        if (preg_match('/(cancel|nahi|ruk|stop|bhool|chhodo)/i', $lower)) {
            $this->updateStatus($active['id'], 'cancelled');
            $this->analytics->logCancelled($action, $this->sessionId);
            return [
                'handled' => true,
                'response' => $flow['cancel_message'],
                'action_data' => ['action' => null, 'step' => null, 'suggestions' => []]
            ];
        }

        // Unknown response during confirmation
        return [
            'handled' => true,
            'response' => "Kya karna hai?\n✅ Confirm & Submit — submit karna hai\n✏️ Edit — details change karni hain\n❌ Cancel — cancel karna hai",
            'action_data' => [
                'action' => $action,
                'step' => 'confirm',
                'collected' => $collected,
                'suggestions' => ['✅ Confirm & Submit', '✏️ Edit', '❌ Cancel'],
                'progress' => $this->makeProgress(count($flow['steps']), count($flow['steps'])),
            ]
        ];
    }

    // ─── Helper Methods ────────────────────────────────────────

    private function validateInput(array $field, string $value): array
    {
        $value = trim($value);

        // Skip check
        if (strtolower($value) === 'skip' && !$field['required']) {
            return ['valid' => true, 'value' => null];
        }

        // Empty check for required fields
        if (empty($value) && $field['required']) {
            return ['valid' => false, 'error' => "Yeh field zaroori hai. " . $field['prompt']];
        }

        if (empty($value) && !$field['required']) {
            return ['valid' => true, 'value' => null];
        }

        // Type validation
        switch ($field['type']) {
            case 'phone':
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) === 10) {
                    return ['valid' => true, 'value' => $digits];
                }
                if (strlen($digits) === 12 && substr($digits, 0, 2) === '91') {
                    return ['valid' => true, 'value' => substr($digits, 2)];
                }
                return ['valid' => false, 'error' => "Sahi 10-digit phone number daaliye."];

            case 'email':
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return ['valid' => true, 'value' => $value];
                }
                return ['valid' => false, 'error' => "Sahi email address daaliye."];

            case 'number':
                $num = (float) preg_replace('/[^0-9.]/', '', $value);
                if ($num > 0) {
                    return ['valid' => true, 'value' => $num];
                }
                return ['valid' => false, 'error' => "Sahi number daaliye."];

            case 'date':
                // Accept natural language dates
                $date = $this->parseNaturalDate($value);
                if ($date) {
                    return ['valid' => true, 'value' => $date];
                }
                return ['valid' => false, 'error' => "Sahi date bataiye (jaise 'kal', '15 July', 'next Monday')"];

            case 'choice':
                if ($field['options']) {
                    $lower = strtolower($value);
                    foreach ($field['options'] as $opt) {
                        if (strtolower($opt) === $lower || strpos($lower, strtolower($opt)) !== false) {
                            return ['valid' => true, 'value' => $opt];
                        }
                    }
                    // Allow free text for choices (user might say something not in options)
                    return ['valid' => true, 'value' => ucfirst($value)];
                }
                return ['valid' => true, 'value' => $value];

            case 'password':
                if (strlen($value) >= 6) {
                    return ['valid' => true, 'value' => $value];
                }
                return ['valid' => false, 'error' => "Password kam se kam 6 characters ka hona chahiye."];

            default:
                return ['valid' => true, 'value' => $value];
        }
    }

    private function parseNaturalDate(string $value): ?string
    {
        $lower = strtolower(trim($value));

        if ($lower === 'kal' || $lower === 'tomorrow') {
            return date('Y-m-d', strtotime('+1 day'));
        }
        if ($lower === 'aaj' || $lower === 'today') {
            return date('Y-m-d');
        }
        if (preg_match('/next\s*(monday|tuesday|wednesday|thursday|friday|saturday|sunday)/i', $lower, $m)) {
            return date('Y-m-d', strtotime("next {$m[1]}"));
        }
        // Try parsing as date
        $parsed = strtotime($value);
        if ($parsed && $parsed > time()) {
            return date('Y-m-d', $parsed);
        }
        return null;
    }

    private function getStepSuggestions(array $field): array
    {
        if ($field['type'] === 'choice' && !empty($field['options'])) {
            return $field['options'];
        }
        if (!$field['required']) {
            return ['Skip'];
        }
        return [];
    }

    private function getFieldLabel(string $field): string
    {
        $labels = [
            'property_type' => '🏠 Type',
            'name' => '📝 Naam',
            'price' => '💰 Price',
            'location' => '📍 Location',
            'phone' => '📱 Phone',
            'email' => '📧 Email',
            'description' => '📄 Description',
            'budget' => '💰 Budget',
            'interest' => '🏗️ Interest',
            'date' => '📅 Date',
            'property' => '🏢 Property',
            'role' => '👤 Role',
            'password' => '🔑 Password',
            'type' => '📋 Type',
            'identifier' => '🔍 Identifier',
        ];
        return $labels[$field] ?? ucfirst($field);
    }

    private function formatField(string $label, $value, string $type): string
    {
        if ($value === null || $value === '') return "{$label}: (skip)";
        if ($type === 'number' && is_numeric($value)) return "{$label}: ₹{$value} Lakh";
        return "{$label}: {$value}";
    }

    private function makeProgress(int $current, int $total): string
    {
        if ($total <= 1) return '';
        $filled = str_repeat('●', $current);
        $empty = str_repeat('○', $total - $current);
        return "━━━ Step {$current}/{$total} ━━━ {$filled}{$empty}";
    }

    private function isCancelIntent(string $lower): bool
    {
        return preg_match('/^(cancel|cancel\s*karo|ruk|stop|nahi\s*chahiye|bhool\s*jao|chhodo|exit)$/i', $lower);
    }

    private function isNewActionIntent(string $lower): bool
    {
        return preg_match('/(naya|new|dubara|phir\s*se|doosra|alag|other|change\s*topic)/i', $lower);
    }

    private function cancelConversation(): array
    {
        $active = $this->getActiveConversation();
        if ($active) {
            $this->updateStatus($active['id'], 'cancelled');
            $flow = self::$flows[$active['current_action']] ?? null;
            return [
                'handled' => true,
                'response' => $flow['cancel_message'] ?? "👍 Cancel kar diya. Koi aur madad chahiye?",
                'action_data' => ['action' => null, 'step' => null, 'suggestions' => []]
            ];
        }
        return [
            'handled' => true,
            'response' => "👍 Koi active conversation nahi thi. Koi aur madad chahiye?",
            'action_data' => ['action' => null, 'step' => null, 'suggestions' => []]
        ];
    }

    private function getActionFollowUps(string $action): array
    {
        $followUps = [
            'post_property' => ['🔄 Post Another Property', '📋 View My Properties', '💬 Chat with Us'],
            'add_lead' => ['➕ Add Another Lead', '📋 View All Leads', '📊 Lead Dashboard'],
            'book_site_visit' => ['📅 Book Another Visit', '📋 My Visits', '💬 Chat with Us'],
            'search_property' => ['🔍 Search Again', '🏠 View All Properties', '📞 Talk to Agent'],
            'register' => ['🏠 Go to Dashboard', '📋 Complete Profile', '🔍 Explore Properties'],
            'file_complaint' => ['📋 View Complaints', '📞 Call Support', '💬 Chat with Us'],
            'check_booking' => ['📋 Check Another', '💰 Make Payment', '💬 Chat with Us'],
        ];
        return $followUps[$action] ?? ['💬 Chat with Us'];
    }

    /**
     * Get suggestions based on user's role — what they CAN do
     */
    private function getRoleBasedSuggestions(): array
    {
        $suggestionMap = [
            'guest' => ['🔍 Search Property', '📅 Book Site Visit', 'Register', '📝 File Complaint'],
            'customer' => ['🔍 Search Property', '📅 Book Visit', '🏠 Post Property', '📋 Check Booking'],
            'associate' => ['➕ Add Lead', '📅 Book Visit', '🏠 Post Property', '🔍 Search Property'],
            'agent' => ['➕ Add Lead', '📅 Book Visit', '🏠 Post Property', '🔍 Search Property'],
            'admin' => ['➕ Add Lead', '🏠 Post Property', '📅 Book Visit', '🔍 Search Property'],
            'manager' => ['➕ Add Lead', '🏠 Post Property', '📅 Book Visit', '🔍 Search Property'],
            'employee' => ['➕ Add Lead', '📅 Book Visit', '🔍 Search Property', '📋 Check Booking'],
            'telecaller' => ['➕ Add Lead', '📋 Check Booking', '🔍 Search Property'],
        ];
        return $suggestionMap[$this->userRole] ?? ['🔍 Search Property', '💬 Chat with Us'];
    }

    // ─── Database Methods ──────────────────────────────────────

    private function saveConversation(string $action, array $data, int $step, int $stepCount, string $status = 'active'): void
    {
        try {
            // Check for existing active conversation
            $existing = $this->db->fetch(
                "SELECT id FROM ai_chat_conversations WHERE session_id = ?{$this->tenantSql()} AND status = 'active' ORDER BY id DESC LIMIT 1",
                array_merge([$this->sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );

            if ($existing) {
                $this->db->query(
                    "UPDATE ai_chat_conversations SET current_action = ?, collected_data = ?, current_step = ?, step_count = ?, status = ?, updated_at = NOW() WHERE id = ?{$this->tenantSql()}",
                    array_merge([$action, json_encode($data), $step, $stepCount, $status, $existing['id']], $this->tenantId() > 1 ? [$this->tenantId()] : [])
                );
            } else {
                $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['session_id', 'user_id', 'user_role', 'current_action', 'collected_data', 'current_step', 'step_count', 'status'], $tenantCols);
            $values  = array_merge([$this->sessionId, $this->userId, $this->userRole, $action, json_encode($data), $step, $stepCount, $status], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $this->db->query("INSERT INTO ai_chat_conversations ($colStr) VALUES ($placeholders)", $values);
            }
        } catch (\Exception $e) {
            error_log("ConversationEngine save error: " . $e->getMessage());
        }
    }

    private function getActiveConversation(): ?array
    {
        try {
            return $this->db->fetch(
                "SELECT * FROM ai_chat_conversations WHERE session_id = ?{$this->tenantSql()} AND status IN ('active', 'confirm') ORDER BY id DESC LIMIT 1",
                array_merge([$this->sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    private function updateStatus(int $id, string $status): void
    {
        try {
$this->db->query(
                    "UPDATE ai_chat_conversations SET status = ?, updated_at = NOW() WHERE id = ?{$this->tenantSql()}",
                    array_merge([$status, $id], $this->tenantId() > 1 ? [$this->tenantId()] : [])
                );
        } catch (\Exception $e) {
            error_log("ConversationEngine update error: " . $e->getMessage());
        }
    }

    private function clearConversation(): void
    {
        try {
$this->db->query(
                    "UPDATE ai_chat_conversations SET status = 'cancelled' WHERE session_id = ?{$this->tenantSql()} AND status IN ('active', 'confirm')",
                    array_merge([$this->sessionId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
                );
        } catch (\Exception $e) {
            error_log("ConversationEngine clear error: " . $e->getMessage());
        }
    }
}
