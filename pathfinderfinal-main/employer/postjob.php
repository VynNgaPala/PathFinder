<?php
// session_start(); // Already in user's code, ensure it's at the very top if not already handled by an include.
// require '../connection.php'; // Already in user's code

// --- PHP logic for handling form submission would go here ---
// For example, checking if the user is logged in as an employer:
/*
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'employer') {
    header("Location: ../loginpage.php"); // Adjust path as needed
    exit();
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (validation and database insertion logic for postjobdb.php would be here or in the action file)
    // For now, this page is just the form presentation.
    // The actual submission is handled by "postjobdb.php"
}
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Post a New Job - PathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { 
            background-color: #f4f7f6; 
            font-family: 'Inter', sans-serif; /* Assuming Inter is available */
        }
        .navbar-brand { 
            font-weight: bold;
        }
        .navbar-brand i {
            margin-right: 5px;
        }
        .form-container-card {
            background-color: #fff;
            padding: 2.5rem; /* More padding */
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
            display: inline-block; /* Center underline */
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
            padding: 0.6rem 1.5rem; /* Custom padding */
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .btn-secondary {
            padding: 0.6rem 1.5rem; /* Custom padding */
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
                    <li class="nav-item">
                        <a class="nav-link" href="dashboardhire.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="postjob.php"><i class="bi bi-plus-square-dotted"></i> Post a Job</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="form-container-card">
                    <div class="text-center">
                         <h2><i class="bi bi-file-earmark-plus-fill"></i> Post a New Job</h2>
                    </div>

                    <?php
                    // Placeholder for PHP messages from postjobdb.php if you redirect back with errors/success
                    // if (session_status() == PHP_SESSION_NONE) { session_start(); } // Ensure session is started
                    // if (isset($_SESSION['form_message'])): 
                    ?>
                        <?php // unset($_SESSION['form_message'], $_SESSION['form_message_type']); endif; ?>

                    <form action="postjobdb.php" method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Senior Web Developer" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="6" placeholder="Provide a detailed description of the job responsibilities, requirements, and company culture." required></textarea>
                            <div class="form-text">Use clear and concise language. Highlight key responsibilities and qualifications.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., San Francisco, CA or Remote" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary Range (Optional)</label>
                                <input type="text" class="form-control" id="salary" name="salary" placeholder="e.g., $70,000 - $90,000 per year">
                                <div class="form-text">You can specify a range, a fixed amount, or leave blank if negotiable.</div>
                            </div>
                        </div>
                        
                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="dashboardhire.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i> Post Job</button>
                        </div>
                    </form>
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
