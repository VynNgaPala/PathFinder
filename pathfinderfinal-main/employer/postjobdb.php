<?php
session_start();
require '../connection.php'; // your database connection file

// Make sure the user is logged in and is an employer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
  die("Access denied.");
}

$employer_id = $_SESSION['user_id']; // from login session
$title = $_POST['title'];
$description = $_POST['description'];
$location = $_POST['location'];
$salary = $_POST['salary'];

$sql = "INSERT INTO jobs (employer_id, title, description, location, salary, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $employer_id, $title, $description, $location, $salary);

if ($stmt->execute()) {
  header("Location: postjob.php?success=1");
  exit();
} else {
  echo "Error: " . $stmt->error;
}
?>
