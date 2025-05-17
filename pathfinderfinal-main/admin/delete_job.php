<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../loginpage.php");
    exit();
}

if (isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
}

header("Location: view_jobs.php");
exit();
