<?php
require_once __DIR__ . '/core/init.php';

// Check if user is logged in and has required privileges
adminAccessControl(['Company Owner', 'super_admin', 'superadmin']);

$admin_id = getAuthUserId();
$db = \App\Core\App::database();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }

    switch ($_POST['action']) {
        case 'add_builder':
            try {
                $db->insert('builders', [
                    'name' => $_POST['builder_name'],
                    'email' => $_POST['builder_email'],
                    'mobile' => $_POST['builder_mobile'],
                    'address' => $_POST['builder_address'],
                    'license_number' => $_POST['license_number'],
                    'experience_years' => $_POST['experience_years'],
                    'specialization' => $_POST['specialization'],
                    'rating' => $_POST['rating'],
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $message = "Builder added successfully!";
            } catch (Exception $e) {
                $error = "Error adding builder: " . $e->getMessage();
            }
            break;

        case 'add_project':
            try {
                $db->insert('construction_projects', [
                    'project_name' => $_POST['project_name'],
                    'builder_id' => $_POST['builder_id'],
                    'site_id' => $_POST['site_id'],
                    'project_type' => $_POST['project_type'],
                    'start_date' => $_POST['start_date'],
                    'estimated_completion' => $_POST['estimated_completion'],
                    'budget_allocated' => $_POST['budget_allocated'],
                    'status' => 'planning',
                    'description' => $_POST['description'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $message = "Construction project created successfully!";
            } catch (Exception $e) {
                $error = "Error creating project: " . $e->getMessage();
            }
            break;

        case 'update_progress':
            try {
                $db->insert('project_progress', [
                    'project_id' => $_POST['project_id'],
                    'progress_percentage' => $_POST['progress_percentage'],
                    'milestone_achieved' => $_POST['milestone_achieved'],
                    'work_description' => $_POST['work_description'],
                    'amount_spent' => $_POST['amount_spent'],
                    'next_milestone' => $_POST['next_milestone'],
                    'updated_by' => $admin_id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $db->update('construction_projects',
                    ['progress_percentage' => $_POST['progress_percentage'], 'last_updated' => date('Y-m-d H:i:s')],
                    'id = ?',
                    [$_POST['project_id']]
                );

                $message = "Project progress updated successfully!";
            } catch (Exception $e) {
                $error = "Error updating progress: " . $e->getMessage();
            }
            break;

        case 'process_payment':
            try {
                $db->insert('builder_payments', [
                    'project_id' => $_POST['project_id'],
                    'builder_id' => $_POST['builder_id'],
                    'payment_amount' => $_POST['payment_amount'],
                    'payment_type' => $_POST['payment_type'],
                    'payment_date' => $_POST['payment_date'],
                    'payment_method' => $_POST['payment_method'],
                    'description' => $_POST['payment_description'],
                    'paid_by' => $admin_id,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $message = "Builder payment processed successfully!";
            } catch (Exception $e) {
                $error = "Error processing payment: " . $e->getMessage();
            }
            break;
    }
}

$builders = $db->fetchAll("SELECT * FROM builders ORDER BY name");
$projects = $db->fetchAll("SELECT cp.*, b.name as builder_name, s.site_name FROM construction_projects cp LEFT JOIN builders b ON cp.builder_id = b.id LEFT JOIN sites s ON cp.site_id = s.id ORDER BY cp.created_at DESC");
$sites = $db->fetchAll("SELECT * FROM sites ORDER BY site_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .table-custom {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .table-custom thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .modal-content {
            border-radius: 20px;
            border: none;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e6ed;
            padding: 0.75rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 25px;
        }
        .progress {
            height: 20px;
            border-radius: 10px;
        }
        .progress-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="dashboard-container">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2><i class="fas fa-hard-hat me-2"></i>Builder Management Dashboard</h2>
                            <div>
                                <button class="btn btn-custom me-2" data-bs-toggle="modal" data-bs-target="#addBuilderModal">
                                    <i class="fas fa-plus me-1"></i>Add Builder
                                </button>
                                <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                    <i class="fas fa-plus me-1"></i>New Project
                                </button>
                            </div>
                        </div>

                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stats-card text-center">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <h4><?php echo $builders_result->num_rows; ?></h4>
                                    <p>Total Builders</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card text-center">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <h4><?php echo $projects_result->num_rows; ?></h4>
                                    <p>Active Projects</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card text-center">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <?php
                                    $total_budget = $db->fetch("SELECT SUM(budget_allocated) as total FROM construction_projects")['total'] ?? 0;
                                    ?>
                                    <h4>₹<?php echo number_format($total_budget); ?></h4>
                                    <p>Total Budget</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card text-center">
                                    <i class="fas fa-tasks fa-2x mb-2"></i>
                                    <?php
                                    $in_progress_count = $db->fetch("SELECT COUNT(*) as count FROM construction_projects WHERE status = 'in_progress'")['count'] ?? 0;
                                    ?>
                                    <h4><?php echo $in_progress_count; ?></h4>
                                    <p>In Progress</p>
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pills-builders-tab" data-bs-toggle="pill" data-bs-target="#pills-builders" type="button" role="tab">
                                    <i class="fas fa-users me-1"></i>Builders
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-projects-tab" data-bs-toggle="pill" data-bs-target="#pills-projects" type="button" role="tab">
                                    <i class="fas fa-building me-1"></i>Projects
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-builders" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>License</th>
                                                <th>Experience</th>
                                                <th>Specialization</th>
                                                <th>Rating</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($builders as $builder): ?>
                                            <tr>
                                                <td><strong><?php echo h($builder['name']); ?></strong></td>
                                                <td>
                                                    <?php echo h($builder['email']); ?><br>
                                                    <small class="text-muted"><?php echo h($builder['mobile']); ?></small>
                                                </td>
                                                <td><?php echo h($builder['license_number']); ?></td>
                                                <td><?php echo $builder['experience_years']; ?> years</td>
                                                <td><?php echo h($builder['specialization']); ?></td>
                                                <td>
                                                    <span class="me-1"><?php echo $builder['rating']; ?></span>
                                                    <div class="text-warning d-inline">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?php echo $i <= $builder['rating'] ? '' : '-o'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $builder['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($builder['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="pills-projects" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>Project Name</th>
                                                <th>Builder</th>
                                                <th>Site</th>
                                                <th>Type</th>
                                                <th>Progress</th>
                                                <th>Budget</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td><strong><?php echo h($project['project_name']); ?></strong></td>
                                                <td><?php echo h($project['builder_name']); ?></td>
                                                <td><?php echo h($project['site_name']); ?></td>
                                                <td><?php echo ucfirst($project['project_type']); ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $project['progress_percentage']; ?>%" aria-valuenow="<?php echo $project['progress_percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                            <?php echo $project['progress_percentage']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>₹<?php echo number_format($project['budget_allocated']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $project['status'] === 'completed' ? 'success' :
                                                            ($project['status'] === 'in_progress' ? 'primary' : 'warning');
                                                    ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-success" onclick="updateProgress(<?php echo $project['id']; ?>)" title="Update Progress">
                                                        <i class="fas fa-chart-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="processPayment(<?php echo $project['id']; ?>, <?php echo $project['builder_id']; ?>)" title="Process Payment">
                                                        <i class="fas fa-money-bill"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Builder Modal -->
    <div class="modal fade" id="addBuilderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Builder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add_builder">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Builder Name *</label>
                                <input type="text" name="builder_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" name="builder_email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile *</label>
                                <input type="tel" name="builder_mobile" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">License Number</label>
                                <input type="text" name="license_number" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="builder_address" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Experience (Years)</label>
                                <input type="number" name="experience_years" class="form-control" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Specialization</label>
                                <select name="specialization" class="form-select">
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="industrial">Industrial</option>
                                    <option value="infrastructure">Infrastructure</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select">
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-custom">Add Builder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Construction Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add_project">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Name *</label>
                                <input type="text" name="project_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Builder *</label>
                                <select name="builder_id" class="form-select" required>
                                    <option value="">Select Builder</option>
                                    <?php foreach ($builders as $builder): ?>
                                    <option value="<?php echo $builder['id']; ?>"><?php echo h($builder['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Site *</label>
                                <select name="site_id" class="form-select" required>
                                    <option value="">Select Site</option>
                                    <?php foreach ($sites as $site): ?>
                                    <option value="<?php echo $site['id']; ?>"><?php echo h($site['site_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Type</label>
                                <select name="project_type" class="form-select">
                                    <option value="residential">Residential</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="infrastructure">Infrastructure</option>
                                    <option value="mixed_use">Mixed Use</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimated Completion</label>
                                <input type="date" name="estimated_completion" class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Budget Allocated</label>
                                <input type="number" name="budget_allocated" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Project Description</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-custom">Create Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateProgress(projectId) {
            // Add progress update functionality
            alert('Progress update feature coming soon for project ' + projectId);
        }

        function processPayment(projectId, builderId) {
            // Add payment processing functionality
            alert('Payment processing feature coming soon for project ' + projectId);
        }
    </script>
</body>
</html>
