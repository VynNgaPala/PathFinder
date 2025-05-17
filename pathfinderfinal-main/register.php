<?php
// Database credentials
$host = 'localhost';
$dbname = 'pafinder';
$username = 'root';
$password = '';

// Create a new PDO instance to connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

// Process form data when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Input validation
    if (empty($fullname) || empty($email) || empty($password) || empty($role)) {
        echo "Please fill in all fields.";
        exit;
    }

    // Handle resume upload
    $resumePath = NULL;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/resumes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['resume']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['resume']['name']);
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $resumePath = $destPath;
        } else {
            echo "Error uploading resume.";
            exit;
        }
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the database
    $sql = "INSERT INTO users (fullname, email, password, role, resume) VALUES (:fullname, :email, :password, :role, :resume)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':resume', $resumePath);

    try {
        $stmt->execute();
        header('Location: loginpage.php');
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
