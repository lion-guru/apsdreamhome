<?php
namespace App\Http\Controllers\Admin;

class CommissionAdminController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $stats = [];

            // Agent commission rates
            $r = $this->db->fetchAll("SELECT acr.*, u.name as agent_name FROM agent_commission_rates acr LEFT JOIN users u ON acr.agent_id = u.id ORDER BY acr.agent_id, acr.property_type");
            $stats['agent_rates_count'] = count($r);
            $stats['agent_rates'] = $r;

            // Rank benefits (replaces associate_commission_structure)
            $r = $this->db->fetchAll("SELECT * FROM mlm_rank_benefits ORDER BY FIELD(rank_name, 'associate','bronze','silver','gold','platinum','diamond')");
            $stats['rank_benefits'] = $r;

            // MLM commission ledger stats (single source of truth)
            try {
                $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as total, COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) as pending FROM mlm_commission_ledger");
                $stats['mlm_records_stats'] = $r[0] ?? ['c'=>0,'total'=>0,'pending'=>0];
            } catch (\Throwable $e) {
                $stats['mlm_records_stats'] = ['c'=>0,'total'=>0,'pending'=>0];
            }

            // Bonus stats from ledger
            try {
                $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as total FROM mlm_commission_ledger WHERE commission_type IN ('performance_bonus','team_bonus')");
                $stats['bonus_stats'] = $r[0] ?? ['c'=>0,'total'=>0];
            } catch (\Throwable $e) {
                $stats['bonus_stats'] = ['c'=>0,'total'=>0];
            }

            // Pending payouts
            try {
                $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(amount),0) as total FROM mlm_payouts WHERE status='pending'");
                $stats['pending_payouts'] = $r[0] ?? ['c'=>0,'total'=>0];
            } catch (\Throwable $e) {
                $stats['pending_payouts'] = ['c'=>0,'total'=>0];
            }

            // MLM levels (from mlm_rank_benefits — single source of truth)
            $r = $this->db->fetchAll("SELECT COUNT(*) as c FROM mlm_rank_benefits");
            $stats['mlm_levels_count'] = $r[0]['c'] ?? 0;

            // Telecaller rules
            try {
                $r = $this->db->fetchAll("SELECT COUNT(*) as c FROM telecaller_commission_rules WHERE is_active=1");
                $stats['tc_rules_count'] = $r[0]['c'] ?? 0;
            } catch (\Throwable $e) {
                $stats['tc_rules_count'] = 0;
            }

            // Telecaller commissions
            try {
                $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(commission_amount),0) as total, COALESCE(SUM(CASE WHEN status='pending' THEN commission_amount ELSE 0 END),0) as pending FROM telecaller_commissions");
                $stats['tc_comm_stats'] = $r[0] ?? ['c'=>0,'total'=>0,'pending'=>0];
            } catch (\Throwable $e) {
                $stats['tc_comm_stats'] = ['c'=>0,'total'=>0,'pending'=>0];
            }

            $this->data['page_title'] = 'Commission Management System';
            $this->data['stats'] = $stats;
            return $this->render('admin/commission/index', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading dashboard: ' . $e->getMessage());
            return $this->render('admin/commission/index', ['page_title'=>'Commission System', 'stats'=>[]]);
        }
    }

    // ===== Agent Commission Rates =====
    public function agentRates()
    {
        try {
            $this->data['rates'] = $this->db->fetchAll(
                "SELECT acr.*, u.name as agent_name FROM agent_commission_rates acr
                 LEFT JOIN users u ON acr.agent_id = u.id
                 ORDER BY acr.agent_id, acr.property_type"
            );
            $this->data['page_title'] = 'Agent Commission Rates';
            return $this->render('admin/commission/agent_rates', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function agentRateStore()
    {
        $this->validateCsrfOrFail();
        try {
            $this->db->query("INSERT INTO agent_commission_rates (agent_id, property_type, base_rate_pct, override_pct, bonus_rate_pct, effective_from, effective_to) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                (int)$_POST['agent_id'], $_POST['property_type'] ?? 'plot', (float)($_POST['base_rate_pct'] ?? 0),
                (float)($_POST['override_pct'] ?? 0), (float)($_POST['bonus_rate_pct'] ?? 0),
                $_POST['effective_from'] ?? date('Y-m-d'), $_POST['effective_to'] ?? null
            ]);
            $this->setFlash('success', 'Agent rate created');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/agent-rates');
    }

    public function agentRateDelete($id)
    {
        try {
            $this->db->query("DELETE FROM agent_commission_rates WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Rate deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/agent-rates');
    }

    // ===== Rank Benefits (replaces Associate Commission Structure) =====
    public function associateStructure()
    {
        try {
            $this->data['levels'] = $this->db->fetchAll("SELECT id, rank_name, direct_sale_pct as commission_percentage, l1_pct as gen1_override_pct, l2_pct as gen2_override_pct, l3_pct as gen3_override_pct FROM mlm_rank_benefits ORDER BY FIELD(rank_name, 'associate','bronze','silver','gold','platinum','diamond')");
            $this->data['page_title'] = 'Rank Commission Benefits';
            return $this->render('admin/commission/associate_structure', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function associateStructureStore()
    {
        $this->validateCsrfOrFail();
        try {
            $rankName = $_POST['level_name'] ?? $_POST['rank_name'] ?? '';
            $this->db->query("UPDATE mlm_rank_benefits SET direct_sale_pct = ?, l1_pct = ?, l2_pct = ?, l3_pct = ? WHERE rank_name = ?", [
                (float)($_POST['commission_percentage'] ?? 0),
                (float)($_POST['gen1_override_pct'] ?? 0),
                (float)($_POST['gen2_override_pct'] ?? 0),
                (float)($_POST['gen3_override_pct'] ?? 0),
                $rankName
            ]);
            $this->setFlash('success', "Rank benefits updated for {$rankName}");
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/associate/structure');
    }

    public function associateStructureDelete($id)
    {
        $this->setFlash('warning', 'Rank benefits cannot be deleted — use edit to modify rates.');
        return $this->redirect('admin/commission/associate/structure');
    }

    // ===== Commission Calculations (from mlm_commission_ledger) =====
    public function associateCalculations()
    {
        try {
            $this->data['calculations'] = $this->db->fetchAll(
                "SELECT mcl.*, u.name as associate_name FROM mlm_commission_ledger mcl
                 LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                 WHERE mcl.commission_type IN ('direct_sale','mlm_level_1','mlm_level_2','mlm_level_3')
                 ORDER BY mcl.created_at DESC LIMIT 100"
            );
            $this->data['page_title'] = 'Commission Calculations';
            return $this->render('admin/commission/associate_calculations', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function associateCalcStatus($id, $status)
    {
        if (!in_array($status, ['confirmed','paid'])) { $status = 'confirmed'; }
        try {
            $dbStatus = ($status === 'confirmed') ? 'approved' : 'paid';
            $this->db->query("UPDATE mlm_commission_ledger SET status = ? WHERE id = ?", [$dbStatus, (int)$id]);
            $this->setFlash('success', "Record #{$id} marked as {$status}");
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/associate/calculations');
    }

    // ===== Performance Bonuses (from mlm_commission_ledger) =====
    public function bonuses()
    {
        try {
            $this->data['bonuses'] = $this->db->fetchAll(
                "SELECT mcl.*, u.name as associate_name FROM mlm_commission_ledger mcl
                 LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                 WHERE mcl.commission_type IN ('performance_bonus','team_bonus','royalty_pool')
                 ORDER BY mcl.created_at DESC LIMIT 100"
            );
            $this->data['users'] = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role IN ('associate','agent','employee') ORDER BY name");
            $this->data['page_title'] = 'Performance Bonuses';
            return $this->render('admin/commission/bonuses', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function bonusStore()
    {
        $this->validateCsrfOrFail();
        try {
            $bonusAmount = (float)$_POST['bonus_amount'];
            $beneficiaryId = (int)$_POST['associate_id'];
            $reason = $_POST['reason'] ?? 'Manual bonus';
            $this->db->query(
                "INSERT INTO mlm_commission_ledger (beneficiary_user_id, source_user_id, commission_type, amount, status, payment_amount, notes) VALUES (?, ?, 'performance_bonus', ?, 'pending', 0, ?)",
                [$beneficiaryId, $beneficiaryId, $bonusAmount, $reason]
            );
            $this->setFlash('success', 'Bonus recorded in commission ledger');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/bonuses');
    }

    public function bonusDelete($id)
    {
        try {
            $this->db->query("UPDATE mlm_commission_ledger SET status = 'reversed' WHERE id = ? AND commission_type = 'performance_bonus'", [(int)$id]);
            $this->setFlash('success', 'Bonus reversed');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/bonuses');
    }

    // ===== Commission Calculations (Agent Resell — from ledger) =====
    public function commissionCalculations()
    {
        try {
            $this->data['calculations'] = $this->db->fetchAll(
                "SELECT mcl.*, u.name as agent_name FROM mlm_commission_ledger mcl
                 LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                 ORDER BY mcl.created_at DESC LIMIT 50"
            );
            $this->data['page_title'] = 'Commission Calculations';
            return $this->render('admin/commission/commission_calculations', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    // ===== MLM Commission Levels (reads from mlm_rank_benefits — single source of truth) =====
    public function mlmLevels()
    {
        try {
            $this->data['levels'] = $this->db->fetchAll("SELECT id, rank_name as name, min_leg_count as min_associates, min_qualifying_volume as min_business, direct_sale_pct as commission_rate, direct_sale_pct as direct_percentage FROM mlm_rank_benefits ORDER BY id");
            $this->data['page_title'] = 'MLM Commission Levels';
            return $this->render('admin/commission/mlm_levels', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function mlmLevelStore()
    {
        $this->validateCsrfOrFail();
        try {
            $this->db->query("INSERT INTO mlm_rank_benefits (rank_name, min_leg_count, min_qualifying_volume, direct_sale_pct, l1_override_pct, l2_override_pct, l3_override_pct) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                strtolower(trim($_POST['name'] ?? '')),
                (int)($_POST['min_associates'] ?? 0),
                (float)($_POST['min_business'] ?? 0),
                (float)($_POST['commission_rate'] ?? 1),
                (float)($_POST['direct_percentage'] ?? 1),
                1.5, 1.0
            ]);
            $this->setFlash('success', 'MLM level created');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/mlm/levels');
    }

    public function mlmLevelDelete($id)
    {
        try {
            $this->db->query("DELETE FROM mlm_rank_benefits WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Level deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/mlm/levels');
    }

    // ===== MLM Commission Ledger =====
    public function mlmRecords()
    {
        try {
            $this->data['records'] = $this->db->fetchAll(
                "SELECT mcl.*, u.name as associate_name FROM mlm_commission_ledger mcl
                 LEFT JOIN users u ON mcl.beneficiary_user_id = u.id ORDER BY mcl.created_at DESC LIMIT 50"
            );
            $this->data['page_title'] = 'MLM Commission Ledger';
            return $this->render('admin/commission/mlm_records', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function mlmRecordStatus($id, $status)
    {
        if (!in_array($status, ['approved','paid','cancelled'])) { $status = 'approved'; }
        try {
            $this->db->query("UPDATE mlm_commission_ledger SET status = ? WHERE id = ?", [$status, (int)$id]);
            $this->setFlash('success', "Record #{$id} marked as {$status}");
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/mlm/records');
    }

    // ===== MLM Commission Analytics (aggregated from ledger) =====
    public function mlmAnalytics()
    {
        try {
            // Per-user commission summary from ledger
            $this->data['analytics'] = $this->db->fetchAll(
                "SELECT mcl.beneficiary_user_id, u.name as associate_name,
                        COUNT(*) as total_entries,
                        COALESCE(SUM(mcl.amount),0) as total_earned,
                        COALESCE(SUM(CASE WHEN mcl.status='paid' THEN mcl.amount ELSE 0 END),0) as total_paid,
                        COALESCE(SUM(CASE WHEN mcl.status='pending' THEN mcl.amount ELSE 0 END),0) as pending_amount,
                        COALESCE(SUM(CASE WHEN mcl.commission_type='direct_sale' THEN mcl.amount ELSE 0 END),0) as direct_commissions,
                        COALESCE(SUM(CASE WHEN mcl.commission_type LIKE 'mlm_level_%' THEN mcl.amount ELSE 0 END),0) as team_commissions,
                        COALESCE(SUM(CASE WHEN mcl.commission_type IN ('performance_bonus','team_bonus') THEN mcl.amount ELSE 0 END),0) as bonus_commissions
                 FROM mlm_commission_ledger mcl
                 LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                 GROUP BY mcl.beneficiary_user_id
                 ORDER BY total_earned DESC LIMIT 50"
            );
            // Overall summary
            $r = $this->db->fetchAll("SELECT COALESCE(SUM(amount),0) as earned, COALESCE(SUM(CASE WHEN status='paid' THEN amount ELSE 0 END),0) as paid, COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) as pending, COALESCE(SUM(CASE WHEN commission_type='direct_sale' THEN amount ELSE 0 END),0) as direct, COALESCE(SUM(CASE WHEN commission_type LIKE 'mlm_level_%' THEN amount ELSE 0 END),0) as team, COALESCE(SUM(CASE WHEN commission_type IN ('performance_bonus','team_bonus') THEN amount ELSE 0 END),0) as bonus FROM mlm_commission_ledger");
            $this->data['summary'] = $r[0] ?? ['earned'=>0,'paid'=>0,'pending'=>0,'direct'=>0,'team'=>0,'bonus'=>0];
            $this->data['page_title'] = 'MLM Commission Analytics';
            return $this->render('admin/commission/mlm_analytics', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    // ===== MLM Commission Ledger Legacy (Audit) =====
    // Legacy table was dropped during ledger consolidation — redirects to live ledger
    public function mlmLedgerLegacy()
    {
        try {
            $this->data['ledger'] = $this->db->fetchAll(
                "SELECT mcl.*, u.name as associate_name FROM mlm_commission_ledger mcl
                 LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                 ORDER BY mcl.created_at DESC LIMIT 100"
            );
            $this->data['page_title'] = 'MLM Commission Ledger (Audit)';
            return $this->render('admin/commission/commission_audit_log', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    // ===== Revenue Commission Daily =====
    // Aggregates daily commission from mlm_commission_ledger (no separate table needed)
    public function revenueDaily()
    {
        try {
            $this->data['daily'] = $this->db->fetchAll(
                "SELECT DATE(mcl.created_at) as stat_date, mcl.beneficiary_user_id, u.name as agent_name,
                        COUNT(*) as deals,
                        SUM(mcl.amount) as commission,
                        SUM(COALESCE(mcl.sale_amount, 0)) as revenue
                 FROM mlm_commission_ledger mcl
                 LEFT JOIN users u ON mcl.beneficiary_user_id = u.id
                 GROUP BY DATE(mcl.created_at), mcl.beneficiary_user_id
                 ORDER BY stat_date DESC LIMIT 60"
            );
            $r = $this->db->fetchAll("SELECT COALESCE(SUM(COALESCE(sale_amount, 0)),0) as total_rev, COALESCE(SUM(amount),0) as total_comm, COUNT(DISTINCT DATE(created_at)) as total_deals FROM mlm_commission_ledger");
            $this->data['summary'] = $r[0] ?? ['total_rev'=>0,'total_comm'=>0,'total_deals'=>0];
            $this->data['users'] = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('agent','associate') ORDER BY name");
            $this->data['page_title'] = 'Daily Revenue Commission';
            return $this->render('admin/commission/revenue_daily', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function revenueDailyStore()
    {
        $this->validateCsrfOrFail();
        $this->setFlash('info', 'Daily revenue is auto-calculated from commission ledger entries. No manual entry needed.');
        return $this->redirect('admin/commission/revenue/daily');
    }

    public function revenueDailyDelete($id)
    {
        $this->setFlash('info', 'Revenue records are auto-generated from the commission ledger and cannot be deleted individually.');
        return $this->redirect('admin/commission/revenue/daily');
    }

    // ===== Telecaller Commission Rules =====
    public function telecallerRules()
    {
        try {
            $this->data['rules'] = $this->db->fetchAll("SELECT * FROM telecaller_commission_rules ORDER BY commission_type");
            $this->data['page_title'] = 'Telecaller Commission Rules';
            return $this->render('admin/commission/telecaller_rules', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function telecallerRuleStore()
    {
        $this->validateCsrfOrFail();
        try {
            $this->db->query("INSERT INTO telecaller_commission_rules (rule_name, commission_type, amount, percentage, min_calls, target_type, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                $_POST['rule_name'], $_POST['commission_type'], (float)$_POST['amount'],
                !empty($_POST['percentage']) ? (float)$_POST['percentage'] : null,
                (int)($_POST['min_calls'] ?? 0), $_POST['target_type'] ?? 'monthly',
                isset($_POST['is_active']) ? 1 : 0
            ]);
            $this->setFlash('success', 'Rule created');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/telecaller/rules');
    }

    public function telecallerRuleToggle($id)
    {
        try {
            $r = $this->db->fetchOne("SELECT is_active FROM telecaller_commission_rules WHERE id = ?", [(int)$id]);
            $new = $r ? ($r['is_active'] ? 0 : 1) : 0;
            $this->db->query("UPDATE telecaller_commission_rules SET is_active = ? WHERE id = ?", [$new, (int)$id]);
            $this->setFlash('success', 'Rule status toggled');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/telecaller/rules');
    }

    public function telecallerRuleDelete($id)
    {
        try {
            $this->db->query("DELETE FROM telecaller_commission_rules WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Rule deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/telecaller/rules');
    }

    // ===== Telecaller Commissions =====
    public function telecallerCommissions()
    {
        try {
            $this->data['commissions'] = $this->db->fetchAll(
                "SELECT tc.*, u.name as telecaller_name, l.name as lead_name, tcr.rule_name
                 FROM telecaller_commissions tc
                 LEFT JOIN users u ON tc.telecaller_id = u.id
                 LEFT JOIN leads l ON tc.lead_id = l.id
                 LEFT JOIN telecaller_commission_rules tcr ON tc.commission_rule_id = tcr.id
                 ORDER BY tc.created_at DESC LIMIT 50"
            );
            $r = $this->db->fetchAll("SELECT COALESCE(SUM(commission_amount),0) as total, COALESCE(SUM(CASE WHEN status='pending' THEN commission_amount ELSE 0 END),0) as pending FROM telecaller_commissions");
            $this->data['summary'] = $r[0] ?? ['total'=>0,'pending'=>0];
            $this->data['page_title'] = 'Telecaller Commissions';
            return $this->render('admin/commission/telecaller_commissions', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    public function telecallerCommissionApprove($id)
    {
        try {
            $this->db->query("UPDATE telecaller_commissions SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?", [
                (int)($_SESSION['admin_id'] ?? 0), (int)$id
            ]);
            $this->setFlash('success', 'Commission approved');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/telecaller/commissions');
    }

    public function telecallerCommissionPay($id)
    {
        try {
            $this->db->query("UPDATE telecaller_commissions SET status = 'paid', paid_at = NOW() WHERE id = ? AND status = 'approved'", [(int)$id]);
            $this->setFlash('success', 'Commission marked as paid');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/telecaller/commissions');
    }

    // Legacy method stubs (keep for backward compat)
    public function rules()
    {
        $this->data['page_title'] = 'Commission Rules';
        return $this->render('admin/commission/rules', $this->data);
    }
    public function createRule()
    {
        $this->data['page_title'] = 'Create Commission Rule';
        return $this->render('admin/commission/create_rule', $this->data);
    }
    public function editRule($id)
    {
        $this->data['page_title'] = 'Edit Commission Rule';
        return $this->render('admin/commission/edit_rule', $this->data);
    }
    public function calculations()
    {
        $this->data['page_title'] = 'Commission Calculations';
        return $this->render('admin/commission/calculations', $this->data);
    }
    public function payments()
    {
        $this->data['page_title'] = 'Commission Payments';
        return $this->render('admin/commission/payments', $this->data);
    }
    public function reports()
    {
        $this->data['page_title'] = 'Commission Reports';
        return $this->render('admin/commission/reports', $this->data);
    }
    public function payouts()
    {
        $this->data['page_title'] = 'Commission Payouts';
        return $this->render('admin/commission/payouts', $this->data);
    }
    public function commissionsList()
    {
        $this->data['page_title'] = 'Commissions';
        return $this->render('admin/commissions/index', $this->data);
    }

    public function reconciliation()
    {
        try {
            $service = new \App\Services\MLM\CommissionReconciliationService();
            $this->data['data'] = $service->reconcile();
            $this->data['page_title'] = 'Commission Reconciliation';
            return $this->render('admin/commission/reconciliation', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }
}
