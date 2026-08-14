<?php
$files = glob('app/Services/*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, 'private $db;') !== false && strpos($c, 'private $pdo;') === false && strpos($c, 'public function __construct($db)') !== false) {
        $c = str_replace('private $db;', "private \$db;\n    private \$pdo;", $c);
        file_put_contents($f, $c);
        echo "[OK] $f\n";
    }
}
echo "Done\n";?>