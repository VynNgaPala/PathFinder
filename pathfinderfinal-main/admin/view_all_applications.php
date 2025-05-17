<?php
include '../connection.php'; // Make sure this path is correct
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginpage.php"); // Make sure this path is correct
    exit();
}

// --- Configuration for Pagination ---
$results_per_page = 15; // Number of applications to display per page
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// --- Filtering and Searching ---
$where_clauses = [];
$bind_params = [];
$bind_types = "";

// Filter by status
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
if (!empty($filter_status) && $filter_status !== 'all') {
    $where_clauses[] = "app.status = ?";
    $bind_params[] = $filter_status;
    $bind_types .= "s";
}

// Search by job title or applicant name
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_term)) {
    $search_like = "%" . $search_term . "%";
    $where_clauses[] = "(j.title LIKE ? OR applicant.fullname LIKE ?)";
    $bind_params[] = $search_like;
    $bind_params[] = $search_like;
    $bind_types .= "ss";
}

$sql_where = "";
if (!empty($where_clauses)) {
    $sql_where = "WHERE " . implode(" AND ", $where_clauses);
}

// --- Fetch Total Number of Applications (for pagination) ---
$total_applications_sql = "SELECT COUNT(app.id) as total
                           FROM applications app
                           JOIN jobs j ON app.job_id = j.id
                           JOIN users applicant ON app.user_id = applicant.id
                           JOIN users employer ON j.employer_id = employer.id
                           $sql_where";

$stmt_total = $conn->prepare($total_applications_sql);
if ($stmt_total) {
    if (!empty($bind_params)) {
        $stmt_total->bind_param($bind_types, ...$bind_params);
    }
    $stmt_total->execute();
    $total_result = $stmt_total->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_applications = $total_row['total'] ?? 0;
    $total_pages = ceil($total_applications / $results_per_page);
    $stmt_total->close();
} else {
    error_log("Prepare failed for total applications count: " . $conn->error);
    $total_applications = 0;
    $total_pages = 0;
}


// --- Fetch Applications for the current page ---
$applications = [];
$applications_sql = "SELECT app.id as application_id, app.applied_date, app.status,
                            j.title as job_title, j.id as job_id,
                            applicant.fullname as applicant_name, applicant.email as applicant_email,
                            employer.fullname as employer_name
                     FROM applications app
                     JOIN jobs j ON app.job_id = j.id
                     JOIN users applicant ON app.user_id = applicant.id
                     JOIN users employer ON j.employer_id = employer.id
                     $sql_where
                     ORDER BY app.applied_date DESC
                     LIMIT ? OFFSET ?";

$stmt_applications = $conn->prepare($applications_sql);
if ($stmt_applications) {
    $current_bind_types = $bind_types . "ii"; // Add types for LIMIT and OFFSET
    $current_bind_params = array_merge($bind_params, [$results_per_page, $offset]);

    $stmt_applications->bind_param($current_bind_types, ...$current_bind_params);
    $stmt_applications->execute();
    $result_applications = $stmt_applications->get_result();
    while ($row = $result_applications->fetch_assoc()) {
        $applications[] = $row;
    }
    $stmt_applications->close();
} else {
    error_log("Prepare failed for fetching applications: " . $conn->error);
    // Optionally set a user-facing error message
}

$status_options = ['all', 'Pending', 'Under Review', 'Interviewing', 'Shortlisted', 'Hired', 'Rejected', 'Not Selected'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>All Applications - Pathfinder Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; }
        .table-responsive { margin-top: 1.5rem; }
        .filter-form .form-control, .filter-form .form-select {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem; /* For smaller screens */
        }
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .badge { font-size: 0.85em; }
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
                    <li class="nav-item"><a class="nav-link" href="view_jobs.php"><i class="bi bi-briefcase-fill"></i> View Jobs</a></li>
                    <li class="nav-item"><a class="nav-link active" href="view_all_applications.php"><i class="bi bi-card-checklist"></i> All Applications</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3><i class="bi bi-card-checklist"></i> All Job Applications (<?= $total_applications ?>)</h3>
        </div>

        <form method="GET" action="view_all_applications.php" class="filter-form mb-3 p-3 bg-light border rounded">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Applicant/Job Title:</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?= htmlspecialchars($search_term) ?>" placeholder="e.g. John Doe or Web Developer">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Filter by Status:</label>
                    <select class="form-select" id="status" name="status">
                        <?php foreach ($status_options as $opt): ?>
                            <option value="<?= htmlspecialchars(strtolower($opt)) ?>" <?= ($filter_status === strtolower($opt)) ? 'selected' : '' ?>>
                                <?= ($opt === 'all') ? 'All Statuses' : htmlspecialchars($opt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                <div class="col-md-2">
                     <a href="view_all_applications.php" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </div>
        </form>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>App ID</th>
                        <th>Job Title</th>
                        <th>Applicant Name</th>
                        <th>Applicant Email</th>
                        <th>Employer</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        </tr>
                </thead>
                <tbody>
                    <?php if (count($applications) > 0): ?>
                        <?php foreach ($applications as $app): ?>
                            <?php
                                $status_badge_class = 'bg-secondary'; // Default
                                switch (strtolower($app['status'])) {
                                    case 'pending': case 'applied': case 'under review': $status_badge_class = 'bg-warning text-dark'; break;
                                    case 'interviewing': case 'shortlisted': $status_badge_class = 'bg-info text-dark'; break;
                                    case 'hired': $status_badge_class = 'bg-success'; break;
                                    case 'rejected': case 'not selected': $status_badge_class = 'bg-danger'; break;
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($app['application_id']) ?></td>
                                <td><a href="../view_job.php?id=<?= htmlspecialchars($app['job_id']) ?>" target="_blank"><?= htmlspecialchars($app['job_title']) ?></a></td>
                                <td><?= htmlspecialchars($app['applicant_name']) ?></td>
                                <td><?= htmlspecialchars($app['applicant_email']) ?></td>
                                <td><?= htmlspecialchars($app['employer_name']) ?></td>
                                <td><?= htmlspecialchars(date("M j, Y, g:i a", strtotime($app['applied_date']))) ?></td>
                                <td><span class="badge rounded-pill <?= $status_badge_class ?>"><?= htmlspecialchars($app['status']) ?></span></td>
                                </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No applications found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page - 1 ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($search_term) ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($search_term) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page + 1 ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($search_term) ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    </div>

    <footer class="text-center mt-5 py-3 bg-body-tertiary">
        <p class="mb-0">&copy; <?= date('Y') ?> PathFinder Admin. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
