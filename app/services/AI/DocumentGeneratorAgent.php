<?php
/**
 * DocumentGeneratorAgent - Auto-generate business documents
 * 
 * Generates: receipts, demand letters, booking confirmations, 
 * commission statements, lead summaries
 * 
 * No external API. Pure PHP HTML generation.
 */

namespace App\Services\AI;

use App\Core\Database\Database;

class DocumentGeneratorAgent
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function generatePaymentReceipt(int $paymentId): array
    {
        try {
            $payment = $this->db->fetch(
                "SELECT p.*, b.booking_number, u.name as customer_name, u.email, u.phone,
                        pl.plot_number, c.name as colony_name
                 FROM payments p
                 LEFT JOIN bookings b ON p.booking_id = b.id
                 LEFT JOIN users u ON p.user_id = u.id
                 LEFT JOIN plots pl ON b.plot_id = pl.id
                 LEFT JOIN colonies c ON pl.colony_id = c.id
                 WHERE p.id = ?",
                [$paymentId]
            );
            if (!$payment) return ['success' => false, 'error' => 'Payment not found'];

            $amount = number_format($payment['amount'], 2);
            $date = date('d M Y', strtotime($payment['created_at']));
            $bookingNum = $payment['booking_number'] ?? 'N/A';
            $plotNum = $payment['plot_number'] ?? 'N/A';
            $colonyName = $payment['colony_name'] ?? 'N/A';
            $method = $payment['payment_method'] ?? 'N/A';

            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Receipt</title>';
            $html .= '<style>body{font-family:sans-serif;margin:40px;color:#333}.hdr{text-align:center;border-bottom:3px solid #0d9488;padding-bottom:20px;margin-bottom:30px}.hdr h1{color:#0d9488;margin:0}.box{border:2px solid #0d9488;border-radius:12px;padding:30px;background:#f8f9ff}.r{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #eee}.r:last-child{border:none}.l{font-weight:600;color:#555}.amt{font-size:24px;color:#0d9488;font-weight:700}.stamp{text-align:center;margin-top:30px;padding:15px;background:#e8ffe8;border-radius:8px;color:#2d7a2d;font-weight:600}.ft{text-align:center;margin-top:30px;color:#888;font-size:12px}</style></head><body>';
            $html .= '<div class="hdr"><h1>APS Dream Home</h1><p>Payment Receipt</p></div>';
            $html .= '<div class="box">';
            $html .= '<div class="r"><span class="l">Receipt No:</span><span>RCP-'.$payment['id'].'-'.$date.'</span></div>';
            $html .= '<div class="r"><span class="l">Date:</span><span>'.$date.'</span></div>';
            $html .= '<div class="r"><span class="l">Customer:</span><span>'.$payment['customer_name'].'</span></div>';
            $html .= '<div class="r"><span class="l">Booking:</span><span>'.$bookingNum.'</span></div>';
            $html .= '<div class="r"><span class="l">Plot:</span><span>'.$plotNum.'</span></div>';
            $html .= '<div class="r"><span class="l">Colony:</span><span>'.$colonyName.'</span></div>';
            $html .= '<div class="r"><span class="l">Payment Method:</span><span>'.$method.'</span></div>';
            $html .= '<div class="r"><span class="l">Amount Paid:</span><span class="amt">₹'.$amount.'</span></div>';
            $html .= '</div><div class="stamp">✅ PAYMENT RECEIVED</div>';
            $html .= '<div class="ft">APS Dream Home | Raghunath Nagri, Gorakhpur | +91 92771 21112</div>';
            $html .= '</body></html>';

            return ['success' => true, 'type' => 'payment_receipt', 'html' => $html, 'data' => $payment];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateDemandLetter(int $installmentId): array
    {
        try {
            $inst = $this->db->fetch(
                "SELECT ips.*, pp.booking_number, u.name as customer_name,
                        pl.plot_number, c.name as colony_name
                 FROM booking_payment_schedules ips
                 LEFT JOIN plot_bookings pp ON ips.booking_id = pp.id
                 LEFT JOIN users u ON pp.customer_id = u.id
                 LEFT JOIN plots pl ON pp.plot_id = pl.id
                 LEFT JOIN colonies c ON pl.colony_id = c.id
                 WHERE ips.id = ?",
                [$installmentId]
            );
            if (!$inst) return ['success' => false, 'error' => 'Installment not found'];

            $amount = number_format($inst['emi_amount'] ?? $inst['amount'] ?? 0, 2);
            $dueDate = date('d M Y', strtotime($inst['due_date']));
            $name = $inst['customer_name'] ?? 'Customer';
            $plot = $inst['plot_number'] ?? 'N/A';
            $colony = $inst['colony_name'] ?? 'N/A';
            $num = $inst['installment_number'] ?? 'N/A';

            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Demand Letter</title>';
            $html .= '<style>body{font-family:sans-serif;margin:40px;color:#333}.hdr{text-align:center;border-bottom:3px solid #e74c3c;padding-bottom:20px;margin-bottom:30px}.hdr h1{color:#e74c3c;margin:0}.content{line-height:1.8;font-size:15px}.abox{background:#fff3cd;border:2px solid #ffc107;border-radius:12px;padding:20px;text-align:center;margin:25px 0}.abox .amt{font-size:32px;color:#e74c3c;font-weight:700}.due{color:#e74c3c;font-weight:600;font-size:18px}.ft{text-align:center;margin-top:40px;color:#888;font-size:12px}</style></head><body>';
            $html .= '<div class="hdr"><h1>DEMAND LETTER</h1><p>Installment Payment Reminder</p></div>';
            $html .= '<div class="content">';
            $html .= '<p>Dear <strong>'.$name.'</strong>,</p>';
            $html .= '<p>This is a reminder for your upcoming installment for Plot <strong>'.$plot.'</strong> in <strong>'.$colony.'</strong>.</p>';
            $html .= '<p><strong>Installment #:</strong> '.$num.'</p>';
            $html .= '<div class="abox"><p>Amount Due</p><p class="amt">₹'.$amount.'</p><p class="due">Due by: '.$dueDate.'</p></div>';
            $html .= '<p>Please make the payment before the due date to avoid late fees.</p>';
            $html .= '<ul><li>UPI: apsdreamhome@upi</li><li>Bank: APS Dream Home, SBI A/C XXXX1234</li><li>Cash/Cheque: Visit our office</li></ul>';
            $html .= '</div><div class="ft">APS Dream Home | Raghunath Nagri, Gorakhpur | +91 92771 21112</div>';
            $html .= '</body></html>';

            return ['success' => true, 'type' => 'demand_letter', 'html' => $html, 'data' => $inst];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateBookingConfirmation(int $bookingId): array
    {
        try {
            $bk = $this->db->fetch(
                "SELECT pb.*, u.name as customer_name, pl.plot_number, pl.area_sqft, c.name as colony_name
                 FROM plot_bookings pb
                 LEFT JOIN users u ON pb.customer_id = u.id
                 LEFT JOIN plots pl ON pb.plot_id = pl.id
                 LEFT JOIN colonies c ON pl.colony_id = c.id
                 WHERE pb.id = ?",
                [$bookingId]
            );
            if (!$bk) return ['success' => false, 'error' => 'Booking not found'];

            $date = date('d M Y', strtotime($bk['created_at']));
            $name = $bk['customer_name'] ?? 'Customer';
            $plot = $bk['plot_number'] ?? 'N/A';
            $area = $bk['area_sqft'] ?? 'N/A';
            $colony = $bk['colony_name'] ?? 'N/A';
            $status = ucfirst($bk['status'] ?? 'confirmed');

            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Booking Confirmation</title>';
            $html .= '<style>body{font-family:sans-serif;margin:40px;color:#333}.hdr{text-align:center;border-bottom:3px solid #27ae60;padding-bottom:20px;margin-bottom:30px}.hdr h1{color:#27ae60;margin:0}.bbox{background:#e8ffe8;border:2px solid #27ae60;border-radius:12px;padding:25px;margin:25px 0}.bbox h3{color:#27ae60;margin-top:0}.r{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #ddd}.stamp{text-align:center;margin-top:30px;padding:20px;background:#27ae60;color:#fff;border-radius:12px;font-size:20px;font-weight:700}.ft{text-align:center;margin-top:30px;color:#888;font-size:12px}</style></head><body>';
            $html .= '<div class="hdr"><h1>BOOKING CONFIRMATION</h1><p>APS Dream Home</p></div>';
            $html .= '<p>Dear <strong>'.$name.'</strong>,</p>';
            $html .= '<p>Congratulations! Your booking has been confirmed:</p>';
            $html .= '<div class="bbox"><h3>Booking Details</h3>';
            $html .= '<div class="r"><span>Booking #:</span><span>'.$bk['booking_number'].'</span></div>';
            $html .= '<div class="r"><span>Plot:</span><span>'.$plot.'</span></div>';
            $html .= '<div class="r"><span>Area:</span><span>'.$area.' sqft</span></div>';
            $html .= '<div class="r"><span>Colony:</span><span>'.$colony.'</span></div>';
            $html .= '<div class="r"><span>Date:</span><span>'.$date.'</span></div>';
            $html .= '<div class="r"><span>Status:</span><span>'.$status.'</span></div>';
            $html .= '</div><div class="stamp">🎉 BOOKING CONFIRMED!</div>';
            $html .= '<div class="ft">APS Dream Home | Raghunath Nagri, Gorakhpur | +91 92771 21112</div>';
            $html .= '</body></html>';

            return ['success' => true, 'type' => 'booking_confirmation', 'html' => $html, 'data' => $bk];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateCommissionStatement(int $userId, string $month): array
    {
        try {
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if (!$user) return ['success' => false, 'error' => 'User not found'];

            $commissions = $this->db->fetchAll(
                "SELECT cl.*, pb.plot_number, c.name as colony_name
                 FROM mlm_commission_ledger cl
                 LEFT JOIN plot_bookings pb ON cl.booking_id = pb.id
                 LEFT JOIN plots pl ON pb.plot_id = pl.id
                 LEFT JOIN colonies c ON pl.colony_id = c.id
                 WHERE cl.beneficiary_user_id = ? AND DATE_FORMAT(cl.created_at, '%Y-%m') = ?
                 ORDER BY cl.created_at DESC",
                [$userId, $month]
            );

            $total = array_sum(array_column($commissions, 'amount'));
            $userName = $user['name'] ?? 'Associate';
            $monthLabel = date('M Y', strtotime($month . '-01'));

            $rows = '';
            foreach ($commissions as $c) {
                $d = date('d M', strtotime($c['created_at']));
                $type = ucfirst(str_replace('_', ' ', $c['type'] ?? 'sale'));
                $plot = $c['plot_number'] ?? 'N/A';
                $amt = number_format($c['amount'], 2);
                $rows .= "<tr><td>$d</td><td>$type</td><td>$plot</td><td>₹$amt</td></tr>";
            }

            $totalFmt = number_format($total, 2);

            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Commission Statement</title>';
            $html .= '<style>body{font-family:sans-serif;margin:40px;color:#333}.hdr{text-align:center;border-bottom:3px solid #0d9488;padding-bottom:20px;margin-bottom:30px}.hdr h1{color:#0d9488;margin:0}table{width:100%;border-collapse:collapse;margin:20px 0}th{background:#0d9488;color:#fff;padding:12px;text-align:left}td{padding:10px 12px;border-bottom:1px solid #eee}.tot{background:#f8f9ff;font-weight:700;font-size:18px;color:#0d9488}.ft{text-align:center;margin-top:30px;color:#888;font-size:12px}</style></head><body>';
            $html .= '<div class="hdr"><h1>COMMISSION STATEMENT</h1><p>'.$monthLabel.'</p></div>';
            $html .= '<p>Dear <strong>'.$userName.'</strong>, here is your statement for <strong>'.$monthLabel.'</strong>:</p>';
            $html .= '<table><tr><th>Date</th><th>Type</th><th>Plot</th><th>Amount</th></tr>';
            $html .= $rows;
            $html .= '<tr class="tot"><td colspan="3">TOTAL EARNED</td><td>₹'.$totalFmt.'</td></tr></table>';
            $html .= '<div class="ft">APS Dream Home | Commission System | +91 92771 21112</div>';
            $html .= '</body></html>';

            return ['success' => true, 'type' => 'commission_statement', 'html' => $html, 'data' => ['user' => $user, 'commissions' => $commissions, 'total' => $total]];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateLeadSummary(int $associateId): array
    {
        try {
            $assoc = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$associateId]);
            $leads = $this->db->fetchAll(
                "SELECT l.*, la.activity_type, la.description as last_activity
                 FROM leads l LEFT JOIN lead_activities la ON l.id = la.lead_id
                 WHERE l.assigned_to = ? ORDER BY l.created_at DESC LIMIT 20",
                [$associateId]
            );

            $name = $assoc['name'] ?? 'Associate';
            $total = count($leads);
            $hot = count(array_filter($leads, fn($l) => ($l['status'] ?? '') === 'qualified'));

            $rows = '';
            foreach ($leads as $l) {
                $color = match($l['status'] ?? 'new') {
                    'qualified' => '#27ae60', 'contacted' => '#f39c12',
                    'converted' => '#0d9488', default => '#95a5a6'
                };
                $s = ucfirst($l['status'] ?? 'new');
                $d = date('d M', strtotime($l['created_at']));
                $rows .= "<tr><td>{$l['name']}</td><td>".($l['phone'] ?? 'N/A')."</td><td style='color:$color;font-weight:600'>$s</td><td>$d</td></tr>";
            }

            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Lead Summary</title>';
            $html .= '<style>body{font-family:sans-serif;margin:40px;color:#333}.hdr{text-align:center;border-bottom:3px solid #0d9488;padding-bottom:20px;margin-bottom:30px}.hdr h1{color:#0d9488;margin:0}.stats{display:flex;gap:20px;margin:20px 0}.sb{flex:1;background:#f8f9ff;border-radius:12px;padding:20px;text-align:center;border:1px solid #e0e0e0}.sb .n{font-size:32px;color:#0d9488;font-weight:700}.sb .l{color:#666;font-size:14px}table{width:100%;border-collapse:collapse;margin:20px 0}th{background:#0d9488;color:#fff;padding:12px;text-align:left}td{padding:10px 12px;border-bottom:1px solid #eee}.ft{text-align:center;margin-top:30px;color:#888;font-size:12px}</style></head><body>';
            $html .= '<div class="hdr"><h1>LEAD SUMMARY</h1><p>'.$name.' - '.date('d M Y').'</p></div>';
            $html .= '<div class="stats"><div class="sb"><div class="n">'.$total.'</div><div class="l">Total Leads</div></div>';
            $html .= '<div class="sb"><div class="n">'.$hot.'</div><div class="l">Hot Leads</div></div></div>';
            $html .= '<table><tr><th>Name</th><th>Phone</th><th>Status</th><th>Date</th></tr>'.$rows.'</table>';
            $html .= '<div class="ft">APS Dream Home | CRM | +91 92771 21112</div></body></html>';

            return ['success' => true, 'type' => 'lead_summary', 'html' => $html, 'data' => ['associate' => $assoc, 'leads' => $leads]];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
