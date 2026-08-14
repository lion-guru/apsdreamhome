<?php
/**
 * CAPTCHA Image Endpoint
 * URL: /captcha/image.php
 */
require_once __DIR__ . '/../app/Helpers/SimpleCaptcha.php';

SimpleCaptcha::generateImage();
?>
