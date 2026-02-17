<?php
/**
 * About View Page
 * Displays about page content from database
 */

// Define page title if not set
$page_title = $page_title ?? "About Content";
?>

<div class="page-header">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="page-title"><?php echo $page_title; ?></h3>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">About</li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">About List</h4>
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success mt-2"><?php echo htmlspecialchars($_GET['msg']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger mt-2"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <a href="<?php echo APP_URL; ?>admin/about/create" class="btn btn-primary float-end">Add New</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="datatable table table-stripped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Image</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($about_items)) {
                                foreach ($about_items as $row) {
                            ?>
                            <tr>
                                <td><?php echo (int)$row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['content'], 0, 100)) . '...'; ?></td>
                                <td>
                                    <?php if(!empty($row['image'])): ?>
                                        <img src="<?php echo APP_URL; ?>upload/<?php echo htmlspecialchars($row['image']); ?>" alt="About image" height="50px" width="50px">
                                    <?php else: ?>
                                        <span class="text-muted">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>admin/about/edit/<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="post" action="<?php echo APP_URL; ?>admin/about/delete/<?php echo (int)$row['id']; ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this about content?');">
                                        <?php echo getCsrfField(); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No content found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
