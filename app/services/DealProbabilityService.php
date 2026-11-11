<?php
/**
 * DealProbabilityService — Calculate deal win probability
 */

namespace App\Services;

use App\Core\Database\Database;

class DealProbabilityService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Calculate deal probability for a booking/deal
     */
    public function calculateProbability(int $bookingId): array
    {
        $booking = $this->db->fetch("
            SELECT pb.*, p.name as plot_name, p.base_price, p.colony_id,
                   c.name as colony_name, l.name as lead_name, l.phone, l.email,
                   l.budget, l.source, l.status as lead_status,
                   u.name as associate_name, u.id as associate_id
            FROM plot_bookings pb
            LEFT JOIN plots p ON pb.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN leads l ON pb.lead_id = l.id
            LEFT JOIN users u ON pb.associate_id = u.id
            WHERE pb.id = ?
        ", [$bookingId]);

        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found'];
        }

        // Calculate probability factors
        $factors = [
            'stage' => $this->scoreStage($booking),
            'payment_progress' => $this->scorePaymentProgress($booking),
            'lead_quality' => $this->scoreLeadQuality($booking),
            'associate_performance' => $this->scoreAssociatePerformance($booking),
            'colony_demand' => $this->scoreColonyDemand($booking),
            'payment_history' => $this->scorePaymentHistory($booking),
            'time_in_stage' => $this->scoreTimeInStage($booking),
        ];

        // Weighted probability
        $weights = [
            'stage' => 0.30,
            'payment_progress' => 0.25,
            'lead_quality' => 0.15,
            'associate_performance' => 0.10,
            'colony_demand' => 0.10,
            'payment_history' => 0.05,
            'time_in_stage' => 0.05,
        ];

        $probability = 0;
        foreach ($weights as $factor => $weight) {
            $probability += ($factors[$factor] ?? 0) * $weight;
        }

        $probability = round(min(95, max(5, $probability)));

        // Get similar historical deals
        $historical = $this->getHistoricalComparison($booking);

        // Risk factors
        $risks = $this->identifyRisks($booking, $factors);

        return [
            'success' => true,
            'booking_id' => $bookingId,
            'probability' => $probability,
            'tier' => $this->getProbabilityTier($probability),
            'factors' => $factors,
            'weights' => $weights,
            'historical_comparison' => $historical,
            'risk_factors' => $risks,
            'recommendations' => $this->getRecommendations($booking, $factors, $risks),
        ];
    }

    /**
     * Score based on booking stage
     */
    private function scoreStage(array $booking): float
    {
        $stageScores = [
            'token' => 15,
            'pending' => 25,
            'confirmed' => 40,
            'ats' => 55,
            'full_payment' => 70,
            'stamp_duty' => 80,
            'registrar_appointment' => 90,
            'deed_upload' => 95,
            'registration_done' => 100,
            'hold' => 30,
            'cancelled' => 0,
        ];

        return $stageScores[$booking['status'] ?? 'pending'] ?? 25;
    }

    /**
     * Score based on payment progress
     */
    private function scorePaymentProgress(array $booking): float
    {
        $total = $booking['total_amount'] ?? $booking['total_price'] ?? 0;
        $paid = $booking['paid_amount'] ?? 0;
        
        if ($total <= 0) return 50;
        
        $ratio = $paid / $total;
        return round(min(100, $ratio * 100));
    }

    /**
     * Score based on lead quality
     */
    private function scoreLeadQuality(array $booking): float
    {
        $score = 50; // base

        // Source quality
        $sourceScores = [
            'referral' => 90,
            'walkin' => 85,
            'website' => 80,
            'call' => 75,
            'social_media' => 65,
            'google' => 70,
            'exhibition' => 75,
        ];
        $source = strtolower($booking['source'] ?? 'unknown');
        $score += $sourceScores[$source] ?? 50;

        // Budget match
        if (!empty($booking['budget']) && !empty($booking['base_price'])) {
            $ratio = $booking['budget'] / $booking['base_price'];
            if ($ratio >= 1.0) $score += 20;
            elseif ($ratio >= 0.9) $score += 15;
            elseif ($ratio >= 0.8) $score += 10;
        }

        // Contact completeness
        if (!empty($booking['phone'])) $score += 5;
        if (!empty($booking['email'])) $score += 5;

        return min(100, $score);
    }

    /**
     * Score based on associate performance
     */
    private function scoreAssociatePerformance(array $booking): float
    {
        $associateId = $booking['associate_id'] ?? 0;
        if (!$associateId) return 50;

        // Get associate's conversion rate
        $stats = $this->db->fetch("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status IN ('registration_done','deed_upload') THEN 1 ELSE 0 END) as closed,
                AVG(paid_amount/total_amount) as avg_payment_ratio
            FROM plot_bookings
            WHERE associate_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ", [$associateId]);

        if (!$stats || $stats['total_bookings'] < 5) return 50;

        $conversionRate = $stats['closed'] / $stats['total_bookings'];
        $paymentRatio = $stats['avg_payment_ratio'] ?? 0.5;

        return min(100, ($conversionRate * 60) + ($paymentRatio * 40));
    }

    /**
     * Score based on colony demand
     */
    private function scoreColonyDemand(array $booking): float
    {
        $colonyId = $booking['colony_id'] ?? 0;
        if (!$colonyId) return 50;

        // Recent bookings in colony
        $recentBookings = $this->db->fetchColumn("
            SELECT COUNT(*) FROM plot_bookings 
            WHERE colony_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ", [$colonyId]) ?? 0;

        // Available vs total
        $stats = $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available
            FROM plots WHERE colony_id = ?
        ", [$colonyId]);

        if (!$stats || $stats['total'] == 0) return 50;

        $demandRatio = $stats['total'] > 0 ? $recentBookings / $stats['total'] : 0;
        $availabilityRatio = $stats['total'] > 0 ? $stats['available'] / $stats['total'] : 1;

        // High demand + low availability = high score
        $demandScore = min(100, $demandRatio * 200);
        $availabilityScore = max(0, (1 - $availabilityRatio) * 100);

        return ($demandScore * 0.6) + ($availabilityScore * 0.4);
    }

    /**
     * Score based on payment history
     */
    private function scorePaymentHistory(array $booking): float
    {
        $bookingId = $booking['id'];
        
        // Check for late payments
        $latePayments = $this->db->fetchColumn("
            SELECT COUNT(*) FROM booking_payment_schedules 
            WHERE booking_id = ? AND status = 'overdue'
        ", [$bookingId]) ?? 0;

        $totalSchedules = $this->db->fetchColumn("
            SELECT COUNT(*) FROM booking_payment_schedules 
            WHERE booking_id = ?
        ", [$bookingId]) ?? 1;

        if ($totalSchedules == 0) return 50;

        $onTimeRatio = 1 - ($latePayments / $totalSchedules);
        return round($onTimeRatio * 100);
    }

    /**
     * Score based on time in current stage
     */
    private function scoreTimeInStage(array $booking): float
    {
        $status = $booking['status'] ?? 'pending';
        $updatedAt = $booking['updated_at'] ?? $booking['created_at'];
        
        if (!$updatedAt) return 50;

        $daysInStage = (time() - strtotime($updatedAt)) / 86400;

        // Optimal time per stage
        $optimalDays = [
            'token' => 7,
            'pending' => 14,
            'confirmed' => 21,
            'ats' => 30,
            'full_payment' => 14,
            'stamp_duty' => 21,
            'registrar_appointment' => 14,
            'deed_upload' => 14,
        ];

        $optimal = $optimalDays[$status] ?? 30;
        
        if ($daysInStage <= $optimal * 0.5) return 100;
        if ($daysInStage <= $optimal) return 85;
        if ($daysInStage <= $optimal * 1.5) return 70;
        if ($daysInStage <= $optimal * 2) return 55;
        return 30;
    }

    /**
     * Get historical comparison
     */
    private function getHistoricalComparison(array $booking): array
    {
        $colonyId = $booking['colony_id'] ?? 0;
        $price = $booking['base_price'] ?? $booking['total_price'] ?? 0;

        // Similar deals in same colony
        $similar = $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('registration_done','deed_upload') THEN 1 ELSE 0 END) as won,
                AVG(paid_amount/total_amount) as avg_payment_ratio
            FROM plot_bookings
            WHERE colony_id = ? 
            AND base_price BETWEEN ? AND ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ", [$colonyId, $price * 0.7, $price * 1.3]);

        if (!$similar || $similar['total'] == 0) {
            return ['note' => 'Insufficient historical data'];
        }

        $conversionRate = $similar['won'] / max(1, $similar['total']);

        return [
            'similar_deals' => (int)$similar['total'],
            'won_deals' => (int)$similar['won'],
            'conversion_rate' => round($conversionRate * 100, 1),
            'avg_payment_ratio' => round(($similar['avg_payment_ratio'] ?? 0) * 100, 1),
            'note' => $similar['total'] < 10 ? 'Limited sample size' : 'Good sample size',
        ];
    }

    /**
     * Identify risk factors
     */
    private function identifyRisks(array $booking, array $factors): array
    {
        $risks = [];

        if ($factors['payment_progress'] < 30) {
            $risks[] = ['type' => 'payment', 'severity' => 'high', 'message' => 'Low payment progress - risk of cancellation'];
        }

        if ($factors['time_in_stage'] < 40) {
            $risks[] = ['type' => 'stalling', 'severity' => 'medium', 'message' => 'Deal stalled in current stage'];
        }

        if ($factors['payment_history'] < 70) {
            $risks[] = ['type' => 'payment', 'severity' => 'high', 'message' => 'History of late payments'];
        }

        if ($factors['lead_quality'] < 50) {
            $risks[] = ['type' => 'lead', 'severity' => 'medium', 'message' => 'Low lead quality - may need nurturing'];
        }

        if ($factors['associate_performance'] < 50) {
            $risks[] = ['type' => 'associate', 'severity' => 'medium', 'message' => 'Associate has low conversion rate'];
        }

        if ($factors['colony_demand'] < 40) {
            $risks[] = ['type' => 'market', 'severity' => 'low', 'message' => 'Low demand in colony'];
        }

        return $risks;
    }

    /**
     * Get probability tier
     */
    private function getProbabilityTier(int $probability): string
    {
        if ($probability >= 80) return 'Very High';
        if ($probability >= 60) return 'High';
        if ($probability >= 40) return 'Medium';
        if ($probability >= 20) return 'Low';
        return 'Very Low';
    }

    /**
     * Get actionable recommendations
     */
    private function getRecommendations(array $booking, array $factors, array $risks): array
    {
        $recommendations = [];

        foreach ($risks as $risk) {
            switch ($risk['type']) {
                case 'payment':
                    $recommendations[] = [
                        'action' => 'Follow up on payment',
                        'priority' => $risk['severity'],
                        'details' => 'Contact lead for pending payment. Offer payment plan if needed.',
                    ];
                    break;
                case 'stalling':
                    $recommendations[] = [
                        'action' => 'Re-engage lead',
                        'priority' => $risk['severity'],
                        'details' => 'Schedule follow-up call. Share new offers or colony updates.',
                    ];
                    break;
                case 'lead':
                    $recommendations[] = [
                        'action' => 'Qualify lead further',
                        'priority' => $risk['severity'],
                        'details' => 'Understand budget and timeline better. Add to nurture campaign.',
                    ];
                    break;
                case 'associate':
                    $recommendations[] = [
                        'action' => 'Provide associate support',
                        'priority' => $risk['severity'],
                        'details' => 'Pair with senior associate or provide additional training.',
                    ];
                    break;
                case 'market':
                    $recommendations[] = [
                        'action' => 'Review pricing strategy',
                        'priority' => $risk['severity'],
                        'details' => 'Consider promotional offers or price adjustment for the colony.',
                    ];
                    break;
            }
        }

        // Add proactive recommendations based on stage
        $stage = $booking['status'] ?? 'pending';
        if (in_array($stage, ['token', 'pending'])) {
            $recommendations[] = [
                'action' => 'Schedule site visit',
                'priority' => 'high',
                'details' => 'Convert token to confirmed booking with site visit within 7 days.',
            ];
        } elseif ($stage === 'ats') {
            $recommendations[] = [
                'action' => 'Complete ATS documentation',
                'priority' => 'high',
                'details' => 'Collect all required documents for ATS submission within 3 days.',
            ];
        } elseif ($stage === 'full_payment') {
            $recommendations[] = [
                'action' => 'Initiate stamp duty process',
                'priority' => 'high',
                'details' => 'Prepare stamp duty calculation and schedule payment.',
            ];
        }

        return $recommendations;
    }

    /**
     * Get probability for all bookings (bulk)
     */
    public function bulkCalculate(array $bookingIds): array
    {
        $results = [];
        foreach ($bookingIds as $id) {
            $result = $this->calculateProbability($id);
            if ($result['success']) {
                $results[] = [
                    'booking_id' => $id,
                    'probability' => $result['probability'],
                    'tier' => $result['tier'],
                ];
            } else {
                $results[] = ['booking_id' => $id, 'error' => $result['error']];
            }
        }
        return ['success' => true, 'results' => $results];
    }
}