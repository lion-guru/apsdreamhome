<?php
$files = glob('app/Services/*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    $orig = $c;
    $c = str_replace('private PDO $db;', 'private $db;', $c);
    $c = str_replace('public function __construct(PDO $db)', 'public function __construct($db)', $c);
    $c = str_replace('private PDO $pdo;', 'private $pdo;', $c);
    $c = str_replace('public function __construct(PDO $pdo)', 'public function __construct($pdo)', $c);

    if ($c !== $orig) {
        file_put_contents($f, $c);
        echo "[OK] $f\n";
    }
}
echo "Done\n";?>