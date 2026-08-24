<?php
/**
 * PdfService — Unified PDF generation facade.
 *
 * Public API (5 methods):
 *   - generate($type, $data)         - dispatch to a template method
 *   - receipt($bookingId)            - payment receipt PDF
 *   - invoice($bookingId)            - tax invoice PDF
 *   - agreement($bookingId)          - booking agreement / sale deed draft
 *   - report($reportId)              - report PDF
 *   - brochure($propertyId)          - property brochure
 *
 * Plus helpers:
 *   - getStats()                     - cache hit/miss + generated count
 *   - getRecent($limit)              - recent generated files
 *
 * Storage: storage/pdfs/{type}/{id}_{hash}.pdf  (zero-fill safe).
 * Download route: GET /pdf/download/{type}/{id}  (signed token).
 *
 * All methods return envelope: {success, data, error}
 *   - {success: true,  data: {path, url, bytes, generated_at}}
 *   - {success: false, error: 'message'}
 */
namespace App\Services\Pdf;

use App\Vendor\MinimalPDF;
use App\Core\Autoloader;
use PDO;

class PdfService
{
    const VERSION = '1.0.0';

    const TYPE_RECEIPT   = 'receipt';
    const TYPE_INVOICE   = 'invoice';
    const TYPE_AGREEMENT = 'agreement';
    const TYPE_REPORT    = 'report';
    const TYPE_BROCHURE  = 'brochure';

    const ALL_TYPES = [
        self::TYPE_RECEIPT, self::TYPE_INVOICE, self::TYPE_AGREEMENT,
        self::TYPE_REPORT, self::TYPE_BROCHURE,
    ];

    /** @var \PDO|null */
    protected $db;

    /** @var array */
    protected $stats = [
        'generated' => 0,
        'cache_hits' => 0,
        'cache_misses' => 0,
        'errors' => 0,
        'by_type' => [
            self::TYPE_RECEIPT => 0, self::TYPE_INVOICE => 0,
            self::TYPE_AGREEMENT => 0, self::TYPE_REPORT => 0,
            self::TYPE_BROCHURE => 0,
        ],
    ];

    /** @var string */
    protected $storagePath;

    public function __construct($db = null)
    {
        $this->db = $this->normalizeDb($db) ?: $this->resolveDb();
        if (defined('STORAGE_PATH')) {
            $this->storagePath = STORAGE_PATH . '/pdfs';
        } else {
            // Fallback: 3 levels up from app/Services/Pdf/ → project root
            $this->storagePath = dirname(__DIR__, 3) . '/storage/pdfs';
        }
        if (!is_dir($this->storagePath)) @mkdir($this->storagePath, 0775, true);
    }

    /**
     * Dispatcher: route to a type-specific generator.
     *
     * @param string $type  one of self::ALL_TYPES
     * @param int    $id     entity id
     * @return array        {success, data|error}
     */
    public function generate($type, $id)
    {
        if (!in_array($type, self::ALL_TYPES, true)) {
            return ['success' => false, 'error' => "Unknown PDF type: $type"];
        }
        $id = (int)$id;
        if ($id <= 0) return ['success' => false, 'error' => 'Invalid ID'];

        $method = $type;
        if (!method_exists($this, $method)) {
            return ['success' => false, 'error' => "Generator not found: $type"];
        }

        try {
            $result = $this->$method($id);
            if ($result['success'] ?? false) {
                $this->stats['generated']++;
                $this->stats['by_type'][$type] = ($this->stats['by_type'][$type] ?? 0) + 1;
                $this->logToDb($type, $id, $result);
            }
            return $result;
        } catch (\Throwable $e) {
            $this->stats['errors']++;
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  1. Payment Receipt                                                */
    /* ------------------------------------------------------------------ */

    public function receipt($bookingId)
    {
        $booking = $this->loadBooking($bookingId);
        if (!$booking) return ['success' => false, 'error' => 'Booking not found'];

        $payment = $this->loadPaymentForBooking($bookingId);
        if (!$payment) return ['success' => false, 'error' => 'No payment found for booking'];

        $cached = $this->checkCache(self::TYPE_RECEIPT, $bookingId, $payment['id']);
        if ($cached) return $cached;

        $pdf = new MinimalPDF();
        $this->renderHeader($pdf, 'Payment Receipt');

        $this->renderKvBlock($pdf, 110, [
            'Receipt No'      => 'RCT-' . str_pad((string)$payment['id'], 6, '0', STR_PAD_LEFT),
            'Booking ID'      => 'BKG-' . str_pad((string)$bookingId, 6, '0', STR_PAD_LEFT),
            'Payment Date'    => date('d-M-Y H:i', strtotime($payment['created_at'] ?? 'now')),
            'Payment Method'  => strtoupper($payment['method'] ?? 'razorpay'),
            'Transaction ID'  => $payment['gateway_payment_id'] ?? $payment['id'],
        ]);

        $pdf->setFont('Helvetica', 'B', 11);
        $pdf->text(40, 230, 'Billed To:');
        $pdf->setFont('Helvetica', '', 11);
        $pdf->text(40, 248, ($booking['customer_name'] ?? 'Customer') . ' <' . ($booking['customer_email'] ?? 'n/a') . '>');
        if (!empty($booking['customer_phone'])) {
            $pdf->text(40, 264, 'Phone: ' . $booking['customer_phone']);
        }

        $pdf->setFont('Helvetica', 'B', 11);
        $pdf->text(40, 300, 'Property:');
        $pdf->setFont('Helvetica', '', 11);
        $pdf->multiText(40, 318, ($booking['property_title'] ?? 'Property') . ' - ' . ($booking['plot_number'] ?? ''), 515);

        $y = 360;
        $this->renderTable($pdf, $y,
            ['', 'Amount'],
            [
                ['Plot/Property Cost',  'Rs. ' . number_format((float)($payment['amount'] ?? 0), 2)],
                ['Tax (GST 5%)',         'Rs. ' . number_format((float)($payment['tax'] ?? 0), 2)],
                ['Total Paid',           'Rs. ' . number_format((float)($payment['total'] ?? $payment['amount'] ?? 0), 2)],
            ],
            [380, 100]
        );

        $pdf->setFont('Helvetica', 'I', 9);
        $pdf->multiText(40, 460,
            "This is a computer-generated receipt. No signature required.\n" .
            "For queries: support@apsdreamhome.com | +91 92771 21112",
            515
        );

        $path = $this->writePdf($pdf, self::TYPE_RECEIPT, $bookingId, $payment['id']);
        return $this->returnResult($path, $bookingId, $payment['id']);
    }

    /* ------------------------------------------------------------------ */
    /*  2. Tax Invoice                                                    */
    /* ------------------------------------------------------------------ */

    public function invoice($bookingId)
    {
        $booking = $this->loadBooking($bookingId);
        if (!$booking) return ['success' => false, 'error' => 'Booking not found'];

        $cached = $this->checkCache(self::TYPE_INVOICE, $bookingId);
        if ($cached) return $cached;

        $pdf = new MinimalPDF();
        $this->renderHeader($pdf, 'Tax Invoice');

        $this->renderKvBlock($pdf, 110, [
            'Invoice No'   => 'INV-' . str_pad((string)$bookingId, 6, '0', STR_PAD_LEFT),
            'Invoice Date' => date('d-M-Y'),
            'GST No'       => '09ABCDE1234F1Z5',
            'Place of Supply' => 'Uttar Pradesh (09)',
        ]);

        $pdf->setFont('Helvetica', 'B', 11);
        $pdf->text(40, 230, 'Bill To:');
        $pdf->setFont('Helvetica', '', 11);
        $pdf->text(40, 248, ($booking['customer_name'] ?? 'Customer') . ' <' . ($booking['customer_email'] ?? 'n/a') . '>');

        $amount = (float)($booking['amount'] ?? 0);
        $cgst   = $amount * 0.025;
        $sgst   = $amount * 0.025;
        $total  = $amount + $cgst + $sgst;

        $y = 290;
        $this->renderTable($pdf, $y,
            ['Description', 'SAC', 'Amount (Rs.)'],
            [
                ['Real Estate Plot Booking', '9972', number_format($amount, 2)],
                ['CGST @ 2.5%',              '',    number_format($cgst, 2)],
                ['SGST @ 2.5%',              '',    number_format($sgst, 2)],
            ],
            [280, 80, 100]
        );
        $pdf->setFont('Helvetica', 'B', 12);
        $pdf->text(40, $y + 130, 'Total:');
        $pdf->text(450, $y + 130, 'Rs. ' . number_format($total, 2));
        $pdf->line(40, $y + 138, 555, $y + 138);

        $pdf->setFont('Helvetica', 'I', 9);
        $pdf->text(40, 740, 'This is a computer-generated invoice. E. & O.E.');
        $pdf->text(40, 754, 'For APS Dream Home Pvt Ltd');
        $pdf->text(40, 768, 'Authorized Signatory');

        $path = $this->writePdf($pdf, self::TYPE_INVOICE, $bookingId);
        return $this->returnResult($path, $bookingId);
    }

    /* ------------------------------------------------------------------ */
    /*  3. Booking Agreement / Sale Deed Draft                            */
    /* ------------------------------------------------------------------ */

    public function agreement($bookingId)
    {
        $booking = $this->loadBooking($bookingId);
        if (!$booking) return ['success' => false, 'error' => 'Booking not found'];

        $cached = $this->checkCache(self::TYPE_AGREEMENT, $bookingId);
        if ($cached) return $cached;

        $pdf = new MinimalPDF();
        $this->renderHeader($pdf, 'Booking Agreement (Draft)');

        $pdf->setFont('Helvetica', 'B', 12);
        $pdf->text(40, 110, 'AGREEMENT FOR SALE');
        $pdf->setFont('Helvetica', '', 10);

        $body = "This Agreement for Sale is executed on " . date('d-M-Y') . " between:\n\n" .
            "M/s APS Dream Home Pvt. Ltd., a company incorporated under the Companies Act, 2013, " .
            "having its registered office at Gorakhpur, Uttar Pradesh (hereinafter referred to as \"the Developer/Seller\"),\n\n" .
            "AND\n\n" .
            ($booking['customer_name'] ?? 'Mr/Mrs __________') . ", S/o, D/o, W/o __________, " .
            "aged ___ years, residing at __________ (hereinafter referred to as \"the Buyer/Purchaser\").\n\n" .
            "WHEREAS:\n" .
            "1. The Developer is the absolute owner of the property described herein.\n" .
            "2. The Buyer has approached the Developer for purchase of a residential plot.\n" .
            "3. Both parties have agreed to the terms and conditions set out below.\n\n" .
            "NOW THIS AGREEMENT WITNESSETH AS FOLLOWS:\n\n" .
            "1. SUBJECT OF SALE:\n" .
            "   Plot No: " . ($booking['plot_number'] ?? '___') . "\n" .
            "   Project: " . ($booking['property_title'] ?? '___') . "\n" .
            "   Area: " . ($booking['area_sqft'] ?? '___') . " sq. ft.\n\n" .
            "2. CONSIDERATION:\n" .
            "   Total Sale Consideration: Rs. " . number_format((float)($booking['amount'] ?? 0), 2) . "\n" .
            "   Earnest Money Paid: Rs. " . number_format((float)($booking['amount'] ?? 0) * 0.1, 2) . " (10%)\n" .
            "   Balance: Payable in installments as per schedule.\n\n" .
            "3. POSSESSION:\n" .
            "   Possession shall be handed over within 24 months from the date of this agreement, " .
            "subject to timely payment by the Buyer.\n\n" .
            "4. REGISTRATION:\n" .
            "   This agreement shall be registered before the Sub-Registrar, Gorakhpur. " .
            "All registration charges and stamp duty shall be borne by the Buyer.\n\n" .
            "5. DEFAULT:\n" .
            "   In case of default by the Buyer in payment of installments, the Developer reserves " .
            "the right to cancel this agreement after 30 days notice.\n\n" .
            "IN WITNESS WHEREOF, the parties have signed this Agreement on the date first above written.\n\n\n" .
            "________________________          ________________________\n" .
            "Developer (APS Dream Home)       Buyer / Purchaser\n";

        $pdf->multiText(40, 130, $body, 515, 1.4);

        $path = $this->writePdf($pdf, self::TYPE_AGREEMENT, $bookingId);
        return $this->returnResult($path, $bookingId);
    }

    /* ------------------------------------------------------------------ */
    /*  4. Report PDF                                                     */
    /* ------------------------------------------------------------------ */

    public function report($reportId)
    {
        $reportId = (int)$reportId;
        $cached = $this->checkCache(self::TYPE_REPORT, $reportId);
        if ($cached) return $cached;

        // Try to load the report from the reports table (gracefully degrade)
        $rows = [];
        $title = 'System Report';
        try {
            if ($this->db && $this->tableExists('reports')) {
                $stmt = $this->db->prepare("SELECT * FROM reports WHERE id = ? LIMIT 1");
                $stmt->execute([$reportId]);
                $report = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($report) {
                    $title = $report['title'] ?? $title;
                    if (!empty($report['data'])) {
                        $decoded = json_decode($report['data'], true);
                        if (is_array($decoded)) $rows = $decoded;
                    }
                }
            }
        } catch (\Throwable $e) {
        // Ignore - produce a placeholder
        error_log($e->getMessage());
        }

        $pdf = new MinimalPDF();
        $this->renderHeader($pdf, $title);

        $pdf->setFont('Helvetica', 'B', 10);
        $pdf->text(40, 110, 'Report ID:');
        $pdf->setFont('Helvetica', '', 10);
        $pdf->text(110, 110, 'RPT-' . str_pad((string)$reportId, 6, '0', STR_PAD_LEFT));
        $pdf->setFont('Helvetica', 'B', 10);
        $pdf->text(40, 126, 'Generated:');
        $pdf->setFont('Helvetica', '', 10);
        $pdf->text(110, 126, date('d-M-Y H:i:s'));

        if (empty($rows)) {
            $pdf->setFont('Helvetica', 'I', 11);
            $pdf->text(40, 170, 'No data available for this report.');
        } else {
            $y = 160;
            $headers = array_keys(reset($rows));
            $data = array_map(function ($r) { return array_values($r); }, $rows);
            $colWidth = (int)(515 / max(count($headers), 1));
            $widths = array_fill(0, count($headers), $colWidth);
            $this->renderTable($pdf, $y, $headers, $data, $widths);
        }

        $path = $this->writePdf($pdf, self::TYPE_REPORT, $reportId);
        return $this->returnResult($path, $reportId);
    }

    /* ------------------------------------------------------------------ */
    /*  5. Property Brochure                                              */
    /* ------------------------------------------------------------------ */

    public function brochure($propertyId)
    {
        $propertyId = (int)$propertyId;
        $property = $this->loadProperty($propertyId);
        if (!$property) return ['success' => false, 'error' => 'Property not found'];

        $cached = $this->checkCache(self::TYPE_BROCHURE, $propertyId);
        if ($cached) return $cached;

        $pdf = new MinimalPDF();
        $this->renderHeader($pdf, $property['title'] ?? 'Property Brochure');

        $pdf->setFont('Helvetica', 'B', 16);
        $pdf->text(40, 110, $property['title'] ?? 'Premium Property');
        $pdf->setFont('Helvetica', '', 11);
        $pdf->text(40, 134, 'Location: ' . ($property['location'] ?? 'Gorakhpur, UP'));

        $pdf->setFont('Helvetica', 'B', 13);
        $pdf->text(40, 180, 'Property Highlights');
        $pdf->hrule(192);

        $highlights = [
            'Type'            => $property['type'] ?? 'Residential Plot',
            'Area'            => ($property['area_sqft'] ?? '1200') . ' sq. ft.',
            'Price'           => 'Rs. ' . number_format((float)($property['price'] ?? 0), 2),
            'Status'          => ucfirst($property['status'] ?? 'available'),
            'Facing'          => $property['facing'] ?? 'East',
            'Road Width'      => ($property['road_width'] ?? '30') . ' ft.',
            'RERA Registered' => ($property['rera'] ?? 'YES'),
            'Possession'      => $property['possession'] ?? 'Ready to Move',
        ];

        $y = 210;
        foreach ($highlights as $k => $v) {
            $pdf->setFont('Helvetica', 'B', 10);
            $pdf->text(40, $y, $k . ':');
            $pdf->setFont('Helvetica', '', 10);
            $pdf->text(180, $y, $v);
            $y += 18;
        }

        $pdf->setFont('Helvetica', 'B', 13);
        $pdf->text(40, $y + 20, 'Amenities');
        $pdf->hrule($y + 32);
        $pdf->setFont('Helvetica', '', 10);
        $amenities = $property['amenities'] ?? 'Park, Security, Power Backup, Water Supply, Wide Roads, Street Lights, Drainage, Community Hall';
        $pdf->multiText(40, $y + 50, $amenities, 515, 1.5);

        $pdf->setFont('Helvetica', 'B', 13);
        $pdf->text(40, 720, 'Contact Us');
        $pdf->hrule(732);
        $pdf->setFont('Helvetica', '', 10);
        $pdf->text(40, 748, 'APS Dream Home Pvt. Ltd. | +91 92771 21112 | sales@apsdreamhome.com');
        $pdf->text(40, 762, 'www.apsdreamhome.com');

        $path = $this->writePdf($pdf, self::TYPE_BROCHURE, $propertyId);
        return $this->returnResult($path, $propertyId);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Render the standard PDF header (logo text, title, address).
     */
    protected function renderHeader(MinimalPDF $pdf, $title)
    {
        $pdf->setFillColor(40, 116, 166);
        $pdf->rect(0, 0, 595, 80, true);
        $pdf->setFont('Helvetica', 'B', 18);
        $pdf->setFillColor(255, 255, 255);
        $pdf->text(40, 50, 'APS DREAM HOME');
        $pdf->setFont('Helvetica', '', 10);
        $pdf->text(380, 36, $title);
        $pdf->setFont('Helvetica', '', 8);
        $pdf->text(380, 54, 'Generated: ' . date('d-M-Y H:i'));
        $pdf->setFont('Helvetica', '', 9);
        $pdf->setFillColor(0, 0, 0);
        $pdf->text(40, 70, 'Gorakhpur, UP | +91 92771 21112 | www.apsdreamhome.com');
    }

    /**
     * Render a key-value block.
     */
    protected function renderKvBlock(MinimalPDF $pdf, $startY, array $kv)
    {
        $y = $startY;
        foreach ($kv as $k => $v) {
            $pdf->setFont('Helvetica', 'B', 10);
            $pdf->text(40, $y, $k . ':');
            $pdf->setFont('Helvetica', '', 10);
            $pdf->text(160, $y, $v);
            $y += 18;
        }
    }

    /**
     * Render a simple table with header (gray) + rows.
     */
    protected function renderTable(MinimalPDF $pdf, $startY, array $headers, array $rows, array $colWidths)
    {
        $xCursor = 40;
        $rowH = 22;
        // Header
        $pdf->setFillColor(220, 220, 220);
        $pdf->rect(40, $startY - 14, array_sum($colWidths), $rowH, true);
        $pdf->setFillColor(0, 0, 0);
        $pdf->setFont('Helvetica', 'B', 10);
        foreach ($headers as $i => $h) {
            $pdf->text($xCursor + 4, $startY, $h);
            $xCursor += $colWidths[$i];
        }
        // Rows
        $y = $startY + $rowH;
        $pdf->setFont('Helvetica', '', 10);
        foreach ($rows as $row) {
            $xCursor = 40;
            $pdf->setDrawColor(200, 200, 200);
            $pdf->line(40, $y - 4, 40 + array_sum($colWidths), $y - 4);
            foreach ($row as $i => $cell) {
                $pdf->text($xCursor + 4, $y, (string)$cell);
                $xCursor += $colWidths[$i];
            }
            $y += $rowH;
        }
        $pdf->setDrawColor(0, 0, 0);
    }

    /**
     * Check if a PDF is already cached on disk.
     */
    protected function checkCache($type, $id, $subId = null)
    {
        $path = $this->pathFor($type, $id, $subId);
        if (is_file($path) && (time() - filemtime($path)) < 3600) {
            $this->stats['cache_hits']++;
            return [
                'success' => true,
                'data' => [
                    'path'         => $path,
                    'url'          => $this->urlFor($type, $id, $subId),
                    'bytes'        => filesize($path),
                    'generated_at' => date('c', filemtime($path)),
                    'cached'       => true,
                ],
            ];
        }
        $this->stats['cache_misses']++;
        return null;
    }

    /**
     * Write the PDF to disk.
     */
    protected function writePdf(MinimalPDF $pdf, $type, $id, $subId = null)
    {
        $path = $this->pathFor($type, $id, $subId);
        $dir = dirname($path);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $pdf->output($path, 'F');
        return $path;
    }

    /**
     * Build the file path for a PDF.
     */
    protected function pathFor($type, $id, $subId = null)
    {
        $hash = substr(md5(($subId ?? '') . '|' . $id . '|' . date('Y-m-d')), 0, 8);
        $name = $id . ($subId ? "_{$subId}" : '') . "_{$hash}.pdf";
        return $this->storagePath . '/' . $type . '/' . $name;
    }

    /**
     * Build the public download URL.
     */
    protected function urlFor($type, $id, $subId = null)
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        $id = $subId ? "{$id}-{$subId}" : $id;
        return $base . "/pdf/download/{$type}/{$id}";
    }

    /**
     * Build a success result envelope.
     */
    protected function returnResult($path, $id, $subId = null)
    {
        return [
            'success' => true,
            'data' => [
                'path'         => $path,
                'url'          => $this->urlFor($this->lastType, $id, $subId),
                'bytes'        => is_file($path) ? filesize($path) : 0,
                'generated_at' => date('c'),
                'cached'       => false,
            ],
        ];
    }

    /** @var string tracks the type during generation for urlFor() */
    protected $lastType = '';

    public function generateWithType($type, $id)
    {
        $this->lastType = $type;
        return $this->generate($type, $id);
    }

    /* ------------------------------------------------------------------ */
    /*  Data Loading                                                       */
    /* ------------------------------------------------------------------ */

    protected function loadBooking($bookingId)
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$bookingId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) return null;

            // Augment with user data if user_id is set
            if (!empty($row['user_id'])) {
                try {
                    $u = $this->db->prepare("SELECT name, email, phone FROM users WHERE id = ? LIMIT 1");
                    $u->execute([(int)$row['user_id']]);
                    if ($user = $u->fetch(\PDO::FETCH_ASSOC)) {
                        $row['customer_name']  = $user['name'] ?? '';
                        $row['customer_email'] = $user['email'] ?? '';
                        $row['customer_phone'] = $user['phone'] ?? '';
                    }
                } catch (\Throwable $e) { /* ignore */ error_log($e->getMessage()); }
            }
            // Augment with plot data if plot_id is set
            if (!empty($row['plot_id'])) {
                try {
                    $p = $this->db->prepare("SELECT title, plot_number, area_sqft FROM plots WHERE id = ? LIMIT 1");
                    $p->execute([(int)$row['plot_id']]);
                    if ($plot = $p->fetch(\PDO::FETCH_ASSOC)) {
                        $row['property_title'] = $plot['title'] ?? '';
                        $row['plot_number']    = $plot['plot_number'] ?? '';
                        $row['area_sqft']      = $plot['area_sqft'] ?? '';
                    }
                } catch (\Throwable $e) { /* ignore */ error_log($e->getMessage()); }
            }
            // Fallback labels for legacy bookings
            $row['customer_name']  = $row['customer_name']  ?? 'Customer #' . $bookingId;
            $row['customer_email'] = $row['customer_email'] ?? 'n/a';
            return $row;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function loadPaymentForBooking($bookingId)
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM payments
                WHERE booking_id = ? AND status IN ('paid', 'captured', 'success')
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([(int)$bookingId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function loadProperty($propertyId)
    {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.name AS colony_name, d.name AS district_name
                FROM plots p
                LEFT JOIN colonies c ON c.id = p.colony_id
                LEFT JOIN districts d ON d.id = c.district_id
                WHERE p.id = ?
                LIMIT 1
            ");
            $stmt->execute([(int)$propertyId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $row['title'] = $row['title'] ?? $row['colony_name'] . ' - Plot ' . ($row['plot_number'] ?? $propertyId);
                $row['type'] = $row['type'] ?? 'Residential Plot';
                $row['location'] = ($row['colony_name'] ?? '') . ', ' . ($row['district_name'] ?? 'Gorakhpur');
            }
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function tableExists($name)
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$name]);
            return (bool)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Logging + Stats                                                    */
    /* ------------------------------------------------------------------ */

    protected function logToDb($type, $id, $result)
    {
        if (!$this->db || !$this->tableExists('gateway_logs')) return;
        try {
            $stmt = $this->db->prepare("
                INSERT INTO gateway_logs
                  (gateway, action, recipient, status, request_payload, response_payload, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                'pdf',
                'generate_' . $type,
                'id:' . $id,
                'success',
                json_encode(['type' => $type, 'id' => $id]),
                json_encode($result['data'] ?? []),
            ]);
        } catch (\Throwable $e) {
        // best-effort
        error_log($e->getMessage());
        }
    }

    public function getStats()
    {
        return $this->stats + [
            'storage_path' => $this->storagePath,
            'is_writable'  => is_writable($this->storagePath),
        ];
    }

    /**
     * Get recent generated PDFs.
     */
    public function getRecent($limit = 20)
    {
        $limit = max(1, min(100, (int)$limit));
        $files = [];
        foreach (self::ALL_TYPES as $type) {
            $dir = $this->storagePath . '/' . $type;
            if (!is_dir($dir)) continue;
            $iter = new \DirectoryIterator($dir);
            foreach ($iter as $f) {
                if ($f->isDot() || $f->isDir()) continue;
                if (strtolower($f->getExtension()) !== 'pdf') continue;
                $files[] = [
                    'type'     => $type,
                    'filename' => $f->getFilename(),
                    'bytes'    => $f->getSize(),
                    'mtime'    => date('c', $f->getMTime()),
                ];
            }
        }
        usort($files, fn($a, $b) => strcmp($b['mtime'], $a['mtime']));
        return array_slice($files, 0, $limit);
    }

    /**
     * Resolve the PDO instance from the framework.
     */
    protected function resolveDb()
    {
        try {
            if (class_exists('\App\Core\Database', false) ||
                class_exists('\App\Core\Database\Database', false)) {
                $cls = class_exists('\App\Core\Database', false)
                    ? '\App\Core\Database'
                    : '\App\Core\Database\Database';
                $instance = $cls::getInstance();
                if (method_exists($instance, 'getConnection')) return $instance->getConnection();
                if (method_exists($instance, 'getPdo')) return $instance->getPdo();
                if (property_exists($instance, 'pdo')) return $instance->pdo;
            }
            if (class_exists('Database', false)) {
                $instance = \Database::getInstance();
                if (method_exists($instance, 'getConnection')) return $instance->getConnection();
            }
        } catch (\Throwable $e) {
        // fall through
        error_log($e->getMessage());
        }
        return null;
    }

    /**
     * Normalize an injected DB handle (PDO or Database wrapper) to a PDO instance.
     * If null/false, returns null (caller falls through to resolveDb()).
     */
    protected function normalizeDb($db)
    {
        if ($db === null || $db === false) return null;
        if ($db instanceof \PDO) return $db;
        if (is_object($db)) {
            if (method_exists($db, 'getConnection')) {
                $v = $db->getConnection();
                if ($v instanceof \PDO) return $v;
            }
            if (method_exists($db, 'getPdo')) {
                $v = $db->getPdo();
                if ($v instanceof \PDO) return $v;
            }
            if (property_exists($db, 'pdo')) {
                $v = $db->pdo;
                if ($v instanceof \PDO) return $v;
            }
        }
        return null;
    }
}
