<?php
// Start session and check admin authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Include database connection
require_once '../includes/db_connection.php';

// Handle testimonial actions (approve/reject/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    
    try {
        $conn = getDbConnection();
        
        switch ($action) {
            case 'approve':
                $stmt = $conn->prepare("UPDATE testimonials SET status = 'approved' WHERE id = ?");
                break;
            case 'reject':
                $stmt = $conn->prepare("UPDATE testimonials SET status = 'rejected' WHERE id = ?");
                break;
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
                break;
            default:
                throw new Exception('Invalid action');
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        $_SESSION['success'] = "Testimonial {$action}d successfully";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all testimonials
$testimonials = [];
try {
    $conn = getDbConnection();
    $result = $conn->query("SELECT 
        id,
        client_name as name,
        email,
        rating,
        testimonial,
        status,
        created_at 
    FROM testimonials 
    ORDER BY created_at DESC");
    
    if ($result) {
        $testimonials = [];
        while ($row = $result->fetch_assoc()) {
            // Ensure all required fields exist with default values
            $testimonials[] = array_merge([
                'id' => 0,
                'name' => 'Unknown',
                'email' => '',
                'rating' => 5,
                'testimonial' => '',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ], $row);
        }
    }
} catch (Exception $e) {
    $error = "Error fetching testimonials: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .testimonial-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .testimonial-card.approved { border-left-color: #198754; }
        .testimonial-card.rejected { border-left-color: #dc3545; }
        .testimonial-card.pending { border-left-color: #ffc107; }
        .rating {
            color: #ffc107;
            margin: 0.5rem 0;
        }
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Include your admin header/navigation here -->
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Manage Testimonials</h1>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($testimonials)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                                <p class="h5">No testimonials found</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Rating</th>
                                            <th>Testimonial</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($testimonials as $testimonial): ?>
                                            <tr class="align-middle">
                                                <td><?php echo htmlspecialchars($testimonial['id']); ?></td>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($testimonial['name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($testimonial['email']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?= $i > $testimonial['rating'] ? '-o' : '' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </td>
                                                <td class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($testimonial['testimonial']); ?>">
                                                    <?php echo htmlspecialchars(substr($testimonial['testimonial'], 0, 100)); ?><?php echo strlen($testimonial['testimonial']) > 100 ? '...' : ''; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo 
                                                        $testimonial['status'] === 'approved' ? 'success' : 
                                                        ($testimonial['status'] === 'rejected' ? 'danger' : 'warning');
                                                    ?>">
                                                        <?php echo ucfirst($testimonial['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($testimonial['status'] !== 'approved'): ?>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Approve this testimonial?')">
                                                                <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                                                <input type="hidden" name="action" value="approve">
                                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($testimonial['status'] !== 'rejected'): ?>
                                                            <form method="POST" class="d-inline" onsubmit="return confirm('Reject this testimonial?')">
                                                                <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this testimonial? This cannot be undone.')">
                                                            <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-primary view-testimonial" 
                                                                data-bs-toggle="modal" data-bs-target="#testimonialModal"
                                                                data-name="<?php echo htmlspecialchars($testimonial['name']); ?>"
                                                                data-email="<?php echo htmlspecialchars($testimonial['email']); ?>"
                                                                data-rating="<?php echo $testimonial['rating']; ?>"
                                                                data-testimonial="<?php echo htmlspecialchars($testimonial['testimonial']); ?>"
                                                                data-date="<?php echo date('F j, Y', strtotime($testimonial['created_at'])); ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonial View Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Testimonial Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Name:</strong> <span id="modalName"></span></p>
                            <p class="mb-1"><strong>Email:</strong> <span id="modalEmail"></span></p>
                            <p class="mb-1"><strong>Date:</strong> <span id="modalDate"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Rating:</strong></p>
                            <div class="rating mb-3" id="modalRating"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Testimonial:</strong>
                        <p id="modalTestimonial" class="mt-2 p-3 bg-light rounded"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle view testimonial modal
        document.querySelectorAll('.view-testimonial').forEach(button => {
            button.addEventListener('click', function() {
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');
                const rating = parseInt(this.getAttribute('data-rating'));
                const testimonial = this.getAttribute('data-testimonial');
                const date = this.getAttribute('data-date');
                
                document.getElementById('modalName').textContent = name;
                document.getElementById('modalEmail').textContent = email;
                document.getElementById('modalDate').textContent = date;
                document.getElementById('modalTestimonial').textContent = testimonial;
                
                // Update rating stars
                const ratingEl = document.getElementById('modalRating');
                ratingEl.innerHTML = '';
                for (let i = 1; i <= 5; i++) {
                    const star = document.createElement('i');
                    star.className = `fas fa-star${i > rating ? '-o' : ''} me-1`;
                    star.style.color = i <= rating ? '#ffc107' : '';
                    ratingEl.appendChild(star);
                }
            });
        });
    </script>
</body>
</html>
