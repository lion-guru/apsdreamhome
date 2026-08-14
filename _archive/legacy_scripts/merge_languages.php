<?php
// scripts/merge_languages.php
// Run via: php scripts/merge_languages.php

echo "Merging legacy translation arrays...\n";

$addKeysScript = __DIR__ . '/add_phase2_translation_keys.php';
if (file_exists($addKeysScript)) {
    require_once $addKeysScript;
    echo "âœ“ Successfully merged translation keys via add_phase2_translation_keys.php!\n";
} else {
    echo "âœ— Error: add_phase2_translation_keys.php not found.\n";
}?>