<?php
$content = file_get_contents(dirname(__DIR__) . '/routes/web.php');
echo "Matches for 'bank':\n";
preg_match_all('/.*bank.*/i', $content, $matches);
foreach ($matches[0] as $match) {
    echo "  $match\n";
}

echo "\nMatches for 'wallet':\n";
preg_match_all('/.*wallet.*/i', $content, $matches);
foreach ($matches[0] as $match) {
    echo "  $match\n";
}
?>
