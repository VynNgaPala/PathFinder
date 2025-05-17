<?php
session_start();
include '../connection.php'; // Ensure this path is correct

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginpage.php"); // Ensure this path is correct
    exit();
}

// --- Configuration for Pagination ---
$results_per_page = 15; // Number of jobs to display per page
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// --- Filtering and Searching ---
$where_clauses = [];
$bind_params = [];
$bind_types = "";

// Search by job title, description, or employer name
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_term)) {
    $search_like = "%" . $search_term . "%";
    $where_clauses[] = "(j.title LIKE ? OR j.description LIKE ? OR u.fullname LIKE ?)";
    $bind_params[] = $search_like;
    $bind_params[] = $search_like;
    $bind_params[] = $search_like;
    $bind_types .= "sss";
}

// Filter by location
$filter_location = isset($_GET['location']) ? trim($_GET['location']) : '';
if (!empty($filter_location)) {
    $location_like = "%" . $filter_location . "%";
    $where_clauses[] = "j.location LIKE ?";
    $bind_params[] = $location_like;
    $bind_types .= "s";
}

$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// --- Fetch Total Number of Jobs (for pagination) ---
$total_jobs_sql = "SELECT COUNT(j.id) as total
                   FROM jobs j
                   JOIN users u ON j.employer_id = u.id
                   $sql_where";

$stmt_total = $conn->prepare($total_jobs_sql);
if ($stmt_total) {
    if (!empty($bind_params)) {
        $stmt_total->bind_param($bind_types, ...$bind_params);
    }
    $stmt_total->execute();
    $total_result = $stmt_total->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_jobs = $total_row['total'] ?? 0;
    $total_pages = ceil($total_jobs / $results_per_page);
    $stmt_total->close();
} else {
    error_log("Prepare failed for total jobs count (admin view_jobs): " . $conn->error);
    $total_jobs = 0;
    $total_pages = 0;
}

// --- Fetch Jobs for the current page ---
$jobs = [];
$jobs_sql = "SELECT j.id, j.title, j.description, j.location, j.salary, j.created_at, u.fullname AS employer_name
             FROM jobs j
             JOIN users u ON j.employer_id = u.id
             $sql_where
             ORDER BY j.created_at DESC
             LIMIT ? OFFSET ?";

$stmt_jobs = $conn->prepare($jobs_sql);
if ($stmt_jobs) {
    $current_bind_types = $bind_types . "ii"; // Add types for LIMIT and OFFSET
    $current_bind_params = array_merge($bind_params, [$results_per_page, $offset]);

    $stmt_jobs->bind_param($current_bind_types, ...$current_bind_params);
    $stmt_jobs->execute();
    $result_jobs = $stmt_jobs->get_result();
    while ($row = $result_jobs->fetch_assoc()) {
        $jobs[] = $row;
    }
    $stmt_jobs->close();
} else {
    error_log("Prepare failed for fetching jobs (admin view_jobs): " . $conn->error);
    $_SESSION['error_message'] = "Could not retrieve job listings.";
}

// For filter dropdowns - fetch distinct locations
$distinct_locations = [];
$loc_result = $conn->query("SELECT DISTINCT location FROM jobs WHERE location IS NOT NULL AND location != '' ORDER BY location ASC");
if ($loc_result) {
    while ($row = $loc_result->fetch_assoc()) {
        $distinct_locations[] = $row['location'];
    }
}

// Handle session messages for success/error after actions (e.g., delete)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null; // This might be overwritten by fetch error, handle carefully
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View All Job Posts - Pathfinder Admin</title>
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
        .description-truncate {
            max-width: 250px; /* Adjust as needed */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block; /* Important for ellipsis to work with max-width */
        }
        .filter-form .form-control, .filter-form .form-select {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .action-buttons .btn { margin-right: 5px; }
        .action-buttons .btn i { margin-right: 3px; }
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
                    <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="bi bi-people-fill"></i> Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="view_jobs.php"><i class="bi bi-briefcase-fill"></i> View Jobs</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_all_applications.php"><i class="bi bi-card-checklist"></i> All Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="page-header"><i class="bi bi-briefcase-fill"></i> All Job Posts (<?= $total_jobs ?>)</h3>
            <a href="dashboardadmin.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Back to Dashboard</a>
        </div>

        <form method="GET" action="view_jobs.php" class="filter-form mb-4 p-3 bg-light border rounded">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search (Title, Desc, Employer):</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" value="<?= htmlspecialchars($search_term) ?>" placeholder="e.g. Developer, Marketing, ACME Corp">
                </div>
                <div class="col-md-3">
                    <label for="location" class="form-label">Filter by Location:</label>
                    <input type="text" class="form-control form-control-sm" id="location" name="location" value="<?= htmlspecialchars($filter_location) ?>" placeholder="e.g. Manila" list="location_options">
                    <datalist id="location_options">
                        <?php foreach ($distinct_locations as $loc): ?>
                            <option value="<?= htmlspecialchars($loc) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                </div>
                <div class="col-md-2">
                     <a href="view_jobs.php" class="btn btn-outline-secondary btn-sm w-100"><i class="bi bi-x-lg"></i> Clear</a>
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
                        <th>Title</th>
                        <th>Employer</th>
                        <th style="min-width: 200px;">Description</th>
                        <th>Location</th>
                        <th>Salary</th>
                        <th>Posted On</th>
                        <th style="min-width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($jobs) > 0): ?>
                        <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td><?= $job['id'] ?></td>
                            <td><?= htmlspecialchars($job['title']) ?></td>
                            <td><?= htmlspecialchars($job['employer_name']) ?></td>
                            <td><span class="description-truncate" title="<?= htmlspecialchars($job['description']) ?>"><?= htmlspecialchars($job['description']) ?></span></td>
                            <td><?= htmlspecialchars($job['location']) ?></td>
                            <td><?= htmlspecialchars($job['salary'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars(date("M j, Y, g:i a", strtotime($job['created_at']))) ?></td>
                            <td class="action-buttons">
                                <a href="../view_job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-info" target="_blank" title="View Public Page">
                                    <i class="bi bi-eye-fill"></i> View
                                </a>
                                <a href="delete_job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this job post? This action cannot be undone and might affect existing applications.');">
                                    <i class="bi bi-trash3-fill"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No job posts found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                    // Build query string for pagination links, preserving filters
                    $query_params = [];
                    if (!empty($filter_location)) $query_params['location'] = $filter_location;
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
