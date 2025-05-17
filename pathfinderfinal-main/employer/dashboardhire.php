<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../loginpage.php"); // Ensure this path is correct
    exit();
}

require '../connection.php'; // Your database connection, ensure path is correct

$employer_id = $_SESSION['user'];

// Fetch jobs posted by the current employer
// Removed 'job_type' from the select list
$sql = "SELECT id, title, description, location, salary, created_at FROM jobs WHERE employer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed in dashboardhire.php: (" . $conn->errno . ") " . $conn->error);
    exit("An error occurred while preparing to fetch your jobs.");
}
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();
$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}
$stmt->close();

// Handle session messages for success/error after actions
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Employer Dashboard - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { 
            background-color: #f4f7f6; /* Lighter, slightly bluish gray */
            font-family: 'Inter', sans-serif; /* Assuming Inter is available */
        }
        .navbar-brand { 
            font-weight: bold;
        }
        .navbar-brand i {
            margin-right: 5px;
        }
        .page-header {
            color: #333;
            font-weight: 700;
            border-bottom: 2px solid #0d6efd; /* Primary color underline */
            padding-bottom: 0.5rem;
            display: inline-block;
        }

        .job-card {
            background-color: #fff;
            border: none; /* Remove default border */
            border-radius: 10px; /* Softer radius */
            margin-bottom: 1.5rem; /* Spacing between cards */
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08); /* Softer, more diffused shadow */
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            overflow: hidden; /* To contain absolutely positioned elements if any */
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .job-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f0f0f0; /* Lighter border */
            background-color: #fafbff; /* Very light blue tint for header */
        }
        .job-card-header h5 {
            margin-bottom: 0.25rem;
            font-weight: 600;
            color: #0d6efd; /* Primary color for title */
        }
        .job-card-header .text-muted {
            font-size: 0.85rem;
        }
        .job-card-body {
            padding: 1.5rem;
        }
        .job-description-truncate {
            font-size: 0.95rem;
            color: #555;
            line-height: 1.6;
            height: 4.8em; /* Approx 3 lines (1.6em * 3) */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Limit to 3 lines */
            -webkit-box-orient: vertical;
        }
        .job-meta-info {
            font-size: 0.9rem;
            color: #6c757d; /* Bootstrap's text-muted color */
            margin-bottom: 1rem;
        }
        .job-meta-info .badge {
            margin-right: 0.5rem;
            font-size: 0.8rem;
            padding: 0.4em 0.7em;
        }
        .job-actions {
            padding: 1rem 1.5rem;
            background-color: #f8f9fa; /* Light background for actions footer */
            border-top: 1px solid #f0f0f0;
            text-align: right;
        }
        .job-actions .btn {
            margin-left: 0.5rem;
            font-size: 0.875rem; /* Slightly smaller buttons */
        }
        .job-actions .btn i {
            margin-right: 4px;
        }
        .btn-success i {
            margin-right: 5px;
        }
        .alert-info a {
            font-weight: 500;
        }
        .footer {
            padding: 2rem 0;
            background-color: #e9ecef; /* Light gray for footer */
            color: #6c757d;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboardhire.php"><i class="bi bi-buildings-fill"></i> PathFinder Employer</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboardhire.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="postjob.php"><i class="bi bi-plus-square-dotted"></i> Post a Job</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-header"><i class="bi bi-briefcase-fill"></i> Your Job Postings</h3>
            <a href="postjob.php" class="btn btn-success btn-lg"><i class="bi bi-plus-circle-fill"></i> Post New Job</a>
        </div>

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

        <?php if (count($jobs) > 0): ?>
            <div class="row">
                <?php foreach ($jobs as $job): ?>
                    <div class="col-lg-6"> 
                        <div class="job-card">
                            <div class="job-card-header">
                                <h5><?= htmlspecialchars($job['title']) ?></h5>
                                <small class="text-muted"><i class="bi bi-clock-history"></i> Posted: <?= date("M j, Y", strtotime($job['created_at'])) ?></small>
                            </div>
                            <div class="job-card-body">
                                <p class="job-description-truncate"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                                <div class="job-meta-info">
                                    <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt-fill text-primary"></i> <?= htmlspecialchars($job['location']) ?></span>
                                    <?php if (!empty($job['salary'])): ?>
                                        <span class="badge bg-light text-dark border"><i class="bi bi-cash-stack text-success"></i> <?= htmlspecialchars($job['salary']) ?></span>
                                    <?php endif; ?>
                                    <?php 
                                     ?>
                                </div>
                            </div>
                            <div class="job-actions">
                                <a href="view_applicants.php?job_id=<?= $job['id'] ?>" class="btn btn-info btn-sm" title="View Applicants">
                                    <i class="bi bi-people-fill"></i> Applicants
                                </a>
                                <a href="edit_job.php?id=<?= $job['id'] ?>" class="btn btn-outline-primary btn-sm" title="Edit Job">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <a href="delete_job.php?id=<?= $job['id'] ?>" class="btn btn-outline-danger btn-sm" title="Delete Job"
                                   onclick="return confirm('Are you sure you want to delete this job posting? This action cannot be undone and will also remove associated applications if configured.');">
                                    <i class="bi bi-trash3-fill"></i> Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-4">
                <h4 class="alert-heading"><i class="bi bi-info-circle-fill"></i> No Jobs Posted Yet</h4>
                <p class="mb-0">You haven't posted any jobs. Click the button above to post your first job opening!</p>
                <hr>
                <a href="postjob.php" class="btn btn-primary"><i class="bi bi-plus-square-dotted"></i> Post Your First Job</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer text-center">
        <p class="mb-0">&copy; <?= date('Y') ?> PathFinder Employer Portal. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
