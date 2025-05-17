<?php
session_start();
require 'connection.php'; // Your database connection file

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'client') {
    header("Location: loginpage.php");
    exit();
}

// --- Configuration for Pagination ---
$results_per_page = 10; // Number of jobs to display per page
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

// --- Filtering and Searching ---
$base_sql = "FROM jobs j JOIN users u ON j.employer_id = u.id";
$count_sql_base = "SELECT COUNT(j.id) as total FROM jobs j JOIN users u ON j.employer_id = u.id";

$where_clauses = [];
$bind_params = [];
$bind_types = "";

// Search by keywords (title, description)
$search_keywords = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search_keywords)) {
    $search_like = "%" . $search_keywords . "%";
    $where_clauses[] = "(j.title LIKE ? OR j.description LIKE ?)";
    $bind_params[] = $search_like;
    $bind_params[] = $search_like;
    $bind_types .= "ss";
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
    $sql_where = " WHERE " . implode(" AND ", $where_clauses);
}

// --- Fetch Total Number of Jobs (for pagination) ---
$total_jobs_sql = $count_sql_base . $sql_where;
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
    error_log("Prepare failed for total jobs count: " . $conn->error);
    $total_jobs = 0;
    $total_pages = 0;
}

// --- Fetch Jobs for the current page ---
$jobs = [];
// Select specific columns to avoid ambiguity and select only what's needed
// Removed j.job_type from this list
$select_columns = "j.id, j.title, j.description, j.location, j.salary, j.created_at, u.fullname AS employer_name";
$jobs_sql = "SELECT " . $select_columns . " " . $base_sql . $sql_where . " ORDER BY j.created_at DESC LIMIT ? OFFSET ?";

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
    error_log("Prepare failed for fetching jobs: " . $conn->error);
    // Optionally set a user-facing error message
}

// For filter dropdowns - fetch distinct locations
$distinct_locations = [];
$loc_result = $conn->query("SELECT DISTINCT location FROM jobs WHERE location IS NOT NULL AND location != '' ORDER BY location ASC");
if ($loc_result) {
    while ($row = $loc_result->fetch_assoc()) {
        $distinct_locations[] = $row['location'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Client Dashboard - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f8; font-family: 'Segoe UI', sans-serif; }
        .job-card { background: #ffffff; border: 1px solid #ddd; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s ease; }
        .job-card:hover { transform: translateY(-4px); }
        .job-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 8px; }
        .navbar-brand { font-weight: bold; }
        .job-tags { margin-top: 10px; }
        .tag { display: inline-block; background-color: #e7f3ff; color: #0d6efd; padding: 4px 8px; border-radius: 5px; margin-right: 8px; margin-bottom: 5px; font-size: 0.8rem; }
        .btn-sm { min-width: 90px; }
        .filter-section { background-color: #fff; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .pagination .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
        .description-truncate {
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Number of lines to show */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4em; /* Adjust based on font size */
            max-height: 2.8em; /* line-height * number of lines */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboardclient.php"><i class="bi bi-compass"></i> PathFinder</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
               <li class="nav-item">
                     <a class="nav-link" href="account.php" title="My Account">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37c.318-.374.81-.87 1.33-1.244C4.972 10.418 6.393 10 8 10s3.028.418 4.138 1.126c.52.374 1.012.87 1.33 1.244A7 7 0 0 0 8 1z"/>
                        </svg>
                        <span class="ms-1">My Account</span>
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link active" href="dashboardclient.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="mb-4 text-center">💼 Available Job Listings</h3>

    <section class="filter-section">
        <form method="GET" action="dashboardclient.php">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Keywords</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Job title, skills, company..." value="<?= htmlspecialchars($search_keywords) ?>">
                </div>
                <div class="col-md-4">
                    <label for="location" class="form-label">Location</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="e.g. City, State" list="location_options" value="<?= htmlspecialchars($filter_location) ?>">
                    <datalist id="location_options">
                        <?php foreach ($distinct_locations as $loc): ?>
                            <option value="<?= htmlspecialchars($loc) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 me-2"><i class="bi bi-search"></i> Filter</button>
                    <a href="dashboardclient.php" class="btn btn-outline-secondary w-100" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </section>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <?php if (count($jobs) > 0): ?>
        <div class="row">
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-6">
                    <div class="job-card">
                        <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>
                        <p class="text-muted small">Posted by: <?= htmlspecialchars($job['employer_name']) ?></p>
                        <p class="description-truncate"><?= nl2br(htmlspecialchars(substr($job['description'], 0, 120))) . (strlen($job['description']) > 120 ? '...' : '') ?></p>
                        <div class="job-tags mb-3">
                            <span class="tag"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($job['location']) ?></span>
                            <?php if(!empty($job['salary'])): ?>
                                <span class="tag"><i class="bi bi-cash-stack"></i> <?= htmlspecialchars($job['salary']) ?></span>
                            <?php endif; ?>
                            <span class="tag"><i class="bi bi-clock-fill"></i> <?= date("M j, Y", strtotime($job['created_at'])) ?></span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="view_job.php?id=<?= $job['id'] ?>" class="btn btn-outline-primary btn-sm">View Job</a>
                            <a href="apply.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">Apply Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($current_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page - 1 ?>&search=<?= urlencode($search_keywords) ?>&location=<?= urlencode($filter_location) ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                        $show_page_link = false;
                        if ($total_pages <= 7 ||
                            ($i == 1 || $i == $total_pages) ||
                            ($i >= $current_page - 2 && $i <= $current_page + 2)) {
                            $show_page_link = true;
                        }
                    ?>
                    <?php if ($show_page_link): ?>
                        <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search_keywords) ?>&location=<?= urlencode($filter_location) ?>"><?= $i ?></a>
                        </li>
                    <?php elseif (($i == $current_page - 3 && $current_page > 4) || ($i == $current_page + 3 && $current_page < $total_pages - 3)): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $current_page + 1 ?>&search=<?= urlencode($search_keywords) ?>&location=<?= urlencode($filter_location) ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info text-center">No jobs available matching your criteria. Try adjusting your search or filters.</div>
    <?php endif; ?>
</div>

<footer class="text-center mt-5 py-3 bg-light">
    <p class="mb-0">&copy; <?= date('Y') ?> PathFinder. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
