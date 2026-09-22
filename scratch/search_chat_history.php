<?php
$brainDir = 'C:/Users/abhay/.gemini/antigravity-ide/brain';
$dirs = glob($brainDir . '/*', GLOB_ONLYDIR);

$keywords = ['about', 'privacy', 'terms', 'refund', 'career', 'contact', 'content', 'likha', 'kaha', 'kya', 'page', 'site-content'];

foreach ($dirs as $d) {
    $transcript = $d . '/.system_generated/logs/transcript.jsonl';
    if (!file_exists($transcript)) continue;

    $convId = basename($d);
    $handle = fopen($transcript, 'r');
    if (!$handle) continue;

    $lineNum = 0;
    while (($line = fgets($handle)) !== false) {
        $lineNum++;
        if (strpos($line, '"USER_INPUT"') === false) continue;
        $data = json_decode($line, true);
        if (!$data || empty($data['content'])) continue;
        
        $text = $data['content'];
        $match = false;
        if (preg_match('/(content|page|privacy|terms|about|refund|career|contact|likh)/i', $text)) {
            // Check if it's discussing planning content or pages
            if (preg_match('/(kaha|likha|plan|bana|content|karna|likh)/i', $text)) {
                $snippet = preg_replace('/\s+/', ' ', substr(strip_tags($text), 0, 200));
                echo "Conv: {$convId} (Line {$lineNum}) [{$data['created_at']}]:\n  {$snippet}\n\n";
            }
        }
    }
    fclose($handle);
}
