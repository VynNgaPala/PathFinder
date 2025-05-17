<?php
session_start();
require 'connection.php'; // Your database connection file

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$show_form = false;

if (empty($token)) {
    $message = "Invalid or missing reset token.";
    $message_type = "danger";
    // Optionally redirect to login page
    // header("Location: loginpage.php"); exit();
} else {
    // Validate token
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $email_to_reset = $row['email'];
            $expires_at = strtotime($row['expires_at']);

            if (time() > $expires_at) {
                $message = "This password reset token has expired. Please request a new one.";
                $message_type = "danger";
                // Optionally delete expired token
                // $conn->query("DELETE FROM password_resets WHERE token = '$token'");
            } else {
                $show_form = true; // Token is valid and not expired
            }
        } else {
            $message = "Invalid password reset token. It may have already been used or does not exist.";
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Database error validating token: " . $conn->error;
        $message_type = "danger";
        error_log("Error preparing statement to validate token: " . $conn->error);
    }
}

// Handle new password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $message = "Please enter and confirm your new password.";
        $message_type = "warning";
    } elseif (strlen($new_password) < 8) { // Basic password length validation
        $message = "Password must be at least 8 characters long.";
        $message_type = "warning";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "warning";
    } else {
        // Passwords match and meet basic criteria, update user's password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("ss", $hashed_password, $email_to_reset);
            if ($update_stmt->execute()) {
                // Password updated successfully, now delete the token
                $delete_token_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?"); // Or by token
                if($delete_token_stmt){
                    $delete_token_stmt->bind_param("s", $email_to_reset);
                    $delete_token_stmt->execute();
                    $delete_token_stmt->close();
                }

                $_SESSION['message'] = "Your password has been reset successfully! You can now login.";
                $_SESSION['message_type'] = "success";
                header("Location: loginpage.php");
                exit();
            } else {
                $message = "Error updating password: " . $update_stmt->error;
                $message_type = "danger";
                error_log("Error updating user password: " . $update_stmt->error);
            }
            $update_stmt->close();
        } else {
            $message = "Database error preparing password update: " . $conn->error;
            $message_type = "danger";
            error_log("Error preparing statement to update password: " . $conn->error);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="logindes.css"> <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; }
        .form-container { background-color: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        .form-container h2 { text-align: center; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Reset Your Password</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <?php if ($message_type === 'danger' || $message_type === 'warning' && !$show_form): ?>
                     <p class="mt-2 mb-0"><a href="forgot_password.php">Request a new reset link</a> or <a href="loginpage.php">Back to Login</a></p>
                <?php endif; ?>
                 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($show_form): ?>
            <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password" required>
                     <div id="passwordHelpBlock" class="form-text">
                        Your password must be at least 8 characters long.
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        <?php endif; ?>
         <?php if (!$show_form && empty($message)): // Fallback if token was initially empty but no message set ?>
            <div class="alert alert-danger">Invalid request. <a href="loginpage.php">Return to login.</a></div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
