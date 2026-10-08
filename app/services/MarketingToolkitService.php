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