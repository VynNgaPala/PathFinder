<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: loginpage.php");
    exit();
}

$sql = "SELECT id, fullname, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-3">All Registered Users</h2>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['fullname'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['role'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="dashboardadmin.php" class="btn btn-secondary mt-2">Back to Dashboard</a>
</div>
</body>
</html>
