<?php
$files = glob('app/Services/*.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, 'public function __construct($db)') !== false && strpos($c, '$this->pdo($this->db)') === false) {
        $c = str_replace(
            'public function __construct($db) { $this->db = $db; }',
            'public function __construct($db) { $this->db = $db; if (is_object($db) && method_exists($db, "getPdo")) { $this->pdo = $db->getPdo(); } elseif ($db instanceof PDO) { $this->pdo = $db; } else { $this->pdo = $db; } }',
            $c
        );
        file_put_contents($f, $c);
        echo "[OK] $f\n";
    }
}
echo "Done\n";?>