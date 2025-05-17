<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../loginpage.php"); // Adjust path as needed
    exit();
}

require '../connection.php'; // Adjust path as needed
$employer_id = $_SESSION['user'];
$job_id_to_edit = null;
$job = null;
$errors = [];
// $success_message = ''; // Success message handled by session on redirect

// Check if ID is provided and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $job_id_to_edit = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $job_id_to_edit, $employer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $job = $result->fetch_assoc();
        } else {
            $_SESSION['error_message'] = "Job not found or you do not have permission to edit it.";
            header("Location: dashboardhire.php"); // Adjust path as needed
            exit();
        }
        $stmt->close();
    } else {
        error_log("Prepare failed in edit_job.php (fetch job): " . $conn->error);
        $errors[] = "An error occurred while fetching job details.";
    }
} else {
    $_SESSION['error_message'] = "Invalid job ID specified for editing.";
    header("Location: dashboardhire.php"); // Adjust path as needed
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $job) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $salary = trim($_POST['salary'] ?? '');
    // $job_type = trim($_POST['job_type'] ?? ''); // Uncomment if you have job_type

    if (empty($title)) $errors[] = "Job title is required.";
    if (empty($description)) $errors[] = "Job description is required.";
    if (empty($location)) $errors[] = "Location is required.";

    if (empty($errors)) {
        // Remove job_type if not used
        $update_sql = "UPDATE jobs SET title = ?, description = ?, location = ?, salary = ? WHERE id = ? AND employer_id = ?";
        $stmt_update = $conn->prepare($update_sql);
        if ($stmt_update) {
            // Adjust bind_param if job_type is used: "sssssii", $title, ..., $job_type, ...
            $stmt_update->bind_param("ssssii", $title, $description, $location, $salary, $job_id_to_edit, $employer_id);
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Job posting updated successfully!";
                header("Location: dashboardhire.php"); // Adjust path as needed
                exit();
            } else {
                $errors[] = "Failed to update job posting: " . $stmt_update->error;
                error_log("Execute failed in edit_job.php (update job): " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            $errors[] = "Error preparing job update: " . $conn->error;
            error_log("Prepare failed in edit_job.php (update job): " . $conn->error);
        }
    }
    // Keep POSTed values in form if errors
    $job['title'] = $title;
    $job['description'] = $description;
    $job['location'] = $location;
    $job['salary'] = $salary;
    // $job['job_type'] = $job_type; // Uncomment if you have job_type
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Job: <?= htmlspecialchars($job['title'] ?? 'Job') ?> - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .form-container-card {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .form-container-card h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 0.75rem;
            display: inline-block;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 0.6rem 1.5rem;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .btn-outline-secondary {
             padding: 0.6rem 1.5rem;
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
        .form-text {
            font-size: 0.875em;
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

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="form-container-card">
                    <div class="text-center">
                         <h2><i class="bi bi-pencil-square"></i> Edit Job Posting</h2>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($job): ?>
                    <form method="POST" action="edit_job.php?id=<?= $job_id_to_edit ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($job['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="6" required><?= htmlspecialchars($job['description'] ?? '') ?></textarea>
                            <div class="form-text">Update the responsibilities, requirements, and company culture details.</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($job['location'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary Range (Optional)</label>
                                <input type="text" class="form-control" id="salary" name="salary" value="<?= htmlspecialchars($job['salary'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="dashboardhire.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Save Changes</button>
                        </div>
                    </form>
                    <?php elseif (empty($errors)): ?>
                        <div class="alert alert-warning">Could not load job details for editing.</div>
                    <?php endif; ?>
                </div>
            </div>
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
