<?php
// scripts/add_plots_translation_keys.php
// Run via: php scripts/add_plots_translation_keys.php

$root = dirname(__DIR__);
$enFile = $root . '/lang/en.php';
$hiFile = $root . '/lang/hi.php';

function addTranslationKeys($file, $newKeys) {
    if (!file_exists($file)) {
        echo "Error: File $file does not exist.\n";
        return;
    }
    
    $existing = require $file;
    $modified = false;
    
    foreach ($newKeys as $key => $val) {
        if (!isset($existing[$key])) {
            $existing[$key] = $val;
            $modified = true;
            echo "Added key '$key' to " . basename($file) . "\n";
        }
    }
    
    if ($modified) {
        // Sort keys for clean organization
        ksort($existing);
        
        $php = "<?php\n/**\n * Language File\n * APS Dream Home - Real Estate CRM\n */\nreturn " . var_export($existing, true) . ";\n";
        file_put_contents($file, $php);
        echo "Successfully updated " . basename($file) . "\n";
    } else {
        echo "No keys needed to be added to " . basename($file) . "\n";
    }
}

$enKeys = [
    'nav_plots' => 'Plots',
    'plots_title' => 'Available Plots',
];

$hiKeys = [
    'nav_plots' => 'प्लॉट्स',
    'plots_title' => 'उपलब्ध प्लॉट्स',
];

addTranslationKeys($enFile, $enKeys);
addTranslationKeys($hiFile, $hiKeys);
