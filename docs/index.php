<?php
/**
 * APS Dream Home - Documentation Hub
 * 
 * This file serves as the central documentation hub for the APS Dream Home system,
 * providing access to all documentation resources.
 * 
 * @package    APS Dream Home
 * @category   Documentation
 * @author     APS Dream Home Team
 * @version    1.0.0
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Include the simple Markdown parser
require_once __DIR__ . '/parsedown.php';

// Define base URL
$baseUrl = rtrim(dirname($_SERVER['PHP_SELF']), '/');

// Documentation sections
$docSections = [
    'getting-started' => [
        'title' => 'Getting Started',
        'description' => 'Introduction to APS Dream Home system and quick start guide',
        'icon' => 'fas fa-rocket',
        'file' => 'getting-started.md'
    ],
    'user-guides' => [
        'title' => 'User Guides',
        'description' => 'Step-by-step guides for using the system',
        'icon' => 'fas fa-book',
        'file' => 'user-guides/README.md'
    ],
    'admin-guide' => [
        'title' => 'Admin Guide',
        'description' => 'Administrator documentation and system configuration',
        'icon' => 'fas fa-user-shield',
        'file' => 'admin-guide/README.md'
    ],
    'api' => [
        'title' => 'API Documentation',
        'description' => 'API endpoints, authentication, and usage examples',
        'icon' => 'fas fa-code',
        'file' => 'api/README.md'
    ],
    'database' => [
        'title' => 'Database Documentation',
        'description' => 'Database schema, relationships, and important queries',
        'icon' => 'fas fa-database',
        'file' => 'database/README.md'
    ],
    'deployment' => [
        'title' => 'Deployment Guide',
        'description' => 'Server setup and deployment instructions',
        'icon' => 'fas fa-server',
        'file' => 'deployment/README.md'
    ],
    'faq' => [
        'title' => 'FAQ',
        'description' => 'Frequently asked questions',
        'icon' => 'fas fa-question-circle',
        'file' => 'FAQ.md'
    ]
];

// Check if a specific documentation file is requested
$requestedDoc = isset($_GET['doc']) ? trim($_GET['doc']) : '';
$docContent = '';

// Initialize variables
$filePath = '';
$fileDir = '';

// Handle file requests within documentation
if (isset($_GET['file']) && !empty($_GET['file'])) {
    $fileName = ltrim(explode('#', $_GET['file'])[0], '/');
    
    // First try to find the file in the same directory as the current doc
    if (!empty($requestedDoc) && isset($docSections[$requestedDoc])) {
        $fileDir = dirname($docSections[$requestedDoc]['file']);
        $filePath = __DIR__ . '/' . ($fileDir ? $fileDir . '/' : '') . $fileName;
    }
    
    // If not found, try in the database directory (common location for migration guides)
    if ((empty($filePath) || !file_exists($filePath)) && strpos($fileName, 'migration') !== false) {
        $filePath = __DIR__ . '/database/' . $fileName;
    }
    
    // If still not found, try in the root directory
    if (empty($filePath) || !file_exists($filePath)) {
        $filePath = __DIR__ . '/' . $fileName;
    }
    
    if (file_exists($filePath)) {
        $parsedown = new Parsedown();
        $markdownContent = @file_get_contents($filePath);
        if ($markdownContent !== false) {
            // Process markdown content to handle relative links
            $backLink = '';
            if (!empty($requestedDoc) && isset($docSections[$requestedDoc])) {
                $backLink = '<a href="?doc=' . htmlspecialchars($requestedDoc) . '" class="back-to-docs"><i class="fas fa-arrow-left"></i> Back to ' . 
                          htmlspecialchars($docSections[$requestedDoc]['title'] ?? 'Documentation') . '</a>';
            }
            
            $markdownContent = preg_replace_callback(
                '/\]\(([^:)]+\.md(?:#[^)]*)?)\)/', 
                function($matches) use ($requestedDoc) {
                    return '](?doc=' . urlencode($requestedDoc) . '&file=' . urlencode($matches[1]) . ')';
                },
                $markdownContent
            );
            
            $docContent = $parsedown->text($markdownContent);
            $docContent = $backLink . $docContent;
            
            if (!empty($requestedDoc)) {
                $docContent = str_replace('<a href="?', '<a href="?doc=' . htmlspecialchars($requestedDoc) . '&', $docContent);
            }
        } else {
            $docContent = '<div class="alert alert-danger">Error: Could not read documentation file.</div>';
        }
    } else {
        $docContent = '<div class="alert alert-warning">Documentation file not found: ' . 
                     htmlspecialchars($fileName) . ' in ' . htmlspecialchars($fileDir ?: 'root') . ' directory</div>';
    }
} else if ($requestedDoc) {
    // Special case for getting-started
    if ($requestedDoc === 'getting-started') {
        $docFile = __DIR__ . '/getting-started.md';
        if (file_exists($docFile)) {
            $parsedown = new Parsedown();
            $markdownContent = @file_get_contents($docFile);
            if ($markdownContent !== false) {
                $docContent = $parsedown->text($markdownContent);
                $docContent = str_replace('<a href="#', '<a href="' . $baseUrl . '/?doc=getting-started&', $docContent);
            } else {
                $docContent = '<div class="alert alert-danger">Error: Could not read Getting Started guide.</div>';
            }
        } else {
            $docContent = '<div class="alert alert-warning">Getting Started guide not found.</div>';
        }
    } 
    // Handle other documentation sections
    else if (isset($docSections[$requestedDoc])) {
        $docFile = __DIR__ . '/' . $docSections[$requestedDoc]['file'];
        if (file_exists($docFile)) {
            $parsedown = new Parsedown();
            $markdownContent = @file_get_contents($docFile);
            if ($markdownContent !== false) {
                // Process markdown content to handle relative links
                $markdownContent = preg_replace('/\]\(([^:)]+\.md)\)/', '](#doc=' . urlencode($requestedDoc) . '&file=\1)', $markdownContent);
                $docContent = $parsedown->text($markdownContent);
                
                // Add base URL to relative links
                $docContent = str_replace('<a href="#', '<a href="' . $baseUrl . '/?doc=' . $requestedDoc . '&', $docContent);
            } else {
                $docContent = '<div class="alert alert-danger">Error: Could not read documentation file.</div>';
            }
        } else {
            $docContent = '<div class="alert alert-warning">Documentation file not found: ' . htmlspecialchars($docFile) . '</div>';
        }
    } else {
        $docContent = '<div class="alert alert-warning">Invalid documentation section selected.</div>';
    }
} else {
    $docContent = '<div class="alert alert-danger">Error: Could not read documentation file.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Documentation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle internal link clicks
        document.body.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            
            // Handle markdown file links
            if (link.href && (link.href.endsWith('.md') || link.href.includes('.md#'))) {
                e.preventDefault();
                const url = new URL(link.href, window.location.origin);
                const pathParts = url.pathname.split('/');
                let fileName = pathParts[pathParts.length - 1];
                let currentDoc = new URLSearchParams(window.location.search).get('doc') || '';
                
                // If it's a relative path from current directory
                if (link.href.startsWith('./')) {
                    // Keep the current document context
                    fileName = link.href.replace('./', '');
                } else if (link.href.includes('/')) {
                    // Handle paths with directories
                    const dirParts = link.href.split('/');
                    currentDoc = dirParts[0];
                    fileName = dirParts[dirParts.length - 1];
                }
                
                if (url.hash) {
                    // Handle anchor links within the same document
                    const targetId = url.hash.substring(1);
                    const targetElement = document.getElementById(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                        return;
                    }
                }
                
                // Load new document
                window.location.href = `?doc=${currentDoc}&file=${fileName}${url.hash || ''}`;
            }
        });
        
        // Handle initial page load with hash
        window.addEventListener('load', function() {
            if (window.location.hash) {
                const targetId = window.location.hash.substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView();
                }
            }
        });
    });
    </script>
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #0d47a1;
            --accent-color: #2962ff;
            --light-color: #f8f9fa;
            --dark-color: #212121;
            --success-color: #2e7d32;
            --warning-color: #ff8f00;
            --danger-color: #c62828;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.7;
            background-color: #f8fafc;
            color: #333;
            padding: 0;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #0d47a1 100%) !important;
            color: white;
            min-height: 100vh;
            padding: 8px 0 20px 0;
            position: fixed;
            width: 180px;
            height: 100%;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
            z-index: 100;
            left: 0;
            top: 0;
        }
        .sidebar-header {
            padding: 4px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2px;
        }
        .sidebar-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            padding: 4px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav {
            padding: 0 8px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 8px 12px;
            margin: 1px 0;
            border-radius: 4px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }
        .sidebar .nav-link i {
            width: 16px;
            margin-right: 8px;
            font-size: 0.85rem;
            text-align: center;
        }
        .main-content {
            margin: 0 auto 0 180px;
            padding: 20px 30px;
            min-height: 100vh;
            background-color: #f8fafc;
            width: calc(100% - 180px);
            max-width: 1100px;
            position: relative;
            z-index: 90;
        }
        .doc-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 12px 0 8px 0;
            margin: -20px -30px 20px -30px;
            border-radius: 0 0 8px 8px;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding-right: 120px; /* Space for the back button */
            z-index: 80;
        }
        .doc-header p {
            font-size: 0.85rem;
            opacity: 0.9;
            margin: 2px 0 0 0;
            font-weight: 400;
        }
        .doc-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></svg>');
            opacity: 0.3;
        }
        .doc-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
            margin: 0 0 30px 0;
        }
        .doc-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
            transition: var(--transition);
            height: 100%;
            border-top: 3px solid var(--accent-color);
            position: relative;
            overflow: hidden;
        }
        .doc-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .doc-card i {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 15px;
            background: rgba(41, 98, 255, 0.1);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }
        .doc-header h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
            color: white;
            letter-spacing: -0.3px;
        }
        .doc-card h3 {
            color: var(--dark-color);
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 1.2rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .doc-card h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }
        .doc-card p {
            color: #555;
            margin-bottom: 20px;
            line-height: 1.7;
        }
        .markdown-content {
            background: white;
            padding: 25px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin: 0 auto 30px;
            max-width: 100%;
        }
        
        .markdown-content img {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius);
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            margin-top: 1.5em;
            margin-bottom: 0.8em;
            color: var(--primary-color);
        }
        .markdown-content h1 { font-size: 2.2em; }
        .markdown-content h2 { font-size: 1.8em; }
        .markdown-content h3 { font-size: 1.5em; }
        .markdown-content p { margin-bottom: 1.2em; }
        .markdown-content ul, 
        .markdown-content ol { 
            margin-bottom: 1.2em;
            padding-left: 2em;
        }
        .markdown-content code {
            background-color: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .markdown-content pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin-bottom: 1.2em;
        }
        .markdown-content pre code {
            background: none;
            padding: 0;
        }
        .markdown-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.2em;
        }
        .markdown-content table th,
        .markdown-content table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        .markdown-content table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .back-to-docs {
            position: absolute;
            top: 15px;
            right: 30px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        
        .back-to-docs i {
            margin-right: 5px;
            font-size: 0.9em;
            transition: all 0.2s ease;
        }
        
        .back-to-docs:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-3px);
        }
        .back-to-docs:hover {
            color: white;
            text-decoration: none;
            opacity: 1;
        }
        /* Code blocks */
        pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: var(--border-radius);
            overflow-x: auto;
            font-family: 'Fira Code', 'Consolas', monospace;
            font-size: 0.9em;
            line-height: 1.6;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 0 0 1px #e2e8f0;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        tr:hover {
            background-color: #f8fafc;
        }
        
        /* Blockquotes */
        blockquote {
            border-left: 4px solid var(--accent-color);
            padding: 15px 20px;
            margin: 20px 0;
            background: #f8fafc;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            font-style: italic;
            color: #555;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
                height: auto;
                padding: 15px 0;
                margin-bottom: 20px;
                box-shadow: none;
                position: relative;
                z-index: 2;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px 15px;
                position: relative;
                z-index: 1;
            }
            .doc-header {
                margin: 0 -15px 20px -15px;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0 m-0">
        <div class="row g-0 m-0">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <h3>Documentation</h3>
                </div>
                <ul class="nav flex-column">
                    <?php foreach ($docSections as $key => $section): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= (isset($requestedDoc) && $requestedDoc === $key) ? 'active' : '' ?>" 
                               href="?doc=<?= htmlspecialchars($key) ?>">
                                <i class="<?= $section['icon'] ?>"></i>
                                <span><?= $section['title'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Main content -->
            <div class="main-content">
                <?php if (!empty($requestedDoc) && !empty($docContent)): ?>
                    <div class="doc-header">
                        <div class="container">
                            <a href="?" class="back-to-docs">
                                <i class="fas fa-arrow-left"></i> Back to Documentation
                            </a>
                            <?php if (isset($docSections[$requestedDoc])): ?>
                                <h1><?= htmlspecialchars($docSections[$requestedDoc]['title'] ?? 'Documentation') ?></h1>
                                <?php if (!empty($docSections[$requestedDoc]['description'])): ?>
                                    <p class="lead"><?= htmlspecialchars($docSections[$requestedDoc]['description']) ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <h1>Documentation</h1>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="markdown-content">
                        <?= $docContent ?>
                    </div>
                <?php else: ?>
                    <!-- Documentation Hub -->
                    <div class="doc-header">
                        <div class="container">
                            <h1>APS Dream Home Documentation</h1>
                            <p class="lead">Welcome to the official documentation for APS Dream Home Real Estate Management System</p>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($docSections as $key => $section): ?>
                            <div class="col">
                                <div class="doc-card">
                                    <i class="<?= $section['icon'] ?>"></i>
                                    <h3><?= htmlspecialchars($section['title']) ?></h3>
                                    <p><?= htmlspecialchars($section['description']) ?></p>
                                    <a href="?doc=<?= $key ?>" class="btn btn-primary">
                                        View Documentation <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
