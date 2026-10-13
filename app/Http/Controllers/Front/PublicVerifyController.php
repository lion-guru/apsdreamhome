<?php

namespace App\Http\Controllers\Front;

use App\Core\Database\Database;

/**
 * Universal public document QR verification gateway.
 * GET /verify/{type}/{ref} — no login. Shows green verified card or red
 * invalid card. Customer names are masked for privacy.
 */
class PublicVerifyController extends PageController
{
    private static function maskName(?string $name): string
    {
        $name = trim((string)$name);
        if ($name === '') return '—';
        $parts = preg_split('/\s+/', $name);
        $out = [];
        foreach ($parts as $w) {
            $out[] = mb_strtoupper(mb_substr($w, 0, 1)) . '****';
        }
        return implode(' ', $out);
    }

    public function verify($type = null, $ref = null)
    {
        $type = strtolower(trim((string)($type ?? '')));
        $ref = trim((string)($ref ?? ''));
        $validTypes = ['receipt', 'allotment', 'statement', 'noc', 'title_cert', 'transfer', 'registry_deed', 'cancellation_deed', 'possession_certificate', 'allotment_letter', 'stamp_paper'];
        $found = null;
        if (in_array($type, $validTypes, true) && $ref !== '') {
            try {
                $found = $this->lookup(Database::getInstance(), $type, $ref);
            } catch (\Throwable $e) {
                error_log('[PublicVerifyController::verify] ' . $e->getMessage());
                $found = null;
            }
        }
        $this->render('pages/public_verify', [
            'page_title' => 'Document Verification - APS Dream Home',
            'doc_type' => $type,
            'ref_no' => $ref,
            'doc' => $found,
        ]);
    }

    /**
     * @return array|null normalized doc fields or null when not found
     */
    private function lookup($db, string $type, string $ref): ?array
    {
        switch ($type) {
            case 'receipt':
                $r = $db->fetchOne(
                    "SELECT r.receipt_number, r.receipt_date, r.amount, r.payment_mode, r.status,
                            COALESCE(u.name, u2.name) AS customer_name,
                            COALESCE(p.plot_number, p2.plot_number) AS plot_number,
                            COALESCE(c.name, c2.name) AS colony_name
                     FROM booking_payment_receipts r
                     LEFT JOIN plot_bookings b ON b.id = r.booking_id
                     LEFT JOIN users u ON u.id = b.customer_id
                     LEFT JOIN plots p ON p.id = b.plot_id
                     LEFT JOIN colonies c ON c.id = p.colony_id
                     LEFT JOIN bookings bb ON bb.id = r.booking_id
                     LEFT JOIN users u2 ON u2.id = bb.customer_id
                     LEFT JOIN plots p2 ON p2.id = bb.plot_id
                     LEFT JOIN colonies c2 ON c2.id = p2.colony_id
                     WHERE r.receipt_number = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Payment Receipt',
                    'ref' => $r['receipt_number'],
                    'date' => $r['receipt_date'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => '₹' . number_format((float)($r['amount'] ?? 0), 2) . ' (' . ($r['payment_mode'] ?? '') . ', ' . ($r['status'] ?? '') . ')',
                ];
            case 'allotment':
                $r = $db->fetchOne(
                    "SELECT a.allotment_number, a.issued_at, a.total_amount,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name
                     FROM allotment_documents a
                     LEFT JOIN users u ON u.id = a.customer_id
                     LEFT JOIN plots p ON p.id = a.plot_id
                     LEFT JOIN colonies c ON c.id = a.colony_id
                     WHERE a.allotment_number = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Allotment Letter',
                    'ref' => $r['allotment_number'],
                    'date' => $r['issued_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => '₹' . number_format((float)($r['total_amount'] ?? 0), 2),
                ];
            case 'statement':
                $r = $db->fetchOne(
                    "SELECT b.booking_number, b.created_at,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name,
                            (SELECT COALESCE(SUM(paid_amount),0) FROM booking_payment_schedules s WHERE s.booking_id = b.id) AS paid
                     FROM plot_bookings b
                     LEFT JOIN users u ON u.id = b.customer_id
                     LEFT JOIN plots p ON p.id = b.plot_id
                     LEFT JOIN colonies c ON c.id = p.colony_id
                     WHERE b.booking_number = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Passbook Statement',
                    'ref' => $r['booking_number'],
                    'date' => $r['created_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => 'Paid ₹' . number_format((float)($r['paid'] ?? 0), 2) . ' (ledger-verified)',
                ];
            case 'noc':
                if (!ctype_digit($ref)) return null;
                $r = $db->fetchOne(
                    "SELECT n.id, n.status, n.created_at, n.purpose,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name
                     FROM noc_requests n
                     LEFT JOIN plot_bookings b ON b.id = n.booking_id
                     LEFT JOIN users u ON u.id = COALESCE(n.user_id, b.customer_id)
                     LEFT JOIN plots p ON p.id = COALESCE(n.plot_id, b.plot_id)
                     LEFT JOIN colonies c ON c.id = p.colony_id
                     WHERE n.id = ? LIMIT 1",
                    [(int)$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'NOC Request #' . $r['id'],
                    'ref' => (string)$r['id'],
                    'date' => $r['created_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => ucfirst((string)($r['status'] ?? '')) . ' · ' . ($r['purpose'] ?? ''),
                ];
            case 'title_cert':
                $r = $db->fetchOne(
                    "SELECT t.cert_no, t.created_at, t.verdict,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name
                     FROM title_certificates t
                     LEFT JOIN plots p ON p.id = t.plot_id
                     LEFT JOIN colonies c ON c.id = t.colony_id
                     LEFT JOIN users u ON u.id = t.customer_id
                     WHERE t.cert_no = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Title Certificate',
                    'ref' => $r['cert_no'],
                    'date' => $r['created_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => (string)($r['verdict'] ?? ''),
                ];
case 'transfer':
                $r = $db->fetchOne(
                    "SELECT t.noc_number, t.noc_issued_at, t.created_at, t.transfer_type, t.status,
                            t.new_buyer_name, u.name AS old_customer_name,
                            p.plot_number, c.name AS colony_name
                         FROM plot_transfers t
                         JOIN plots p ON p.id = t.plot_id
                         JOIN colonies c ON c.id = p.colony_id
                         LEFT JOIN users u ON u.id = t.from_customer_id
                         WHERE t.noc_number = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Ownership Transfer NOC',
                    'ref' => $r['noc_number'],
                    'date' => $r['noc_issued_at'] ?? $r['created_at'] ?? date('Y-m-d'),
                    'who' => 'From: ' . self::maskName($r['old_customer_name'] ?? '') . ' → To: ' . self::maskName($r['new_buyer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => ucfirst((string)($r['transfer_type'] ?? 'resale')) . ' Transfer · Status: ' . strtoupper((string)($r['status'] ?? '')),
                ];
            case 'registry_deed':
                $r = $db->fetchOne(
                    "SELECT rd.cert_no, rd.created_at, rd.verdict,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name
                         FROM registry_documents rd
                         LEFT JOIN plots p ON p.id = rd.plot_id
                         LEFT JOIN colonies c ON c.id = rd.colony_id
                         LEFT JOIN users u ON u.id = rd.customer_id
                         WHERE rd.cert_no = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Registry Deed',
                    'ref' => $r['cert_no'],
                    'date' => $r['created_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => (string)($r['verdict'] ?? 'Verified Registry Deed'),
                ];
            case 'cancellation_deed':
                $r = $db->fetchOne(
                    "SELECT cd.cert_no, cd.created_at, cd.verdict,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name
                         FROM cancellation_documents cd
                         LEFT JOIN plots p ON p.id = cd.plot_id
                         LEFT JOIN colonies c ON c.id = cd.colony_id
                         LEFT JOIN users u ON u.id = cd.customer_id
                         WHERE cd.cert_no = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Cancellation Deed',
                    'ref' => $r['cert_no'],
                    'date' => $r['created_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => (string)($r['verdict'] ?? 'Cancellation Deed Verified'),
                ];
            case 'possession_certificate':
                $r = $db->fetchOne(
                    "SELECT pc.cert_no, pc.created_at, pc.verdict,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name
                         FROM possession_certificates pc
                         LEFT JOIN plots p ON p.id = pc.plot_id
                         LEFT JOIN colonies c ON c.id = pc.colony_id
                         LEFT JOIN users u ON u.id = pc.customer_id
                         WHERE pc.cert_no = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Possession Certificate',
                    'ref' => $r['cert_no'],
                    'date' => $r['created_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => (string)($r['verdict'] ?? 'Possession Certificate Verified'),
                ];
            case 'allotment_letter':
                $r = $db->fetchOne(
                    "SELECT ad.allotment_number, ad.issued_at, ad.total_amount,
                            u.name AS customer_name, p.plot_number, c.name AS colony_name
                         FROM allotment_documents ad
                         LEFT JOIN users u ON u.id = ad.customer_id
                         LEFT JOIN plots p ON p.id = ad.plot_id
                         LEFT JOIN colonies c ON c.id = ad.colony_id
                         WHERE ad.allotment_number = ? LIMIT 1",
                    [$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Allotment Letter',
                    'ref' => $r['allotment_number'],
                    'date' => $r['issued_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => '₹' . number_format((float)($r['total_amount'] ?? 0), 2),
                ];
            case 'stamp_paper':
                if (!ctype_digit($ref)) return null;
                $r = $db->fetchOne(
                    "SELECT sdc.*, b.booking_number, c.name AS customer_name, p.plot_number, col.name AS colony_name
                         FROM stamp_duty_calculations sdc
                         INNER JOIN bookings b ON sdc.booking_id = b.id
                         INNER JOIN customers c ON b.customer_id = c.id
                         LEFT JOIN plots p ON b.plot_id = p.id
                         LEFT JOIN colonies col ON p.colony_id = col.id
                         WHERE sdc.id = ? LIMIT 1",
                    [(int)$ref]
                );
                if (!$r) return null;
                return [
                    'label' => 'Stamp Paper Verification',
                    'ref' => (string)$r['id'],
                    'date' => $r['created_at'],
                    'who' => self::maskName($r['customer_name'] ?? ''),
                    'plot' => trim(($r['plot_number'] ?? '') . ', ' . ($r['colony_name'] ?? ''), ', '),
                    'amount' => '₹' . number_format((float)($r['total_amount'] ?? 0), 2) . ' (Stamp: ₹' . number_format((float)($r['stamp_duty_amount'] ?? 0), 2) . ' + Reg: ₹' . number_format((float)($r['registration_fee'] ?? 0), 2) . ')',
                ];
        }
        return null;
    }
}
