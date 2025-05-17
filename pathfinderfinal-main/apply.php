<?php
session_start();
require 'connection.php'; // Your database connection file

// Check if user is logged in as a client
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'client') {
    $_SESSION['error_message'] = "You must be logged in to apply for jobs.";
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

// --- Check if the job exists ---
$job_check_sql = "SELECT id FROM jobs WHERE id = ?";
$job_stmt = $conn->prepare($job_check_sql);
if ($job_stmt) {
    $job_stmt->bind_param("i", $job_id);
    $job_stmt->execute();
    $job_result = $job_stmt->get_result();
    if ($job_result->num_rows === 0) {
        $_SESSION['error_message'] = "Job not found or no longer available.";
        header("Location: dashboardclient.php");
        exit();
    }
    $job_stmt->close();
} else {
    error_log("SQL prepare error in apply.php (job check): " . $conn->error);
    $_SESSION['error_message'] = "An error occurred while verifying the job. Please try again.";
    header("Location: dashboardclient.php");
    exit();
}
// --- End Check if the job exists ---


// Check if user has already applied for this specific job
$check_sql = "SELECT id FROM applications WHERE user_id = ? AND job_id = ?";
$check_stmt = $conn->prepare($check_sql);

if ($check_stmt === false) {
    error_log("SQL prepare error in apply.php (check application): " . $conn->error);
    $_SESSION['error_message'] = "An error occurred while checking your application status.";
    // Redirect to the job view page as it's a less critical error than not finding the job
    header("Location: view_job.php?id=" . $job_id); 
    exit();
}

$check_stmt->bind_param("ii", $current_user_id, $job_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $_SESSION['info_message'] = "You have already applied for this job.";
    header("Location: view_job.php?id=" . $job_id); // Redirect to job view page
    exit();
}
$check_stmt->close();

// Insert application
// This query assumes your 'applications' table has at least 'user_id' and 'job_id'.
// If you add 'applied_date' with DEFAULT CURRENT_TIMESTAMP, it will be handled by the DB.
$insert_sql = "INSERT INTO applications (user_id, job_id) VALUES (?, ?)";
// If you have an `applied_date` column and want PHP to set it:
// $insert_sql = "INSERT INTO applications (user_id, job_id, applied_date) VALUES (?, ?, NOW())";

$insert_stmt = $conn->prepare($insert_sql);

if ($insert_stmt === false) {
    error_log("SQL prepare error in apply.php (insert application): " . $conn->error);
    $_SESSION['error_message'] = "An error occurred while processing your application.";
    header("Location: view_job.php?id=" . $job_id);
    exit();
}

$insert_stmt->bind_param("ii", $current_user_id, $job_id);

if ($insert_stmt->execute()) {
    $_SESSION['success_message'] = "Successfully applied for the job!";
    header("Location: view_job.php?id=" . $job_id); // Redirect to job view page to see "Already Applied"
    exit();
} else {
    // Check for duplicate entry error specifically if your DB unique constraint handles it
    // MySQL error code for duplicate entry is 1062
    if ($conn->errno == 1062) { 
         $_SESSION['info_message'] = "It seems you've already applied for this job (checked again).";
    } else {
        error_log("SQL execute error in apply.php (insert application): " . $insert_stmt->error);
        $_SESSION['error_message'] = "Could not apply for the job due to a database error.";
        // More detailed error for debugging: $_SESSION['error_message'] = "DB Error: " . $insert_stmt->error;
    }
    header("Location: view_job.php?id=" . $job_id);
    exit();
}




?>
