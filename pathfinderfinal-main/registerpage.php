<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="registerdes.css"> 
    <style>
        /* Basic styles to center the form, similar to login page */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Light background */
            padding-top: 2rem; /* Add some padding at the top */
            padding-bottom: 2rem; /* Add some padding at the bottom */
        }
        .form-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px; /* Max width for the registration box */
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }
        /* Assuming registerdes.css might not have these, or for consistency */
        .form-container label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="password"],
        .form-container select {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-sizing: border-box; /* Ensures padding doesn't affect width */
        }
        .form-container input[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            background-color: #0d6efd; /* Bootstrap primary blue */
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }
        .form-container input[type="submit"]:hover {
            background-color: #0b5ed7; /* Darker blue on hover */
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        .login-link a {
            color: #0d6efd;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register for PathFinder</h2>

        <form action="register.php" method="POST">
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name:</label>
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Enter your full name" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Create a password (min. 8 characters)" required>
                 <div id="passwordHelpBlock" class="form-text">
                    Your password must be at least 8 characters long.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="role">Select Role:</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="" disabled selected>-- Choose your role --</option>
                    <option value="client">Client (Looking for a job)</option>
                    <option value="employer">Employer (Looking to hire)</option>
                </select>
            </div>

            <input type="submit" class="btn btn-primary" value="Register"> </form>

        <div class="login-link">
            <p>Already have an account? <a href="loginpage.php">Login here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
