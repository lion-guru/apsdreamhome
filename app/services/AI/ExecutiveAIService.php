<?php

namespace App\Services\AI;

/**
 * ExecutiveAIService — CEO-level AI agent for strategic decision support
 * Integrates with all existing AI services and business data sources
 * Provides executive summaries, strategic recommendations, and actionable insights
 */
class ExecutiveAIService
{
    private static $instance = null;
    private $freeAIEngines;
    private $propertyValuationEngine;
    private $leadScorer;
    private $recommendationEngine;
    private $conversationEngine;
    private $db;
    private $systemPrompt;

    private function __construct()
    {
        $this->freeAIEngines = FreeAIEngines::getInstance();
        $this->propertyValuationEngine = new PropertyValuationEngine();
        $this->db = \App\Core\Database\Database::getInstance();
        $this->leadScorer = new LeadScorer($this->db);
        $this->recommendationEngine = new \App\Services\AI\RecommendationEngine($this->db);
        $this->conversationEngine = new ConversationEngine('executive_' . uniqid());
        $this->systemPrompt = $this->buildSystemPrompt();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Build comprehensive system prompt for CEO-level AI
     */
    private function buildSystemPrompt(): string
    {
        return "You are the CEO AI Assistant for APS Dream Home, a premier real estate company in Gorakhpur, Uttar Pradesh. 

You have access to:
- 7 townships (Suryoday, Braj Radha, Raghunath, Budh Bihar, Awadh Vihar, Chandra Green, Shivpuri)
- 756+ plots across all townships
- 38,000+ leads with full tracking
- Complete CRM with associate network, commissions, and wallet systems
- Township operations (water, waste, STP, security, horticulture, CCTV, DG, lighting, RWH)
- Solar subsidy programs (PM Surya Ghar + UP state)
- NRI investment desk with forex support
- Legal/registry pipeline with 7-stage milestone tracking
- Financial dashboards with real-time revenue, commissions, and collections

Your role: Act as a strategic advisor to the CEO. Provide:
1. Executive summaries with key metrics and trends
2. Strategic recommendations based on data analysis
3. Risk identification and mitigation strategies
4. Growth opportunities and resource allocation advice
5. Competitive positioning insights
6. Operational efficiency recommendations

Always respond in a professional, concise, executive-friendly format.
Use bullet points, clear sections, and prioritize actionable insights.
Include confidence levels for predictions.
Never hallucinate data — if unsure, say \"Data not available\" and suggest how to get it.
When asked for specific numbers, query the actual database through available tools.
Language: Professional English with Hindi business terms where appropriate (e.g., 'bigha', 'khasra', 'mutation', 'registry', 'PLC', 'bigha', 'gata').";
    }

    /**
     * Get comprehensive business snapshot for CEO
     */
    public function getBusinessSnapshot(): array
    {
        $snapshot = [
            'timestamp' => date('Y-m-d H:i:s'),
            'properties' => $this->getPropertyMetrics(),
            'leads' => $this->getLeadMetrics(),
            'revenue' => $this->getRevenueMetrics(),
            'team' => $this->getTeamMetrics(),
            'township' => $this->getTownshipMetrics(),
            'commissions' => $this->getCommissionMetrics(),
            'operations' => $this->getOperationsMetrics(),
            'solar' => $this->getSolarMetrics(),
            'nri' => $this->getNRIMetrics(),
        ];
        return $snapshot;
    }

    private function getPropertyMetrics(): array
    {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM plots")['cnt'] ?? 0;
            $available = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM plots WHERE status = 'available'")['cnt'] ?? 0;
            $booked = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM plots WHERE status = 'booked'")['cnt'] ?? 0;
            $sold = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM plots WHERE status = 'sold'")['cnt'] ?? 0;
            $byColony = $this->db->fetchAll("
                SELECT c.name, 
                    COUNT(p.id) as total,
                    SUM(CASE WHEN p.status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN p.status = 'booked' THEN 1 ELSE 0 END) as booked,
                    SUM(CASE WHEN p.status = 'sold' THEN 1 ELSE 0 END) as sold
                FROM plots p
                JOIN colonies c ON p.colony_id = c.id
                GROUP BY c.id, c.name
                ORDER BY c.name
            ");
            return [
                'total' => (int)$total,
                'available' => (int)$available,
                'booked' => (int)$booked,
                'sold' => (int)$sold,
                'conversion_rate' => $total > 0 ? round(($booked + $sold) / $total * 100, 1) : 0,
                'by_colony' => $byColony
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getLeadMetrics(): array
    {
        try {
            $total = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM leads")['cnt'] ?? 0;
            $byStatus = $this->db->fetchAll("
                SELECT status, COUNT(*) as cnt FROM leads GROUP BY status
            ");
            $bySource = $this->db->fetchAll("
                SELECT source, COUNT(*) as cnt FROM leads GROUP BY source ORDER BY cnt DESC LIMIT 10
            ");
            $byAssignee = $this->db->fetchAll("
                SELECT u.name, COUNT(l.id) as cnt 
                FROM leads l 
                JOIN users u ON l.assigned_to = u.id 
                WHERE l.assigned_to IS NOT NULL 
                GROUP BY u.id, u.name 
                ORDER BY cnt DESC LIMIT 10
            ");
            $thisWeek = $this->db->fetchOne("
                SELECT COUNT(*) as cnt FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ")['cnt'] ?? 0;
            $conversions = $this->db->fetchOne("
                SELECT COUNT(*) as cnt FROM leads WHERE status IN ('site_visit', 'proposal', 'negotiation', 'closed_won')
            ")['cnt'] ?? 0;
            
            return [
                'total' => (int)$total,
                'new_this_week' => (int)$thisWeek,
                'conversion_pipeline' => (int)$conversions,
                'conversion_rate' => $total > 0 ? round($conversions / $total * 100, 1) : 0,
                'by_status' => $byStatus,
                'by_source' => $bySource,
                'top_assignees' => $byAssignee
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getRevenueMetrics(): array
    {
        try {
            $totalRevenue = $this->db->fetchOne("
                SELECT COALESCE(SUM(payment_amount), 0) as total 
                FROM booking_payments 
                WHERE 1=1
            ")['total'] ?? 0;
            
            $thisMonth = $this->db->fetchOne("
                SELECT COALESCE(SUM(payment_amount), 0) as total 
                FROM booking_payments 
                WHERE 1=1 
                AND payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")['total'] ?? 0;
            
            $lastMonth = $this->db->fetchOne("
                SELECT COALESCE(SUM(payment_amount), 0) as total 
                FROM booking_payments 
                WHERE 1=1 
                AND payment_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                AND payment_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")['total'] ?? 0;
            
            $monthlyGrowth = $lastMonth > 0 ? round(($thisMonth - $lastMonth) / $lastMonth * 100, 1) : 0;
            
            $byColony = $this->db->fetchAll("
                SELECT c.name, COALESCE(SUM(bp.payment_amount), 0) as revenue
                FROM booking_payments bp
                JOIN plot_bookings pb ON bp.booking_id = pb.id
                JOIN plots p ON pb.plot_id = p.id
                JOIN colonies c ON p.colony_id = c.id
                WHERE 1=1
                GROUP BY c.id, c.name
                ORDER BY revenue DESC
            ");
            
            $pending = $this->db->fetchOne("
                SELECT COALESCE(SUM(total_amount - paid_amount), 0) as pending
                FROM plot_bookings
                WHERE status IN ('booked', 'pending')
            ")['pending'] ?? 0;
            
            return [
                'total' => (float)$totalRevenue,
                'this_month' => (float)$thisMonth,
                'last_month' => (float)$lastMonth,
                'monthly_growth_pct' => $monthlyGrowth,
                'pending_collections' => (float)$pending,
                'by_colony' => $byColony
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getTeamMetrics(): array
    {
        try {
            $associates = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'associate'")['cnt'] ?? 0;
            $activeAssociates = $this->db->fetchOne("
                SELECT COUNT(DISTINCT u.id) as cnt 
                FROM users u 
                JOIN mlm_commission_ledger cl ON u.id = cl.beneficiary_user_id 
                WHERE u.role = 'associate' 
                AND cl.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")['cnt'] ?? 0;
            
            $customers = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'customer'")['cnt'] ?? 0;
            $employees = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role IN ('employee', 'telecaller', 'admin', 'manager')")['cnt'] ?? 0;
            
            return [
                'total_associates' => (int)$associates,
                'active_associates_30d' => (int)$activeAssociates,
                'total_customers' => (int)$customers,
                'total_employees' => (int)$employees,
                'activation_rate' => $associates > 0 ? round($activeAssociates / $associates * 100, 1) : 0
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getTownshipMetrics(): array
    {
        try {
            $colonies = $this->db->fetchAll("
                SELECT c.id, c.name, c.status,
                    COUNT(p.id) as total_plots,
                    SUM(CASE WHEN p.status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN p.status IN ('booked','sold') THEN 1 ELSE 0 END) as sold
                FROM colonies c
                LEFT JOIN plots p ON c.id = p.colony_id
                GROUP BY c.id, c.name, c.status
            ");
            
            $ops = [
                'water_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_water_logs WHERE log_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'genset_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_genset_logs WHERE log_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'security_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_security_rosters WHERE roster_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'cctv_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_cctv_logs WHERE audit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'waste_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_waste_logs WHERE collection_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'horticulture_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_horticulture_logs WHERE audit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'lighting_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_lighting_logs WHERE audit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'rwh_logs' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_rwh_logs WHERE audit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
                'grievances' => $this->db->fetchOne("SELECT COUNT(*) as cnt FROM township_resident_grievances WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['cnt'] ?? 0,
            ];
            
            return [
                'colonies' => $colonies,
                'weekly_operations' => $ops,
                'total_colonies' => count($colonies),
                'active_colonies' => count(array_filter($colonies, fn($c) => $c['status'] === 'active'))
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getCommissionMetrics(): array
    {
        try {
            $totalPaid = $this->db->fetchOne("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM mlm_commission_ledger 
                WHERE status = 'paid'
            ")['total'] ?? 0;
            
            $pending = $this->db->fetchOne("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM mlm_commission_ledger 
                WHERE status = 'pending'
            ")['total'] ?? 0;
            
            $thisMonth = $this->db->fetchOne("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM mlm_commission_ledger 
                WHERE status = 'paid' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")['total'] ?? 0;
            
            $topEarners = $this->db->fetchAll("
                SELECT u.name, COALESCE(SUM(cl.amount), 0) as earnings
                FROM mlm_commission_ledger cl
                JOIN users u ON cl.beneficiary_user_id = u.id
                WHERE cl.status = 'paid'
                GROUP BY u.id, u.name
                ORDER BY earnings DESC LIMIT 10
            ");
            
            return [
                'total_paid' => (float)$totalPaid,
                'pending' => (float)$pending,
                'this_month' => (float)$thisMonth,
                'top_earners' => $topEarners
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getOperationsMetrics(): array
    {
        try {
            $siteVisits = $this->db->fetchOne("
                SELECT COUNT(*) as cnt FROM site_visits 
                WHERE visit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ")['cnt'] ?? 0;
            
            $completedVisits = $this->db->fetchOne("
                SELECT COUNT(*) as cnt FROM site_visits 
                WHERE status = 'completed' 
                AND visit_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")['cnt'] ?? 0;
            
            $registryPipeline = $this->db->fetchAll("
                SELECT stage_name as stage, COUNT(*) as cnt 
                FROM booking_registry_milestones 
                WHERE status = 'completed'
                GROUP BY stage
            ");
            
            return [
                'site_visits_week' => (int)$siteVisits,
                'completed_visits_month' => (int)$completedVisits,
                'registry_pipeline' => $registryPipeline
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getSolarMetrics(): array
    {
        try {
            $totalRequests = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM customer_solar_requests")['cnt'] ?? 0;
            $byStatus = $this->db->fetchAll("
                SELECT status, COUNT(*) as cnt FROM customer_solar_requests GROUP BY status
            ");
            $totalCapacity = $this->db->fetchOne("
                SELECT COALESCE(SUM(system_capacity_kw), 0) as total FROM customer_solar_requests
            ")['total'] ?? 0;
            
            return [
                'total_requests' => (int)$totalRequests,
                'by_status' => $byStatus,
                'total_capacity_kw' => (float)$totalCapacity
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function getNRIMetrics(): array
    {
        try {
            $totalInvestments = $this->db->fetchOne("SELECT COUNT(*) as cnt FROM nri_investments")['cnt'] ?? 0;
            $byStatus = $this->db->fetchAll("
                SELECT verification_status, COUNT(*) as cnt FROM nri_investments GROUP BY verification_status
            ");
            $totalValue = $this->db->fetchOne("
                SELECT COALESCE(SUM(investment_amount), 0) as total FROM nri_investments
            ")['total'] ?? 0;
            
            return [
                'total_investments' => (int)$totalInvestments,
                'by_status' => $byStatus,
                'total_value' => (float)$totalValue
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Generate executive summary using AI
     */
    public function generateExecutiveSummary(array $context = []): array
    {
        $snapshot = $this->getBusinessSnapshot();
        
        $prompt = "Generate an executive summary for the CEO of APS Dream Home based on this business data:\n\n";
        $prompt .= json_encode($snapshot, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide a concise executive summary with:\n";
        $prompt .= "1. Overall health score (1-10) with justification\n";
        $prompt .= "2. Top 3 achievements this period\n";
        $prompt .= "2. Top 3 risks/concerns requiring attention\n";
        $prompt .= "3. Top 3 strategic recommendations with priority\n";
        $prompt .= "4. Key metrics to watch next week\n";
        $prompt .= "Format as JSON with sections: health_score, achievements, risks, recommendations, watchlist\n";
        $prompt .= "Be specific with numbers from the data. No generic statements.";

        try {
            $result = $this->freeAIEngines->generate($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 1500,
                'purpose' => 'executive'
            ]);
            
            $text = $result['text'] ?? '';
            $jsonText = $this->extractJson($text);
            
            return [
                'success' => true,
                'summary' => $jsonText ?: $this->buildFallbackSummary($snapshot),
                'raw_snapshot' => $snapshot,
                'engine' => $result['engine'] ?? 'unknown',
                'model' => $result['model'] ?? 'unknown'
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'summary' => $this->buildFallbackSummary($snapshot)
            ];
        }
    }

    /**
     * Answer CEO's strategic questions
     */
    public function askCEO(string $question, array $context = []): array
    {
        $snapshot = $this->getBusinessSnapshot();
        
        $prompt = "As CEO AI Assistant for APS Dream Home, answer this strategic question:\n\n";
        $prompt .= "Question: $question\n\n";
        $prompt .= "Business Context:\n";
        $prompt .= json_encode($snapshot, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide a clear, actionable answer with:\n";
        $prompt .= "1. Direct answer\n";
        $prompt .= "2. Supporting data from context\n";
        $prompt .= "3. Recommendations\n";
        $prompt .= "4. Confidence level (0-100%)\n";
        $prompt .= "Format as JSON.";

        try {
            $result = $this->freeAIEngines->generate($prompt, [
                'temperature' => 0.4,
                'max_tokens' => 1200,
                'purpose' => 'executive'
            ]);
            
            $text = $result['text'] ?? '';
            $jsonText = $this->extractJson($text);
            
            return [
                'success' => true,
                'answer' => $jsonText ?? ['direct_answer' => $text, 'confidence' => 70],
                'engine' => $result['engine'] ?? 'unknown',
                'model' => $result['model'] ?? 'unknown'
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'answer' => ['direct_answer' => "Unable to process question: " . $e->getMessage(), 'confidence' => 0]
            ];
        }
    }

    /**
     * Generate strategic report on specific topic
     */
    public function generateStrategicReport(string $topic, array $params = []): array
    {
        $snapshot = $this->getBusinessSnapshot();
        
        $prompt = "Generate a strategic report for the CEO of APS Dream Home on: $topic\n\n";
        $prompt .= "Parameters: " . json_encode($params) . "\n\n";
        $prompt .= "Business Data:\n" . json_encode($snapshot, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Report Structure:\n";
        $prompt .= "1. Executive Summary\n2. Current State Analysis\n3. Trend Analysis\n4. Opportunities\n5. Risks\n6. Recommendations with timeline\n7. Resource Requirements\n8. Success Metrics\n";
        $prompt .= "Format as structured JSON with each section.\n";
        $prompt .= "Use specific numbers from the data. Be actionable.";

        try {
            $result = $this->freeAIEngines->generate($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 2000,
                'purpose' => 'executive'
            ]);
            
            $text = $result['text'] ?? '';
            $jsonText = $this->extractJson($text);
            
            return [
                'success' => true,
                'report' => $jsonText ?: ['executive_summary' => $text],
                'topic' => $topic,
                'engine' => $result['engine'] ?? 'unknown',
                'model' => $result['model'] ?? 'unknown'
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get competitive analysis
     */
    public function getCompetitiveAnalysis(): array
    {
        // Internal metrics from live tables (no external market API)
        $colonies = 0;
        $available = 0;
        $sold = 0;
        $avgPps = 0;
        try {
            $c = $this->db->fetchOne("SELECT COUNT(*) as n FROM colonies WHERE deleted_at IS NULL");
            $colonies = (int) ($c['n'] ?? 0);
            $a = $this->db->fetchOne("SELECT COUNT(*) as n FROM plots WHERE status = 'available'");
            $available = (int) ($a['n'] ?? 0);
            $s = $this->db->fetchOne("SELECT COUNT(*) as n FROM plots WHERE status IN ('booked','sold')");
            $sold = (int) ($s['n'] ?? 0);
            $p = $this->db->fetchOne("SELECT AVG(price_per_sqft) as avg FROM plots WHERE price_per_sqft > 0");
            $avgPps = round((float) ($p['avg'] ?? 0), 0);
        } catch (\Throwable $e) {
            // metrics stay 0
        }
        $sellThrough = ($available + $sold) > 0 ? round($sold / ($available + $sold) * 100, 1) : 0;
        return [
            'market_position' => 'Leading organized real estate developer in Gorakhpur (internal inventory metrics)',
            'data_source' => 'internal_inventory',
            'data_freshness' => 'live',
            'inventory' => [
                'colonies' => $colonies,
                'available_plots' => $available,
                'booked_sold_plots' => $sold,
                'sell_through_pct' => $sellThrough,
                'avg_price_per_sqft' => $avgPps,
            ],
            'key_competitors' => [
                'Local unorganized developers',
                'National chains (DLF, Godrej, etc.) - limited presence',
                'Land brokers and aggregators'
            ],
            'competitive_advantages' => [
                'Verified land with clear titles (143 mutation)',
                'RERA registered townships',
                'Integrated township infrastructure (STP, water, power, security)',
                'Digital CRM with transparent booking',
                'Solar-ready townships (PM Surya Ghar)',
                'NRI-ready with forex support',
                'Free site visits with AC car pickup'
            ],
            'threats' => [
                'Unorganized sector price competition',
                'Regulatory changes (RERA, land ceiling)',
                'Infrastructure delays (road, metro connectivity)',
                'Interest rate fluctuations affecting EMI affordability'
            ],
            'recommendations' => [
                'Accelerate digital marketing for NRI segment',
                'Launch referral program for existing customers',
                'Partner with banks for pre-approved loans',
                'Expand to adjacent districts (Kushinagar, Maharajganj)'
            ]
        ];
    }

    /**
     * Get resource allocation recommendations
     */
    public function getResourceAllocation(): array
    {
        $snapshot = $this->getBusinessSnapshot();
        
        $recommendations = [];
        
        // Sales team allocation
        $leadsPerAssociate = $snapshot['team']['active_associates_30d'] > 0 
            ? $snapshot['leads']['total'] / $snapshot['team']['active_associates_30d'] 
            : 0;
        
        if ($leadsPerAssociate > 500) {
            $recommendations[] = [
                'area' => 'Sales Team',
                'priority' => 'HIGH',
                'issue' => "Each active associate handling ~{$leadsPerAssociate} leads",
                'recommendation' => 'Recruit 3-5 new associates or implement lead prioritization automation',
                'timeline' => '30 days'
            ];
        }
        
        // Inventory focus
        $conversionRate = $snapshot['properties']['conversion_rate'];
        if ($conversionRate < 20) {
            $recommendations[] = [
                'area' => 'Inventory',
                'priority' => 'HIGH',
                'issue' => "Property conversion rate at {$conversionRate}%",
                'recommendation' => 'Focus marketing on top 3 colonies by availability; launch targeted campaigns',
                'timeline' => '15 days'
            ];
        }
        
        // Collections
        $pendingRatio = $snapshot['revenue']['total'] > 0 
            ? $snapshot['revenue']['pending_collections'] / $snapshot['revenue']['total'] 
            : 0;
        if ($pendingRatio > 0.3) {
            $recommendations[] = [
                'area' => 'Collections',
                'priority' => 'MEDIUM',
                'issue' => "Pending collections ratio: " . round($pendingRatio * 100, 1) . "%",
                'recommendation' => 'Deploy dedicated collections team; offer early payment incentives',
                'timeline' => '30 days'
            ];
        }
        
        // Township operations
        $ops = $snapshot['township']['weekly_operations'] ?? [];
        $grievances = $ops['grievances'] ?? 0;
        if ($grievances > 20) {
            $recommendations[] = [
                'area' => 'Township Operations',
                'priority' => 'MEDIUM',
                'issue' => "$grievances resident grievances this week",
                'recommendation' => 'Deploy dedicated grievance resolution team; implement SLA tracking',
                'timeline' => '15 days'
            ];
        }
        
        return [
            'leads_per_associate' => round($leadsPerAssociate, 1),
            'conversion_rate' => $conversionRate,
            'pending_collections_ratio' => round($pendingRatio * 100, 1),
            'weekly_grievances' => $grievances,
            'recommendations' => $recommendations
        ];
    }

    private function extractJson(string $text): ?array
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $jsonText = substr($text, $start, $end - $start + 1);
            // Fix common JSON issues
            $jsonText = preg_replace('/([{,]\s*)([A-Za-z_][A-Za-z0-9_]*)\s*:/', '$1"$2":', $jsonText);
            $json = json_decode($jsonText, true);
            if (is_array($json)) return $json;
        }
        return null;
    }

    private function buildFallbackSummary(array $snapshot): array
    {
        $totalSoldBooked = ($snapshot['properties']['booked'] ?? 0) + ($snapshot['properties']['sold'] ?? 0);
        $pendingRevenue = $snapshot['revenue']['pending_collections'] ?? 0;
        $conversionRate = $snapshot['properties']['conversion_rate'] ?? 0;
        $activeAssociates = $snapshot['team']['active_associates_30d'] ?? 0;
        $grievances = $snapshot['township']['weekly_operations']['grievances'] ?? 0;

        return [
            'health_score' => 7,
            'achievements' => [
                "₹{$snapshot['revenue']['this_month']}L revenue this month",
                "{$totalSoldBooked} plots sold/booked",
                "{$activeAssociates} active associates",
            ],
            'risks' => [
                "Conversion rate: {$conversionRate}%",
                "Conversion rate: {$snapshot['properties']['conversion_rate']}%",
                "{$grievances} grievances this week"
            ],
            'recommendations' => [
                ['priority' => 'HIGH', 'action' => 'Focus on pending collections'],
                ['priority' => 'HIGH', 'action' => 'Improve lead conversion rate'],
                ['priority' => 'MEDIUM', 'action' => 'Deploy grievance resolution team']
            ],
            'watchlist' => [
                'Daily revenue vs target',
                'Lead conversion funnel',
                'Grievance resolution SLA'
            ]
        ];
    }

    /**
     * Execute predefined CEO actions
     */
    public function executeAction(string $action, array $params = []): array
    {
        switch ($action) {
            case 'daily_briefing':
                return $this->generateDailyBriefing();
            case 'weekly_review':
                return $this->generateWeeklyReview();
            case 'resource_allocation':
                return $this->getResourceAllocation();
            case 'competitive_analysis':
                return $this->getCompetitiveAnalysis();
            case 'strategic_report':
                return $this->generateStrategicReport($params['topic'] ?? 'Business Overview', $params);
            case 'answer_question':
                return $this->askCEO($params['question'] ?? '', $params);
            case 'executive_summary':
                return $this->generateExecutiveSummary();
            default:
                return ['success' => false, 'error' => "Unknown action: $action"];
        }
    }

    private function generateDailyBriefing(): array
    {
        $snapshot = $this->getBusinessSnapshot();
        
        return [
            'success' => true,
            'type' => 'daily_briefing',
            'date' => date('Y-m-d'),
            'metrics' => [
                'new_leads_today' => $this->db->fetchOne("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")['cnt'] ?? 0,
                'bookings_today' => $this->db->fetchOne("SELECT COUNT(*) FROM plot_bookings WHERE DATE(created_at) = CURDATE()")['cnt'] ?? 0,
                'collections_today' => $this->db->fetchOne("SELECT COALESCE(SUM(payment_amount),0) FROM booking_payments WHERE DATE(payment_date) = CURDATE() ")['cnt'] ?? 0,
                'site_visits_today' => $this->db->fetchOne("SELECT COUNT(*) FROM site_visits WHERE DATE(visit_date) = CURDATE()")['cnt'] ?? 0,
                'grievances_today' => $this->db->fetchOne("SELECT COUNT(*) FROM township_resident_grievances WHERE DATE(created_at) = CURDATE()")['cnt'] ?? 0,
            ],
            'top_priorities' => [
                'Follow up on high-value leads',
                'Process pending collections',
                'Review grievance SLA compliance'
            ]
        ];
    }

    private function generateWeeklyReview(): array
    {
        $snapshot = $this->getBusinessSnapshot();
        
        return [
            'success' => true,
            'type' => 'weekly_review',
            'week_ending' => date('Y-m-d'),
            'summary' => $this->generateExecutiveSummary($snapshot)['summary'] ?? [],
            'key_metrics' => [
                'leads_generated' => $snapshot['leads']['new_this_week'] ?? 0,
                'conversion_rate' => $snapshot['leads']['conversion_rate'] ?? 0,
                'revenue_growth' => $snapshot['revenue']['monthly_growth_pct'] ?? 0,
                'team_activation' => $snapshot['team']['activation_rate'] ?? 0,
            ],
            'action_items' => $this->getResourceAllocation()['recommendations'] ?? []
        ];
    }

    /**
     * Health check for the CEO AI service
     */
    public function healthCheck(): array
    {
        $checks = [
            'free_ai_engines' => $this->freeAIEngines ? 'OK' : 'UNAVAILABLE',
            'property_valuation' => $this->propertyValuationEngine ? 'OK' : 'UNAVAILABLE',
            'lead_scorer' => $this->leadScorer ? 'OK' : 'UNAVAILABLE',
            'recommendation_engine' => $this->recommendationEngine ? 'OK' : 'UNAVAILABLE',
            'database' => $this->db ? 'OK' : 'UNAVAILABLE',
        ];
        
        $allOk = !in_array('UNAVAILABLE', $checks, true);
        
        return [
            'status' => $allOk ? 'HEALTHY' : 'DEGRADED',
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}