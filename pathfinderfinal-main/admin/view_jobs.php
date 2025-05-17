<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginpage.php");
    exit();
}

$sql = "SELECT jobs.*, users.fullname AS employer_name FROM jobs 
        JOIN users ON jobs.employer_id = users.id 
        ORDER BY jobs.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Job Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-3">📄 All Job Posts</h3>
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Employer</th>
                <th>Title</th>
                <th>Description</th>
                <th>Location</th>
                <th>Salary</th>
                <th>Posted On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['employer_name']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['salary']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="delete_job.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this job post?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="dashboardadmin.php" class="btn btn-secondary mt-2">← Back to Dashboard</a>
</div>
</body>
</html>
