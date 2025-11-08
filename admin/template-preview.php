<?php
require_once 'config.php';
require_once 'admin-functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid template ID');
}

$db = getDBConnection();
$layoutTemplate = new Admin\Models\LayoutTemplate($db);

$template = $layoutTemplate->getById($_GET['id']);
if (!$template) {
    die('Template not found');
}

// Sample data for preview
$previewData = [
    'title' => 'Sample Page Title',
    'content' => '<h2>Welcome to the Sample Page</h2><p>This is a preview of how your content will look in this template. The content can be customized based on your needs.</p>',
    'sidebar' => '<div class="widget"><h3>About Us</h3><p>Sample sidebar content goes here.</p></div>',
    'header' => '<div class="site-branding"><h1>Website Name</h1><p>Your tagline goes here</p></div>',
    'footer' => '<div class="footer-content"><p>&copy; 2024 Your Website. All rights reserved.</p></div>',
    'navigation' => '<ul><li><a href="#">Home</a></li><li><a href="#">About</a></li><li><a href="#">Services</a></li><li><a href="#">Contact</a></li></ul>'
];

// Get compiled template with sample data
$compiledContent = $layoutTemplate->compileTemplate($template['id']);
$previewContent = $layoutTemplate->previewTemplate($compiledContent, $previewData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: <?php echo htmlspecialchars($template['name']); ?></title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        .preview-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #333;
            color: #fff;
            padding: 10px;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-toolbar select {
            padding: 5px;
            margin: 0 10px;
        }

        .preview-container {
            display: flex;
            margin-top: 50px;
            transition: all 0.3s ease;
        }

        .preview-frame {
            border: none;
            flex: 1;
            height: calc(100vh - 50px);
            transition: all 0.3s ease;
        }

        .editor-panel {
            width: 300px;
            background: #f5f5f5;
            padding: 15px;
            height: calc(100vh - 50px);
            overflow-y: auto;
        }

        .editor-panel textarea {
            width: 100%;
            min-height: 100px;
            margin-bottom: 10px;
        }

        .editor-panel input {
            width: 100%;
            margin-bottom: 10px;
        }

        .editor-panel label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .preview-toolbar {
                flex-direction: column;
                padding: 5px;
            }

            .preview-toolbar select {
                margin: 5px 0;
            }

            .preview-container {
                flex-direction: column;
            }

            .editor-panel {
                width: 100%;
                height: auto;
                order: -1;
            }

            .preview-frame {
                height: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="preview-toolbar">
        <div>
            <strong>Template Preview:</strong> 
            <?php echo htmlspecialchars($template['name']); ?>
        </div>
        <div>
            <label>Screen Size:</label>
            <select id="screen-size">
                <option value="100%">Desktop (Full Width)</option>
                <option value="1440px">Desktop (1440px)</option>
                <option value="1024px">Laptop (1024px)</option>
                <option value="768px">Tablet (768px)</option>
                <option value="425px">Mobile L (425px)</option>
                <option value="375px">Mobile M (375px)</option>
                <option value="320px">Mobile S (320px)</option>
            </select>
            <button onclick="window.close()" class="btn btn-sm btn-light">Close Preview</button>
        </div>
    </div>

    <div class="preview-container">
        <iframe id="preview-frame" class="preview-frame" srcdoc="<?php 
            echo htmlspecialchars('<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"><link href="css/bootstrap.min.css" rel="stylesheet"></head><body>' . $previewContent . '</body></html>'); 
        ?>"></iframe>
        <div class="editor-panel">
            <h4>Edit Preview Content</h4>
            <div class="mb-3">
                <label for="title">Page Title</label>
                <input type="text" id="title" class="form-control" value="<?php echo htmlspecialchars($previewData['title']); ?>">
            </div>
            <div class="mb-3">
                <label for="content">Main Content</label>
                <textarea id="content" class="form-control"><?php echo htmlspecialchars($previewData['content']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="sidebar">Sidebar Content</label>
                <textarea id="sidebar" class="form-control"><?php echo htmlspecialchars($previewData['sidebar']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="header">Header Content</label>
                <textarea id="header" class="form-control"><?php echo htmlspecialchars($previewData['header']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="footer">Footer Content</label>
                <textarea id="footer" class="form-control"><?php echo htmlspecialchars($previewData['footer']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="navigation">Navigation Content</label>
                <textarea id="navigation" class="form-control"><?php echo htmlspecialchars($previewData['navigation']); ?></textarea>
            </div>
        </div>
    </div>

    <script>
        // Store compiled template content for JavaScript access
        window.compiledTemplate = `<?php echo str_replace("`", "'", $compiledContent); ?>`;

        // Screen size change handler
        document.getElementById('screen-size').addEventListener('change', function() {
            document.getElementById('preview-frame').style.width = this.value;
        });
    </script>
    <script src="js/template-preview.js"></script>
</body>
</html>