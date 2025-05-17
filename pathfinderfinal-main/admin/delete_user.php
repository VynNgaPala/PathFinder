<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: loginpage.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Prevent deletion of current logged-in admin
    if ($_SESSION['user'] == $id) {
        echo "You cannot delete yourself.";
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_users.php");
}
?>
