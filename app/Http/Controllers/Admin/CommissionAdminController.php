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
            $r = $this->db->fetchAll("SELECT * FROM agent_commission_rates ORDER BY min_sqft");
            $stats['agent_rates_count'] = count($r);
            $stats['agent_rates'] = $r;

            // Associate structure levels
            $r = $this->db->fetchAll("SELECT * FROM associate_commission_structure WHERE status='active' ORDER BY level_number");
            $stats['structure_levels'] = $r;

            // Associate calculations
            $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(commission_amount),0) as total, COALESCE(SUM(CASE WHEN status='pending' THEN commission_amount ELSE 0 END),0) as pending_total FROM associate_commission_calculations");
            $stats['calc_stats'] = $r[0] ?? ['c'=>0,'total'=>0,'pending_total'=>0];

            // Commission bonuses
            $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(bonus_amount),0) as total FROM commission_bonuses");
            $stats['bonus_stats'] = $r[0] ?? ['c'=>0,'total'=>0];

            // Commission calculations (agent resell)
            $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(final_commission),0) as total, COALESCE(SUM(CASE WHEN payment_status='pending' THEN final_commission ELSE 0 END),0) as pending FROM commission_calculations");
            $stats['calc_agent_stats'] = $r[0] ?? ['c'=>0,'total'=>0,'pending'=>0];

            // MLM levels
            $r = $this->db->fetchAll("SELECT COUNT(*) as c FROM mlm_commission_levels");
            $stats['mlm_levels_count'] = $r[0]['c'] ?? 0;

            // MLM records
            $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(total_commission),0) as total FROM mlm_commission_records");
            $stats['mlm_records_stats'] = $r[0] ?? ['c'=>0,'total'=>0];

            // MLM analytics
            $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(total_earned),0) as earned, COALESCE(SUM(total_paid),0) as paid FROM mlm_commission_analytics");
            $stats['mlm_analytics_stats'] = $r[0] ?? ['c'=>0,'earned'=>0,'paid'=>0];

            // Revenue daily
            $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(revenue),0) as rev, COALESCE(SUM(commission),0) as comm FROM revenue_commission_daily");
            $stats['revenue_stats'] = $r[0] ?? ['c'=>0,'rev'=>0,'comm'=>0];

            try {
                // Telecaller rules
                $r = $this->db->fetchAll("SELECT COUNT(*) as c FROM telecaller_commission_rules WHERE is_active=1");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
                $r = null;
            }
            $stats['tc_rules_count'] = $r[0]['c'] ?? 0;

            // Telecaller commissions
            $r = $this->db->fetchAll("SELECT COUNT(*) as c, COALESCE(SUM(commission_amount),0) as total, COALESCE(SUM(CASE WHEN status='pending' THEN commission_amount ELSE 0 END),0) as pending FROM telecaller_commissions");
            $stats['tc_comm_stats'] = $r[0] ?? ['c'=>0,'total'=>0,'pending'=>0];

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
            $this->data['rates'] = $this->db->fetchAll("SELECT * FROM agent_commission_rates ORDER BY min_sqft");
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
            $this->db->query("INSERT INTO agent_commission_rates (min_sqft, max_sqft, commission_per_sqft, commission_percentage, status) VALUES (?, ?, ?, ?, ?)", [
                (int)$_POST['min_sqft'], (int)$_POST['max_sqft'], (float)$_POST['commission_per_sqft'],
                (float)($_POST['commission_percentage'] ?? 0), $_POST['status'] ?? 'active'
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

    // ===== Associate Commission Structure =====
    public function associateStructure()
    {
        try {
            $this->data['levels'] = $this->db->fetchAll("SELECT * FROM associate_commission_structure ORDER BY level_number");
            $this->data['page_title'] = 'Associate Commission Structure';
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
            $this->db->query("INSERT INTO associate_commission_structure (level_number, level_name, commission_percentage, min_property_value, max_property_value, status) VALUES (?, ?, ?, ?, ?, ?)", [
                (int)$_POST['level_number'], $_POST['level_name'], (float)$_POST['commission_percentage'],
                (float)($_POST['min_property_value'] ?? 0), (float)($_POST['max_property_value'] ?? 999999999.99),
                $_POST['status'] ?? 'active'
            ]);
            $this->setFlash('success', 'Level created');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/associate/structure');
    }

    public function associateStructureDelete($id)
    {
        try {
            $this->db->query("DELETE FROM associate_commission_structure WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Level deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/associate/structure');
    }

    // ===== Associate Commission Calculations =====
    public function associateCalculations()
    {
        try {
            $this->data['calculations'] = $this->db->fetchAll(
                "SELECT acc.*, u.name as associate_name FROM associate_commission_calculations acc
                 LEFT JOIN users u ON acc.associate_id = u.id ORDER BY acc.created_at DESC"
            );
            $this->data['page_title'] = 'Associate Commission Calculations';
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
            $this->db->query("UPDATE associate_commission_calculations SET status = ? WHERE id = ?", [$status, (int)$id]);
            $this->setFlash('success', "Calculation #{$id} marked as {$status}");
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/associate/calculations');
    }

    // ===== Commission Bonuses =====
    public function bonuses()
    {
        try {
            $this->data['bonuses'] = $this->db->fetchAll(
                "SELECT cb.*, u.name as associate_name FROM commission_bonuses cb
                 LEFT JOIN users u ON cb.associate_id = u.id ORDER BY cb.created_at DESC"
            );
            $this->data['users'] = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role IN ('associate','agent') ORDER BY name");
            $this->data['page_title'] = 'Commission Bonuses';
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
            $bonusPct = null;
            if (!empty($_POST['bonus_percentage']) && $bonusAmount == 0) {
                $bonusPct = (float)$_POST['bonus_percentage'];
            }
            $this->db->query("INSERT INTO commission_bonuses (associate_id, achievement_id, bonus_percentage, bonus_amount) VALUES (?, ?, ?, ?)", [
                (int)$_POST['associate_id'], !empty($_POST['achievement_id']) ? (int)$_POST['achievement_id'] : null,
                $bonusPct ?? 0.00, $bonusAmount
            ]);
            $this->setFlash('success', 'Bonus created');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/bonuses');
    }

    public function bonusDelete($id)
    {
        try {
            $this->db->query("DELETE FROM commission_bonuses WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Bonus deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/bonuses');
    }

    // ===== Commission Calculations (Resell Agent) =====
    public function commissionCalculations()
    {
        try {
            $this->data['calculations'] = $this->db->fetchAll(
                "SELECT * FROM commission_calculations ORDER BY created_at DESC LIMIT 50"
            );
            $this->data['page_title'] = 'Commission Calculations (Resell)';
            return $this->render('admin/commission/commission_calculations', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    // ===== MLM Commission Levels =====
    public function mlmLevels()
    {
        try {
            $this->data['levels'] = $this->db->fetchAll("SELECT * FROM mlm_commission_levels ORDER BY plan_id, level");
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
            $this->db->query("INSERT INTO mlm_commission_levels (plan_id, level, name, commission_rate, min_associates, direct_percentage, min_business, max_business) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
                (int)$_POST['plan_id'], (int)$_POST['level'], $_POST['name'],
                (float)$_POST['commission_rate'], (int)($_POST['min_associates'] ?? 0),
                (float)$_POST['direct_percentage'], (float)($_POST['min_business'] ?? 0),
                !empty($_POST['max_business']) ? (float)$_POST['max_business'] : null
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
            $this->db->query("DELETE FROM mlm_commission_levels WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Level deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/mlm/levels');
    }

    // ===== MLM Commission Records =====
    public function mlmRecords()
    {
        try {
            $this->data['records'] = $this->db->fetchAll(
                "SELECT mcr.*, u.name as associate_name FROM mlm_commission_records mcr
                 LEFT JOIN users u ON mcr.associate_id = u.id ORDER BY mcr.created_at DESC LIMIT 50"
            );
            $this->data['page_title'] = 'MLM Commission Records';
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
            $this->db->query("UPDATE mlm_commission_records SET status = ? WHERE id = ?", [$status, (int)$id]);
            $this->setFlash('success', "Record #{$id} marked as {$status}");
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/mlm/records');
    }

    // ===== MLM Commission Analytics =====
    public function mlmAnalytics()
    {
        try {
            $this->data['analytics'] = $this->db->fetchAll(
                "SELECT mca.*, u.name as associate_name FROM mlm_commission_analytics mca
                 LEFT JOIN users u ON mca.associate_id = u.id ORDER BY mca.period_date DESC LIMIT 50"
            );
            // Summary
            $r = $this->db->fetchAll("SELECT COALESCE(SUM(total_earned),0) as earned, COALESCE(SUM(total_paid),0) as paid, COALESCE(SUM(pending_amount),0) as pending, COALESCE(SUM(direct_commissions),0) as direct, COALESCE(SUM(team_commissions),0) as team, COALESCE(SUM(bonus_commissions),0) as bonus FROM mlm_commission_analytics");
            $this->data['summary'] = $r[0] ?? ['earned'=>0,'paid'=>0,'pending'=>0,'direct'=>0,'team'=>0,'bonus'=>0];
            $this->data['page_title'] = 'MLM Commission Analytics';
            return $this->render('admin/commission/mlm_analytics', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    // ===== MLM Commission Ledger Legacy (Audit) =====
    public function mlmLedgerLegacy()
    {
        try {
            $this->data['ledger'] = $this->db->fetchAll(
                "SELECT * FROM mlm_commission_ledger_legacy ORDER BY created_at DESC LIMIT 100"
            );
            $this->data['page_title'] = 'MLM Commission Ledger (Legacy Audit)';
            return $this->render('admin/commission/mlm_ledger_legacy', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            return $this->redirect('admin/commission');
        }
    }

    // ===== Revenue Commission Daily =====
    public function revenueDaily()
    {
        try {
            $this->data['daily'] = $this->db->fetchAll(
                "SELECT rcd.*, u.name as agent_name FROM revenue_commission_daily rcd
                 LEFT JOIN users u ON rcd.agent_id = u.id ORDER BY rcd.stat_date DESC LIMIT 60"
            );
            $r = $this->db->fetchAll("SELECT COALESCE(SUM(revenue),0) as total_rev, COALESCE(SUM(commission),0) as total_comm, COALESCE(SUM(deals),0) as total_deals FROM revenue_commission_daily");
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
        try {
            $this->db->query("INSERT INTO revenue_commission_daily (stat_date, agent_id, revenue, deals, commission) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE revenue = VALUES(revenue), deals = VALUES(deals), commission = VALUES(commission)", [
                $_POST['stat_date'], (int)$_POST['agent_id'], (float)$_POST['revenue'],
                (int)($_POST['deals'] ?? 0), (float)$_POST['commission']
            ]);
            $this->setFlash('success', 'Daily revenue recorded');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        return $this->redirect('admin/commission/revenue/daily');
    }

    public function revenueDailyDelete($id)
    {
        try {
            $this->db->query("DELETE FROM revenue_commission_daily WHERE id = ?", [(int)$id]);
            $this->setFlash('success', 'Record deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
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
}
