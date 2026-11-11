<?php
/**
 * EmailClassificationController - Email Classification & Smart Routing
 * Categorizes emails: inquiry, complaint, support, spam, referral
 * Auto-assigns to best associate/team based on expertise/load
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\AI\FreeAIEngines;

class EmailClassificationController extends BaseController
{
    protected $aiEngines;

    public function __construct()
    {
        parent::__construct();
        $this->aiEngines = FreeAIEngines::getInstance();
    }

    protected function skipCsrfProtection(): bool
    {
        return true; // Bearer-token API; CSRF handled by auth
    }

    /**
     * POST /api/ai/email/classify
     * Classify email content
     */
    public function classify()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $input = $this->getJsonInput();
            $emailContent = trim($input['content'] ?? '');
            $subject = trim($input['subject'] ?? '');
            $sender = trim($input['sender'] ?? '');

            if (empty($emailContent)) {
                return $this->jsonError('Email content is required', 400);
            }

            $classification = $this->classifyEmail($subject, $emailContent, $sender);

            // Log classification for training
            $this->logClassification($userId, $subject, $emailContent, $classification);

            return $this->jsonSuccess($classification);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Email Classification');
        }
    }

    /**
     * POST /api/ai/email/route
     * Smart routing to best associate/team
     */
    public function route()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $input = $this->getJsonInput();
            $classification = $input['classification'] ?? [];
            $leadData = $input['lead_data'] ?? [];

            if (empty($classification)) {
                return $this->jsonError('Classification data required', 400);
            }

            $routing = $this->determineRouting($classification, $leadData);

            // Log routing decision
            $this->logRouting($userId, $classification, $routing);

            return $this->jsonSuccess($routing);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Email Routing');
        }
    }

    /**
     * POST /api/ai/email/batch-classify
     * Batch classify multiple emails
     */
    public function batchClassify()
    {
        $this->setCorsHeaders();
        try {
            $userId = (int)($GLOBALS['api_user_id'] ?? 0);
            if (!$userId) {
                return $this->jsonError('Authentication required', 401);
            }

            $input = $this->getJsonInput();
            $emails = $input['emails'] ?? [];

            if (empty($emails) || !is_array($emails)) {
                return $this->jsonError('Emails array required', 400);
            }

            $results = [];
            foreach ($emails as $index => $email) {
                $classification = $this->classifyEmail(
                    $email['subject'] ?? '',
                    $email['content'] ?? '',
                    $email['sender'] ?? ''
                );
                $results[] = [
                    'index' => $index,
                    'classification' => $classification
                ];
            }

            return $this->jsonSuccess(['results' => $results]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Batch Email Classification');
        }
    }

    /**
     * GET /api/ai/email/categories
     * Get available classification categories
     */
    public function categories()
    {
        $this->setCorsHeaders();
        try {
            $categories = [
                'inquiry' => [
                    'label' => 'Property Inquiry',
                    'description' => 'Questions about properties, pricing, availability',
                    'keywords' => ['plot', 'price', 'availability', 'visit', 'booking', 'emi', 'colony'],
                    'priority' => 'high',
                    'sla_hours' => 4
                ],
                'complaint' => [
                    'label' => 'Complaint',
                    'description' => 'Customer complaints, issues, grievances',
                    'keywords' => ['complaint', 'issue', 'problem', 'wrong', 'delay', 'unhappy', 'disappointed'],
                    'priority' => 'critical',
                    'sla_hours' => 2
                ],
                'support' => [
                    'label' => 'Support Request',
                    'description' => 'Technical support, account help, document requests',
                    'keywords' => ['help', 'support', 'document', 'certificate', 'password', 'login', 'account'],
                    'priority' => 'medium',
                    'sla_hours' => 24
                ],
                'referral' => [
                    'label' => 'Referral',
                    'description' => 'Referral leads, partner inquiries',
                    'keywords' => ['referral', 'refer', 'partner', 'associate', 'commission', 'network'],
                    'priority' => 'high',
                    'sla_hours' => 4
                ],
                'spam' => [
                    'label' => 'Spam/Irrelevant',
                    'description' => 'Marketing, promotional, irrelevant content',
                    'keywords' => ['unsubscribe', 'promotion', 'offer', 'discount', 'free', 'click here'],
                    'priority' => 'low',
                    'sla_hours' => 0
                ],
                'payment' => [
                    'label' => 'Payment Related',
                    'description' => 'Payment confirmations, receipts, EMI queries',
                    'keywords' => ['payment', 'paid', 'receipt', 'emi', 'installment', 'transaction', 'upi', 'razorpay'],
                    'priority' => 'high',
                    'sla_hours' => 4
                ],
                'legal' => [
                    'label' => 'Legal/Registry',
                    'description' => 'Registry, legal documents, compliance',
                    'keywords' => ['registry', 'legal', 'lawyer', 'court', 'mutation', 'deed', 'agreement', 'stamp duty'],
                    'priority' => 'high',
                    'sla_hours' => 4
                ]
            ];

            return $this->jsonSuccess(['categories' => $categories]);

        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Get Categories');
        }
    }

    /**
     * Classify email using AI with keyword fallback
     */
    private function classifyEmail($subject, $content, $sender)
    {
        $fullText = strtolower($subject . ' ' . $content . ' ' . $sender);
        
        // Keyword-based classification (fast, no API call)
        $categoryScores = [
            'inquiry' => 0,
            'complaint' => 0,
            'support' => 0,
            'referral' => 0,
            'spam' => 0,
            'payment' => 0,
            'legal' => 0
        ];

        $keywords = [
            'inquiry' => ['plot', 'price', 'availability', 'visit', 'booking', 'emi', 'colony', 'property', 'size', 'area', 'bhk', 'budget'],
            'complaint' => ['complaint', 'issue', 'problem', 'wrong', 'delay', 'unhappy', 'disappointed', 'angry', 'frustrated', 'poor', 'bad'],
            'support' => ['help', 'support', 'document', 'certificate', 'password', 'login', 'account', 'access', 'reset', 'technical'],
            'referral' => ['referral', 'refer', 'partner', 'associate', 'commission', 'network', 'join', 'team', 'mlm'],
            'spam' => ['unsubscribe', 'promotion', 'offer', 'discount', 'free', 'click here', 'buy now', 'limited time', 'guaranteed'],
            'payment' => ['payment', 'paid', 'receipt', 'emi', 'installment', 'transaction', 'upi', 'razorpay', 'bank', 'transfer', 'neft', 'rtgs'],
            'legal' => ['registry', 'legal', 'lawyer', 'court', 'mutation', 'deed', 'agreement', 'stamp duty', 'law', 'compliance', 'noc']
        ];

        foreach ($keywords as $category => $words) {
            foreach ($words as $word) {
                if (strpos($fullText, $word) !== false) {
                    $categoryScores[$category] += 2;
                }
            }
        }

        // Boost scores based on subject
        $subjectLower = strtolower($subject);
        foreach ($categoryScores as $category => &$score) {
            foreach ($keywords[$category] as $word) {
                if (strpos($subjectLower, $word) !== false) {
                    $score += 3; // Subject matches are more important
                }
            }
        }

        // Find top category
        arsort($categoryScores);
        $topCategory = array_key_first($categoryScores);
        $topScore = $categoryScores[$topCategory];
        $totalScore = array_sum($categoryScores);
        $confidence = $totalScore > 0 ? round(($topScore / $totalScore) * 100) : 0;

        // If confidence too low, use AI
        if ($confidence < 50) {
            $aiClassification = $this->classifyWithAI($subject, $content, $sender);
            if ($aiClassification) {
                return array_merge($aiClassification, ['method' => 'ai']);
            }
        }

        // Category metadata
        $categoryMeta = [
            'inquiry' => ['label' => 'Property Inquiry', 'priority' => 'high', 'sla_hours' => 4],
            'complaint' => ['label' => 'Complaint', 'priority' => 'critical', 'sla_hours' => 2],
            'support' => ['label' => 'Support Request', 'priority' => 'medium', 'sla_hours' => 24],
            'referral' => ['label' => 'Referral', 'priority' => 'high', 'sla_hours' => 4],
            'spam' => ['label' => 'Spam/Irrelevant', 'priority' => 'low', 'sla_hours' => 0],
            'payment' => ['label' => 'Payment Related', 'priority' => 'high', 'sla_hours' => 4],
            'legal' => ['label' => 'Legal/Registry', 'priority' => 'high', 'sla_hours' => 4]
        ];

        return [
            'category' => $topCategory,
            'label' => $categoryMeta[$topCategory]['label'] ?? $topCategory,
            'confidence' => $confidence,
            'priority' => $categoryMeta[$topCategory]['priority'] ?? 'medium',
            'sla_hours' => $categoryMeta[$topCategory]['sla_hours'] ?? 24,
            'scores' => $categoryScores,
            'method' => 'keyword'
        ];
    }

    /**
     * Use AI for classification when keywords fail
     */
    private function classifyWithAI($subject, $content, $sender)
    {
        try {
            $prompt = "Classify this email into ONE category: inquiry, complaint, support, referral, spam, payment, or legal.\n\nSubject: {$subject}\nFrom: {$sender}\nContent: {$content}\n\nReply with ONLY the category name.";

            $result = $this->aiEngines->generate($prompt, [
                'temperature' => 0.1,
                'max_tokens' => 20
            ]);

            if ($result && isset($result['text'])) {
                $category = strtolower(trim($result['text']));
                $validCategories = ['inquiry', 'complaint', 'support', 'referral', 'spam', 'payment', 'legal'];
                
                if (in_array($category, $validCategories)) {
                    $meta = [
                        'inquiry' => ['label' => 'Property Inquiry', 'priority' => 'high', 'sla_hours' => 4],
                        'complaint' => ['label' => 'Complaint', 'priority' => 'critical', 'sla_hours' => 2],
                        'support' => ['label' => 'Support Request', 'priority' => 'medium', 'sla_hours' => 24],
                        'referral' => ['label' => 'Referral', 'priority' => 'high', 'sla_hours' => 4],
                        'spam' => ['label' => 'Spam/Irrelevant', 'priority' => 'low', 'sla_hours' => 0],
                        'payment' => ['label' => 'Payment Related', 'priority' => 'high', 'sla_hours' => 4],
                        'legal' => ['label' => 'Legal/Registry', 'priority' => 'high', 'sla_hours' => 4]
                    ];

                    return [
                        'category' => $category,
                        'label' => $meta[$category]['label'] ?? $category,
                        'confidence' => 85,
                        'priority' => $meta[$category]['priority'] ?? 'medium',
                        'sla_hours' => $meta[$category]['sla_hours'] ?? 24,
                        'method' => 'ai'
                    ];
                }
            }
        } catch (\Throwable $e) {
            error_log("AI email classification failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Determine routing based on classification and lead data
     */
    private function determineRouting($classification, $leadData)
    {
        $category = $classification['category'] ?? 'support';
        $priority = $classification['priority'] ?? 'medium';
        $slaHours = $classification['sla_hours'] ?? 24;

        // Get available associates/teams
        $associates = $this->getAvailableAssociates($category);

        if (empty($associates)) {
            return [
                'assigned_to' => null,
                'team' => 'general_support',
                'reason' => 'No available associates, routed to general queue',
                'sla_hours' => $slaHours,
                'priority' => $priority
            ];
        }

        // Score associates based on expertise and current load
        $bestAssociate = $this->scoreAssociates($associates, $category, $leadData);

        return [
            'assigned_to' => $bestAssociate['id'],
            'associate_name' => $bestAssociate['name'],
            'team' => $bestAssociate['team'] ?? 'general',
            'reason' => "Best match for {$classification['label']} - expertise: {$bestAssociate['expertise']}, current load: {$bestAssociate['current_load']} leads",
            'sla_hours' => $slaHours,
            'priority' => $priority,
            'estimated_response' => date('Y-m-d H:i:s', strtotime("+{$slaHours} hours"))
        ];
    }

    /**
     * Get available associates for a category
     */
    private function getAvailableAssociates($category)
    {
        $expertiseMap = [
            'inquiry' => ['sales', 'property'],
            'complaint' => ['support', 'customer_care'],
            'support' => ['support', 'technical'],
            'referral' => ['sales', 'network'],
            'payment' => ['finance', 'accounts'],
            'legal' => ['legal', 'compliance']
        ];

        $requiredExpertise = $expertiseMap[$category] ?? ['general'];

        $placeholders = implode(',', array_fill(0, count($requiredExpertise), '?'));
        $sql = "SELECT u.id, u.name, u.role, e.department, e.expertise, 
                (SELECT COUNT(*) FROM leads l WHERE l.assigned_to = u.id AND l.status IN ('new', 'contacted', 'qualified')) as current_load
                FROM users u
                LEFT JOIN employees e ON e.user_id = u.id
                WHERE u.role IN ('associate', 'agent', 'manager') 
                AND u.status = 'active'
                AND (" . implode(' OR ', array_fill(0, count($requiredExpertise), "e.expertise LIKE ?")) . ")
                ORDER BY current_load ASC";

        $params = [];
        foreach ($requiredExpertise as $exp) {
            $params[] = "%{$exp}%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Score associates and pick best
     */
    private function scoreAssociates($associates, $category, $leadData)
    {
        $best = null;
        $bestScore = -1;

        foreach ($associates as $assoc) {
            $score = 0;

            // Load factor (lower is better)
            $load = (int)($assoc['current_load'] ?? 0);
            $score += max(0, 100 - $load * 10);

            // Expertise match
            $expertise = strtolower($assoc['expertise'] ?? '');
            $categoryKeywords = [
                'inquiry' => ['sales', 'property', 'plots'],
                'complaint' => ['support', 'care', 'customer'],
                'support' => ['support', 'technical', 'help'],
                'referral' => ['network', 'referral', 'mlm'],
                'payment' => ['finance', 'accounts', 'payment'],
                'legal' => ['legal', 'compliance', 'registry']
            ];

            foreach ($categoryKeywords[$category] ?? [] as $kw) {
                if (strpos($expertise, $kw) !== false) {
                    $score += 20;
                }
            }

            // Department match
            $dept = strtolower($assoc['department'] ?? '');
            if (in_array($dept, ['sales', 'support', 'finance', 'legal', 'customer_care'])) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $assoc;
            }
        }

        return $best ?: $associates[0];
    }

    /**
     * Log classification for analytics
     */
    private function logClassification($userId, $subject, $content, $classification)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO email_classifications (user_id, subject, content_hash, category, confidence, priority, method, created_at) VALUES (?, ?, MD5(?), ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $userId,
                substr($subject, 0, 255),
                $content,
                $classification['category'],
                $classification['confidence'],
                $classification['priority'],
                $classification['method'] ?? 'keyword'
            ]);
        } catch (\Throwable $e) {
            error_log("Failed to log classification: " . $e->getMessage());
        }
    }

    /**
     * Log routing decision
     */
    private function logRouting($userId, $classification, $routing)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO email_routings (user_id, category, assigned_to, team, reason, sla_hours, priority, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $userId,
                $classification['category'],
                $routing['assigned_to'] ?? null,
                $routing['team'] ?? 'general',
                $routing['reason'] ?? '',
                $routing['sla_hours'] ?? 24,
                $routing['priority'] ?? 'medium'
            ]);
        } catch (\Throwable $e) {
            error_log("Failed to log routing: " . $e->getMessage());
        }
    }
}