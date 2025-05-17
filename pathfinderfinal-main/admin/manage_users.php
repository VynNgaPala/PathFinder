<?php
session_start();
include '../connection.php'; // Ensure this path is correct

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginpage.php"); // Ensure this path is correct
    exit();
}

// --- Configuration for Pagination ---
$results_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// --- Filtering and Searching ---
$where_clauses = [];
$bind_params = [];
$bind_types = "";

// Search by fullname or email
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_term)) {
    $search_like = "%" . $search_term . "%";
    $where_clauses[] = "(fullname LIKE ? OR email LIKE ?)";
    $bind_params[] = $search_like;
    $bind_params[] = $search_like;
    $bind_types .= "ss";
}

// Filter by role
$filter_role = isset($_GET['role']) ? trim($_GET['role']) : '';
if (!empty($filter_role) && $filter_role !== 'all') {
    $where_clauses[] = "role = ?";
    $bind_params[] = $filter_role;
    $bind_types .= "s";
}

$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// --- Fetch Total Number of Users (for pagination) ---
$total_users_sql = "SELECT COUNT(id) as total FROM users $sql_where";
$stmt_total = $conn->prepare($total_users_sql);
if ($stmt_total) {
    if (!empty($bind_params)) {
        $stmt_total->bind_param($bind_types, ...$bind_params);
    }
    $stmt_total->execute();
    $total_result = $stmt_total->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_users = $total_row['total'] ?? 0;
    $total_pages = ceil($total_users / $results_per_page);
    $stmt_total->close();
} else {
    error_log("Prepare failed for total users count (admin manage_users): " . $conn->error);
    $total_users = 0;
    $total_pages = 0;
}

// --- Fetch Users for the current page ---
$users = [];
$users_sql = "SELECT id, fullname, email, role, created_at FROM users 
              $sql_where
              ORDER BY created_at DESC
              LIMIT ? OFFSET ?";

$stmt_users = $conn->prepare($users_sql);
if ($stmt_users) {
    $current_bind_types = $bind_types . "ii"; // Add types for LIMIT and OFFSET
    $current_bind_params = array_merge($bind_params, [$results_per_page, $offset]);

    $stmt_users->bind_param($current_bind_types, ...$current_bind_params);
    $stmt_users->execute();
    $result_users = $stmt_users->get_result();
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt_users->close();
} else {
    error_log("Prepare failed for fetching users (admin manage_users): " . $conn->error);
    $_SESSION['error_message'] = "Could not retrieve user list.";
}

$role_options = ['all', 'admin', 'client', 'employer']; // Available roles for filtering

// Handle session messages for success/error after actions (e.g., delete)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Users - Pathfinder Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .navbar-brand { font-weight: bold; }
        .navbar-brand i { margin-right: 5px; }
        .page-header {
            color: #333;
            font-weight: 700;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        .table-responsive { margin-top: 1.5rem; }
        .table th { white-space: nowrap; }
        .table td { vertical-align: middle; }
        .filter-form .form-control, .filter-form .form-select {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem; /* For smaller screens */
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .action-buttons .btn { margin-right: 5px; }
        .action-buttons .btn i { margin-right: 3px; }
        .role-badge {
            font-size: 0.85em;
            padding: 0.4em 0.65em;
            text-transform: capitalize;
        }
        .footer {
            padding: 2rem 0;
            background-color: #e9ecef;
            color: #6c757d;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboardadmin.php"><i class="bi bi-shield-lock-fill"></i> Pathfinder Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboardadmin.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="manage_users.php"><i class="bi bi-people-fill"></i> Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_jobs.php"><i class="bi bi-briefcase-fill"></i> View Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_all_applications.php"><i class="bi bi-card-checklist"></i> All Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="page-header"><i class="bi bi-people-fill"></i> Manage Users (<?= $total_users ?>)</h3>
            <a href="dashboardadmin.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Back to Dashboard</a>
            </div>

        <form method="GET" action="manage_users.php" class="filter-form mb-4 p-3 bg-light border rounded">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search Name/Email:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="<?= htmlspecialchars($search_term) ?>" placeholder="e.g. John Doe or john@example.com">
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Filter by Role:</label>
                    <select class="form-select form-select-sm" id="role" name="role">
                        <?php foreach ($role_options as $role_opt): ?>
                            <option value="<?= htmlspecialchars(strtolower($role_opt)) ?>" <?= ($filter_role === strtolower($role_opt)) ? 'selected' : '' ?>>
                                <?= ($role_opt === 'all') ? 'All Roles' : htmlspecialchars(ucfirst($role_opt)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                </div>
                 <div class="col-md-2">
                     <a href="manage_users.php" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-x-lg"></i> Clear</a>
                </div>
            </div>
        </form>

        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive shadow-sm bg-white rounded p-3">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered On</th>
                        <th style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $role_badge_class = 'bg-secondary'; // Default
                                switch (strtolower($user['role'])) {
                                    case 'admin': $role_badge_class = 'bg-danger'; break;
                                    case 'employer': $role_badge_class = 'bg-info text-dark'; break;
                                    case 'client': $role_badge_class = 'bg-success'; break;
                                }
                            ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['fullname']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge rounded-pill <?= $role_badge_class ?> role-badge"><?= htmlspecialchars($user['role']) ?></span></td>
                            <td><?= htmlspecialchars(date("M j, Y, g:i a", strtotime($user['created_at']))) ?></td>
                            <td class="action-buttons">
                                <?php if ($_SESSION['user'] != $user['id']): // Prevent admin from deleting themselves ?>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this user? This action might be irreversible and could affect related data like jobs or applications.');" title="Delete User">
                                    <i class="bi bi-trash3-fill"></i> Delete
                                </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-danger" disabled title="Cannot delete yourself"><i class="bi bi-trash3-fill"></i> Delete</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No users found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                    $query_params = [];
                    if (!empty($filter_role) && $filter_role !== 'all') $query_params['role'] = $filter_role;
                    if (!empty($search_term)) $query_params['search'] = $search_term;
                    $query_string = http_build_query($query_params);
                ?>
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page - 1 ?>&<?= $query_string ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= $query_string ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page + 1 ?>&<?= $query_string ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p class="mb-0">&copy; <?= date('Y') ?> PathFinder Admin. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
