<?php
namespace App\Services;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

/**
 * MarketingToolkitService — photo watermark, banner generator, AI content writer.
 *
 * Uses GD library (already available) for image manipulation.
 * Uses AIGateway (Ollama local LLM, free) for Hindi post generation.
 *
 * WhatsApp/Instagram note:
 * - Direct auto-posting needs paid APIs (WhatsApp Business API costs money,
 *   Instagram API needs Meta approval taking weeks). NOT implemented.
 * - Instead: generate branded images + wa.me click-to-share links (free, works now).
 * - User downloads image → manually posts to Instagram/FB status → zero cost.
 */
class MarketingToolkitService
{
    use ServiceTenantTrait;

    private $db;
    private $pdo;

    // Brand colors (matches catalog dark theme)
    private const BRAND_GOLD = [250, 204, 21];   // #FACC15
    private const BRAND_DARK = [7, 12, 24];       // #070C18
    private const WHITE = [255, 255, 255];

    public function __construct($db = null)
    {
        $this->db = $db ?? Database::getInstance();
        if (is_object($this->db) && method_exists($this->db, "getPdo")) {
            $this->pdo = $this->db->getPdo();
        } elseif ($this->db instanceof \PDO) {
            $this->pdo = $this->db;
        } else {
            $this->pdo = $this->db;
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

    /**
     * Add company logo watermark to a photo (bottom-right corner).
     *
     * @param string $sourcePath Absolute path to source image
     * @param string|null $logoPath Absolute path to logo (default: assets/images/logo.png)
     * @param int $opacity 0-100 (default 80)
     * @param string $position bottom-right|bottom-left|top-right|top-left|center
     * @return array ['success' => bool, 'path' => string, 'url' => string, 'message' => string]
     */
    public function addWatermark(string $sourcePath, ?string $logoPath = null, int $opacity = 80, string $position = 'bottom-right'): array
    {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'message' => 'Source image not found'];
        }

        $logoPath = $logoPath ?? (defined('APS_ROOT') ? APS_ROOT . '/assets/images/logo.png' : __DIR__ . '/../../assets/images/logo.png');
        if (!file_exists($logoPath)) {
            return ['success' => false, 'message' => 'Logo file not found'];
        }

        if (!function_exists('imagecreatefromjpeg')) {
            return ['success' => false, 'message' => 'GD library not available'];
        }

        try {
            $source = $this->loadImage($sourcePath);
            if (!$source) {
                return ['success' => false, 'message' => 'Unsupported image format'];
            }

            $logo = $this->loadImage($logoPath);
            if (!$logo) {
                imagedestroy($source);
                return ['success' => false, 'message' => 'Unsupported logo format'];
            }

            $srcW = imagesx($source);
            $srcH = imagesy($source);
            $logoW = imagesx($logo);
            $logoH = imagesy($logo);

            // Scale logo to ~12% of source width
            $targetLogoW = (int)($srcW * 0.12);
            $targetLogoH = (int)($logoH * ($targetLogoW / $logoW));

            // Create scaled logo
            $scaledLogo = imagecreatetruecolor($targetLogoW, $targetLogoH);
            imagealphablending($scaledLogo, false);
            imagesavealpha($scaledLogo, true);
            $transparent = imagecolorallocatealpha($scaledLogo, 0, 0, 0, 127);
            imagefill($scaledLogo, 0, 0, $transparent);
            imagecopyresampled($scaledLogo, $logo, 0, 0, 0, 0, $targetLogoW, $targetLogoH, $logoW, $logoH);

            // Calculate position with 20px margin
            $margin = 20;
            [$dstX, $dstY] = match($position) {
                'bottom-left' => [$margin, $srcH - $targetLogoH - $margin],
                'top-right' => [$srcW - $targetLogoW - $margin, $margin],
                'top-left' => [$margin, $margin],
                'center' => [(int)(($srcW - $targetLogoW) / 2), (int)(($srcH - $targetLogoH) / 2)],
                default => [$srcW - $targetLogoW - $margin, $srcH - $targetLogoH - $margin],
            };

            // Merge with opacity
            imagecopymerge($source, $scaledLogo, $dstX, $dstY, 0, 0, $targetLogoW, $targetLogoH, $opacity);

            // Save output
            $outputDir = $this->getOutputDir();
            $filename = 'wm_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $outputPath = $outputDir . '/' . $filename;

            imagejpeg($source, $outputPath, 90);
            imagedestroy($source);
            imagedestroy($logo);
            imagedestroy($scaledLogo);

            // Log usage
            $this->logToolkitUsage('watermark', $outputPath);

            return [
                'success' => true,
                'path' => $outputPath,
                'url' => $this->pathToUrl($outputPath),
                'filename' => $filename,
                'message' => 'Watermark added successfully',
            ];

        } catch (\Throwable $e) {
            error_log("MarketingToolkitService::addWatermark: " . $e->getMessage());
            return ['success' => false, 'message' => 'Watermark failed: ' . $e->getMessage()];
        }
    }

    /**
     * Generate branded price banner (WhatsApp status size: 1080x1920).
     *
     * @param string $sourcePath Property photo
     * @param array $data {price, location, offer, phone, property_type}
     * @return array
     */
    public function generateBanner(string $sourcePath, array $data): array
    {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'message' => 'Source image not found'];
        }

        if (!function_exists('imagecreatefromjpeg')) {
            return ['success' => false, 'message' => 'GD library not available'];
        }

        try {
            // Canvas: 1080x1920 (WhatsApp status / Instagram story size)
            $canvasW = 1080;
            $canvasH = 1920;
            $canvas = imagecreatetruecolor($canvasW, $canvasH);

            // Load and fit source photo (top 60% of canvas)
            $photo = $this->loadImage($sourcePath);
            if (!$photo) {
                imagedestroy($canvas);
                return ['success' => false, 'message' => 'Unsupported image format'];
            }

            $photoAreaH = (int)($canvasH * 0.62);
            $this->fillAreaWithImage($canvas, $photo, 0, 0, $canvasW, $photoAreaH);
            imagedestroy($photo);

            // Bottom panel: dark background
            $dark = imagecolorallocate($canvas, ...self::BRAND_DARK);
            imagefilledrectangle($canvas, 0, $photoAreaH, $canvasW, $canvasH, $dark);

            // Gold accent line
            $gold = imagecolorallocate($canvas, ...self::BRAND_GOLD);
            imagefilledrectangle($canvas, 0, $photoAreaH, $canvasW, $photoAreaH + 8, $gold);

            $white = imagecolorallocate($canvas, ...self::WHITE);
            $goldText = imagecolorallocate($canvas, ...self::BRAND_GOLD);

            $y = $photoAreaH + 60;

            // Company name
            $this->drawText($canvas, 'APS DREAM HOMES', 5, 60, $y, $goldText);
            $y += 70;

            // Price (large)
            $price = $data['price'] ?? '';
            if ($price) {
                $this->drawText($canvas, $price, 5, 60, $y, $white);
                $y += 100;
            }

            // Location
            $location = $data['location'] ?? '';
            if ($location) {
                $this->drawText($canvas, $location, 4, 60, $y, $white);
                $y += 70;
            }

            // Offer badge
            $offer = $data['offer'] ?? '';
            if ($offer) {
                // Draw offer background
                $offerBg = imagecolorallocate($canvas, 16, 185, 129); // emerald
                $offerText = mb_substr($offer, 0, 60);
                imagefilledrectangle($canvas, 60, $y, $canvasW - 60, $y + 70, $offerBg);
                $this->drawText($canvas, $offerText, 4, 80, $y + 15, $white);
                $y += 100;
            }

            // Property type
            $ptype = $data['property_type'] ?? '';
            if ($ptype) {
                $this->drawText($canvas, $ptype, 3, 60, $y, $white);
                $y += 60;
            }

            // Phone at bottom
            $phone = $data['phone'] ?? '+91 73092 68077';
            $this->drawText($canvas, 'Call/WhatsApp: ' . $phone, 4, 60, $canvasH - 120, $goldText);

            // Watermark logo (small, bottom-right)
            $logoPath = defined('APS_ROOT') ? APS_ROOT . '/assets/images/logo.png' : __DIR__ . '/../../assets/images/logo.png';
            if (file_exists($logoPath)) {
                $logo = $this->loadImage($logoPath);
                if ($logo) {
                    $lw = imagesx($logo);
                    $lh = imagesy($logo);
                    $tw = 120;
                    $th = (int)($lh * ($tw / $lw));
                    $scaled = imagecreatetruecolor($tw, $th);
                    imagealphablending($scaled, false);
                    imagesavealpha($scaled, true);
                    $trans = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
                    imagefill($scaled, 0, 0, $trans);
                    imagecopyresampled($scaled, $logo, 0, 0, 0, 0, $tw, $th, $lw, $lh);
                    imagecopymerge($canvas, $scaled, $canvasW - $tw - 30, $canvasH - $th - 30, 0, 0, $tw, $th, 85);
                    imagedestroy($logo);
                    imagedestroy($scaled);
                }
            }

            // Save
            $outputDir = $this->getOutputDir();
            $filename = 'banner_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $outputPath = $outputDir . '/' . $filename;
            imagejpeg($canvas, $outputPath, 88);
            imagedestroy($canvas);

            $this->logToolkitUsage('banner', $outputPath);

            return [
                'success' => true,
                'path' => $outputPath,
                'url' => $this->pathToUrl($outputPath),
                'filename' => $filename,
                'size' => '1080x1920 (WhatsApp Status / Instagram Story)',
                'message' => 'Banner generated successfully',
            ];

        } catch (\Throwable $e) {
            error_log("MarketingToolkitService::generateBanner: " . $e->getMessage());
            return ['success' => false, 'message' => 'Banner failed: ' . $e->getMessage()];
        }
    }

    /**
     * AI Content Writer — photo context + price → Hindi marketing post.
     * Uses AIGateway (Ollama local LLM, free). Falls back to template if AI unavailable.
     *
     * @param array $data {property_type, price, location, offer, size_sqft}
     * @return array ['success' => bool, 'headline' => string, 'body' => string, 'hashtags' => string, 'ai_generated' => bool]
     */
    public function generatePostContent(array $data): array
    {
        $ptype = $data['property_type'] ?? 'प्लॉट';
        $price = $data['price'] ?? '';
        $location = $data['location'] ?? 'गोरखपुर';
        $offer = $data['offer'] ?? '';
        $size = $data['size_sqft'] ?? '';

        // Try AI first
        try {
            $gateway = \App\Services\AI\AIGateway::getInstance();
            $prompt = "APS Dream Home ke liye ek chhota Hindi marketing post likho. "
                . "Details: {$ptype}, Price: {$price}, Location: {$location}, "
                . ($offer ? "Offer: {$offer}, " : "")
                . ($size ? "Size: {$size} sq.ft., " : "")
                . "Format: pehle 1 catchy headline (emoji ke saath), phir 2-3 line description, "
                . "phir 5 hashtags. Total 280 characters se kam. Friendly tone, Hindi+English mix.";
            $res = $gateway->process('chat', ['message' => $prompt], ['user_role' => 'associate']);

            $out = trim($res['response'] ?? '');
            if (!empty($out) && mb_strlen($out) > 20 && mb_strlen($out) < 2000) {
                // Split AI output into headline/body/hashtags (best effort)
                $lines = preg_split('/\r?\n/', $out);
                $headline = trim(array_shift($lines) ?? '');
                $rest = trim(implode("\n", $lines));

                // Extract hashtags (last line with #)
                $hashtags = '';
                if (preg_match_all('/#\S+/', $rest, $m)) {
                    $hashtags = implode(' ', array_unique($m[0]));
                }

                return [
                    'success' => true,
                    'headline' => $headline ?: "🏡 {$location} में {$ptype}!",
                    'body' => $rest ?: $out,
                    'hashtags' => $hashtags ?: '#APSDreamHome #GorakhpurPlots #RealEstate',
                    'ai_generated' => true,
                ];
            }
        } catch (\Throwable $e) {
            error_log("MarketingToolkitService::generatePostContent AI failed: " . $e->getMessage());
        }

        // Fallback: template-based (always works, no AI needed)
        $headline = "🏡 {$location} में {$ptype}!" . ($price ? " मात्र {$price}" : "");
        $body = "APS Dream Home lekar aaya hai {$location} me shandaar {$ptype}!"
            . ($size ? "\n📐 Size: {$size} sq.ft." : "")
            . ($price ? "\n💰 Price: {$price}" : "")
            . ($offer ? "\n🎁 Offer: {$offer}" : "")
            . "\n\n📞 Site visit book karein: +91 73092 68077"
            . "\n✅ 0% ब्याज EMI | ✅ तुरंत रजिस्ट्री";

        return [
            'success' => true,
            'headline' => $headline,
            'body' => $body,
            'hashtags' => '#APSDreamHome #GorakhpurPlots #RealEstate #PlotForSale #Investment',
            'ai_generated' => false,
        ];
    }

    /**
     * Generate WhatsApp click-to-share link (free, no API needed).
     */
    public function getWhatsAppShareLink(string $message, string $phone = ''): string
    {
        $base = 'https://wa.me/';
        if ($phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            return $base . $phone . '?text=' . urlencode($message);
        }
        return $base . '?text=' . urlencode($message);
    }

    /**
     * Get toolkit usage stats for a user.
     */
    public function getUsageStats(int $userId, int $days = 30): array
    {
        try {
            $tid = $this->getTenantId();
            $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId, $days];
            if ($tid > 1) $params[] = $tid;

            $stmt = $this->pdo->prepare("
                SELECT tool_type, COUNT(*) as count, MAX(created_at) as last_used
                FROM marketing_toolkit_usage
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY){$tenantWhere}
                GROUP BY tool_type
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* ── V2: Personal Branding ── */

    /**
     * Save personal branding (name/phone/photo). Applied auto to all outputs.
     */
    public function saveBranding(int $userId, array $data): array
    {
        $tid = $this->getTenantId();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_branding (user_id, display_name, phone, photo_path, tagline, tenant_id, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), phone = VALUES(phone),
                    photo_path = VALUES(photo_path), tagline = VALUES(tagline), updated_at = NOW()
            ");
            $stmt->execute([
                $userId,
                mb_substr(trim($data['display_name'] ?? ''), 0, 100),
                mb_substr(preg_replace('/[^0-9+\- ]/', '', $data['phone'] ?? ''), 0, 20),
                mb_substr(trim($data['photo_path'] ?? ''), 0, 500),
                mb_substr(trim($data['tagline'] ?? ''), 0, 200),
                $tid,
            ]);
            return ['success' => true, 'message' => 'Branding saved'];
        } catch (\Throwable $e) {
            // Table may not exist — try creating it
            try {
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS `user_branding` (
                        `user_id` BIGINT UNSIGNED NOT NULL,
                        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                        `display_name` VARCHAR(100) NULL,
                        `phone` VARCHAR(20) NULL,
                        `photo_path` VARCHAR(500) NULL,
                        `tagline` VARCHAR(200) NULL,
                        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`user_id`),
                        KEY `idx_tenant` (`tenant_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $stmt = $this->pdo->prepare("
                    INSERT INTO user_branding (user_id, display_name, phone, photo_path, tagline, tenant_id, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), phone = VALUES(phone),
                        photo_path = VALUES(photo_path), tagline = VALUES(tagline), updated_at = NOW()
                ");
                $stmt->execute([$userId, mb_substr(trim($data['display_name'] ?? ''), 0, 100), mb_substr(trim($data['phone'] ?? ''), 0, 20), mb_substr(trim($data['photo_path'] ?? ''), 0, 500), mb_substr(trim($data['tagline'] ?? ''), 0, 200), $tid]);
                return ['success' => true, 'message' => 'Branding saved'];
            } catch (\Throwable $e2) {
                error_log("MarketingToolkit::saveBranding: " . $e2->getMessage());
                return ['success' => false, 'message' => 'Failed to save branding'];
            }
        }
    }

    /**
     * Get personal branding for a user (falls back to users table).
     */
    public function getBranding(int $userId): array
    {
        $tid = $this->getTenantId();
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM user_branding WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) return $row;
        } catch (\Throwable $e) {
            // table missing — fall through
        }
        // Fallback to users table
        try {
            $stmt = $this->pdo->prepare("SELECT name as display_name, phone FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                return ['user_id' => $userId, 'display_name' => $row['display_name'] ?? '', 'phone' => $row['phone'] ?? '', 'photo_path' => '', 'tagline' => ''];
            }
        } catch (\Throwable $e) {
            // ignore
        }
        return ['user_id' => $userId, 'display_name' => '', 'phone' => '', 'photo_path' => '', 'tagline' => ''];
    }

    /* ── V2: Stickers / Badges ── */

    /**
     * Add sticker badge (NEW LAUNCH / PRICE DROP / SOLD / OFFER / URGENT) to photo.
     */
    public function addSticker(string $sourcePath, string $stickerType, string $customText = ''): array
    {
        if (!file_exists($sourcePath)) {
            return ['success' => false, 'message' => 'Source image not found'];
        }

        $stickers = [
            'new_launch' => ['text' => 'NEW LAUNCH', 'bg' => [16, 185, 129]],
            'price_drop' => ['text' => 'PRICE DROP', 'bg' => [244, 63, 94]],
            'sold' => ['text' => 'SOLD OUT', 'bg' => [100, 116, 139]],
            'offer' => ['text' => $customText ?: 'SPECIAL OFFER', 'bg' => [245, 158, 11]],
            'urgent' => ['text' => 'URGENT SALE', 'bg' => [220, 38, 38]],
            'verified' => ['text' => 'VERIFIED', 'bg' => [37, 99, 235]],
        ];

        $sticker = $stickers[$stickerType] ?? $stickers['offer'];

        try {
            $img = $this->loadImage($sourcePath);
            if (!$img) return ['success' => false, 'message' => 'Unsupported image format'];

            $w = imagesx($img);
            $h = imagesy($img);

            // Ribbon banner across top
            $bg = imagecolorallocate($img, ...$sticker['bg']);
            $white = imagecolorallocate($img, 255, 255, 255);
            $ribbonH = (int)($h * 0.09);
            imagefilledrectangle($img, 0, 0, $w, $ribbonH, $bg);

            // Centered text (font 5, approx centering)
            $text = mb_substr($sticker['text'], 0, 40);
            $fontW = imagefontwidth(5);
            $tx = (int)(($w - strlen($text) * $fontW) / 2);
            $ty = (int)(($ribbonH - imagefontheight(5)) / 2);
            imagestring($img, 5, max(10, $tx), max(5, $ty), $text, $white);

            $outputDir = $this->getOutputDir();
            $filename = 'sticker_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $outputPath = $outputDir . '/' . $filename;
            imagejpeg($img, $outputPath, 90);
            imagedestroy($img);

            $this->logToolkitUsage('sticker', $outputPath);

            return ['success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath), 'filename' => $filename, 'message' => 'Sticker added'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::addSticker: " . $e->getMessage());
            return ['success' => false, 'message' => 'Sticker failed'];
        }
    }

    /* ── V2: Collage Maker ── */

    /**
     * Combine 2-4 photos into one branded collage.
     * @param array $paths Absolute paths (2-4 images)
     * @param string $layout 'grid2x2'|'row3'|'row2'
     */
    public function makeCollage(array $paths, string $layout = 'grid2x2', array $branding = []): array
    {
        $paths = array_values(array_filter($paths, 'file_exists'));
        $paths = array_slice($paths, 0, 4);
        if (count($paths) < 2) {
            return ['success' => false, 'message' => 'Need at least 2 photos'];
        }

        try {
            $cellW = 540;
            $cellH = 540;
            $footerH = 140;

            if ($layout === 'row3' || count($paths) === 3) {
                $canvasW = $cellW * 3;
                $canvasH = $cellH + $footerH;
                $cols = 3;
            } elseif ($layout === 'row2' || count($paths) === 2) {
                $canvasW = $cellW * 2;
                $canvasH = $cellH + $footerH;
                $cols = 2;
            } else {
                $canvasW = $cellW * 2;
                $canvasH = $cellH * 2 + $footerH;
                $cols = 2;
            }

            $canvas = imagecreatetruecolor($canvasW, $canvasH);
            $dark = imagecolorallocate($canvas, ...self::BRAND_DARK);
            imagefill($canvas, 0, 0, $dark);

            foreach ($paths as $i => $p) {
                $photo = $this->loadImage($p);
                if (!$photo) continue;
                $cx = ($i % $cols) * $cellW;
                $cy = (int)($i / $cols) * $cellH;
                $this->fillAreaWithImage($canvas, $photo, $cx, $cy, $cellW, $cellH);
                imagedestroy($photo);
                // White divider
                $white = imagecolorallocate($canvas, 255, 255, 255);
                imagerectangle($canvas, $cx, $cy, $cx + $cellW - 1, $cy + $cellH - 1, $white);
            }

            // Footer with branding
            $fy = $canvasH - $footerH;
            $gold = imagecolorallocate($canvas, ...self::BRAND_GOLD);
            imagefilledrectangle($canvas, 0, $fy, $canvasW, $fy + 6, $gold);
            $white = imagecolorallocate($canvas, ...self::WHITE);
            $name = mb_substr($branding['display_name'] ?? 'APS Dream Home', 0, 50);
            $phone = mb_substr($branding['phone'] ?? '+91 73092 68077', 0, 25);
            $this->drawText($canvas, $name . '  |  ' . $phone, 5, 30, $fy + 40, $gold);
            $this->drawText($canvas, 'APS Dream Homes Pvt. Ltd., Gorakhpur', 3, 30, $fy + 80, $white);

            $outputDir = $this->getOutputDir();
            $filename = 'collage_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $outputPath = $outputDir . '/' . $filename;
            imagejpeg($canvas, $outputPath, 88);
            imagedestroy($canvas);

            $this->logToolkitUsage('collage', $outputPath);

            return ['success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath), 'filename' => $filename, 'whatsapp_share' => $this->getWhatsAppShareLink("Check these properties from APS Dream Home! {$phone}"), 'message' => 'Collage created'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::makeCollage: " . $e->getMessage());
            return ['success' => false, 'message' => 'Collage failed'];
        }
    }

    /* ── V2: Festival / Greeting Posts ── */

    /**
     * Generate festival greeting post (1080x1080 square).
     */
    public function generateFestivalPost(string $festival, array $branding = []): array
    {
        $festivals = [
            'diwali' => ['title' => 'Happy Diwali', 'subtitle' => 'शुभ दीपावली', 'bg' => [60, 20, 0], 'accent' => [250, 204, 21], 'emoji' => '🪔'],
            'holi' => ['title' => 'Happy Holi', 'subtitle' => 'रंगों का त्योहार', 'bg' => [80, 10, 60], 'accent' => [244, 114, 182], 'emoji' => '🎨'],
            'newyear' => ['title' => 'Happy New Year', 'subtitle' => 'नव वर्ष की शुभकामनाएं', 'bg' => [7, 12, 40], 'accent' => [96, 165, 250], 'emoji' => '🎉'],
            'dussehra' => ['title' => 'Happy Dussehra', 'subtitle' => 'विजयादशमी की शुभकामनाएं', 'bg' => [50, 10, 10], 'accent' => [251, 146, 60], 'emoji' => '🏹'],
            'eid' => ['title' => 'Eid Mubarak', 'subtitle' => 'ईद मुबारक', 'bg' => [5, 50, 30], 'accent' => [52, 211, 153], 'emoji' => '🌙'],
            'independence' => ['title' => 'Happy Independence Day', 'subtitle' => 'स्वतंत्रता दिवस', 'bg' => [10, 30, 60], 'accent' => [250, 204, 21], 'emoji' => '🇮🇳'],
        ];

        $f = $festivals[$festival] ?? $festivals['diwali'];

        try {
            $W = 1080;
            $H = 1080;
            $canvas = imagecreatetruecolor($W, $H);
            $bg = imagecolorallocate($canvas, ...$f['bg']);
            imagefill($canvas, 0, 0, $bg);

            // Decorative border
            $accent = imagecolorallocate($canvas, ...$f['accent']);
            imagerectangle($canvas, 20, 20, $W - 21, $H - 21, $accent);
            imagerectangle($canvas, 30, 30, $W - 31, $H - 31, $accent);

            $white = imagecolorallocate($canvas, ...self::WHITE);
            $gold = imagecolorallocate($canvas, ...self::BRAND_GOLD);

            // Company header
            $this->drawCentered($canvas, 'APS DREAM HOMES', 5, 200, $gold, $W);
            // Festival title
            $this->drawCentered($canvas, $f['title'], 5, 420, $white, $W);
            $this->drawCentered($canvas, $f['subtitle'], 4, 500, $accent, $W);
            // Greeting message
            $this->drawCentered($canvas, 'Wishing you prosperity', 3, 620, $white, $W);
            $this->drawCentered($canvas, 'and your dream home!', 3, 660, $white, $W);

            // Associate branding at bottom
            $name = mb_substr($branding['display_name'] ?? '', 0, 50);
            $phone = mb_substr($branding['phone'] ?? '', 0, 25);
            if ($name || $phone) {
                $this->drawCentered($canvas, trim($name . '  |  ' . $phone, ' |'), 4, 850, $gold, $W);
            }
            $this->drawCentered($canvas, '+91 73092 68077 | Gorakhpur', 3, 920, $white, $W);

            $outputDir = $this->getOutputDir();
            $filename = 'festival_' . $festival . '_' . time() . '.jpg';
            $outputPath = $outputDir . '/' . $filename;
            imagejpeg($canvas, $outputPath, 88);
            imagedestroy($canvas);

            $this->logToolkitUsage('festival', $outputPath);

            return ['success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath), 'filename' => $filename, 'whatsapp_share' => $this->getWhatsAppShareLink("{$f['title']} - {$f['subtitle']}! APS Dream Home, Gorakhpur"), 'message' => 'Festival post created'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::festival: " . $e->getMessage());
            return ['success' => false, 'message' => 'Festival post failed'];
        }
    }

    /* ── V2: QR Code ── */

    /**
     * Generate QR code PNG with referral link using bacon matrix + GD.
     */
    public function generateQR(string $text, int $size = 300): array
    {
        try {
            if (!class_exists('BaconQrCode\Encoder\Encoder')) {
                if (file_exists((defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 2)) . '/vendor/autoload.php')) {
                    require_once (defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 2)) . '/vendor/autoload.php';
                }
            }

            $qr = \BaconQrCode\Encoder\Encoder::encode($text, \BaconQrCode\Common\ErrorCorrectionLevel::M());
            $matrix = $qr->getMatrix();
            $modules = $matrix->getWidth();

            $scale = max(2, (int)($size / ($modules + 8)));
            $margin = 4;
            $imgSize = ($modules + $margin * 2) * $scale;
            $img = imagecreatetruecolor($imgSize, $imgSize);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefill($img, 0, 0, $white);
            for ($y = 0; $y < $modules; $y++) {
                for ($x = 0; $x < $modules; $x++) {
                    if ($matrix->get($x, $y) === 1) {
                        imagefilledrectangle($img, ($x + $margin) * $scale, ($y + $margin) * $scale, ($x + $margin + 1) * $scale - 1, ($y + $margin + 1) * $scale - 1, $black);
                    }
                }
            }

            $outputDir = $this->getOutputDir();
            $filename = 'qr_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
            $outputPath = $outputDir . '/' . $filename;
            imagepng($img, $outputPath);
            imagedestroy($img);

            $this->logToolkitUsage('qr', $outputPath);

            return ['success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath), 'filename' => $filename, 'message' => 'QR generated'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::generateQR: " . $e->getMessage());
            return ['success' => false, 'message' => 'QR failed: ' . $e->getMessage()];
        }
    }

    /**
     * Add QR code (referral link) overlay to a photo.
     */
    public function addQRToPhoto(string $sourcePath, string $qrText, int $userId = 0): array
    {
        $qr = $this->generateQR($qrText, 200);
        if (!$qr['success']) return $qr;

        try {
            $img = $this->loadImage($sourcePath);
            if (!$img) return ['success' => false, 'message' => 'Invalid photo'];
            $qrImg = $this->loadImage($qr['path']);
            if (!$qrImg) {
                imagedestroy($img);
                return ['success' => false, 'message' => 'QR render failed'];
            }

            $w = imagesx($img);
            $h = imagesy($img);
            $qrSize = (int)($w * 0.18);
            $tmp = imagecreatetruecolor($qrSize, $qrSize);
            // White background for QR scannability
            $white = imagecolorallocate($tmp, 255, 255, 255);
            imagefill($tmp, 0, 0, $white);
            imagecopyresampled($tmp, $qrImg, 0, 0, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));

            // Bottom-left with white padding
            $pad = 12;
            $bx = 20;
            $by = $h - $qrSize - 20;
            imagefilledrectangle($img, $bx - $pad, $by - $pad, $bx + $qrSize + $pad, $by + $qrSize + $pad, $white);
            imagecopy($img, $tmp, $bx, $by, 0, 0, $qrSize, $qrSize);
            imagedestroy($tmp);
            imagedestroy($qrImg);

            // "Scan to Enquire" label
            $black = imagecolorallocate($img, 0, 0, 0);
            $this->drawText($img, 'Scan to Enquire', 2, $bx, $by + $qrSize + $pad + 4, $black);

            $outputDir = $this->getOutputDir();
            $filename = 'qrphoto_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $outputPath = $outputDir . '/' . $filename;
            imagejpeg($img, $outputPath, 90);
            imagedestroy($img);

            $this->logToolkitUsage('qr_photo', $outputPath);

            return ['success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath), 'filename' => $filename, 'message' => 'QR added to photo'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::addQRToPhoto: " . $e->getMessage());
            return ['success' => false, 'message' => 'QR overlay failed'];
        }
    }

    /* ── V2: Video Slideshow (animated GIF, no ffmpeg needed) ── */

    /**
     * Create animated GIF slideshow from 2-5 photos.
     * Works with GD only — no ffmpeg required.
     */
    public function makeSlideshow(array $paths, int $delayCs = 150, array $branding = []): array
    {
        $paths = array_values(array_filter(array_slice($paths, 0, 5), 'file_exists'));
        if (count($paths) < 2) {
            return ['success' => false, 'message' => 'Need at least 2 photos'];
        }

        try {
            $W = 640;
            $H = 640;
            $frames = [];
            $delays = [];

            foreach ($paths as $p) {
                $photo = $this->loadImage($p);
                if (!$photo) continue;
                $frame = imagecreatetruecolor($W, $H);
                $this->fillAreaWithImage($frame, $photo, 0, 0, $W, $H);
                imagedestroy($photo);

                // Branding bar at bottom
                $dark = imagecolorallocate($frame, ...self::BRAND_DARK);
                imagefilledrectangle($frame, 0, $H - 60, $W, $H, $dark);
                $gold = imagecolorallocate($frame, ...self::BRAND_GOLD);
                $name = mb_substr(($branding['display_name'] ?? 'APS Dream Home') . ' | ' . ($branding['phone'] ?? '+91 73092 68077'), 0, 55);
                imagestring($frame, 3, 15, $H - 40, $name, $gold);

                // Convert frame to GIF binary for encoder
                ob_start();
                imagegif($frame);
                $frames[] = ob_get_clean();
                $delays[] = $delayCs;
                imagedestroy($frame);
            }

            if (count($frames) < 2) {
                return ['success' => false, 'message' => 'Could not process photos'];
            }

            $gifBinary = $this->encodeAnimatedGif($frames, $delays);
            if (!$gifBinary) {
                return ['success' => false, 'message' => 'GIF encoding failed'];
            }

            $outputDir = $this->getOutputDir();
            $filename = 'slideshow_' . time() . '_' . bin2hex(random_bytes(4)) . '.gif';
            $outputPath = $outputDir . '/' . $filename;
            file_put_contents($outputPath, $gifBinary);

            $this->logToolkitUsage('slideshow', $outputPath);

            return ['success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath), 'filename' => $filename, 'frames' => count($frames), 'whatsapp_share' => $this->getWhatsAppShareLink("Check this property slideshow from APS Dream Home!"), 'message' => 'Slideshow created (' . count($frames) . ' photos)'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::makeSlideshow: " . $e->getMessage());
            return ['success' => false, 'message' => 'Slideshow failed'];
        }
    }

    /* ── V2: Visiting Card ── */

    /**
     * Generate digital visiting card (1080x600).
     */
    public function generateVisitingCard(array $branding): array
    {
        try {
            $W = 1080;
            $H = 600;
            $canvas = imagecreatetruecolor($W, $H);
            $dark = imagecolorallocate($canvas, ...self::BRAND_DARK);
            imagefill($canvas, 0, 0, $dark);

            // Gold left stripe
            $gold = imagecolorallocate($canvas, ...self::BRAND_GOLD);
            imagefilledrectangle($canvas, 0, 0, 24, $H, $gold);

            $white = imagecolorallocate($canvas, ...self::WHITE);

            $name = mb_substr($branding['display_name'] ?? 'Your Name', 0, 40);
            $phone = mb_substr($branding['phone'] ?? '', 0, 25);
            $tagline = mb_substr($branding['tagline'] ?? 'Associate | APS Dream Home', 0, 60);

            $this->drawText($canvas, 'APS DREAM HOMES', 4, 70, 60, $gold);
            $this->drawText($canvas, $name, 5, 70, 150, $white);
            $this->drawText($canvas, $tagline, 3, 70, 220, $white);
            if ($phone) {
                $this->drawText($canvas, 'Call/WhatsApp: ' . $phone, 4, 70, 320, $gold);
            }
            $this->drawText($canvas, 'Gorakhpur | Plots | EMI 0%', 3, 70, 400, $white);
            $this->drawText($canvas, '+91 73092 68077', 3, 70, 460, $white);

            // QR of referral link on right side
            $refCode = $branding['referral_code'] ?? '';
            if ($refCode) {
                $qrUrl = (defined('BASE_URL') ? BASE_URL : '') . '/register?ref=' . urlencode($refCode);
                $qr = $this->generateQR($qrUrl, 160);
                if ($qr['success']) {
                    $qrImg = $this->loadImage($qr['path']);
                    if ($qrImg) {
                        $qs = 160;
                        $tmp = imagecreatetruecolor($qs, $qs);
                        $white2 = imagecolorallocate($tmp, 255, 255, 255);
                        imagefill($tmp, 0, 0, $white2);
                        imagecopyresampled($tmp, $qrImg, 0, 0, 0, 0, $qs, $qs, imagesx($qrImg), imagesy($qrImg));
                        imagecopy($canvas, $tmp, $W - $qs - 60, (int)(($H - $qs) / 2), 0, 0, $qs, $qs);
                        imagedestroy($tmp);
                        imagedestroy($qrImg);
                        $this->drawText($canvas, 'Scan to Join', 2, $W - $qs - 60, (int)(($H - $qs) / 2) + $qs + 10, $white);
                    }
                }
            }

            $outputDir = $this->getOutputDir();
            $filename = 'vcard_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $outputPath = $outputDir . '/' . $filename;
            imagejpeg($canvas, $outputPath, 90);
            imagedestroy($canvas);

            $this->logToolkitUsage('visiting_card', $outputPath);

            return ['success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath), 'filename' => $filename, 'message' => 'Visiting card created'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::visitingCard: " . $e->getMessage());
            return ['success' => false, 'message' => 'Visiting card failed'];
        }
    }

    /* ── V3: Template System ── */

    /**
     * List active templates, optionally filtered by category.
     */
    public function listTemplates(string $category = ''): array
    {
        $tid = $this->getTenantId();
        try {
            $sql = "SELECT * FROM marketing_templates WHERE is_active = 1";
            $params = [];
            if ($category !== '') {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            $sql .= " ORDER BY sort_order ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Render a template with photo + data into a branded post.
     *
     * @param string $slug Template slug
     * @param string|null $photoPath Optional property photo
     * @param array $data {price, location, offer, property_type}
     * @param array $branding {display_name, phone, referral_code}
     */
    public function renderTemplate(string $slug, ?string $photoPath, array $data, array $branding): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM marketing_templates WHERE slug = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$slug]);
            $tpl = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$tpl) {
                return ['success' => false, 'message' => 'Template not found'];
            }

            $W = (int)$tpl['canvas_w'];
            $H = (int)$tpl['canvas_h'];
            $canvas = imagecreatetruecolor($W, $H);

            $bg = $this->hexToColor($canvas, $tpl['bg_color'] ?: '#070C18');
            imagefill($canvas, 0, 0, $bg);
            $accent = $this->hexToColor($canvas, $tpl['accent_color'] ?: '#FACC15');
            $white = imagecolorallocate($canvas, 255, 255, 255);

            $layout = $tpl['photo_layout'] ?? 'top';
            $photoH = 0;

            // Photo area
            if ($layout !== 'none' && $photoPath && file_exists($photoPath)) {
                $photo = $this->loadImage($photoPath);
                if ($photo) {
                    if ($layout === 'background') {
                        // Full-bleed background with dark overlay for text readability
                        $this->fillAreaWithImage($canvas, $photo, 0, 0, $W, $H);
                        $overlay = imagecolorallocatealpha($canvas, 0, 0, 0, 70);
                        imagefilledrectangle($canvas, 0, 0, $W, $H, $overlay);
                    } elseif ($layout === 'side') {
                        $pw = (int)($W * 0.45);
                        $this->fillAreaWithImage($canvas, $photo, 0, 0, $pw, $H);
                        // Text starts after photo
                    } else { // top
                        $photoH = (int)($H * 0.55);
                        $this->fillAreaWithImage($canvas, $photo, 0, 0, $W, $photoH);
                        // Accent divider
                        imagefilledrectangle($canvas, 0, $photoH, $W, $photoH + 8, $accent);
                    }
                    imagedestroy($photo);
                }
            }

            // Text origin depends on layout
            $tx = ($layout === 'side') ? (int)($W * 0.45) + 40 : 60;
            $maxW = $W - $tx - 40;
            $y = ($layout === 'top' && $photoH > 0) ? $photoH + 50 : 120;
            if ($layout === 'background') {
                $y = (int)($H * 0.25);
            }

            // Title
            if (!empty($tpl['title_text'])) {
                $this->drawTextFit($canvas, $tpl['title_text'], 5, $tx, $y, $accent, $maxW);
                $y += 70;
            }
            // Subtitle
            if (!empty($tpl['subtitle_text'])) {
                $this->drawTextFit($canvas, $tpl['subtitle_text'], 4, $tx, $y, $white, $maxW);
                $y += 60;
            }
            // Price
            if (!empty($tpl['show_price']) && !empty($data['price'])) {
                $this->drawTextFit($canvas, $data['price'], 5, $tx, $y, $white, $maxW);
                $y += 70;
            }
            // Location
            if (!empty($tpl['show_location']) && !empty($data['location'])) {
                $this->drawTextFit($canvas, $data['location'], 4, $tx, $y, $white, $maxW);
                $y += 60;
            }
            // Offer badge
            if (!empty($tpl['show_offer']) && !empty($data['offer'])) {
                $offerBg = imagecolorallocate($canvas, 16, 185, 129);
                $oy2 = $y + 55;
                if ($oy2 > $H - 170) $oy2 = $H - 170;
                imagefilledrectangle($canvas, $tx, $y, min($tx + 600, $W - 40), $oy2, $offerBg);
                $this->drawTextFit($canvas, mb_substr($data['offer'], 0, 50), 4, $tx + 15, $y + 12, $white, 560);
                $y = $oy2 + 25;
            }

            // Branding footer
            if (!empty($tpl['show_branding'])) {
                $name = mb_substr($branding['display_name'] ?? 'APS Dream Home', 0, 45);
                $phone = mb_substr($branding['phone'] ?? '+91 73092 68077', 0, 25);
                $this->drawTextFit($canvas, trim($name . ' | ' . $phone, ' |'), 4, $tx, $H - 90, $accent, $maxW);
            }

            // QR (referral link)
            if (!empty($tpl['show_qr'])) {
                $refCode = $branding['referral_code'] ?? '';
                $qrUrl = (defined('BASE_URL') ? BASE_URL : '') . '/register' . ($refCode ? '?ref=' . urlencode($refCode) : '');
                $qr = $this->generateQR($qrUrl, 140);
                if ($qr['success']) {
                    $qrImg = $this->loadImage($qr['path']);
                    if ($qrImg) {
                        $qs = 140;
                        $tmp = imagecreatetruecolor($qs, $qs);
                        $w2 = imagecolorallocate($tmp, 255, 255, 255);
                        imagefill($tmp, 0, 0, $w2);
                        imagecopyresampled($tmp, $qrImg, 0, 0, 0, 0, $qs, $qs, imagesx($qrImg), imagesy($qrImg));
                        $qx = $W - $qs - 40;
                        $qy = $H - $qs - 40;
                        // White padding for scannability
                        imagefilledrectangle($canvas, $qx - 8, $qy - 8, $qx + $qs + 8, $qy + $qs + 8, $w2);
                        imagecopy($canvas, $tmp, $qx, $qy, 0, 0, $qs, $qs);
                        imagedestroy($tmp);
                        imagedestroy($qrImg);
                    }
                }
            }

            $outputDir = $this->getOutputDir();
            $filename = 'tpl_' . preg_replace('/[^a-z0-9]/', '', $slug) . '_' . time() . '.jpg';
            $outputPath = $outputDir . '/' . $filename;
            imagejpeg($canvas, $outputPath, 88);
            imagedestroy($canvas);

            // Bump use count
            try {
                $this->pdo->prepare("UPDATE marketing_templates SET use_count = use_count + 1 WHERE slug = ?")->execute([$slug]);
            } catch (\Throwable $e) {
                // ignore
            }
            $this->logToolkitUsage('template', $outputPath);

            $shareText = trim(($tpl['title_text'] ?? '') . ' ' . ($data['price'] ?? '') . ' ' . ($data['location'] ?? ''));
            return [
                'success' => true, 'path' => $outputPath, 'url' => $this->pathToUrl($outputPath),
                'filename' => $filename, 'template' => $tpl['name'],
                'whatsapp_share' => $this->getWhatsAppShareLink($shareText . ' - APS Dream Home'),
                'message' => 'Template rendered',
            ];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::renderTemplate: " . $e->getMessage());
            return ['success' => false, 'message' => 'Template render failed'];
        }
    }

    /**
     * Admin: create or update a template.
     */
    public function saveTemplate(array $data, int $userId = 0): array
    {
        $tid = $this->getTenantId();
        try {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $data['slug'] ?? $data['name'] ?? ''), '-'));
            if ($slug === '') return ['success' => false, 'message' => 'Name/slug required'];

            $stmt = $this->pdo->prepare("
                INSERT INTO marketing_templates
                (name, slug, category, description, canvas_w, canvas_h, bg_color, accent_color, title_text, subtitle_text, show_price, show_location, show_offer, show_branding, show_qr, photo_layout, is_active, is_system, sort_order, created_by, tenant_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE name = VALUES(name), category = VALUES(category), description = VALUES(description),
                    canvas_w = VALUES(canvas_w), canvas_h = VALUES(canvas_h), bg_color = VALUES(bg_color), accent_color = VALUES(accent_color),
                    title_text = VALUES(title_text), subtitle_text = VALUES(subtitle_text), show_price = VALUES(show_price),
                    show_location = VALUES(show_location), show_offer = VALUES(show_offer), show_branding = VALUES(show_branding),
                    show_qr = VALUES(show_qr), photo_layout = VALUES(photo_layout), is_active = VALUES(is_active), sort_order = VALUES(sort_order)
            ");
            $stmt->execute([
                mb_substr($data['name'], 0, 100), $slug,
                in_array($data['category'] ?? '', ['festival', 'offer', 'launch', 'status', 'greeting', 'info'], true) ? $data['category'] : 'offer',
                mb_substr($data['description'] ?? '', 0, 255),
                max(300, min(2000, (int)($data['canvas_w'] ?? 1080))),
                max(300, min(2500, (int)($data['canvas_h'] ?? 1080))),
                $this->sanitizeHex($data['bg_color'] ?? '#070C18'),
                $this->sanitizeHex($data['accent_color'] ?? '#FACC15'),
                mb_substr($data['title_text'] ?? '', 0, 200),
                mb_substr($data['subtitle_text'] ?? '', 0, 200),
                !empty($data['show_price']) ? 1 : 0,
                !empty($data['show_location']) ? 1 : 0,
                !empty($data['show_offer']) ? 1 : 0,
                !empty($data['show_branding']) ? 1 : 0,
                !empty($data['show_qr']) ? 1 : 0,
                in_array($data['photo_layout'] ?? '', ['top', 'background', 'side', 'none'], true) ? $data['photo_layout'] : 'top',
                isset($data['is_active']) ? (int)$data['is_active'] : 1,
                (int)($data['sort_order'] ?? 0),
                $userId, $tid,
            ]);
            return ['success' => true, 'slug' => $slug, 'message' => 'Template saved'];
        } catch (\Throwable $e) {
            error_log("MarketingToolkit::saveTemplate: " . $e->getMessage());
            return ['success' => false, 'message' => 'Save failed'];
        }
    }

    /* ── Helpers ── */

    private function loadImage(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) return null;
        return match($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    private function fillAreaWithImage($canvas, $photo, int $x, int $y, int $w, int $h): void
    {
        $pw = imagesx($photo);
        $ph = imagesy($photo);
        // Cover-fit: scale to fill, crop overflow
        $scale = max($w / $pw, $h / $ph);
        $nw = (int)($pw * $scale);
        $nh = (int)($ph * $scale);
        $tmp = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($tmp, $photo, 0, 0, 0, 0, $nw, $nh, $pw, $ph);
        $sx = (int)(($nw - $w) / 2);
        $sy = (int)(($nh - $h) / 2);
        imagecopy($canvas, $tmp, $x, $y, $sx, $sy, $w, $h);
        imagedestroy($tmp);
    }

    private function drawText($canvas, string $text, int $font, int $x, int $y, int $color): void
    {
        // GD built-in fonts (1-5). Wraps long text.
        $text = mb_substr($text, 0, 120);
        $maxChars = [1 => 80, 2 => 60, 3 => 50, 4 => 40, 5 => 30][$font] ?? 40;
        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars - 3) . '...';
        }
        imagestring($canvas, $font, $x, $y, $text, $color);
    }

    private function drawCentered($canvas, string $text, int $font, int $y, int $color, int $canvasW): void
    {
        $text = mb_substr($text, 0, 80);
        $fontW = imagefontwidth($font);
        $tx = (int)(($canvasW - strlen($text) * $fontW) / 2);
        imagestring($canvas, $font, max(10, $tx), $y, $text, $color);
    }

    private function hexToColor($canvas, string $hex): int
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = '070C18';
        }
        return imagecolorallocate($canvas, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }

    private function sanitizeHex(string $hex): string
    {
        $hex = trim($hex);
        if (!str_starts_with($hex, '#')) $hex = '#' . $hex;
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $hex) && !preg_match('/^#[0-9a-fA-F]{3}$/', $hex)) {
            return '#070C18';
        }
        return strtoupper($hex);
    }

    private function drawTextFit($canvas, string $text, int $font, int $x, int $y, int $color, int $maxW): void
    {
        $text = trim(mb_substr($text, 0, 100));
        if ($text === '') return;
        $fontW = imagefontwidth($font);
        $maxChars = max(10, (int)($maxW / max(1, $fontW)));
        if (mb_strlen($text) > $maxChars) {
            $text = mb_substr($text, 0, $maxChars - 3) . '...';
        }
        imagestring($canvas, $font, $x, $y, $text, $color);
    }

    /**
     * Encode multiple GIF frames into one animated GIF binary.
     * Pure-PHP GIF89a encoder (NETSCAPE loop extension). No external deps.
     */
    private function encodeAnimatedGif(array $frames, array $delays): ?string
    {
        if (count($frames) < 2) return null;

        // Parse first frame header to reuse global color table
        $first = $frames[0];
        if (substr($first, 0, 6) !== 'GIF89a' && substr($first, 0, 6) !== 'GIF87a') {
            return null;
        }

        $width = ord($first[6]) + (ord($first[7]) << 8);
        $height = ord($first[8]) + (ord($first[9]) << 8);
        $packed = ord($first[10]);
        $hasGCT = ($packed & 0x80) !== 0;
        $gctSize = 0;
        $header = 'GIF89a' . substr($first, 6, 7); // width/height/packed/bg/aspect

        $offset = 13;
        $gct = '';
        if ($hasGCT) {
            $gctSize = 2 << ($packed & 0x07);
            $gct = substr($first, $offset, 3 * $gctSize);
            $offset += 3 * $gctSize;
        }

        // NETSCAPE2.0 loop forever
        $out = $header . $gct . "!\xFF\x0BNETSCAPE2.0\x03\x01\x00\x00\x00";

        foreach ($frames as $i => $frame) {
            $delay = (int)($delays[$i] ?? 100);
            // Graphic Control Extension
            $out .= "!\xF9\x04\x00" . chr($delay & 0xFF) . chr(($delay >> 8) & 0xFF) . "\x00\x00";
            // Find image descriptor in frame
            $pos = strpos($frame, "\x2C");
            if ($pos === false) continue;
            // Image descriptor (10 bytes) + LZW data until trailer
            $imgEnd = strrpos($frame, "\x3B");
            if ($imgEnd === false || $imgEnd <= $pos) continue;
            $imgBlock = substr($frame, $pos, $imgEnd - $pos);
            // Strip local color table if present (use global instead)
            $descriptor = substr($imgBlock, 0, 10);
            $dPacked = ord($descriptor[9]);
            $dataStart = 10;
            if (($dPacked & 0x80) !== 0) {
                $lctSize = 2 << ($dPacked & 0x07);
                $dataStart += 3 * $lctSize;
                // Clear LCT flag, keep other bits
                $descriptor[9] = chr($dPacked & 0x7F);
            }
            $out .= $descriptor . substr($imgBlock, $dataStart);
        }

        $out .= "\x3B"; // trailer
        return strlen($out) > 100 ? $out : null;
    }

    private function getOutputDir(): string
    {
        $dir = (defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 2)) . '/storage/marketing';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    private function pathToUrl(string $path): string
    {
        $root = defined('APS_ROOT') ? APS_ROOT : dirname(__DIR__, 2);
        $rel = str_replace($root, '', $path);
        $rel = str_replace('\\', '/', $rel);
        return (defined('BASE_URL') ? BASE_URL : '') . $rel;
    }

    private function logToolkitUsage(string $toolType, string $outputPath): void
    {
        try {
            $tid = $this->getTenantId();
            $userId = $_SESSION['user_id'] ?? ($GLOBALS['api_user_id'] ?? 0);
            $stmt = $this->pdo->prepare("
                INSERT INTO marketing_toolkit_usage (user_id, tool_type, output_path, tenant_id, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $toolType, $outputPath, $tid]);
        } catch (\Throwable $e) {
            // Table may not exist yet — non-fatal
        }
    }
}