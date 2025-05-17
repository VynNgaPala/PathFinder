<?php
// It's crucial that session_start() is called at the very beginning of any script that uses sessions.
// If 'connection.php' doesn't start it, and this is the first point of session use, it should be here.
// However, if 'loginpage.php' (which includes this) or 'connection.php' already starts it, this might be redundant
// but generally safe if placed correctly. Best practice: one session_start() at the entry point of your application flow.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'connection.php'; // Your database connection file

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not a POST request, redirect to login page or show an error
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = "danger";
    header("Location: loginpage.php"); // Or your main login form page
    exit();
}

// Validate inputs
if (!isset($_POST['email'], $_POST['password']) || empty(trim($_POST['email'])) || empty(trim($_POST['password']))) {
    $_SESSION['message'] = "Email and password are required.";
    $_SESSION['message_type'] = "warning";
    header("Location: loginpage.php");
    exit();
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "Invalid email format.";
    $_SESSION['message_type'] = "warning";
    header("Location: loginpage.php");
    exit();
}

// Query to check user
$sql = "SELECT id, email, password, role, fullname FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    error_log("Login Prepare failed: (" . $conn->errno . ") " . $conn->error);
    $_SESSION['message'] = "An error occurred during login. Please try again later.";
    $_SESSION['message_type'] = "danger";
    header("Location: loginpage.php");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();


if ($user && password_verify($password, $user['password'])) {
    // Password is correct, set up the session
    $_SESSION['user'] = $user['id']; // Storing user ID in 'user' key
    $_SESSION['user_id'] = $user['id']; // Storing user ID also in 'user_id' for consistency if used elsewhere
    $_SESSION['role'] = $user['role'];
    $_SESSION['fullname'] = $user['fullname']; // Optional: store fullname for display

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Redirect based on role
    switch ($user['role']) {
        case 'admin':
            header("Location: admin/dashboardadmin.php");
            exit();
        case 'employer':
            header("Location: employer/dashboardhire.php");
            exit();
        case 'client':
            header("Location: dashboardclient.php");
            exit();
        default:
            // This case should ideally not be reached if roles are well-defined
            error_log("Login successful but role not recognized for user ID: " . $user['id'] . " with role: " . $user['role']);
            $_SESSION['message'] = "Login successful, but your role is not recognized. Please contact support.";
            $_SESSION['message_type'] = "warning";
            // Log out the user as a safety measure if role is unknown
            unset($_SESSION['user'], $_SESSION['user_id'], $_SESSION['role'], $_SESSION['fullname']);
            header("Location: loginpage.php");
            exit();
    }
} else {
    // Invalid credentials (email not found or password incorrect)
    $_SESSION['message'] = "Invalid email or password. Please try again.";
    $_SESSION['message_type'] = "danger";
    header("Location: loginpage.php");
    exit();
}

// Close the connection if it's not automatically closed by PHP at script end
// $conn->close(); // Usually not necessary if connection.php handles this or script ends
?>
