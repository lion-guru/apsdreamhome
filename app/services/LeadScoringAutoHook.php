<?php

namespace App\Services;

use App\Services\Lead\LeadService as UnifiedLeadService;

/**
 * LeadScoringAutoHook - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\Lead\LeadService instead
 * Lead scoring auto-hooks merged into unified LeadService.
 */
class LeadScoringAutoHook extends UnifiedLeadService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Score a single lead (best-effort, never throws)
     */
    public static function scoreLead(int $leadId): int
    {
        if ($leadId <= 0) return 0;
        try {
            $service = new self();
            $result = $service->calculateScore($leadId);
            return $result['success'] ? (int)($result['score'] ?? 0) : 0;
        } catch (\Throwable $e) {
            error_log("LeadScoringAutoHook::scoreLead($leadId) failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Score multiple leads (best-effort)
     */
    public static function scoreLeads(array $leadIds): array
    {
        $results = [];
        foreach ($leadIds as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $results[$id] = self::scoreLead($id);
            }
        }
        return $results;
    }

    /**
     * Called after a new lead is created
     */
    public static function onLeadCreated(int $leadId): void
    {
        if ($leadId <= 0) return;
        self::scoreLead($leadId);
    }

    /**
     * Called after a lead is updated
     */
    public static function onLeadUpdated(int $leadId): void
    {
        if ($leadId <= 0) return;
        self::scoreLead($leadId);
    }

    /**
     * Called after a lead status changes
     */
    public static function onStatusChanged(int $leadId, string $newStatus): void
    {
        if ($leadId <= 0) return;
        self::scoreLead($leadId);
    }

    /**
     * Called after a payment is recorded
     */
    public static function onPaymentRecorded(int $leadId, float $amount): void
    {
        if ($leadId <= 0) return;
        self::scoreLead($leadId);
    }

    /**
     * Called after a site visit is completed
     */
    public static function onSiteVisitCompleted(int $leadId): void
    {
        if ($leadId <= 0) return;
        self::scoreLead($leadId);
    }

    /**
     * @deprecated Use LeadService directly
     */
    public static function getService(): \App\Services\Lead\LeadService
    {
        return new \App\Services\Lead\LeadService();
    }
}
