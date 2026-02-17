<?php
require_once __DIR__ . '/core/init.php';

// Initialize the database connection
$db = \App\Core\App::database();
$layoutTemplate = new Admin\Models\LayoutTemplate($db->getConnection());

// Get template ID from URL if editing existing template
$templateId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$template = null;

if ($templateId) {
    $template = $layoutTemplate->getById($templateId);
}

// Get all available components
$components = $layoutTemplate->getAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'content' => $_POST['content'],
        'is_active' => isset($_POST['is_active']),
        'user_id' => getAuthUserId()
    ];

    if ($templateId) {
        $layoutTemplate->update($templateId, $data);
    } else {
        $layoutTemplate->create($data);
    }

    header('Location: template-list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Builder</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/template-builder.css" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Components</h5>
                    </div>
                    <div class="card-body">
                        <div id="components-list" class="list-group">
                            <div class="component-item" data-type="header">Header</div>
                            <div class="component-item" data-type="navigation">Navigation</div>
                            <div class="component-item" data-type="content">Content Area</div>
                            <div class="component-item" data-type="sidebar">Sidebar</div>
                            <div class="component-item" data-type="footer">Footer</div>
                            <?php foreach ($components as $component): ?>
                                <?php if ($component['id'] != $templateId): ?>
                                    <div class="component-item" data-type="custom" data-id="<?php echo $component['id']; ?>">
                                        <?php echo h($component['name']); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Canvas</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary" id="preview-btn">Preview</button>
                            <button class="btn btn-sm btn-outline-secondary" id="responsive-preview">Responsive</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="template-form" method="POST">
                            <div class="form-group">
                                <input type="text" class="form-control" name="name" placeholder="Template Name"
                                       value="<?php echo $template ? h($template['name']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" name="description" placeholder="Template Description"><?php
                                    echo $template ? h($template['description']) : '';
                                ?></textarea>
                            </div>
                            <div id="canvas" class="border p-3 min-vh-50">
                                <?php echo $template ? $template['content'] : ''; ?>
                            </div>
                            <input type="hidden" name="content" id="template-content">
                            <div class="form-check mt-3">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is-active"
                                       <?php echo ($template && $template['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is-active">Active</label>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Save Template</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Properties</h5>
                    </div>
                    <div class="card-body">
                        <div id="properties-panel">
                            <p class="text-muted">Select a component to edit its properties</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Make components draggable
            $('.component-item').draggable({
                helper: 'clone',
                revert: 'invalid'
            });

            // Make canvas droppable
            $('#canvas').droppable({
                accept: '.component-item',
                drop: function(event, ui) {
                    const componentType = ui.helper.data('type');
                    const componentId = ui.helper.data('id');
                    addComponent(componentType, componentId);
                }
            });

            // Handle form submission
            $('#template-form').on('submit', function() {
                $('#template-content').val($('#canvas').html());
            });

            // Preview button click handler
            $('#preview-btn').click(function() {
                const content = $('#canvas').html();
                const previewWindow = window.open('', '_blank');
                previewWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Template Preview</title>
                        <link href="css/bootstrap.min.css" rel="stylesheet">
                    </head>
                    <body>
                        ${content}
                    </body>
                    </html>
                `);
            });

            // Responsive preview handler
            $('#responsive-preview').click(function() {
                // Implementation for responsive preview
            });
        });

        function addComponent(type, id) {
            let component = '';
            switch(type) {
                case 'header':
                    component = '<header class="template-component" data-type="header"><h1>Header</h1></header>';
                    break;
                case 'navigation':
                    component = '<nav class="template-component" data-type="navigation"><ul><li>Home</li><li>About</li></ul></nav>';
                    break;
                case 'content':
                    component = '<main class="template-component" data-type="content"><p>Content Area</p></main>';
                    break;
                case 'sidebar':
                    component = '<aside class="template-component" data-type="sidebar"><p>Sidebar</p></aside>';
                    break;
                case 'footer':
                    component = '<footer class="template-component" data-type="footer"><p>Footer</p></footer>';
                    break;
                case 'custom':
                    // Load custom component content via AJAX
                    $.get('ajax/get-component.php', { id: id }, function(response) {
                        $('#canvas').append(response);
                    });
                    return;
            }
            $('#canvas').append(component);
            makeComponentEditable();
        }

        function makeComponentEditable() {
            $('.template-component').draggable({
                containment: '#canvas',
                handle: '.component-handle'
            }).each(function() {
                if (!$(this).find('.component-handle').length) {
                    $(this).prepend('<div class="component-handle">Drag</div>');
                }
            });
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
