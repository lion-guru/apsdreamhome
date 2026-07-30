<?php
/**
 * Module 5: DailyOperationsService
 * Handles attendance, leave, payslips, leads, operations log, reports
 */

namespace App\Services\Backoffice;

use App\Core\Middleware\TenantContext;

class DailyOperationsService
{
    private $pdo;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo;
        if ($this->pdo === null) {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $this->pdo = $db->getPdo();
            } catch (\Throwable $e) {
                $this->pdo = null;
            }
        }
    }

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function tJoin(string $alias): string
    {
        return $this->getTenantId() > 1 ? " AND {$alias}.tenant_id = ?" : '';
    }

    private function tEnd(): string
    {
        return $this->getTenantId() > 1 ? ' AND tenant_id = ?' : '';
    }

    private function tVal(): array
    {
        $tid = $this->getTenantId();
        return $tid > 1 ? [$tid] : [];
    }

    private function fetchAll($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('DailyOperationsService::fetchAll: ' . $e->getMessage());
            return [];
        }
    }

    private function fetchOne($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('DailyOperationsService::fetchOne: ' . $e->getMessage());
            return null;
        }
    }

    private function execute($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int) $this->pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('DailyOperationsService::execute: ' . $e->getMessage());
            return 0;
        }
    }

    /* ── ATTENDANCE ────────────────────────────────────── */

    public function markAttendance(array $data)
    {
        $employeeId = (int)($data['employee_id'] ?? 0);
        $date = $data['attendance_date'] ?? date('Y-m-d');
        $status = $data['status'] ?? 'present';
        $checkIn = $data['check_in_time'] ?? null;
        $checkOut = $data['check_out_time'] ?? null;
        $hoursWorked = 0.0;
        $overtimeHours = 0.0;
        $lateMinutes = 0;

        if ($checkIn && $checkOut) {
            $in = strtotime($checkIn);
            $out = strtotime($checkOut);
            $hoursWorked = round(($out - $in) / 3600, 2);
            if ($hoursWorked < 0) $hoursWorked = 0;
            if ($hoursWorked > 8) $overtimeHours = round($hoursWorked - 8, 2);
            $workStart = strtotime($date . ' 09:30');
            if ($in > $workStart) $lateMinutes = (int)(($in - $workStart) / 60);
        }

        return $this->execute(
            "INSERT INTO employee_attendance (employee_id,attendance_date,status,check_in_time,check_out_time,hours_worked,overtime_hours,late_minutes,remarks) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),check_in_time=VALUES(check_in_time),check_out_time=VALUES(check_out_time),hours_worked=VALUES(hours_worked),overtime_hours=VALUES(overtime_hours),late_minutes=VALUES(late_minutes),remarks=VALUES(remarks)",
            [$employeeId,$date,$status,$checkIn,$checkOut,$hoursWorked,$overtimeHours,$lateMinutes,$data['remarks']??null]
        );
    }

    public function getAttendance($employeeId, $month)
    {
        return $this->fetchAll("SELECT * FROM employee_attendance WHERE employee_id=? AND DATE_FORMAT(attendance_date,'%Y-%m')=? ORDER BY attendance_date", [$employeeId, $month]);
    }

    public function getMonthlyAttendance($month)
    {
        return $this->fetchAll("SELECT ea.*,u.name AS employee_name FROM employee_attendance ea LEFT JOIN users u ON ea.employee_id=u.id{$this->tJoin('u')} WHERE DATE_FORMAT(ea.attendance_date,'%Y-%m')=? ORDER BY ea.attendance_date,u.name", array_merge($this->tVal(), [$month]));
    }

    /* ── LEAVES ────────────────────────────────────────── */

    public function submitLeaveRequest(array $data)
    {
        $start = $data['start_date'] ?? '';
        $end = $data['end_date'] ?? '';
        $days = $data['total_days'] ?? 0;
        if (!$days && $start && $end) $days = round((strtotime($end) - strtotime($start)) / 86400 + 1, 1);
        return $this->execute("INSERT INTO employee_leave_requests (employee_id,leave_type,start_date,end_date,total_days,reason,status) VALUES (?,?,?,?,?,?,'pending')", [
            (int)($data['employee_id']??0), $data['leave_type']??'casual', $start, $end, $days, $data['reason']??''
        ]);
    }

    public function approveLeave($leaveId, $approverId)
    {
        $this->execute("UPDATE employee_leave_requests SET status='approved',approved_by=?,approval_date=CURDATE() WHERE id=? AND status='pending'", [$approverId, $leaveId]);
        return true;
    }

    public function rejectLeave($leaveId, $approverId, $reason)
    {
        $this->execute("UPDATE employee_leave_requests SET status='rejected',approved_by=?,approval_date=CURDATE(),remarks=? WHERE id=? AND status='pending'", [$approverId, $reason, $leaveId]);
        return true;
    }

    public function getPendingLeaves()
    {
        return $this->fetchAll("SELECT lr.*,u.name AS employee_name FROM employee_leave_requests lr LEFT JOIN users u ON lr.employee_id=u.id{$this->tJoin('u')} WHERE lr.status='pending' ORDER BY lr.created_at DESC", $this->tVal());
    }

    public function getAllLeaves($status = '')
    {
        $sql = "SELECT lr.*,u.name AS employee_name,a.name AS approver_name FROM employee_leave_requests lr LEFT JOIN users u ON lr.employee_id=u.id{$this->tJoin('u')} LEFT JOIN users a ON lr.approved_by=a.id{$this->tJoin('a')}";
        $params = array_merge($this->tVal(), $this->tVal());
        if ($status) { $sql .= " WHERE lr.status=?"; $params[] = $status; }
        $sql .= " ORDER BY lr.created_at DESC";
        return $this->fetchAll($sql, $params);
    }

    /* ── PAYSLIPS ──────────────────────────────────────── */

    public function generatePayslip($employeeId, $month, $year)
    {
        $emp = $this->fetchOne("SELECT * FROM users WHERE id=?{$this->tEnd()}", array_merge([$employeeId], $this->tVal()));
        if (!$emp) return ['error' => 'Employee not found'];

        $empExt = $this->fetchOne("SELECT * FROM employees WHERE user_id=?", [$employeeId]);
        if (!$empExt) return ['error' => 'Employee details not found in employees table'];

        $empTableId = (int)$empExt['id'];
        $ctc = isset($empExt['salary']) ? (float)$empExt['salary'] : 50000.0;

        $basic = round($ctc * 0.60, 2);
        $hra = round($basic * 0.40, 2);
        $allowances = 15000.00;
        $pf = round($basic * 0.12, 2);
        $esi = $ctc < 21000 ? round($ctc * 0.0075, 2) : 0;
        $pt = $ctc > 15000 ? 200.00 : 0;

        $annual = $ctc * 12;
        $tds = 0.0;
        if ($annual > 1500000) $tds = round((($annual - 1500000) * 0.30 + 187500) / 12, 2);
        elseif ($annual > 1200000) $tds = round((($annual - 1200000) * 0.20 + 112500) / 12, 2);
        elseif ($annual > 900000) $tds = round((($annual - 900000) * 0.15 + 67500) / 12, 2);
        elseif ($annual > 600000) $tds = round((($annual - 600000) * 0.10 + 30000) / 12, 2);
        elseif ($annual > 300000) $tds = round((($annual - 300000) * 0.05) / 12, 2);

        $daysInMonth = (int)date('t', mktime(0,0,0,$month,1,$year));
        $leaves = $this->fetchAll("SELECT total_days FROM employee_leave_requests WHERE employee_id=? AND status='approved' AND MONTH(start_date)=? AND YEAR(start_date)=?", [$employeeId, $month, $year]);
        $lopDays = 0;
        foreach ($leaves as $l) $lopDays += (int)$l['total_days'];

        $att = $this->fetchOne("SELECT COUNT(*) AS cnt FROM employee_attendance WHERE employee_id=? AND status='present' AND MONTH(attendance_date)=? AND YEAR(attendance_date)=?", [$employeeId, $month, $year]);
        $daysPresent = (int)($att['cnt'] ?? 0);
        if ($daysPresent === 0) $daysPresent = $daysInMonth - $lopDays;

        $dailyRate = $basic / max($daysInMonth, 1);
        $lopDeduction = round($dailyRate * $lopDays, 2);
        $deductions = round($pf + $esi + $pt + $lopDeduction, 2);
        $totalDeductions = round($tds + $deductions, 2);
        $gross = round($basic + $hra + $allowances, 2);
        $net = max(0, round($gross - $totalDeductions, 2));

        $existing = $this->fetchOne("SELECT id FROM employee_payslips WHERE employee_id=? AND period_month=? AND period_year=?", [$empTableId, $month, $year]);

        if ($existing) {
            $this->execute("UPDATE employee_payslips SET basic_salary=?,hra=?,allowances=?,deductions=?,tds=?,pf=?,esi=?,professional_tax=?,net_salary=?,days_present=?,lop_days=?,status='draft' WHERE id=?", [$basic,$hra,$allowances,$deductions,$tds,$pf,$esi,$pt,$net,$daysPresent,$lopDays,$existing['id']]);
            $payslipId = (int)$existing['id'];
        } else {
            $payslipId = $this->execute("INSERT INTO employee_payslips (employee_id,period_month,period_year,basic_salary,hra,allowances,deductions,tds,pf,esi,professional_tax,net_salary,days_present,lop_days,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'draft')", [$empTableId,$month,$year,$basic,$hra,$allowances,$deductions,$tds,$pf,$esi,$pt,$net,$daysPresent,$lopDays]);
        }

        return ['id'=>$payslipId,'employee_id'=>$employeeId,'period_month'=>$month,'period_year'=>$year,'basic_salary'=>$basic,'hra'=>$hra,'allowances'=>$allowances,'deductions'=>$deductions,'tds'=>$tds,'pf'=>$pf,'esi'=>$esi,'professional_tax'=>$pt,'net_salary'=>$net,'days_present'=>$daysPresent,'lop_days'=>$lopDays,'status'=>'draft'];
    }

    public function getPayslipHistory($employeeId)
    {
        $empExt = $this->fetchOne("SELECT id FROM employees WHERE user_id=?", [$employeeId]);
        if (!$empExt) return [];
        return $this->fetchAll("SELECT * FROM employee_payslips WHERE employee_id=? ORDER BY period_year DESC,period_month DESC", [$empExt['id']]);
    }

    public function getAllPayslips($month = '', $year = '')
    {
        $sql = "SELECT ep.*,u.name AS employee_name FROM employee_payslips ep LEFT JOIN employees e ON ep.employee_id=e.id LEFT JOIN users u ON e.user_id=u.id{$this->tJoin('u')}";
        $params = $this->tVal();
        if ($month && $year) { $sql .= " WHERE ep.period_month=? AND ep.period_year=?"; $params[] = $month; $params[] = $year; }
        $sql .= " ORDER BY ep.period_year DESC,ep.period_month DESC,u.name";
        return $this->fetchAll($sql, $params);
    }

    public function getPayslipById($id)
    {
        return $this->fetchOne("SELECT ep.*,u.name AS employee_name,u.email AS employee_email FROM employee_payslips ep LEFT JOIN employees e ON ep.employee_id=e.id LEFT JOIN users u ON e.user_id=u.id{$this->tJoin('u')} WHERE ep.id=?", array_merge($this->tVal(), [$id]));
    }

    public function payPayslip($payslipId, $paymentMode, $bankAccountId = null)
    {
        $payslip = $this->getPayslipById($payslipId);
        if (!$payslip) {
            throw new \Exception('Payslip not found');
        }
        if ($payslip['status'] === 'paid') {
            throw new \Exception('Payslip is already paid');
        }

        $netSalary = (float)$payslip['net_salary'];
        $employeeName = $payslip['employee_name'];
        $month = (int)$payslip['period_month'];
        $year = (int)$payslip['period_year'];
        $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));

        $dbPaymentMode = 'cash';
        if ($paymentMode === 'bank') {
            $dbPaymentMode = 'bank_transfer';
            if (empty($bankAccountId)) {
                throw new \Exception('Bank account is required for bank payments');
            }
        } elseif ($paymentMode !== 'cash') {
            throw new \Exception('Invalid payment mode');
        }

        $adminId = $_SESSION['admin_id'] ?? null;

        $this->pdo->beginTransaction();
        try {
            $moneyService = new \App\Services\Accounting\MoneyWorkflowService();
            $txnResult = $moneyService->recordCashTransaction([
                'transaction_type' => 'payment',
                'amount' => $netSalary,
                'bank_account_id' => $paymentMode === 'bank' ? $bankAccountId : null,
                'party_name' => $employeeName,
                'narration' => "Salary payment for {$monthName} {$year} to {$employeeName}",
                'transaction_date' => date('Y-m-d'),
                'payment_mode' => $paymentMode === 'bank' ? 'bank' : 'cash',
                'reference_type' => 'payroll',
                'reference_id' => $payslipId,
                'recorded_by' => $adminId
            ]);

            $voucherNumber = $txnResult['voucher_number'] ?? null;

            $stmt = $this->pdo->prepare("
                UPDATE employee_payslips 
                SET status = 'paid', 
                    paid_date = CURDATE(), 
                    payment_mode = ?, 
                    transaction_ref = ?, 
                    paid_by = ? 
                WHERE id = ?
            ");
            $stmt->execute([$dbPaymentMode, $voucherNumber, $adminId, $payslipId]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /* ── LEADS ─────────────────────────────────────────── */

    public function createLead(array $data)
    {
        $count = $this->fetchOne("SELECT COUNT(*) AS cnt FROM lead_pipeline");
        $num = ($count['cnt'] ?? 0) + 1;
        $leadNumber = 'APS-LD-' . str_pad($num, 4, '0', STR_PAD_LEFT);
        $scoreMap = ['new'=>50,'contacted'=>60,'qualified'=>70,'viewing'=>80,'negotiation'=>90,'closed_won'=>100,'closed_lost'=>0,'on_hold'=>50];
        $status = $data['status'] ?? 'new';

        return $this->execute("INSERT INTO lead_pipeline (lead_number,lead_name,lead_source,lead_type,contact_name,contact_phone,contact_email,property_type,budget_min,budget_max,preferred_location,requirement_details,assigned_to,priority,score,status,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
            $leadNumber, $data['lead_name']??'', $data['lead_source']??'other', $data['lead_type']??'buyer',
            $data['contact_name']??'', $data['contact_phone']??'', $data['contact_email']??'',
            $data['property_type']??'', $data['budget_min']??null, $data['budget_max']??null,
            $data['preferred_location']??'', $data['requirement_details']??'',
            $data['assigned_to']??null, $data['priority']??'warm', $scoreMap[$status]??50, $status, $data['created_by']??null
        ]);
    }

    public function updateLead($leadId, array $data)
    {
        $sets = [];
        $params = [];
        foreach (['lead_name','lead_source','lead_type','contact_name','contact_phone','contact_email','property_type','budget_min','budget_max','preferred_location','requirement_details','assigned_to','follow_up_date','priority','status','closure_notes','closed_date'] as $f) {
            if (array_key_exists($f, $data)) { $sets[] = "$f=?"; $params[] = $data[$f]; }
        }
        if (isset($data['status'])) {
            $scoreMap = ['new'=>50,'contacted'=>60,'qualified'=>70,'viewing'=>80,'negotiation'=>90,'closed_won'=>100,'closed_lost'=>0,'on_hold'=>50];
            $sets[] = "score=?"; $params[] = $scoreMap[$data['status']] ?? 50;
        }
        if (!$sets) return false;
        $params[] = $leadId;
        $this->execute("UPDATE lead_pipeline SET " . implode(',',$sets) . " WHERE id=?", $params);
        return true;
    }

    public function getLeadById($id)
    {
        return $this->fetchOne("SELECT lp.*,u.name AS assigned_name,c.name AS creator_name FROM lead_pipeline lp LEFT JOIN users u ON lp.assigned_to=u.id{$this->tJoin('u')} LEFT JOIN users c ON lp.created_by=c.id{$this->tJoin('c')} WHERE lp.id=?", array_merge($this->tVal(), $this->tVal(), [$id]));
    }

    public function listLeads(array $filters = [])
    {
        $sql = "SELECT lp.*,u.name AS assigned_name FROM lead_pipeline lp LEFT JOIN users u ON lp.assigned_to=u.id{$this->tJoin('u')}";
        $params = $this->tVal();
        $wh = [];
        if (!empty($filters['status'])) { $wh[]="lp.status=?"; $params[]=$filters['status']; }
        if (!empty($filters['source'])) { $wh[]="lp.lead_source=?"; $params[]=$filters['source']; }
        if (!empty($filters['type'])) { $wh[]="lp.lead_type=?"; $params[]=$filters['type']; }
        if (!empty($filters['priority'])) { $wh[]="lp.priority=?"; $params[]=$filters['priority']; }
        if (!empty($filters['search'])) { $wh[]="(lp.lead_name LIKE ? OR lp.contact_name LIKE ? OR lp.contact_phone LIKE ?)"; $s='%'.$filters['search'].'%'; $params[]=$s; $params[]=$s; $params[]=$s; }
        if ($wh) $sql .= " WHERE " . implode(' AND ',$wh);
        $sql .= " ORDER BY lp.created_at DESC";
        if (!empty($filters['limit'])) { $sql .= " LIMIT ".(int)$filters['limit']." OFFSET ".(int)($filters['offset']??0); }
        return $this->fetchAll($sql, $params);
    }

    public function countLeads(array $filters = [])
    {
        $sql = "SELECT COUNT(*) AS cnt FROM lead_pipeline"; $params = [];
        if (!empty($filters['status'])) { $sql .= " WHERE status=?"; $params[]=$filters['status']; }
        $row = $this->fetchOne($sql, $params);
        return (int)($row['cnt'] ?? 0);
    }

    public function addLeadActivity($leadId, array $data)
    {
        $id = $this->execute("INSERT INTO lead_pipeline_activities (lead_id,activity_type,subject,description,activity_date,next_follow_up,outcome,created_by) VALUES (?,?,?,?,?,?,?,?)", [
            $leadId, $data['activity_type']??'note', $data['subject']??'', $data['description']??'',
            $data['activity_date']??date('Y-m-d H:i:s'), $data['next_follow_up']??null, $data['outcome']??null, $data['created_by']??null
        ]);
        if (!empty($data['next_follow_up'])) {
            $this->execute("UPDATE lead_pipeline SET follow_up_date=?,follow_up_count=follow_up_count+1 WHERE id=?", [$data['next_follow_up'], $leadId]);
        }
        return $id;
    }

    public function getLeadTimeline($leadId)
    {
        return $this->fetchAll("SELECT lpa.*,u.name AS creator_name FROM lead_pipeline_activities lpa LEFT JOIN users u ON lpa.created_by=u.id{$this->tJoin('u')} WHERE lpa.lead_id=? ORDER BY lpa.activity_date DESC", array_merge($this->tVal(), [$leadId]));
    }

    public function advanceLeadStage($leadId, $newStage)
    {
        $valid = ['new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold'];
        if (!in_array($newStage, $valid)) return false;
        $lead = $this->getLeadById($leadId);
        if (!$lead) return false;
        $oldStage = $lead['status'];
        $scoreMap = ['new'=>50,'contacted'=>60,'qualified'=>70,'viewing'=>80,'negotiation'=>90,'closed_won'=>100,'closed_lost'=>0,'on_hold'=>50];
        $sets = "status=?,score=?"; $params = [$newStage, $scoreMap[$newStage]??50];
        if (in_array($newStage, ['closed_won','closed_lost'])) { $sets .= ",closed_date=CURDATE()"; }
        $params[] = $leadId;
        $this->execute("UPDATE lead_pipeline SET $sets WHERE id=?", $params);
        $this->addLeadActivity($leadId, ['activity_type'=>'status_change','subject'=>"Stage: $oldStage -> $newStage",'description'=>"Advanced from $oldStage to $newStage",'created_by'=>$lead['assigned_to']??null]);
        return true;
    }

    public function getLeadPipelineSummary()
    {
        $stages = $this->fetchAll("SELECT status,COUNT(*) AS count,AVG(score) AS avg_score,AVG(DATEDIFF(COALESCE(closed_date,CURDATE()),created_at)) AS avg_days FROM lead_pipeline GROUP BY status ORDER BY FIELD(status,'new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold')");
        $total = $this->fetchOne("SELECT COUNT(*) AS cnt FROM lead_pipeline");
        $won = $this->fetchOne("SELECT COUNT(*) AS cnt FROM lead_pipeline WHERE status='closed_won'");
        $rate = ($total['cnt']??0) > 0 ? round(($won['cnt']??0)/($total['cnt']??1)*100,1) : 0;
        return ['stages'=>$stages,'total'=>(int)($total['cnt']??0),'won'=>(int)($won['cnt']??0),'conversion_rate'=>$rate];
    }

    public function getLeadSummary()
    {
        return [
            'by_source' => $this->fetchAll("SELECT lead_source,COUNT(*) AS count FROM lead_pipeline GROUP BY lead_source ORDER BY count DESC"),
            'by_priority' => $this->fetchAll("SELECT priority,COUNT(*) AS count FROM lead_pipeline GROUP BY priority ORDER BY FIELD(priority,'hot','warm','cold','dead')"),
            'by_status' => $this->fetchAll("SELECT status,COUNT(*) AS count FROM lead_pipeline GROUP BY status ORDER BY FIELD(status,'new','contacted','qualified','viewing','negotiation','closed_won','closed_lost','on_hold')"),
        ];
    }

    /* ── OPERATIONS LOG ────────────────────────────────── */

    public function logOperation(array $data)
    {
        return $this->execute("INSERT INTO daily_operations_log (log_date,log_type,colony_id,plot_id,description,amount,party_name,party_type,status,priority,assigned_to,completed_at,notes,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
            $data['log_date']??date('Y-m-d'), $data['log_type']??'other', $data['colony_id']??null, $data['plot_id']??null,
            $data['description']??'', $data['amount']??null, $data['party_name']??'', $data['party_type']??'other',
            $data['status']??'pending', $data['priority']??'medium', $data['assigned_to']??null,
            $data['completed_at']??null, $data['notes']??null, $data['created_by']??null
        ]);
    }

    public function getOperationsLog($date = '', array $filters = [])
    {
        $sql = "SELECT dol.*,u.name AS assigned_name,c.name AS colony_name FROM daily_operations_log dol LEFT JOIN users u ON dol.assigned_to=u.id{$this->tJoin('u')} LEFT JOIN colonies c ON dol.colony_id=c.id";
        $params = $this->tVal(); $wh = [];
        if ($date) { $wh[]="dol.log_date=?"; $params[]=$date; }
        if (!empty($filters['log_type'])) { $wh[]="dol.log_type=?"; $params[]=$filters['log_type']; }
        if (!empty($filters['status'])) { $wh[]="dol.status=?"; $params[]=$filters['status']; }
        if ($wh) $sql .= " WHERE " . implode(' AND ',$wh);
        $sql .= " ORDER BY dol.log_date DESC,dol.created_at DESC";
        return $this->fetchAll($sql, $params);
    }

    /* ── REPORTS ───────────────────────────────────────── */

    public function getReportList()
    {
        return $this->fetchAll("SELECT * FROM report_definitions WHERE is_active=1 ORDER BY report_name");
    }

    public function executeReport($reportId, array $params, $executedBy)
    {
        $report = $this->fetchOne("SELECT * FROM report_definitions WHERE id=?", [$reportId]);
        if (!$report) return ['error' => 'Report not found'];
        $execId = $this->execute("INSERT INTO report_executions (report_id,executed_by,parameters_used,status) VALUES (?,?,?,'running')", [$reportId, $executedBy, json_encode($params)]);
        try {
            $sql = $report['sql_template'];
            $pdoParams = [];
            foreach ($params as $key => $val) { $sql = str_replace(":$key", "?", $sql); $pdoParams[] = $val; }
            $result = $this->fetchAll($sql, $pdoParams);
            $rowCount = count($result);
            $this->execute("UPDATE report_executions SET end_time=NOW(),row_count=?,status='completed' WHERE id=?", [$rowCount, $execId]);
            $this->execute("UPDATE report_definitions SET last_run_at=NOW() WHERE id=?", [$reportId]);
            return ['rows'=>$result,'row_count'=>$rowCount,'execution_id'=>$execId];
        } catch (\Throwable $e) {
            $this->execute("UPDATE report_executions SET end_time=NOW(),status='failed',error_message=? WHERE id=?", [$e->getMessage(), $execId]);
            return ['error'=>$e->getMessage()];
        }
    }

    public function getReportHistory($reportId, $limit = 20)
    {
        return $this->fetchAll("SELECT re.*,u.name AS executed_by_name FROM report_executions re LEFT JOIN users u ON re.executed_by=u.id{$this->tJoin('u')} WHERE re.report_id=? ORDER BY re.created_at DESC LIMIT $limit", array_merge($this->tVal(), [$reportId]));
    }

    /* ── DASHBOARD ─────────────────────────────────────── */

    public function getDashboardStats()
    {
        $today = date('Y-m-d');
        $month = date('Y-m');
        $todayOps = $this->fetchOne("SELECT COUNT(*) AS cnt FROM daily_operations_log WHERE log_date=?", [$today]);
        $activeLeads = $this->fetchOne("SELECT COUNT(*) AS cnt FROM lead_pipeline WHERE status NOT IN ('closed_won','closed_lost')");
        $pendingLeaves = $this->fetchOne("SELECT COUNT(*) AS cnt FROM employee_leave_requests WHERE status='pending'");
        $presentToday = $this->fetchOne("SELECT COUNT(*) AS cnt FROM employee_attendance WHERE attendance_date=? AND status='present'", [$today]);
        $totalEmp = $this->fetchOne("SELECT COUNT(*) AS cnt FROM users WHERE role='employee'{$this->tEnd()}", $this->tVal());
        $reportsMonth = $this->fetchOne("SELECT COUNT(*) AS cnt FROM report_executions WHERE DATE_FORMAT(created_at,'%Y-%m')=?", [$month]);
        $attendancePct = ($totalEmp['cnt']??0) > 0 ? round(($presentToday['cnt']??0)/($totalEmp['cnt']??1)*100,1) : 0;

        return [
            'today_ops'=>(int)($todayOps['cnt']??0),'active_leads'=>(int)($activeLeads['cnt']??0),
            'pending_leaves'=>(int)($pendingLeaves['cnt']??0),'attendance_pct'=>$attendancePct,
            'present_today'=>(int)($presentToday['cnt']??0),'total_employees'=>(int)($totalEmp['cnt']??0),
            'reports_this_month'=>(int)($reportsMonth['cnt']??0),
        ];
    }

    public function getEmployees()
    {
        return $this->fetchAll("SELECT id,name,email FROM users WHERE role='employee'{$this->tEnd()} ORDER BY name", $this->tVal());
    }

    public function getColonies()
    {
        return $this->fetchAll("SELECT id,name FROM colonies ORDER BY name");
    }

    public function getPlots($colonyId = 0)
    {
        if ($colonyId) return $this->fetchAll("SELECT id,plot_no FROM inventory_plots WHERE colony_id=? ORDER BY plot_no", [$colonyId]);
        return $this->fetchAll("SELECT id,plot_no FROM inventory_plots ORDER BY plot_no");
    }
}
