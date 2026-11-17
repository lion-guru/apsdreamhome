<?php

namespace App\Http\Controllers\Admin;

class PlotAILayoutController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    /** GET /admin/plots/import/ai-layout */
    public function index()
    {
        $this->requireAdmin();

        try {
            $colonies = $this->db->fetchAll('SELECT id, name FROM colonies ORDER BY name');
        } catch (\Throwable $e) {
            $colonies = [];
        }

        $preview = $_SESSION['ai_layout_preview'] ?? null;

        return $this->render('admin/plot-import/ai_layout', [
            'page_title' => 'AI Layout-to-Plot Creator',
            'colonies' => $colonies,
            'preview' => $preview,
        ]);
    }

    /** POST /admin/plots/import/ai-layout/extract */
    public function extract()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (!isset($_FILES['layout_file']) || $_FILES['layout_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please select a layout image (JPG/PNG/WEBP)');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $file = $_FILES['layout_file'];
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->setFlash('error', 'Image must be under 5 MB');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->setFlash('error', 'Only JPG, PNG or WEBP images are supported');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $colonyId = !empty($_POST['colony_id']) ? (int) $_POST['colony_id'] : null;
        if (!$colonyId) {
            $this->setFlash('error', 'Select a colony before extracting');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $colony = $this->db->fetchOne('SELECT id, name FROM colonies WHERE id = ?', [$colonyId]);
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $mime = $ext === 'png' ? 'image/png' : ($ext === 'webp' ? 'image/webp' : 'image/jpeg');
        $imageBase64 = base64_encode(file_get_contents($file['tmp_name']));

        $prompt = "You are analysing a real estate colony layout map image. Extract every residential plot visible in the image. "
            . "Return STRICT JSON in this exact shape: "
            . '{"plots":[{"plot_number":"A-001","width_ft":40,"length_ft":60,"sqft":2400,"facing":"East","block":"A"}],"notes":"short observation about the image"}'
            . " Rules: plot_number must come from the image; if you cannot read a plot number, OMIT it entirely; "
            . "facings use only North/South/East/West/North-East/etc.; omit a field you cannot see instead of guessing; "
            . "SQFT may be estimated from dimensions only when both length and width are readable. Output ONLY the JSON object.";

        try {
            $engine = \App\Services\AI\FreeAIEngines::getInstance();
            $result = $engine->generateWithImage($prompt, $imageBase64, $mime, [
                'system' => 'You are a precise vision assistant for Indian real estate colony layout maps. Output only valid JSON, never commentary.',
                'max_tokens' => 4000,
                'temperature' => 0.1,
            ]);
        } catch (\Throwable $e) {
            error_log('PlotAILayoutController::extract AI error: ' . $e->getMessage());
            $this->setFlash('error', 'AI extraction failed: ' . $e->getMessage());
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $engineName = (string) ($result['engine'] ?? 'none');
        if ($engineName === 'none') {
            $this->setFlash('error', 'AI Vision is not configured. Add a Gemini API key in /admin/settings to enable layout extraction.');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $parsed = $this->parseStrictJson((string) ($result['text'] ?? ''));
        if (!$parsed || !isset($parsed['plots']) || !is_array($parsed['plots']) || count($parsed['plots']) === 0) {
            $raw = substr((string) ($result['text'] ?? ''), 0, 120);
            error_log('PlotAILayoutController::extract parse miss. Engine=' . $engineName . ' raw=' . $raw);
            $this->setFlash('warning', 'AI could not find readable plots in the image. Try a sharper, higher-resolution layout map.');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $plots = [];
        foreach ($parsed['plots'] as $p) {
            if (!is_array($p) || empty($p['plot_number'])) {
                continue; // skip unreadable plots — never invent numbers
            }
            $plots[] = [
                'plot_number' => trim((string) $p['plot_number']),
                'block' => isset($p['block']) ? trim((string) $p['block']) : null,
                'width_ft' => $this->floatOrNull($p['width_ft'] ?? null),
                'length_ft' => $this->floatOrNull($p['length_ft'] ?? null),
                'sqft' => $this->floatOrNull($p['sqft'] ?? $p['area_sqft'] ?? null),
                'facing' => isset($p['facing']) ? trim((string) $p['facing']) : null,
            ];
        }

        $_SESSION['ai_layout_preview'] = [
            'colony_id' => $colonyId,
            'colony_name' => $colony['name'],
            'plots' => $plots,
            'engine' => $engineName,
            'notes' => $parsed['notes'] ?? null,
            'extracted_at' => date('Y-m-d H:i:s'),
        ];

        $this->setFlash('success', count($plots) . ' plots extracted by AI (' . $engineName . '). Review below, select the ones to keep, then Create Plots.');
        return $this->redirect('/admin/plots/import/ai-layout');
    }

    /** POST /admin/plots/import/ai-layout/commit */
    public function commit()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $preview = $_SESSION['ai_layout_preview'] ?? null;
        if (!$preview || empty($preview['colony_id']) || empty($preview['plots'])) {
            $this->redirect('/admin/plots/import/ai-layout');
        }

        $colonyId = (int) $preview['colony_id'];
        $colony = $this->db->fetchOne('SELECT id FROM colonies WHERE id = ?', [$colonyId]);
        if (!$colony) {
            unset($_SESSION['ai_layout_preview']);
            $this->setFlash('error', 'Colony not found');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $selected = $_POST['selected'] ?? [];
        if (!is_array($selected) || count($selected) === 0) {
            $this->setFlash('error', 'No plots selected');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $toInsert = [];
        foreach ($preview['plots'] as $i => $plot) {
            if (!in_array((string) $i, array_map('strval', $selected), true)) {
                continue;
            }
            $number = trim((string) ($plot['plot_number'] ?? ''));
            if ($number === '') {
                continue;
            }
            $toInsert[] = [$number, $plot];
        }

        if (empty($toInsert)) {
            $this->setFlash('error', 'No valid plots selected');
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        $created = 0;
        $skipped = 0;

        $this->db->beginTransaction();
        try {
            foreach ($toInsert as [$number, $p]) {
                $existing = $this->db->fetchOne(
                    'SELECT id FROM plots WHERE tenant_id = 1 AND colony_id = ? AND plot_number = ?',
                    [$colonyId, $number]
                );
                if ($existing) {
                    $skipped++;
                    continue;
                }

                $area = $p['sqft'] ?? (($p['width_ft'] && $p['length_ft']) ? $p['width_ft'] * $p['length_ft'] : 0);

                $this->db->query(
                    'INSERT INTO plots (tenant_id, colony_id, plot_number, block, area_sqft, width_ft, length_ft, facing, status, price_per_sqft, plc_amount, final_price_per_sqft, plot_type, created_at, updated_at)
                     VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, ?, NOW(), NOW())',
                    [
                        $colonyId,
                        $number,
                        $p['block'] ?? null,
                        $area,
                        $p['width_ft'] ?? 0,
                        $p['length_ft'] ?? 0,
                        $p['facing'] ?? null,
                        'available',
                        'residential',
                    ]
                );
                $created++;
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('PlotAILayoutController::commit error: ' . $e->getMessage());
            $this->setFlash('error', 'Create failed: ' . $e->getMessage());
            return $this->redirect('/admin/plots/import/ai-layout');
        }

        unset($_SESSION['ai_layout_preview']);
        $this->setFlash('success', $created . ' plots created' . ($skipped ? ', ' . $skipped . ' duplicates skipped (same plot number already exists)' : '') . '.');
        return $this->redirect('/admin/plots/import/ai-layout');
    }

    /** @return array<string, mixed>|null */
    private function parseStrictJson(string $text): ?array
    {
        $text = trim($text);
        // Strip common markdown fences
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $text, $m)) {
            $text = trim($m[1]);
        }
        // Locate first top-level { ... } block
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }
        $depth = 0;
        for ($i = $start, $len = strlen($text); $i < $len; $i++) {
            $ch = $text[$i];
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    $json = substr($text, $start, $i - $start + 1);
                    $decoded = json_decode($json, true);
                    return is_array($decoded) ? $decoded : null;
                }
            }
        }
        return null;
    }

    private function floatOrNull($value): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }
        $f = (float) $value;
        if (!is_finite($f) || $f < 0 || $f > 1000000) {
            return null;
        }
        return $f;
    }
}
