<?php
$files = [
    'app/Services/AI/PatternLearner.php',
    'app/Services/AI/IntentDetector.php',
    'app/Services/AI/RecommendationEngine.php',
    'app/Services/AI/LeadScorer.php',
    'app/Services/AI/PricePredictor.php'
];

foreach ($files as $f) {
    $c = file_get_contents($f);
    $orig = $c;

    $c = str_replace('private PDO $db;', "private \$db;\n    private \$pdo;", $c);

    $c = preg_replace(
        '/public function __construct\(PDO \$db\)\s*\{\s*\$this->db = \$db;\s*\}/',
        "public function __construct(\$db)\n    {\n        \$this->db = \$db;\n        \$this->pdo = is_object(\$db) && method_exists(\$db, 'getPdo') ? \$db->getPdo() : \$db;\n    }",
        $c
    );

    if ($c !== $orig) {
        file_put_contents($f, $c);
        echo "Fixed: $f\n";
    } else {
        echo "Unchanged: $f\n";
    }
}?>