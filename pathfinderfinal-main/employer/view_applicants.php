<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../loginpage.php"); // Adjust path as needed
    exit();
}

require '../connection.php'; // Adjust path as needed
$employer_id = $_SESSION['user'];
$job_id = null;
$job_title = "Applicants"; // Default title
$applicants = [];

// Validate and get job_id from URL
if (isset($_GET['job_id']) && is_numeric($_GET['job_id'])) {
    $job_id = (int)$_GET['job_id'];
} else {
    $_SESSION['error_message'] = "Invalid job ID specified.";
    header("Location: dashboardhire.php"); // Adjust path as needed
    exit();
}

// Verify the job belongs to the current employer and fetch job title
$job_stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ? AND employer_id = ?");
if ($job_stmt) {
    $job_stmt->bind_param("ii", $job_id, $employer_id);
    $job_stmt->execute();
    $job_result = $job_stmt->get_result();
    if ($job_result->num_rows === 1) {
        $job_data = $job_result->fetch_assoc();
        $job_title = htmlspecialchars($job_data['title']);
    } else {
        $_SESSION['error_message'] = "Job not found or you do not have permission to view its applicants.";
        header("Location: dashboardhire.php"); // Adjust path as needed
        exit();
    }
    $job_stmt->close();
} else {
    error_log("Prepare failed in view_applicants.php (fetch job title): " . $conn->error);
    $_SESSION['error_message'] = "An error occurred while verifying the job.";
    header("Location: dashboardhire.php"); // Adjust path as needed
    exit();
}

// Handle status update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (isset($_POST['application_id'], $_POST['new_status']) && is_numeric($_POST['application_id'])) {
        $application_id_to_update = (int)$_POST['application_id'];
        $new_status = trim($_POST['new_status']);
        $allowed_statuses = ['Pending', 'Under Review', 'Interviewing', 'Shortlisted', 'Hired', 'Rejected', 'Not Selected'];

        if (in_array($new_status, $allowed_statuses)) {
            $update_stmt = $conn->prepare(
                "UPDATE applications app
                 JOIN jobs j ON app.job_id = j.id
                 SET app.status = ? 
                 WHERE app.id = ? AND j.employer_id = ? AND j.id = ?"
            );
            if ($update_stmt) {
                $update_stmt->bind_param("siii", $new_status, $application_id_to_update, $employer_id, $job_id);
                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = "Applicant status updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Failed to update status: " . $update_stmt->error;
                    error_log("Execute failed in view_applicants.php (update status): " . $update_stmt->error);
                }
                $update_stmt->close();
            } else {
                $_SESSION['error_message'] = "Error preparing status update: " . $conn->error;
                error_log("Prepare failed in view_applicants.php (update status): " . $conn->error);
            }
        } else {
            $_SESSION['error_message'] = "Invalid status value provided.";
        }
    } else {
        $_SESSION['error_message'] = "Missing data for status update.";
    }
    header("Location: view_applicants.php?job_id=" . $job_id); // PRG pattern
    exit();
}

// Fetch applicants for the job
$applicant_sql = "SELECT u.fullname, u.email, u.resume, app.id as application_id, app.applied_date, app.status
                  FROM applications app
                  JOIN users u ON app.user_id = u.id
                  WHERE app.job_id = ?
                  ORDER BY app.applied_date DESC";
$stmt_applicants = $conn->prepare($applicant_sql);
if ($stmt_applicants) {
    $stmt_applicants->bind_param("i", $job_id);
    $stmt_applicants->execute();
    $result_applicants = $stmt_applicants->get_result();
    while ($row = $result_applicants->fetch_assoc()) {
        $applicants[] = $row;
    }
    $stmt_applicants->close();
} else {
    error_log("Prepare failed in view_applicants.php (fetch applicants): " . $conn->error);
    $_SESSION['error_message'] = "An error occurred while fetching applicants.";
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$status_options = ['Pending', 'Under Review', 'Interviewing', 'Shortlisted', 'Hired', 'Rejected', 'Not Selected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Applicants for <?= $job_title ?> - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Inter', sans-serif;
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
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 0.5rem;
            display: inline-block;
        }
        .applicant-list-container {
            background-color: #fff;
            padding: 1.5rem 2rem; /* Adjusted padding */
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-top: 1rem;
        }
        .applicant-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem 0;
        }
        .applicant-item:last-child {
            border-bottom: none;
            padding-bottom: 0.5rem; /* Less padding for the last item */
        }
        .applicant-item:first-child {
            padding-top: 0.5rem; /* Less padding for the first item */
        }
        .applicant-name {
            font-weight: 600;
            color: #0d6efd;
            font-size: 1.1rem;
        }
        .applicant-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .status-form .form-select {
            max-width: 200px; /* Limit width of select */
            font-size: 0.875rem;
        }
        .status-form .btn {
            font-size: 0.875rem;
        }
        .btn-view-resume {
            font-size: 0.875rem;
        }
        .btn i {
            margin-right: 5px;
        }
        .footer {
            padding: 2rem 0;
            background-color: #e9ecef;
            color: #6c757d;
            text-align: center;
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
                    <li class="nav-item"><a class="nav-link" href="dashboardhire.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="postjob.php"><i class="bi bi-plus-square-dotted"></i> Post a Job</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-header"><i class="bi bi-people-fill"></i> Applicants for: <span class="text-primary"><?= $job_title ?></span></h3>
            <a href="dashboardhire.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Back to Dashboard</a>
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

        <div class="applicant-list-container">
            <?php if (count($applicants) > 0): ?>
                <?php foreach ($applicants as $applicant): ?>
                    <div class="applicant-item">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h5 class="applicant-name mb-1"><?= htmlspecialchars($applicant['fullname']) ?></h5>
                                <p class="applicant-meta mb-1">
                                    <i class="bi bi-envelope-fill text-muted"></i> <?= htmlspecialchars($applicant['email']) ?>
                                </p>
                                <?php if (!empty($applicant['resume'])): ?>
                                    <a href="../uploads/resumes/<?= htmlspecialchars($applicant['resume']) ?>" target="_blank" class="btn btn-sm btn-outline-info btn-view-resume mt-1">
                                        <i class="bi bi-file-earmark-person-fill"></i> View Resume
                                    </a>
                                <?php else: ?>
                                    <span class="applicant-meta mt-1 d-block"><i class="bi bi-exclamation-circle text-warning"></i> No resume uploaded</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-md-start text-muted mt-2 mt-md-0">
                                <p class="applicant-meta mb-0"><i class="bi bi-calendar-check-fill"></i> Applied:</p>
                                <p class="mb-0">
                                    <?= isset($applicant['applied_date']) ? date("M j, Y, g:i a", strtotime($applicant['applied_date'])) : 'N/A' ?>
                                </p>
                            </div>
                            <div class="col-md-5 mt-3 mt-md-0">
                                <form method="POST" action="view_applicants.php?job_id=<?= $job_id ?>" class="status-form d-flex align-items-center gap-2">
                                    <input type="hidden" name="application_id" value="<?= $applicant['application_id'] ?>">
                                    <label for="new_status_<?= $applicant['application_id'] ?>" class="form-label visually-hidden">Status:</label>
                                    <select name="new_status" id="new_status_<?= $applicant['application_id'] ?>" class="form-select form-select-sm">
                                        <?php foreach ($status_options as $status_opt): ?>
                                            <option value="<?= htmlspecialchars($status_opt) ?>" <?= ($applicant['status'] === $status_opt) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status_opt) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                                        <i class="bi bi-check-circle"></i> Update
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-light text-center py-4">
                    <i class="bi bi-person-x-fill fs-1 text-muted mb-2"></i>
                    <h5 class="text-muted">No Applicants Yet</h5>
                    <p class="mb-0 text-muted">There are currently no applicants for this job posting.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer mt-auto py-3">
        <div class="container">
            <p class="mb-0 text-center">&copy; <?= date('Y') ?> PathFinder Employer Portal. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
