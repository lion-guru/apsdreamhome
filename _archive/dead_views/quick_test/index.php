<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .colony { padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
        .colony h3 { margin: 0 0 10px 0; color: #2c3e50; }
        .colony p { margin: 5px 0; color: #666; }
        .empty { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1><?php echo $page_title; ?></h1>
    <p>This is a quick test controller to demonstrate file operations and controller functionality.</p>
    
    <?php if (empty($colonies)): ?>
        <div class="colony empty">
            <h3>No colonies found</h3>
            <p>There are currently no colonies in the database.</p>
        </div>
    <?php else: ?>
        <?php foreach ($colonies as $colony): ?>
            <div class="colony">
                <h3><?php echo $colony['name']; ?></h3>
                <p><strong>ID:</strong> <?php echo $colony['id']; ?></p>
                <p><strong>District:</strong> <?php echo $colony['district_name'] ?? 'N/A'; ?></p>
                <p><strong>State:</strong> <?php echo $colony['state_name'] ?? 'N/A'; ?></p>
                <p><strong>Slug:</strong> <?php echo $colony['slug']; ?></p>
                <p><strong>Status:</strong> <?php echo $colony['is_active'] ? 'Active' : 'Inactive'; ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <hr>
    <p><em>This demonstrates the full MVC workflow: Controller → View</em></p>
</body>
</html>
