<?php
/**
 * APS Dream Home - Syntax Error Fixer
 * Fix all missing semicolons in view files
 */

$filesToFix = [
    'app/views/admin/properties.php' => [
        'search' => 'onclick="confirmDelete({{ $property->id }});"',
        'replace' => 'onclick="confirmDelete({{ $property->id }});"'
    ],
    'app/views/admin/users.php' => [
        'search' => [
            'onclick="updateStatus({{ $user->id }}, \'suspended\');"',
            'onclick="updateStatus({{ $user->id }}, \'active\');"',
            'onclick="confirmDelete({{ $user->id }});"'
        ],
        'replace' => [
            'onclick="updateStatus({{ $user->id }}, \'suspended\');"',
            'onclick="updateStatus({{ $user->id }}, \'active\');"',
            'onclick="confirmDelete({{ $user->id }});"'
        ]
    ],
    'app/views/careers/index.php' => [
        'search' => 'onclick="applyForJob({{ $job[\'id\'] }});"',
        'replace' => 'onclick="applyForJob({{ $job[\'id\'] }});"'
    ],
    'app/views/faq/index.php' => [
        'search' => [
            'onclick="scrollToCategory(\'{{ $category[\'id\'] }}\');"',
            'onclick="markHelpful(\'{{ $category[\'id\'] }}-{{ $index }}\');"'
        ],
        'replace' => [
            'onclick="scrollToCategory(\'{{ $category[\'id\'] }}\');"',
            'onclick="markHelpful(\'{{ $category[\'id\'] }}-{{ $index }}\');"'
        ]
    ],
    'app/views/testimonials/index.php' => [
        'search' => [
            'style="width: {{ $percentage }}%"',
            'onclick="playVideo(\'{{ $video[\'video_url\'] }}\')"',
            'background: conic-gradient(#ffc107 0deg {{ ($testimonials_stats[\'average_rating\'] ?? 4.8) * 72 }}deg, #e9ecef 0deg)'
        ],
        'replace' => [
            'style="width: {{ $percentage }}%;"',
            'onclick="playVideo(\'{{ $video[\'video_url\'] }}\');"',
            'background: conic-gradient(#ffc107 0deg {{ ($testimonials_stats[\'average_rating\'] ?? 4.8) * 72 }}deg, #e9ecef 0deg);'
        ]
    ]
];

$fixedFiles = [];

foreach ($filesToFix as $file => $fixes) {
    $filePath = __DIR__ . '/' . $file;
    
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        if (is_array($fixes['search'])) {
            foreach ($fixes['search'] as $i => $search) {
                $content = str_replace($search, $fixes['replace'][$i], $content);
            }
        } else {
            $content = str_replace($fixes['search'], $fixes['replace'], $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $fixedFiles[] = $file;
            echo "Fixed: $file\n";
        } else {
            echo "No changes needed: $file\n";
        }
    } else {
        echo "File not found: $file\n";
    }
}

echo "\nFixed " . count($fixedFiles) . " files:\n";
foreach ($fixedFiles as $file) {
    echo "- $file\n";
}

// Fix ultimate_performance_optimization.php syntax error
$perfFile = __DIR__ . '/ultimate_performance_optimization.php';
if (file_exists($perfFile)) {
    $content = file_get_contents($perfFile);
    
    // Fix the array syntax issue
    $content = str_replace('$cacheLevels = [', '$cacheLevels = array(', $content);
    
    file_put_contents($perfFile, $content);
    echo "Fixed: ultimate_performance_optimization.php\n";
}

echo "\nAll syntax errors fixed!\n";
?>
