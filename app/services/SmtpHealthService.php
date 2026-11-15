<?php
/**
 * Honest SMTP health reporter (single source of truth).
 *
 * Every outbound email funnels through EmailService, which swallows PHPMailer
 * failures into error_log() and returns success=false. Nothing in the app
 * surfaces that failure to an admin — the queue "fails silently". This helper
 * tail-scans today's php_error.log for SMTP failure signatures and reports
 * how many happened, so admin pages can render an honest banner.
 *
 * Cheap: one fopen/fgets loop over ~1 MB, admin pages only.
 */
namespace App\Services;

final class SmtpHealthService
{
    /**
     * @return array{count:int, sample:string, last_time:string, ok:bool}
     */
    public static function report(): array
    {
        $result = ['count' => 0, 'sample' => '', 'last_time' => '', 'ok' => false];
        $logFile = dirname(__DIR__, 2) . '/logs/php_error.log'; // app/Services → project root
        if (!is_readable($logFile)) {
            return $result;
        }
        $today = date('d-M-Y'); // matches the log prefix, e.g. 15-Nov-2026
        $fh = @fopen($logFile, 'r');
        if (!$fh) {
            return $result;
        }
        try {
            while (($line = fgets($fh)) !== false) {
                if (strpos($line, $today) === false) {
                    continue;
                }
                if (stripos($line, 'SMTP') === false && stripos($line, 'Could not authenticate') === false) {
                    continue;
                }
                $result['count']++;
                $result['sample'] = trim($line);
                if (preg_match('/^\[([^\]]+)\]/', $line, $m)) {
                    $result['last_time'] = $m[1];
                }
            }
        } finally {
            fclose($fh);
        }
        $result['ok'] = $result['count'] === 0;
        return $result;
    }
}
