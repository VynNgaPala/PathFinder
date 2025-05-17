<?php
session_start();
require 'connection.php'; // Make sure this file correctly establishes $conn using mysqli

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'client') {
    header("Location: loginpage.php");
    exit();
}

$user_id = $_SESSION['user']; // Assuming this holds the user ID

// Fetch user info
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
if ($stmt === false) {
    error_log("Prepare failed for user_sql: (" . $conn->errno . ") " . $conn->error);
    exit("An error occurred preparing user data.");
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

if (!$user) {
    error_log("User not found with ID: " . $user_id);
    exit("User not found.");
}
$stmt->close(); // Close statement

// Fetch applied jobs
$applied_jobs = [];
$applied_sql = "SELECT 
                    j.id AS job_id, 
                    j.title, 
                    j.location, 
                    u.fullname AS employer_name, 
                    app.applied_date, 
                    app.status 
                FROM jobs j
                JOIN applications app ON j.id = app.job_id
                JOIN users u ON j.employer_id = u.id 
                WHERE app.user_id = ?
                ORDER BY app.applied_date DESC";

$stmt_applied = $conn->prepare($applied_sql);
if ($stmt_applied) {
    $stmt_applied->bind_param("i", $user_id); 
    $stmt_applied->execute();
    $applied_result = $stmt_applied->get_result();
    while ($row = $applied_result->fetch_assoc()) {
        $applied_jobs[] = $row;
    }
    $stmt_applied->close();
} else {
    error_log("Error preparing statement for applied jobs in account.php: " . $conn->error);
}

// Fetch work experience
$work_experience = [];
$work_sql = "SELECT * FROM experiences WHERE user_id = ? ORDER BY end_date DESC, start_date DESC";
$stmt_work = $conn->prepare($work_sql); // Use different $stmt variable
if ($stmt_work) {
    $stmt_work->bind_param("i", $user_id);
    $stmt_work->execute();
    $work_result = $stmt_work->get_result();
    while ($row = $work_result->fetch_assoc()) {
        $work_experience[] = $row;
    }
    $stmt_work->close();
} else {
    error_log("Error preparing statement for work experience: " . $conn->error);
}

// Fetch education
$education = [];
$education_sql = "SELECT * FROM education WHERE user_id = ? ORDER BY year DESC";
$stmt_edu = $conn->prepare($education_sql); // Use different $stmt variable
if ($stmt_edu) {
    $stmt_edu->bind_param("i", $user_id);
    $stmt_edu->execute();
    $education_result = $stmt_edu->get_result();
    while ($row = $education_result->fetch_assoc()) {
        $education[] = $row;
    }
    $stmt_edu->close();
} else {
    error_log("Error preparing statement for education: " . $conn->error);
}

// Fetch skills
$skills = [];
$skills_sql = "SELECT * FROM skills WHERE user_id = ? ORDER BY name ASC";
$stmt_skills = $conn->prepare($skills_sql); // Use different $stmt variable
if ($stmt_skills) {
    $stmt_skills->bind_param("i", $user_id);
    $stmt_skills->execute();
    $skills_result = $stmt_skills->get_result();
    while ($row = $skills_result->fetch_assoc()) {
        $skills[] = $row;
    }
    $stmt_skills->close();
} else {
    error_log("Error preparing statement for skills: " . $conn->error);
}

$resume_link = $user['resume'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f4f7f6; /* Lighter, slightly bluish gray */
            font-family: 'Inter', sans-serif; /* Assuming Inter is available or use a common sans-serif */
        }
        .navbar-brand i { /* Style for navbar icon */
            margin-right: 5px;
        }
        .main-content-header {
            color: #333;
            font-weight: 700;
            border-bottom: 2px solid #0d6efd; /* Primary color underline */
            padding-bottom: 0.5rem;
            display: inline-block; /* To make underline only as wide as text */
        }
        .section-card {
            background: #fff;
            padding: 25px; /* Increased padding */
            border-radius: 10px; /* Softer radius */
            box-shadow: 0 5px 15px rgba(0,0,0,0.07); /* Softer shadow */
            margin-bottom: 30px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px; /* Increased space */
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef; /* Lighter border */
        }
        .section-title {
            font-size: 1.3rem; /* Slightly larger title */
            font-weight: 600;
            color: #343a40; /* Darker gray */
            margin-bottom: 0;
        }
        .section-title i { /* Icon styling for section titles */
            margin-right: 8px;
            color: #0d6efd; /* Primary color for icons */
        }
        .info-item {
            margin-bottom: 0.8rem;
        }
        .info-item strong {
            color: #495057; /* Medium gray for labels */
        }
        .list-group-item {
            border-left: 3px solid transparent; /* For potential hover/active state */
            transition: border-left-color 0.2s ease-in-out, background-color 0.2s ease-in-out;
        }
        .list-group-item:hover {
            background-color: #f8f9fa; /* Light hover effect */
            border-left-color: #0d6efd;
        }
        .btn-outline-primary i, .btn-outline-secondary i, .btn-outline-success i {
            margin-right: 5px;
        }
        .badge {
            font-size: 0.8em;
            padding: 0.5em 0.75em;
        }
        /* Custom styling for applied job items */
        .applied-job-item .job-title-link {
            font-weight: 500;
            color: #0d6efd;
            text-decoration: none;
        }
        .applied-job-item .job-title-link:hover {
            text-decoration: underline;
        }
        .applied-job-item .text-muted {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboardclient.php"><i class="bi bi-compass-fill"></i> PathFinder</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                     <a class="nav-link active text-white" href="account.php" title="My Account">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 10a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37c.318-.374.81-.87 1.33-1.244C4.972 10.418 6.393 10 8 10s3.028.418 4.138 1.126c.52.374 1.012.87 1.33 1.244A7 7 0 0 0 8 1z"/>
                        </svg>
                        <span class="ms-1">My Account</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboardclient.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <h3 class="mb-4 text-center main-content-header">👤 My Account Panel</h3>

    <section class="section-card">
        <div class="section-header">
            <h4 class="section-title"><i class="bi bi-person-lines-fill"></i> Basic Information</h4>
            <a href="add_info.php?type=basic" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i> Edit</a>
        </div>
        <p class="info-item"><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?></p>
        <p class="info-item"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    </section>  

    <section class="section-card">
        <div class="section-header">
            <h4 class="section-title"><i class="bi bi-briefcase-check-fill"></i> Applied Jobs</h4>
            </div>
        <?php if (!empty($applied_jobs)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($applied_jobs as $applied_job): ?>
                    <?php
                        $status_badge_class = 'bg-secondary'; // Default badge
                        $status_text = htmlspecialchars($applied_job['status']);
                        switch (strtolower($applied_job['status'])) {
                            case 'pending': case 'applied': case 'under review': $status_badge_class = 'bg-warning text-dark'; break;
                            case 'interviewing': case 'shortlisted': $status_badge_class = 'bg-info text-dark'; break;
                            case 'hired': $status_badge_class = 'bg-success'; break;
                            case 'rejected': case 'not selected': $status_badge_class = 'bg-danger'; break;
                        }
                    ?>
                    <div class="list-group-item applied-job-item py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">
                                <a href="view_job.php?id=<?= htmlspecialchars($applied_job['job_id']) ?>" class="job-title-link">
                                    <?= htmlspecialchars($applied_job['title']) ?>
                                </a>
                            </h5>
                            <small class="text-muted">
                                <?php if (isset($applied_job['applied_date']) && $applied_job['applied_date']): ?>
                                    Applied: <?= date("M j, Y", strtotime($applied_job['applied_date'])) ?>
                                <?php endif; ?>
                            </small>
                        </div>
                        <p class="mb-1 text-muted">
                            Employer: <?= htmlspecialchars($applied_job['employer_name']) ?>
                            <?php if (!empty($applied_job['location'])): ?>
                                | Location: <?= htmlspecialchars($applied_job['location']) ?>
                            <?php endif; ?>
                        </p>
                        <div class="mt-2">
                            Status: <span class="badge rounded-pill <?= $status_badge_class ?>"><?= $status_text ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mt-2 text-muted">You haven't applied for any jobs yet. <a href="dashboardclient.php">Find jobs now!</a></p>
        <?php endif; ?>
    </section>

    <section class="section-card">
        <div class="section-header">
            <h4 class="section-title"><i class="bi bi-building-gear"></i> Work Experience</h4>
            <a href="add_info.php?type=experience" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle-fill"></i> Add</a>
        </div>
        <?php if (count($work_experience) > 0): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($work_experience as $work): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h6 class="mb-0"><strong><?= htmlspecialchars($work['position']) ?></strong></h6>
                            <p class="mb-1 text-muted"><?= htmlspecialchars($work['company']) ?></p>
                            <small class="text-muted">
                                <i class="bi bi-calendar-range"></i> 
                                <?= htmlspecialchars(date("M Y", strtotime($work['start_date']))) ?> to 
                                <?= $work['end_date'] ? htmlspecialchars(date("M Y", strtotime($work['end_date']))) : 'Present' ?>
                            </small>
                        </div>
                        <a href="edit_info.php?type=experience&id=<?= $work['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="mt-2 text-muted">No work experience added yet.</p>
        <?php endif; ?>
    </section>
 
    <section class="section-card">
        <div class="section-header">
            <h4 class="section-title"><i class="bi bi-mortarboard-fill"></i> Education</h4>
            <a href="add_info.php?type=education" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle-fill"></i> Add</a>
        </div>
        <?php if (!empty($education)): ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($education as $edu): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h6 class="mb-0"><strong><?= htmlspecialchars($edu['degree']) ?></strong></h6>
                            <p class="mb-1 text-muted"><?= htmlspecialchars($edu['institution']) ?></p>
                            <small class="text-muted"><i class="bi bi-calendar3"></i> Year: <?= htmlspecialchars($edu['year']) ?></small>
                        </div>
                        <a href="edit_info.php?type=education&id=<?= $edu['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="mt-2 text-muted">No education records available.</p>
        <?php endif; ?>
    </section>

    <section class="section-card">
        <div class="section-header">
            <h4 class="section-title"><i class="bi bi-tools"></i> Skills</h4>
            <a href="add_info.php?type=skill" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-circle-fill"></i> Add</a>
        </div>
        <?php if (!empty($skills)): ?>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <?php foreach ($skills as $skill): ?>
                    <div class="badge bg-light text-dark p-2 border d-flex align-items-center">
                        <span><?= htmlspecialchars($skill['name']) ?></span>
                        <a href="edit_info.php?type=skill&id=<?= $skill['id'] ?>" class="ms-2 text-primary" title="Edit Skill"><i class="bi bi-pencil-fill small"></i></a>
                        </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mt-2 text-muted">No skills listed.</p>
        <?php endif; ?>
    </section>

    <section class="section-card">
        <div class="section-header">
            <h4 class="section-title"><i class="bi bi-file-earmark-person-fill"></i> Resume</h4>
            <a href="add_info.php?type=resume" class="btn btn-sm btn-outline-primary">
                <i class="bi <?= $resume_link ? 'bi-arrow-repeat' : 'bi-upload' ?>"></i> <?= $resume_link ? 'Change Resume' : 'Upload Resume' ?>
            </a>
        </div>
        <?php if ($resume_link): ?>
            <a href="uploads/resumes/<?= htmlspecialchars($resume_link) ?>" target="_blank" class="btn btn-outline-success mt-2"><i class="bi bi-eye-fill"></i> View Resume</a>
        <?php else: ?>
            <p class="mt-2 text-muted">No resume uploaded.</p>
        <?php endif; ?>
    </section>

</div> 

<footer class="text-center mt-5 py-4 bg-light">
    <p class="mb-0 text-muted">&copy; <?= date('Y') ?> PathFinder. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
