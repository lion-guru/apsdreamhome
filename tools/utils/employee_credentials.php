<?php
/**
 * Employee Credentials Viewer - APS Dream Homes
 * Display all employee login credentials for testing
 */

require_once 'includes/config.php';

require_once dirname(__DIR__, 2) . '/app/helpers.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Employee Credentials - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .credentials-container { max-width: 1000px; margin: 20px auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .credentials-section { background: white; border-radius: 15px; padding: 25px; margin: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .credential-card { background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 15px 0; border-left: 5px solid #6366f1; }
        .password-field { font-family: monospace; background: #e3f2fd; padding: 8px 12px; border-radius: 6px; font-weight: bold; }
        .status-active { color: #10b981; }
        .status-inactive { color: #ef4444; }
        .role-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .role-employee { background: #ddd6fe; color: #6b21a8; }
        .role-manager { background: #fef3c7; color: #92400e; }
        .role-supervisor { background: #dbeafe; color: #1e40af; }
        .role-executive { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <div class='credentials-container'>
        <div class='text-center mb-4'>
            <h1><i class='fas fa-key me-2'></i>Employee Login Credentials</h1>
            <p class='lead'>APS Dream Homes - Employee Access Information</p>
            <div class='badge bg-warning fs-6'>For Testing Purposes Only</div>
        </div>";

echo "<div class='credentials-section'>
    <h3><i class='fas fa-users me-2'></i>All Employee Credentials</h3>
    <p>Below are all employee login credentials for testing purposes:</p>";

try {
    $result = $conn->query("SELECT id, name, email, department, role, status, created_at FROM employees ORDER BY created_at DESC");

    if ($result->num_rows > 0) {
        echo "<div class='table-responsive'>
            <table class='table table-striped table-hover'>
                <thead class='table-dark'>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>";

        $count = 1;
        while ($employee = $result->fetch_assoc()) {
            $status_class = $employee['status'] === 'active' ? 'status-active' : 'status-inactive';
            $role_class = 'role-' . $employee['role'];

            echo "<tr>
                <td><strong>$count</strong></td>
                <td>" . h($employee['name']) . "</td>
                <td>" . h($employee['email']) . "</td>
                <td><span class='password-field'>Employee123!</span></td>
                <td>" . h($employee['department'] ?? 'General') . "</td>
                <td><span class='role-badge $role_class'>" . h(ucfirst($employee['role'])) . "</span></td>
                <td><span class='$status_class'><i class='fas fa-circle me-1'></i>" . h(ucfirst($employee['status'])) . "</span></td>
                <td>" . h(date('M j, Y', strtotime($employee['created_at']))) . "</td>
            </tr>";

            $count++;
        }

        echo "</tbody>
            </table>
        </div>";

        // Show summary
        $total_employees = $result->num_rows;
        $result->data_seek(0);
        $active_count = 0;
        while ($employee = $result->fetch_assoc()) {
            if ($employee['status'] === 'active') {
                $active_count++;
            }
        }

        echo "<div class='row mt-4'>
            <div class='col-md-4'>
                <div class='card text-center'>
                    <div class='card-body'>
                        <h5 class='card-title text-primary'>$total_employees</h5>
                        <p class='card-text'>Total Employees</p>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-center'>
                    <div class='card-body'>
                        <h5 class='card-title text-success'>$active_count</h5>
                        <p class='card-text'>Active Employees</p>
                    </div>
                </div>
            </div>
            <div class='col-md-4'>
                <div class='card text-center'>
                    <div class='card-body'>
                        <h5 class='card-title text-info'>Employee123!</h5>
                        <p class='card-text'>Default Password</p>
                    </div>
                </div>
            </div>
        </div>";

    } else {
        echo "<div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            No employees found in the database. Please run the employee setup script first.
        </div>";
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-times-circle me-2'></i>
        Error fetching employee credentials: " . $e->getMessage() . "
    </div>";
}

echo "</div>";

// Quick login section
echo "<div class='credentials-section'>
    <h3><i class='fas fa-sign-in-alt me-2'></i>Quick Login Access</h3>

    <div class='row'>
        <div class='col-md-6'>
            <div class='card'>
                <div class='card-body'>
                    <h5><i class='fas fa-user me-2'></i>Employee Login</h5>
                    <p>Use any employee credentials above to login:</p>
                    <a href='employee_login.php' class='btn btn-primary w-100'>
                        <i class='fas fa-sign-in-alt me-2'></i>Go to Employee Login
                    </a>
                </div>
            </div>
        </div>
        <div class='col-md-6'>
            <div class='card'>
                <div class='card-body'>
                    <h5><i class='fas fa-user-shield me-2'></i>Admin Login</h5>
                    <p>Access administrative controls:</p>
                    <a href='admin/' class='btn btn-warning w-100'>
                        <i class='fas fa-cog me-2'></i>Go to Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>";

// Password information
echo "<div class='credentials-section'>
    <h3><i class='fas fa-info-circle me-2'></i>Password Information</h3>

    <div class='alert alert-info'>
        <h5><i class='fas fa-key me-2'></i>Default Password Policy</h5>
        <ul class='mb-0'>
            <li><strong>Default Password:</strong> <code>Employee123!</code> for all employees</li>
            <li><strong>Case Sensitive:</strong> Use exact capitalization</li>
            <li><strong>Special Character:</strong> Includes exclamation mark (!)</li>
            <li><strong>Security:</strong> Employees should change password after first login</li>
            <li><strong>Format:</strong> Mix of letters, numbers, and special characters</li>
        </ul>
    </div>

    <div class='alert alert-warning'>
        <h5><i class='fas fa-exclamation-triangle me-2'></i>Security Notice</h5>
        <p class='mb-0'>This is a testing environment. In production, employees should have unique, secure passwords and be required to change them on first login.</p>
    </div>
</div>";

// Create sample employee if needed
echo "<div class='credentials-section'>
    <h3><i class='fas fa-plus me-2'></i>Create Sample Employee</h3>

    <div class='card'>
        <div class='card-body'>
            <p>If no employees exist, create a sample employee for testing:</p>
            <button class='btn btn-success' onclick='createSampleEmployee()'>
                <i class='fas fa-user-plus me-2'></i>Create Sample Employee
            </button>
            <div id='creation-result'></div>
        </div>
    </div>
</div>";

echo "<script>
function createSampleEmployee() {
    const resultDiv = document.getElementById('creation-result');
    resultDiv.innerHTML = '<div class=\"alert alert-info\"><i class=\"fas fa-spinner fa-spin me-2\"></i>Creating sample employee...</div>';

    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=create_sample_employee'
    })
    .then(response => response.text())
    .then(data => {
        resultDiv.innerHTML = '<div class=\"alert alert-success\"><i class=\"fas fa-check-circle me-2\"></i>Sample employee created! Refresh page to see credentials.</div>';
        setTimeout(() => location.reload(), 2000);
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class=\"alert alert-danger\"><i class=\"fas fa-times-circle me-2\"></i>Error creating sample employee.</div>';
    });
}
</script>";

// Handle sample employee creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_sample_employee') {
    try {
        $hashed_password = password_hash('Employee123!', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO employees (name, email, password, department, role, status, created_by)
            VALUES (?, ?, ?, ?, ?, 'active', 'credentials_tool')
        ");

        $name = 'Test Employee';
        $email = 'test@apsdreamhome.com';
        $department = 'IT';
        $role = 'employee';

        $stmt->bind_param("sssss", $name, $email, $hashed_password, $department, $role);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>
                <i class='fas fa-check-circle me-2'></i>
                Sample employee created successfully!
            </div>";
        }
        $stmt->close();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>
            <i class='fas fa-times-circle me-2'></i>
            Error creating sample employee: " . $e->getMessage() . "
        </div>";
    }
}

echo "<div class='text-center mt-4'>
    <hr>
    <p class='text-muted'>
        <i class='fas fa-key me-1'></i>
        Employee Credentials Viewer - APS Dream Homes<br>
        <small>For testing and development purposes only</small>
    </p>
</div>

</div>
</body>
</html>";
?>
