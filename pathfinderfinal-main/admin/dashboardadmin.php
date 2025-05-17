<?php
include '../connection.php'; // Make sure this path is correct
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginpage.php"); // Make sure this path is correct
    exit();
}

// --- Fetch Statistics ---
$total_users = 0;
$total_clients = 0;
$total_employers = 0;
$total_jobs = 0;
$total_applications = 0;

// Total Users
$result_users = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result_users && $row = $result_users->fetch_assoc()) {
    $total_users = $row['count'];
}

// Total Clients
$result_clients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client'");
if ($result_clients && $row = $result_clients->fetch_assoc()) {
    $total_clients = $row['count'];
}

// Total Employers
$result_employers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'employer'");
if ($result_employers && $row = $result_employers->fetch_assoc()) {
    $total_employers = $row['count'];
}

// Total Job Postings (you might want to add a filter for 'active' jobs if you have such a status)
$result_jobs = $conn->query("SELECT COUNT(*) as count FROM jobs");
if ($result_jobs && $row = $result_jobs->fetch_assoc()) {
    $total_jobs = $row['count'];
}

// Total Applications
$result_applications = $conn->query("SELECT COUNT(*) as count FROM applications");
if ($result_applications && $row = $result_applications->fetch_assoc()) {
    $total_applications = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; }
        .stat-card {
            border-left-width: .25rem;
            border-left-style: solid;
        }
        .stat-card .card-body { padding: 1.5rem; }
        .stat-card .display-4 { font-weight: 700; }
        .border-left-primary { border-left-color: #0d6efd !important; }
        .border-left-success { border-left-color: #198754 !important; }
        .border-left-info    { border-left-color: #0dcaf0 !important; }
        .border-left-warning { border-left-color: #ffc107 !important; }
        .border-left-danger  { border-left-color: #dc3545 !important; }
        .quick-link-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 150px;
        }
        .quick-link-card .card-title { font-size: 1.25rem; }
        .quick-link-card .btn { margin-top: 1rem; }
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
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboardadmin.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php"><i class="bi bi-people-fill"></i> Manage Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_jobs.php"><i class="bi bi-briefcase-fill"></i> View Jobs</a>
                    </li>
                     <li class="nav-item"><a class="nav-link" href="view_all_applications.php"><i class="bi bi-card-checklist"></i> All Applications</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h3 class="mb-4"><i class="bi bi-speedometer2"></i> Welcome, Admin! Platform Overview</h3>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-primary shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                                <div class="display-4 mb-0"><?= $total_users ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill fs-1 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-success shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Client Accounts</div>
                                <div class="display-4 mb-0"><?= $total_clients ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-person-check-fill fs-1 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-info shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Employer Accounts</div>
                                <div class="display-4 mb-0"><?= $total_employers ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-person-workspace fs-1 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-warning shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Job Postings</div>
                                <div class="display-4 mb-0"><?= $total_jobs ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-briefcase-fill fs-1 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-left-danger shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Applications</div>
                                <div class="display-4 mb-0"><?= $total_applications ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-file-earmark-text-fill fs-1 text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <h4 class="mb-3">Quick Management Links</h4>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card quick-link-card text-white bg-primary shadow h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill display-3 mb-2"></i>
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text small">View, edit, or manage all user accounts.</p>
                        <a href="manage_users.php" class="btn btn-light stretched-link">Go to Users</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card quick-link-card text-white bg-success shadow h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-briefcase-fill display-3 mb-2"></i>
                        <h5 class="card-title">View Job Posts</h5>
                        <p class="card-text small">Oversee all job listings on the platform.</p>
                        <a href="view_jobs.php" class="btn btn-light stretched-link">Go to Jobs</a>
                    </div>
                </div>
            </div>
             <div class="col-lg-4 col-md-6 mb-4">
                <div class="card quick-link-card text-white bg-info shadow h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-card-checklist display-3 mb-2"></i>
                        <h5 class="card-title">View All Applications</h5>
                        <p class="card-text small">Review all submitted applications.</p>
                        <a href="view_all_applications.php" class="btn btn-light stretched-link">Go to Applications</a>
                    </div>
                </div>
            </div>
            </div>
    </div>

    <footer class="text-center mt-5 py-3 bg-body-tertiary">
        <p class="mb-0">&copy; <?= date('Y') ?> PathFinder Admin. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
