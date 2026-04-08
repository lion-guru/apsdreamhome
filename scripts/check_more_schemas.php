<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$tables = ['ai_training_sessions', 'ai_user_interactions', 'ai_learning_data', 'ai_user_profiles', 'ai_learning_progress'];
foreach ($tables as $table) {
    echo "$table:\n";
    try {
        $cols = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
        foreach($cols as $c) {
            echo "  " . $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . "\n";
        }
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
