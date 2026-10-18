<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;

/**
 * Associate WhatsApp Poster Generator
 *
 * 1-click branded colony posters (1080x1350, status-story size) with the
 * associate's name, phone and referral QR auto-stamped. GD-based, cached.
 */
class PosterController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    private function currentAssociate(): array
    {
        $db = \App\Core\Database\Database::getInstance();
        $tidSql = \App\Core\Middleware\TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = [$_SESSION['user_id']];
        if (\App\Core\Middleware\TenantContext::getId() > 1) $params[] = \App\Core\Middleware\TenantContext::getId();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?{$tidSql} LIMIT 1", $params);
        return $user ?: [];
    }

    /**
     * Poster studio page.
     * GET /associate/marketing/posters
     */
    public function index()
    {
        $this->requireAuth();
        $user = $this->currentAssociate();
        try {
            $db = \App\Core\Database\Database::getInstance();
            $colonies = $db->fetchAll(
                "SELECT id, name, location, starting_price, min_price_per_sqft, available_plots
                 FROM colonies WHERE is_active = 1 ORDER BY name LIMIT 100"
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('PosterController::index: ' . $e->getMessage());
            $colonies = [];
        }
        $themes = [
            'teal' => 'Teal Premium', 'navy' => 'Midnight Navy', 'maroon' => 'Royal Maroon',
        ];
        $this->render('associate/posters', [
            'page_title' => 'WhatsApp Poster Studio',
            'page_description' => 'Branded colony posters with your name & referral QR',
            'user' => $user,
            'colonies' => $colonies,
            'themes' => $themes,
            'referral_code' => $user['referral_code'] ?? ('APS' . str_pad($_SESSION['user_id'], 6, '0', STR_PAD_LEFT)),
        ], 'layouts/associate');
    }

    /**
     * Rendered poster PNG (cached 24h).
     * GET /associate/marketing/posters/image?colony_id=&theme=
     */
    public function image()
    {
        $this->requireAuth();
        $user = $this->currentAssociate();
        $colonyId = (int)($_GET['colony_id'] ?? 0);
        $theme = (string)($_GET['theme'] ?? 'teal');
        if (!in_array($theme, ['teal', 'navy', 'maroon'], true)) $theme = 'teal';
        if ($colonyId <= 0) { http_response_code(400); exit('colony_id required'); }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $colony = $db->fetchOne("SELECT * FROM colonies WHERE id = ? LIMIT 1", [$colonyId]);
        } catch (\Throwable $e) { $colony = null; }
        if (!$colony) { http_response_code(404); exit('Colony not found'); }

        $dir = APS_ROOT . '/storage/app/posters';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $file = $dir . "/poster_{$_SESSION['user_id']}_{$colonyId}_{$theme}.png";
        if (!is_file($file) || filemtime($file) < time() - 86400 || isset($_GET['fresh'])) {
            $this->renderPoster($file, $colony, $user, $theme);
        }
        if (!is_file($file)) { http_response_code(500); exit('Render failed'); }
        // Drop any stray buffered output (a single junk byte kills PNG validity)
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }

    /* ================= GD rendering ================= */

    private function font(bool $bold): string
    {
        $p = $bold ? 'C:\\Windows\\Fonts\\arialbd.ttf' : 'C:\\Windows\\Fonts\\arial.ttf';
        return is_file($p) ? $p : '';
    }

    private function text($im, string $s, int $x, int $y, int $size, int $color, bool $bold = false, bool $center = false)
    {
        $font = $this->font($bold);
        if ($font !== '') {
            if ($center) {
                $box = imagettfbbox($size, 0, $font, $s);
                $x = (int)($x - ($box[2] - $box[0]) / 2);
            }
            imagettftext($im, $size, 0, $x, $y, $color, $font, $s);
        } else {
            imagestring($im, 5, $x, $y - 12, mb_substr($s, 0, 40), $color);
        }
    }

    private function gradient($im, int $w, int $h, array $top, array $bottom)
    {
        for ($y = 0; $y < $h; $y++) {
            $t = $y / max(1, $h - 1);
            $c = imagecolorallocate($im,
                (int)($top[0] + ($bottom[0] - $top[0]) * $t),
                (int)($top[1] + ($bottom[1] - $top[1]) * $t),
                (int)($top[2] + ($bottom[2] - $top[2]) * $t));
            imageline($im, 0, $y, $w, $y, $c);
        }
    }

    private function renderPoster(string $file, array $colony, array $user, string $theme): void
    {
        $W = 1080; $H = 1350;
        $im = imagecreatetruecolor($W, $H);
        $palettes = [
            'teal' => [[13, 148, 136], [15, 23, 42]],
            'navy' => [[30, 58, 138], [2, 6, 23]],
            'maroon' => [[153, 27, 27], [28, 10, 10]],
        ];
        [$top, $bottom] = $palettes[$theme];
        $this->gradient($im, $W, $H, $top, $bottom);

        $white = imagecolorallocate($im, 255, 255, 255);
        $gold = imagecolorallocate($im, 251, 191, 36);
        $muted = imagecolorallocate($im, 226, 232, 240);
        $navy = imagecolorallocate($im, 15, 23, 42);
        $green = imagecolorallocate($im, 22, 163, 74);

        // Brand header
        $this->text($im, 'APS DREAM HOME', $W / 2, 95, 62, $white, true, true);
        $this->text($im, 'Premium Plots  •  Gorakhpur', $W / 2, 140, 30, $gold, false, true);
        imagefilledrectangle($im, 90, 162, $W - 90, 166, $gold);

        // Photo band (local colony image, else deco rings)
        $photoH = 480; $photoY = 195;
        $placed = false;
        foreach (['banner_image', 'image_path', 'layout_image'] as $k) {
            $rel = ltrim((string)($colony[$k] ?? ''), '/');
            if ($rel === '' || preg_match('#^https?://#i', $rel)) continue;
            $abs = APS_ROOT . '/public/' . $rel;
            if (!is_file($abs)) continue;
            $src = @imagecreatefromstring(@file_get_contents($abs));
            if (!$src) continue;
            $sw = imagesx($src); $sh = imagesy($src);
            $scale = max($W / $sw, $photoH / $sh);
            $dw = (int)($sw * $scale); $dh = (int)($sh * $scale);
            $tmp = imagecreatetruecolor($dw, $dh);
            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
            imagecopy($im, $tmp, 0, $photoY, (int)(($dw - $W) / 2), (int)(($dh - $photoH) / 2), $W, $photoH);
            imagedestroy($tmp); imagedestroy($src);
            $placed = true;
            break;
        }
        if (!$placed) {
            // decorative rings
            for ($i = 0; $i < 3; $i++) {
                $c = imagecolorallocatealpha($im, 251, 191, 36, 100);
                imageellipse($im, $W / 2, $photoY + $photoH / 2, 300 + $i * 180, 220 + $i * 130, $c);
            }
            $this->text($im, 'PRIME LOCATION', $W / 2, $photoY + $photoH / 2, 44, $gold, true, true);
        }
        // dark strip for text contrast
        $shade = imagecolorallocatealpha($im, 0, 0, 0, 70);
        imagefilledrectangle($im, 0, $photoY + $photoH - 150, $W, $photoY + $photoH, $shade);

        // Colony name + location
        $cname = mb_substr((string)($colony['name'] ?? 'Premium Colony'), 0, 34);
        $this->text($im, $cname, $W / 2, $photoY + $photoH - 88, 54, $white, true, true);
        $this->text($im, mb_substr((string)($colony['location'] ?? ''), 0, 48), $W / 2, $photoY + $photoH - 38, 30, $muted, false, true);

        // Rate + loan pills (pick the candidate that looks like a per-sqft rate;
        // colony data mixes per-sqft and lump-sum semantics across rows)
        $rate = 0.0;
        foreach ([(float)($colony['min_price_per_sqft'] ?? 0), (float)($colony['starting_price'] ?? 0)] as $cand) {
            if ($cand >= 100 && $cand < 100000) { $rate = $cand; break; }
        }
        $rateTxt = $rate > 0 ? 'Rs.' . number_format($rate) . '/sq.ft' : 'Best Price Guaranteed';
        imagefilledrectangle($im, 90, 745, $W - 90, 845, $gold);
        $this->text($im, $rateTxt, $W / 2, 812, 52, $navy, true, true);
        imagefilledrectangle($im, 220, 862, $W - 220, 922, $green);
        $this->text($im, 'Bank Loan Available', $W / 2, 903, 32, $white, true, true);
        $avail = (int)($colony['available_plots'] ?? 0);
        if ($avail > 0) $this->text($im, $avail . ' Plots Available  •  Easy EMI', $W / 2, 968, 30, $muted, false, true);

        // Associate card
        imagefilledrectangle($im, 40, 1010, $W - 40, $H - 40, $white);
        $this->text($im, 'YOUR TRUSTED ASSOCIATE', 80, 1062, 26, imagecolorallocate($im, 100, 116, 139), true, false);
        $this->text($im, mb_substr((string)($user['name'] ?? 'Associate'), 0, 26), 80, 1125, 50, $navy, true, false);
        $this->text($im, (string)($user['phone'] ?? ''), 80, 1180, 42, imagecolorallocate($im, 13, 148, 136), true, false);
        $ref = (string)($user['referral_code'] ?? ('APS' . str_pad($_SESSION['user_id'], 6, '0', STR_PAD_LEFT)));
        $this->text($im, 'Ref: ' . $ref, 80, 1232, 30, imagecolorallocate($im, 100, 116, 139), false, false);
        $this->text($im, 'Call / WhatsApp for site visit', 80, 1282, 28, imagecolorallocate($im, 100, 116, 139), false, false);

        // Referral QR (220px, right side of card)
        $qrUrl = BASE_URL . '/register?ref=' . urlencode($ref);
        $qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($qrUrl);
        $qrRaw = @file_get_contents($qrApi);
        $qr = $qrRaw ? @imagecreatefromstring($qrRaw) : null;
        if ($qr) {
            imagecopyresampled($im, $qr, $W - 300, 1040, 0, 0, 220, 220, imagesx($qr), imagesy($qr));
            imagedestroy($qr);
            $this->text($im, 'Scan to explore', $W - 190, 1290, 24, imagecolorallocate($im, 100, 116, 139), false, true);
        } else {
            imagefilledrectangle($im, $W - 300, 1040, $W - 80, 1260, imagecolorallocate($im, 241, 245, 249));
            $this->text($im, $ref, $W - 190, 1160, 28, $navy, true, true);
        }

        // Footer strip
        $this->text($im, 'apsdreamhome.com', $W / 2, $H - 8, 22, $muted, false, true);

        imagepng($im, $file);
        imagedestroy($im);
    }
}
