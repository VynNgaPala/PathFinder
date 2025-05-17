<?php
// Start session at the very beginning to access session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="logindes.css"> 
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f4f7f6; /* Light, slightly bluish gray */
            font-family: 'Inter', sans-serif; /* Assuming Inter is available */
            padding: 1rem;
        }
        .login-container {
            background-color: #ffffff;
            padding: 2.5rem; /* Increased padding */
            border-radius: 10px; /* Softer radius */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); /* Enhanced shadow */
            width: 100%;
            max-width: 450px; /* Max width for the login box */
        }
        .logo-wrapper { /* Renamed from .logo for clarity */
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-wrapper img {
            max-width: 120px; /* Adjusted size */
            height: auto;
            border-radius: 8px; /* Optional: slight rounding for logo image */
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
            font-weight: 700;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-login-submit { /* Renamed for clarity */
            width: 100%;
            padding: 0.75rem;
            font-size: 1.05rem; /* Slightly larger font */
            font-weight: 500;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-login-submit:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .footer-links { /* Renamed from .footer for clarity */
            text-align: center;
            margin-top: 2rem; /* Increased top margin */
            font-size: 0.9rem;
        }
        .footer-links p {
            margin-bottom: 0.6rem; /* Slightly more space between links */
        }
        .footer-links a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
        .input-group-text {
            background-color: #e9ecef;
            /* border-right: none; */ /* Adjusted for password toggle */
        }
        /* Removed border adjustments that might conflict with password toggle */
        /* .form-control {
            border-left: none; 
        }
        .input-group .form-control {
            border-left: 1px solid #ced4da; 
        }
        .input-group .form-control:focus {
             border-left: 1px solid #0d6efd; 
        } */
        .input-group .input-group-text + .form-control { 
            /* border-left: none; */ /* Keep default or adjust as needed */
        }
        .password-toggle-icon {
            cursor: pointer;
            border-left: none; /* Remove left border for seamless look with input */
        }
        .input-group > .form-control:not(:last-child) { /* Ensure right border radius is not squared off by icon */
             border-top-right-radius: var(--bs-border-radius);
             border-bottom-right-radius: var(--bs-border-radius);
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo-wrapper">
            <img src="images/logo.jpg" alt="PathFinder Logo" 
                 onerror="this.onerror=null;this.src='https://placehold.co/120x50/007bff/white?text=PathFinder&font=Inter';">
        </div>

        <h2>Welcome Back!</h2>
        <p class="text-center text-muted mb-4">Login to continue your journey with PathFinder.</p>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php 
            unset($_SESSION['message']); 
            unset($_SESSION['message_type']); 
        endif; 
        ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required 
                           value="<?= htmlspecialchars($_SESSION['form_input']['email'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                     <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    <span class="input-group-text password-toggle-icon" id="togglePassword">
                        <i class="bi bi-eye-fill"></i>
                    </span>
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-login-submit"><i class="bi bi-box-arrow-in-right"></i> Login</button>
            
            <div class="footer-links">
                <p>Don't have an account? <a href="registerpage.php">Sign up here</a></p>
                <p><a href="forgot_password.php">Forgot Password?</a></p> 
                <p><a href="landing.php">Back to Home</a></p> 
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
       
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = togglePassword.querySelector('i');

        togglePassword.addEventListener('click', function () {
           
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
          
            if (type === 'password') {
                eyeIcon.classList.remove('bi-eye-slash-fill');
                eyeIcon.classList.add('bi-eye-fill');
            } else {
                eyeIcon.classList.remove('bi-eye-fill');
                eyeIcon.classList.add('bi-eye-slash-fill');
            }
        });
    </script>
</body>
</html>
