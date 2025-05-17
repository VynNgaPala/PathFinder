<?php
session_start();
require 'connection.php'; // Your database connection file

// Check if user is logged in as a client
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'client') {
    header("Location: loginpage.php");
    exit();
}

$current_user_id = $_SESSION['user']; // Renamed for clarity

// Validate job ID from GET parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid job ID specified.";
    header("Location: dashboardclient.php");
    exit();
}
$job_id = (int)$_GET['id'];

// Fetch job details along with employer name
$sql = "SELECT j.*, u.fullname AS employer_name 
        FROM jobs j
        JOIN users u ON j.employer_id = u.id
        WHERE j.id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("SQL prepare error in view_job.php (job details): " . $conn->error);
    $_SESSION['error_message'] = "An error occurred while fetching job details.";
    header("Location: dashboardclient.php");
    exit();
}

$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Job not found or is no longer available.";
    header("Location: dashboardclient.php");
    exit();
}
$job = $result->fetch_assoc();
$stmt->close();

// Check if the current user has already applied for this job
$has_applied = false;
$check_sql = "SELECT id FROM applications WHERE user_id = ? AND job_id = ?";
$check_stmt = $conn->prepare($check_sql);

if ($check_stmt) {
    $check_stmt->bind_param("ii", $current_user_id, $job_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $has_applied = true;
    }
    $check_stmt->close();
} else {
    error_log("SQL prepare error in view_job.php (check application): " . $conn->error);
    // Not a critical error for viewing, but log it.
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($job['title']) ?> - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f4f6f8; font-family: 'Segoe UI', sans-serif; }
        .navbar-brand { font-weight: bold; }
        .job-details-card { background: #fff; border-radius: 12px; padding: 30px; margin-top: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .job-title-main { font-size: 2rem; font-weight: 700; margin-bottom: 10px; color: #333; }
        .company-name { font-size: 1.3rem; color: #555; margin-bottom: 20px; }
        .job-meta-item { display: inline-block; background-color: #e9ecef; color: #495057; padding: 8px 15px; border-radius: 20px; margin-right: 10px; margin-bottom: 10px; font-size: 0.9rem; }
        .job-description-content { margin-top: 10px; line-height: 1.7; color: #444; }
        .apply-button-container { margin-top: 30px; text-align: right; }
        .badge-applied { font-size: 1rem; }
        .section-title { font-weight: 600; margin-bottom: 0.5rem; font-size: 1.25rem;}
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboardclient.php">PathFinder</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
            </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="account.php" title="My Account">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37c.318-.374.81-.87 1.33-1.244C4.972 10.418 6.393 10 8 10s3.028.418 4.138 1.126c.52.374 1.012.87 1.33 1.244A7 7 0 0 0 8 1z"/>
                        </svg>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboardclient.php">Dashboard</a>
                
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <?php
    // Display session messages if any
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success mt-3">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger mt-3">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['info_message'])) {
        echo '<div class="alert alert-info mt-3">' . htmlspecialchars($_SESSION['info_message']) . '</div>';
        unset($_SESSION['info_message']);
    }
    ?>

    <div class="job-details-card">
        <h1 class="job-title-main"><?= htmlspecialchars($job['title']) ?></h1>
        <h2 class="company-name">Posted by: <?= htmlspecialchars($job['employer_name']) ?></h2>
        
        <div class="job-meta-info mb-4">
            <span class="job-meta-item">📍 Location: <?= htmlspecialchars($job['location']) ?></span>
            <?php if (!empty($job['salary'])): ?>
                <span class="job-meta-item">💰 Salary: <?= htmlspecialchars($job['salary']) ?></span>
            <?php endif; ?>
            <span class="job-meta-item">🕒 Posted: <?= date("F j, Y", strtotime($job['created_at'])) ?></span>
            <?php if (!empty($job['job_type'])): // Assuming you might have a job_type column in 'jobs' table ?>
                <span class="job-meta-item">🏷️ Type: <?= htmlspecialchars($job['job_type']) ?></span>
            <?php endif; ?>
        </div>

        <div class="job-description">
            <h4 class="section-title">Job Description</h4>
            <p class="job-description-content"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
        </div>
        
        <?php if (!empty($job['requirements'])): // Assuming you might have a requirements column in 'jobs' table ?>
        <div class="job-requirements mt-4">
            <h4 class="section-title">Requirements</h4>
            <p class="job-description-content"><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="apply-button-container">
            <?php if ($has_applied): ?>
                <span class="badge bg-success p-2 badge-applied">Already Applied</span>
            <?php else: ?>
                <a href="apply.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-lg">Apply Now</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="py-4 mt-5 bg-light text-center">
    <p class="mb-0">&copy; <?= date('Y') ?> PathFinder. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
