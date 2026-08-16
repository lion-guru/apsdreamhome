<?php
/**
 * Simple CAPTCHA Helper - Prevents brute force attacks
 * No external dependencies - uses GD library
 */
class SimpleCaptcha {
    private static $session_key = 'captcha_code';
    private static $length = 6;
    
    /**
     * Generate a random CAPTCHA code and store in session
     */
    public static function generate($length = 6) {
        self::$length = $length;
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION[self::$session_key] = $code;
        $_SESSION[self::$session_key . '_time'] = time();
        
        return $code;
    }
    
    /**
     * Generate CAPTCHA image
     */
    public static function generateImage($code = null) {
        if (!$code) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $code = $_SESSION[self::$session_key] ?? self::generate();
        }
        
        $length = strlen($code);
        $width = $length * 30 + 20;
        $height = 60;
        
        $image = imagecreate($width, $height);
        $bg = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 50, 50, 50);
        $noise_color = imagecolorallocate($image, 200, 200, 200);
        
        // Add noise
        for ($i = 0; $i < 200; $i++) {
            $x = rand(0, $width);
            $y = rand(0, $height);
            imagesetpixel($image, $x, $y, $noise_color);
        }
        
        // Draw text
        $font_size = 5;
        $text_width = imagefontwidth($font_size) * $length;
        $x = ($width - $text_width) / 2;
        imagestring($image, $font_size, $x, 20, $code, $text_color);
        
        // Output image
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
    
    /**
     * Validate CAPTCHA code
     */
    public static function validate($code) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$session_key])) {
            return false;
        }
        
        $session_code = $_SESSION[self::$session_key];
        
        // Check expiration (5 minutes)
        $time = $_SESSION[self::$session_key . '_time'] ?? 0;
        if (time() - $time > 300) {
            unset($_SESSION[self::$session_key]);
            return false;
        }
        
        // Validate and clear
        $valid = hash_equals($session_code, strtoupper($code));
        if ($valid) {
            unset($_SESSION[self::$session_key]);
            unset($_SESSION[self::$session_key . '_time']);
        }
        
        return $valid;
    }
    
    /**
     * Render CAPTCHA in form
     */
    public static function renderField($label = 'Enter CAPTCHA') {
        $img_src = BASE_URL . '/captcha/image.php?t=' . time();
        return "
        <div class='form-group'>
            <label>{$label}</label>
            <div class='input-group'>
                <input type='text' name='captcha_code' class='form-control' required autocomplete='off'>
                <span class='input-group-text'>
                    <img src='{$img_src}' alt='CAPTCHA' style='height:40px;'>
                </span>
            </div>
        </div>";
    }
}
?>
