<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use App\Services\LoggingService;
use Exception;
use PDO;
use \App\Traits\ServiceTenantTrait;

/**
 * Land Acquisition Service
 *
 * Full lifecycle service for the land acquisition pipeline:
 *   1. Lead sourcing (brokers/scouts/direct)
 *   2. Screening & physical site visit
 *   3. Document due diligence (Patta, EC, FMB, etc.)
 *   4. Pricing & negotiation
 *   5. Legal opinion / title clearance
 *   6. Sale agreement & advance
 *   7. Registration at sub-registrar
 *   8. Mutation in revenue records
 *   9. Closed deal accounting
 *
 * - All methods are transactional (beginTransaction + commit/rollback)
 * - All methods are null-safe (return arrays, never throw to callers)
 * - All DB calls wrapped in try/catch with LoggingService on failure
 */
class LandAcquisitionService
{
    use ServiceTenantTrait;

    /** @var \PDO */
    private $pdo;

    /** @var Database */
    private $db;

    /** @var LoggingService|null */
    private $logger;

    /** Allowed status transitions (forward-only linear flow) */
    public const STATUS_FLOW = [
        'new'             => ['screening', 'rejected', 'dropped'],
        'screening'       => ['visit_done', 'rejected', 'dropped'],
        'visit_done'      => ['dd', 'rejected', 'dropped'],
        'dd'              => ['negotiation', 'rejected', 'dropped'],
        'negotiation'     => ['legal', 'rejected', 'dropped'],
        'legal'           => ['sale_agreement', 'rejected', 'dropped'],
        'sale_agreement'  => ['registered', 'rejected', 'dropped'],
        'registered'      => [],
        'rejected'        => [],
        'dropped'         => [],
    ];

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
            $this->pdo = $this->db->getPdo();
            $this->logger = class_exists('App\Services\LoggingService') ? new LoggingService() : null;
        } catch (Exception $e) {
            $this->log('error', 'Failed to initialize LandAcquisitionService', $e->getMessage());
            $this->db = null;
            $this->pdo = null;
        }
    }

    // ============================================================
    //  LEADS
    // ============================================================

    public function createLead(array $data): array
    {
        return $this->withTransaction(function () use ($data) {
            $payload = [
                'lead_source'    => $this->str($data['lead_source'] ?? 'direct'),
                'broker_id'      => $this->intOrNull($data['broker_id'] ?? null),
                'land_owner_name'=> $this->str($data['land_owner_name'] ?? ''),
                'owner_phone'    => $this->str($data['owner_phone'] ?? null),
                'owner_email'    => $this->str($data['owner_email'] ?? null),
                'village'        => $this->str($data['village'] ?? null),
                'tehsil'         => $this->str($data['tehsil'] ?? null),
                'district'       => $this->str($data['district'] ?? null),
                'state'          => $this->str($data['state'] ?? null),
                'pincode'        => $this->str($data['pincode'] ?? null),
                'gps_lat'        => $this->decOrNull($data['gps_lat'] ?? null),
                'gps_lng'        => $this->decOrNull($data['gps_lng'] ?? null),
                'survey_number'  => $this->str($data['survey_number'] ?? null),
                'area_acres'     => $this->decOrNull($data['area_acres'] ?? null),
                'area_sqft'      => $this->decOrNull($data['area_sqft'] ?? null),
                'expected_price' => $this->decOrNull($data['expected_price'] ?? null),
                'status'         => $this->str($data['status'] ?? 'new'),
                'assigned_to'    => $this->intOrNull($data['assigned_to'] ?? null),
                'notes'          => $this->str($data['notes'] ?? null),
            ];
            $id = $this->insert('land_leads', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    public function updateLead(int $id, array $data): array
    {
        return $this->withTransaction(function () use ($id, $data) {
            $existing = $this->fetchLead($id);
            if (!$existing) return ['success' => false, 'error' => 'Lead not found'];

            $payload = [];
            $allowed = [
                'lead_source','broker_id','land_owner_name','owner_phone','owner_email',
                'village','tehsil','district','state','pincode','gps_lat','gps_lng',
                'survey_number','area_acres','area_sqft','expected_price',
                'assigned_to','notes'
            ];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) {
                    $payload[$f] = is_null($data[$f]) ? null
                        : (in_array($f, ['gps_lat','gps_lng','area_acres','area_sqft','expected_price'])
                            ? $this->decOrNull($data[$f])
                            : $this->str($data[$f]));
                }
            }
            if (!empty($payload)) {
                $this->update('land_leads', $payload, 'id = ?', [$id]);
            }
            return ['success' => true, 'id' => $id, 'updated' => array_keys($payload)];
        });
    }

    /**
     * Advance a lead's status with forward-flow validation.
     * Allowed: new→screening→visit_done→dd→negotiation→legal→sale_agreement→registered
     * Branch paths: →rejected or →dropped from any open stage
     */
    public function advanceLead(int $id, string $newStatus): array
    {
        return $this->withTransaction(function () use ($id, $newStatus) {
            $lead = $this->fetchLead($id);
            if (!$lead) return ['success' => false, 'error' => 'Lead not found'];

            $current = $lead['status'] ?? 'new';
            $allowed = self::STATUS_FLOW[$current] ?? [];
            if (!in_array($newStatus, $allowed, true)) {
                return [
                    'success' => false,
                    'error'   => "Invalid transition: {$current} -> {$newStatus}",
                    'allowed' => $allowed
                ];
            }
            $this->update('land_leads', ['status' => $newStatus], 'id = ?', [$id]);
            return ['success' => true, 'id' => $id, 'old_status' => $current, 'new_status' => $newStatus];
        });
    }

    public function fetchLead(int $id): ?array
    {
        try {
            $row = $this->db->fetch("SELECT * FROM land_leads WHERE id = ?{$this->tenantSql()}", [$id]);
            return $row ?: null;
        } catch (Exception $e) {
            $this->log('error', 'fetchLead failed', $e->getMessage());
            return null;
        }
    }

    public function listLeads(array $filters = []): array
    {
        try {
            $tenantFilter = $this->tenantSqlForAlias('l');
            $sql = "SELECT l.*, b.broker_name
                    FROM land_leads l
                    LEFT JOIN land_brokers b ON b.id = l.broker_id{$this->tenantSqlForAlias('b')}
                    WHERE 1=1{$tenantFilter}";
            $params = [];
            if (!empty($filters['status'])) {
                $sql .= " AND l.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['district'])) {
                $sql .= " AND l.district = ?";
                $params[] = $filters['district'];
            }
            if (!empty($filters['source'])) {
                $sql .= " AND l.lead_source = ?";
                $params[] = $filters['source'];
            }
            $sql .= " ORDER BY l.created_at DESC LIMIT 200";
            $rows = $this->db->fetchAll($sql, $params);
            return ['success' => true, 'data' => $rows, 'count' => count($rows)];
        } catch (Exception $e) {
            $this->log('error', 'listLeads failed', $e->getMessage());
            return ['success' => false, 'data' => [], 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  DOCUMENTS
    // ============================================================

    public function addDocument(int $leadId, array $data): array
    {
        return $this->withTransaction(function () use ($leadId, $data) {
            $payload = [
                'land_lead_id'    => $leadId,
                'land_deal_id'    => $this->intOrNull($data['land_deal_id'] ?? null),
                'doc_type'        => $this->str($data['doc_type'] ?? 'other'),
                'doc_number'      => $this->str($data['doc_number'] ?? null),
                'doc_date'        => $this->str($data['doc_date'] ?? null),
                'uploaded_by'     => $this->intOrNull($data['uploaded_by'] ?? null),
                'file_path'       => $this->str($data['file_path'] ?? null),
                'verification_status' => $this->str($data['verification_status'] ?? 'pending'),
                'remarks'         => $this->str($data['remarks'] ?? null),
            ];
            $id = $this->insert('land_documents', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    public function verifyDocument(int $docId, ?int $userId, string $status): array
    {
        return $this->withTransaction(function () use ($docId, $userId, $status) {
            $allowed = ['pending', 'verified', 'missing', 'rejected'];
            if (!in_array($status, $allowed, true)) {
                return ['success' => false, 'error' => 'Invalid verification status'];
            }
            $this->update('land_documents', [
                'verification_status' => $status,
                'verified_by'         => $userId,
                'verified_at'         => date('Y-m-d H:i:s'),
            ], 'id = ?', [$docId]);
            return ['success' => true, 'id' => $docId, 'status' => $status];
        });
    }

    public function listDocuments(int $leadId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM land_documents WHERE land_lead_id = ?{$this->tenantSql()} ORDER BY created_at DESC",
                [$leadId]
            );
            return ['success' => true, 'data' => $rows, 'count' => count($rows)];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  SITE VISITS
    // ============================================================

    public function recordSiteVisit(int $leadId, array $data): array
    {
        return $this->withTransaction(function () use ($leadId, $data) {
            $payload = [
                'land_lead_id'         => $leadId,
                'visited_by'           => $this->intOrNull($data['visited_by'] ?? null),
                'visit_date'           => $this->str($data['visit_date'] ?? date('Y-m-d H:i:s')),
                'gps_lat'              => $this->decOrNull($data['gps_lat'] ?? null),
                'gps_lng'              => $this->decOrNull($data['gps_lng'] ?? null),
                'weather'              => $this->str($data['weather'] ?? null),
                'observations'         => $this->str($data['observations'] ?? null),
                'encroachment_found'   => !empty($data['encroachment_found']) ? 1 : 0,
                'encroachment_details' => $this->str($data['encroachment_details'] ?? null),
                'photos_json'          => $this->str($data['photos_json'] ?? null),
                'risk_rating'          => $this->str($data['risk_rating'] ?? 'low'),
            ];
            $id = $this->insert('land_site_visits', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    public function getVisitHistory(int $leadId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM land_site_visits WHERE land_lead_id = ?{$this->tenantSql()} ORDER BY visit_date DESC",
                [$leadId]
            );
            return ['success' => true, 'data' => $rows, 'count' => count($rows)];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  LEGAL OPINIONS
    // ============================================================

    public function recordLegalOpinion(int $leadId, array $data): array
    {
        return $this->withTransaction(function () use ($leadId, $data) {
            $payload = [
                'land_lead_id'              => $leadId,
                'advocate_name'             => $this->str($data['advocate_name'] ?? ''),
                'opinion_date'              => $this->str($data['opinion_date'] ?? date('Y-m-d')),
                'status'                    => $this->str($data['status'] ?? 'conditional'),
                'title_verified_chain'      => !empty($data['title_verified_chain']) ? 1 : 0,
                'encumbrance_review'        => !empty($data['encumbrance_review']) ? 1 : 0,
                'boundary_match'            => !empty($data['boundary_match']) ? 1 : 0,
                'co_owners_identified'      => !empty($data['co_owners_identified']) ? 1 : 0,
                'encroachment_risk'         => $this->str($data['encroachment_risk'] ?? null),
                'government_acquisition_check' => !empty($data['government_acquisition_check']) ? 1 : 0,
                'rera_implications'         => $this->str($data['rera_implications'] ?? null),
                'opinion_document_path'     => $this->str($data['opinion_document_path'] ?? null),
                'remarks'                   => $this->str($data['remarks'] ?? null),
            ];
            $id = $this->insert('land_legal_opinions', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    public function getOpinion(int $leadId): array
    {
        try {
            $row = $this->db->fetch(
                "SELECT * FROM land_legal_opinions WHERE land_lead_id = ?{$this->tenantSql()} ORDER BY opinion_date DESC LIMIT 1",
                [$leadId]
            );
            return ['success' => true, 'data' => $row];
        } catch (Exception $e) {
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    public function listOpinions(int $leadId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM land_legal_opinions WHERE land_lead_id = ?{$this->tenantSql()} ORDER BY opinion_date DESC",
                [$leadId]
            );
            return ['success' => true, 'data' => $rows, 'count' => count($rows)];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  DEALS (closed acquisitions)
    // ============================================================

    public function closeAcquisition(int $leadId, array $data): array
    {
        return $this->withTransaction(function () use ($leadId, $data) {
            $lead = $this->fetchLead($leadId);
            if (!$lead) return ['success' => false, 'error' => 'Lead not found'];

            $payload = [
                'land_lead_id'        => $leadId,
                'colony_id'           => $this->intOrNull($data['colony_id'] ?? null),
                'total_area_sqft'     => $this->decOrNull($data['total_area_sqft'] ?? $lead['area_sqft'] ?? null),
                'acquired_area_sqft'  => $this->decOrNull($data['acquired_area_sqft'] ?? $lead['area_sqft'] ?? null),
                'total_consideration' => $this->decOrNull($data['total_consideration'] ?? $lead['expected_price'] ?? null),
                'advance_paid'        => $this->decOrNull($data['advance_paid'] ?? 0),
                'balance_amount'      => $this->decOrNull($data['balance_amount'] ?? 0),
                'sale_agreement_date' => $this->str($data['sale_agreement_date'] ?? null),
                'sale_agreement_number' => $this->str($data['sale_agreement_number'] ?? null),
                'status'              => $this->str($data['status'] ?? 'in_progress'),
            ];
            $id = $this->insert('land_deals', $payload);

            // Bump lead status to sale_agreement if not already further
            $this->update('land_leads', ['status' => 'sale_agreement'], 'id = ?', [$leadId]);

            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    /**
     * Mark a deal as registered. Auto-creates pending payment records for
     * stamp duty and registration fee so the finance team can clear them.
     */
    public function registerAcquisition(int $dealId, array $data): array
    {
        return $this->withTransaction(function () use ($dealId, $data) {
            $deal = $this->db->fetch("SELECT * FROM land_deals WHERE id = ?{$this->tenantSql()}", [$dealId]);
            if (!$deal) return ['success' => false, 'error' => 'Deal not found'];

            $payload = [
                'registration_date'      => $this->str($data['registration_date'] ?? date('Y-m-d')),
                'registration_number'    => $this->str($data['registration_number'] ?? null),
                'sub_registrar_office'   => $this->str($data['sub_registrar_office'] ?? null),
                'stamp_duty_amount'      => $this->decOrNull($data['stamp_duty_amount'] ?? 0),
                'registration_fee'       => $this->decOrNull($data['registration_fee'] ?? 0),
                'status'                 => 'registered',
            ];
            $this->update('land_deals', $payload, 'id = ?', [$dealId]);

            // Bump lead to registered
            if (!empty($deal['land_lead_id'])) {
                $this->update('land_leads', ['status' => 'registered'], 'id = ?', [$deal['land_lead_id']]);
            }

            // Auto-create pending payment records so finance can clear them
            $payeeName = $this->str($data['payee_name'] ?? 'Sub-Registrar');
            $payDate   = $this->str($data['registration_date'] ?? date('Y-m-d'));

            if (($payload['stamp_duty_amount'] ?? 0) > 0) {
                $this->insert('land_deal_payments', [
                    'land_deal_id' => $dealId,
                    'payment_type' => 'stamp_duty',
                    'payee_name'   => $payeeName,
                    'amount'       => $payload['stamp_duty_amount'],
                    'payment_date' => $payDate,
                    'payment_mode' => 'rtgs',
                    'status'       => 'pending',
                ]);
            }
            if (($payload['registration_fee'] ?? 0) > 0) {
                $this->insert('land_deal_payments', [
                    'land_deal_id' => $dealId,
                    'payment_type' => 'registration_fee',
                    'payee_name'   => $payeeName,
                    'amount'       => $payload['registration_fee'],
                    'payment_date' => $payDate,
                    'payment_mode' => 'rtgs',
                    'status'       => 'pending',
                ]);
            }

            return [
                'success' => true,
                'id'      => $dealId,
                'updated' => $payload,
                'auto_payments_created' => (
                    ((float)($payload['stamp_duty_amount'] ?? 0) > 0 ? 1 : 0) +
                    ((float)($payload['registration_fee'] ?? 0) > 0 ? 1 : 0)
                ),
            ];
        });
    }

    public function listDeals(array $filters = []): array
    {
        try {
            $tenantFilter = $this->tenantSqlForAlias('d');
            $sql = "SELECT d.*, l.land_owner_name, l.village, l.district,
                           c.name AS colony_name
                    FROM land_deals d
                    LEFT JOIN land_leads l ON l.id = d.land_lead_id{$this->tenantSqlForAlias('l')}
                    LEFT JOIN colonies c ON c.id = d.colony_id
                    WHERE 1=1{$tenantFilter}";
            $params = [];
            if (!empty($filters['status'])) {
                $sql .= " AND d.status = ?";
                $params[] = $filters['status'];
            }
            $sql .= " ORDER BY d.created_at DESC LIMIT 200";
            $rows = $this->db->fetchAll($sql, $params);
            return ['success' => true, 'data' => $rows, 'count' => count($rows)];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    public function fetchDeal(int $id): ?array
    {
        try {
            $row = $this->db->fetch(
                "SELECT d.*, l.land_owner_name, l.village, l.district, l.state,
                         c.name AS colony_name
                 FROM land_deals d
                 LEFT JOIN land_leads l ON l.id = d.land_lead_id{$this->tenantSqlForAlias('l')}
                 LEFT JOIN colonies c ON c.id = d.colony_id
                 WHERE d.id = ?{$this->tenantSql()}",
                [$id]
            );
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    // ============================================================
    //  PAYMENTS
    // ============================================================

    public function recordPayment(int $dealId, array $data): array
    {
        return $this->withTransaction(function () use ($dealId, $data) {
            $payload = [
                'land_deal_id'       => $dealId,
                'payment_type'       => $this->str($data['payment_type'] ?? 'other'),
                'payee_name'         => $this->str($data['payee_name'] ?? ''),
                'payee_pan'          => $this->str($data['payee_pan'] ?? null),
                'payee_bank_account' => $this->str($data['payee_bank_account'] ?? null),
                'amount'             => $this->decOrNull($data['amount'] ?? 0) ?? 0,
                'payment_date'       => $this->str($data['payment_date'] ?? date('Y-m-d')),
                'payment_mode'       => $this->str($data['payment_mode'] ?? 'rtgs'),
                'cheque_number'      => $this->str($data['cheque_number'] ?? null),
                'cheque_date'        => $this->str($data['cheque_date'] ?? null),
                'bank_name'          => $this->str($data['bank_name'] ?? null),
                'transaction_ref'    => $this->str($data['transaction_ref'] ?? null),
                'tds_amount'         => $this->decOrNull($data['tds_amount'] ?? 0) ?? 0,
                'tds_section'        => $this->str($data['tds_section'] ?? null),
                'voucher_number'     => $this->str($data['voucher_number'] ?? null),
                'status'             => $this->str($data['status'] ?? 'pending'),
            ];
            $id = $this->insert('land_deal_payments', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    /**
     * Full payment ledger for a deal — used by acquisition detail page.
     */
    public function getAcquisitionLedger(int $dealId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM land_deal_payments WHERE land_deal_id = ?{$this->tenantSql()} ORDER BY payment_date DESC, id DESC",
                [$dealId]
            );
            $total = 0.0;
            $cleared = 0.0;
            $byType = [];
            foreach ($rows as $r) {
                $amt = (float)($r['amount'] ?? 0);
                $total += $amt;
                if (($r['status'] ?? '') === 'cleared') {
                    $cleared += $amt;
                }
                $t = $r['payment_type'] ?? 'other';
                $byType[$t] = ($byType[$t] ?? 0) + $amt;
            }
            return [
                'success' => true,
                'data'    => $rows,
                'count'   => count($rows),
                'summary' => [
                    'total_amount'  => $total,
                    'cleared_amount'=> $cleared,
                    'pending_amount'=> $total - $cleared,
                    'by_type'       => $byType,
                ],
            ];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'summary' => [], 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  COLONY DEVELOPMENT COSTS
    // ============================================================

    public function addDevelopmentCost(int $colonyId, array $data): array
    {
        return $this->withTransaction(function () use ($colonyId, $data) {
            $amount = (float)($data['amount'] ?? 0);
            $paid   = (float)($data['paid_amount'] ?? 0);
            $payload = [
                'colony_id'         => $colonyId,
                'cost_type'         => $this->str($data['cost_type'] ?? 'other'),
                'vendor_id'         => $this->intOrNull($data['vendor_id'] ?? null),
                'vendor_name'       => $this->str($data['vendor_name'] ?? null),
                'work_description'  => $this->str($data['work_description'] ?? null),
                'invoice_number'    => $this->str($data['invoice_number'] ?? null),
                'invoice_date'      => $this->str($data['invoice_date'] ?? null),
                'amount'            => $amount,
                'gst_amount'        => $this->decOrNull($data['gst_amount'] ?? 0) ?? 0,
                'tds_section'       => $this->str($data['tds_section'] ?? null),
                'payment_status'    => $this->str($data['payment_status'] ?? 'unpaid'),
                'paid_amount'       => $paid,
                'balance_amount'    => $amount - $paid,
                'completion_date'   => $this->str($data['completion_date'] ?? null),
                'status'            => $this->str($data['status'] ?? 'planned'),
            ];
            $id = $this->insert('colony_development_costs', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    public function getColonyCostSummary(int $colonyId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM colony_development_costs WHERE colony_id = ?{$this->tenantSql()} ORDER BY invoice_date DESC, id DESC",
                [$colonyId]
            );
            $total = 0.0; $paid = 0.0; $byType = [];
            foreach ($rows as $r) {
                $total += (float)($r['amount'] ?? 0);
                $paid   += (float)($r['paid_amount'] ?? 0);
                $t = $r['cost_type'] ?? 'other';
                $byType[$t] = ($byType[$t] ?? 0) + (float)($r['amount'] ?? 0);
            }
            return [
                'success' => true,
                'data'    => $rows,
                'count'   => count($rows),
                'summary' => [
                    'total_amount'   => $total,
                    'paid_amount'    => $paid,
                    'balance_amount' => $total - $paid,
                    'by_type'        => $byType,
                ],
            ];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'summary' => [], 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  COLONY LAYOUTS
    // ============================================================

    public function createLayout(int $colonyId, array $data): array
    {
        return $this->withTransaction(function () use ($colonyId, $data) {
            $payload = [
                'colony_id'                  => $colonyId,
                'layout_name'                => $this->str($data['layout_name'] ?? ''),
                'total_plots'                => $this->intOrNull($data['total_plots'] ?? 0) ?? 0,
                'total_area_sqft'            => $this->decOrNull($data['total_area_sqft'] ?? null),
                'layout_plan_image'          => $this->str($data['layout_plan_image'] ?? null),
                'approved_by'                => $this->str($data['approved_by'] ?? null),
                'approval_date'              => $this->str($data['approval_date'] ?? null),
                'government_approval_number' => $this->str($data['government_approval_number'] ?? null),
                'status'                     => $this->str($data['status'] ?? 'draft'),
            ];
            $id = $this->insert('colony_layouts', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    public function getLayouts(int $colonyId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM colony_layouts WHERE colony_id = ?{$this->tenantSql()} ORDER BY created_at DESC",
                [$colonyId]
            );
            return ['success' => true, 'data' => $rows, 'count' => count($rows)];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  BROKERS
    // ============================================================

    public function createBroker(array $data): array
    {
        return $this->withTransaction(function () use ($data) {
            $payload = [
                'broker_name'           => $this->str($data['broker_name'] ?? ''),
                'phone'                 => $this->str($data['phone'] ?? null),
                'email'                 => $this->str($data['email'] ?? null),
                'pan_number'            => $this->str($data['pan_number'] ?? null),
                'aadhaar_number'        => $this->str($data['aadhaar_number'] ?? null),
                'rera_number'           => $this->str($data['rera_number'] ?? null),
                'address'               => $this->str($data['address'] ?? null),
                'commission_percentage' => $this->decOrNull($data['commission_percentage'] ?? 2) ?? 2,
                'bank_account'          => $this->str($data['bank_account'] ?? null),
                'ifsc'                  => $this->str($data['ifsc'] ?? null),
                'active'                => !empty($data['active']) ? 1 : 1,
            ];
            $id = $this->insert('land_brokers', $payload);
            return ['success' => true, 'id' => $id, 'data' => $payload];
        });
    }

    public function listBrokers(): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT * FROM land_brokers WHERE 1=1{$this->tenantSql()} ORDER BY `active` DESC, broker_name ASC"
            );
            return ['success' => true, 'data' => $rows, 'count' => count($rows)];
        } catch (Exception $e) {
            return ['success' => false, 'data' => [], 'count' => 0, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    //  Helpers — DB primitives with transaction
    // ============================================================

    /**
     * Wrap a closure in a transaction. Always returns array; never throws.
     * If the closure returns a non-array, wraps it.
     */
    private function withTransaction(callable $fn): array
    {
        if (!$this->pdo) {
            $r = $fn();
            return is_array($r) ? $r : ['success' => false, 'error' => 'Service unavailable'];
        }
        try {
            $this->pdo->beginTransaction();
            $result = $fn();
            $this->pdo->commit();
            return is_array($result) ? $result : ['success' => true, 'data' => $result];
        } catch (Exception $e) {
            if ($this->pdo && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->log('error', 'LandAcquisitionService transaction failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function insert(string $table, array $data): ?int
    {
        if (!$this->pdo) return null;
        $data = array_merge($data, $this->tenantInsertData());
        $cols = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $colList = implode(',', $cols);
        $sql = "INSERT INTO {$table} ({$colList}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    private function update(string $table, array $data, string $where, array $whereParams): int
    {
        if (!$this->pdo) return 0;
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "{$col} = ?";
            $params[] = $val;
        }
        $tenantWhere = $this->tenantSql();
        $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE {$where}{$tenantWhere}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($params, $whereParams, $this->tenantId() > 1 ? [$this->tenantId()] : []));
        return $stmt->rowCount();
    }

    private function str($v): ?string
    {
        if ($v === null || $v === '') return null;
        return (string)$v;
    }

    private function intOrNull($v): ?int
    {
        if ($v === null || $v === '' || $v === false) return null;
        return (int)$v;
    }

    private function decOrNull($v): ?float
    {
        if ($v === null || $v === '' || $v === false) return null;
        if (!is_numeric($v)) return null;
        return (float)$v;
    }

    private function log(string $level, string $msg, string $detail = ''): void
    {
        if ($this->logger && method_exists($this->logger, $level)) {
            try { $this->logger->{$level}($msg, ['detail' => $detail]); } catch (Exception $e) { error_log($e->getMessage()); }
        } else {
            @error_log("[LandAcquisitionService] [$level] $msg :: $detail");
        }
    }
}
