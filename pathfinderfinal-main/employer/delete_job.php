<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'employer') {
    // If not an employer or not logged in, redirect to login
    header("Location: ../loginpage.php");
    exit();
}

require '../connection.php'; // Your database connection
$employer_id = $_SESSION['user'];
$job_id_to_delete = null;

// Check if ID is provided and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $job_id_to_delete = (int)$_GET['id'];

    // Optional: Verify the job belongs to the current employer before deleting
    // This adds an extra layer of security.
    $verify_stmt = $conn->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
    if ($verify_stmt) {
        $verify_stmt->bind_param("ii", $job_id_to_delete, $employer_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        if ($verify_result->num_rows === 0) {
            // Job not found for this employer or does not exist
            $_SESSION['error_message'] = "Job not found or you do not have permission to delete it.";
            header("Location: dashboardhire.php");
            exit();
        }
        $verify_stmt->close();
    } else {
        // Handle prepare error
        error_log("Prepare failed in delete_job.php (verify job): (" . $conn->errno . ") " . $conn->error);
        $_SESSION['error_message'] = "An error occurred while verifying job ownership.";
        header("Location: dashboardhire.php");
        exit();
    }


    // Proceed with deletion
    // IMPORTANT: Consider related data. If you have an 'applications' table
    // linked to 'jobs.id', you need to decide how to handle those applications.
    // Option 1: Database ON DELETE CASCADE (preferred if set up)
    // If your 'applications' table's foreign key for 'job_id' has ON DELETE CASCADE,
    // applications will be deleted automatically.

    // Option 2: Manually delete applications (if no CASCADE or specific logic needed)
    
    $delete_apps_stmt = $conn->prepare("DELETE FROM applications WHERE job_id = ?");
    if ($delete_apps_stmt) {
        $delete_apps_stmt->bind_param("i", $job_id_to_delete);
        if (!$delete_apps_stmt->execute()) {
            // Log error, but might still try to delete job or stop here
            error_log("Failed to delete applications for job ID $job_id_to_delete: " . $delete_apps_stmt->error);
            $_SESSION['error_message'] = "Could not delete associated applications. Job not deleted.";
            header("Location: dashboardhire.php");
            exit();
        }
        $delete_apps_stmt->close();
    } else {
        error_log("Prepare failed for deleting applications: " . $conn->error);
        $_SESSION['error_message'] = "Database error preparing to delete applications. Job not deleted.";
        header("Location: dashboardhire.php");
        exit();
    }
    

    // Now delete the job posting itself
    $delete_job_stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND employer_id = ?");
    if ($delete_job_stmt) {
        $delete_job_stmt->bind_param("ii", $job_id_to_delete, $employer_id);
        if ($delete_job_stmt->execute()) {
            if ($delete_job_stmt->affected_rows > 0) {
                $_SESSION['success_message'] = "Job posting deleted successfully.";
            } else {
                // This case should ideally be caught by the verification step above
                $_SESSION['error_message'] = "Job not found or already deleted.";
            }
        } else {
            $_SESSION['error_message'] = "Failed to delete job posting. Please try again. " . $delete_job_stmt->error;
            error_log("Execute failed in delete_job.php (delete job): (" . $delete_job_stmt->errno . ") " . $delete_job_stmt->error);
        }
        $delete_job_stmt->close();
    } else {
        $_SESSION['error_message'] = "An error occurred preparing to delete the job. Please try again.";
        error_log("Prepare failed in delete_job.php (delete job): (" . $conn->errno . ") " . $conn->error);
    }

} else {
    // If ID is not set or not numeric
    $_SESSION['error_message'] = "Invalid job ID specified for deletion.";
}

// Redirect back to the employer dashboard
header("Location: dashboardhire.php");
exit();

?>
