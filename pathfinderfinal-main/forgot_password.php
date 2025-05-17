<?php
session_start();
require 'connection.php'; // Your database connection file

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
            $message_type = "danger";
        } else {
            // Check if email exists in users table
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Email exists, generate token
                    $token = bin2hex(random_bytes(32)); // Generate a cryptographically secure token
                    $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour')); // Token expires in 1 hour

                    // Store token in password_resets table
                    // First, delete any existing tokens for this email to avoid clutter
                    $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                    if($delete_stmt){
                        $delete_stmt->bind_param("s", $email);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                    }

                    $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                    if ($insert_stmt) {
                        $insert_stmt->bind_param("sss", $email, $token, $expires_at);
                        if ($insert_stmt->execute()) {
                            // !!! IMPORTANT: SEND EMAIL HERE !!!
                            // This is where you would integrate an email sending library (e.g., PHPMailer)
                            // The email should contain a link like:
                            // http://yourwebsite.com/reset_password.php?token=THE_GENERATED_TOKEN
                            
                            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                            
                            // For demonstration, we'll just display the link and a success message.
                            // In a real application, DO NOT display the token or link directly to the user on this page.
                            $message = "If an account with that email exists, a password reset link has been sent. Please check your email. (Link for demo: <a href='$reset_link'>$reset_link</a>)";
                            $message_type = "success";
                            
                            // --- Email Sending Placeholder ---
                            // $subject = "Password Reset Request";
                            // $email_body = "Please click the following link to reset your password: \n" . $reset_link . "\n\nIf you did not request this, please ignore this email.";
                            // $headers = "From: no-reply@yourwebsite.com";
                            // if (mail($email, $subject, $email_body, $headers)) {
                            //     $message = "If an account with that email exists, a password reset link has been sent. Please check your email.";
                            //     $message_type = "success";
                            // } else {
                            //     $message = "Could not send reset email. Please contact support. (Demo link: <a href='$reset_link'>$reset_link</a>)";
                            //     $message_type = "danger";
                            //     error_log("Failed to send password reset email to " . $email);
                            // }
                            // --- End Email Sending Placeholder ---

                        } else {
                            $message = "Error storing reset token: " . $insert_stmt->error;
                            $message_type = "danger";
                            error_log("Error inserting password reset token: " . $insert_stmt->error);
                        }
                        $insert_stmt->close();
                    } else {
                         $message = "Database error (prepare insert): " . $conn->error;
                         $message_type = "danger";
                         error_log("Error preparing insert statement for password_resets: " . $conn->error);
                    }
                } else {
                    // Email does not exist, show a generic message for security (don't reveal if email is registered)
                    $message = "If an account with that email exists, a password reset link has been sent. Please check your email.";
                    $message_type = "info";
                }
                $stmt->close();
            } else {
                $message = "Database error (prepare select): " . $conn->error;
                $message_type = "danger";
                error_log("Error preparing select statement for users: " . $conn->error);
            }
        }
    } else {
        $message = "Please enter your email address.";
        $message_type = "warning";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="logindes.css"> <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f8f9fa; }
        .form-container { background-color: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
        .form-container h2 { text-align: center; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Forgot Your Password?</h2>
        <p class="text-muted text-center mb-4">Enter your email address and we'll send you a link to reset your password (if the email is registered).</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
                <?= $message ?> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <p><a href="loginpage.php">Back to Login</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
