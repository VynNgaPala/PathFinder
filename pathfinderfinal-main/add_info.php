<?php
session_start();
require 'connection.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'client') {
  header("Location: loginpage.php");
  exit();
}
$user_id = $_SESSION['user'];
$type = $_GET['type'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  switch ($type) {
    case 'experience':
      $pos = $_POST['position'] ?: $errors[] = 'Position is required.';
      $comp = $_POST['company'] ?: $errors[] = 'Company is required.';
      $start = $_POST['start_date'] ?: $errors[] = 'Start date is required.';
      $end   = $_POST['end_date'] ?: null;
      if (empty($errors)) {
        $stmt = $conn->prepare(
          "INSERT INTO experiences (user_id, position, company, start_date, end_date)
           VALUES (?,?,?,?,?)"
        );
        $stmt->bind_param("issss", $user_id, $pos, $comp, $start, $end);
        $stmt->execute();
        header("Location: account.php");
        exit();
      }
      break;

    case 'education':
      $deg = $_POST['degree'] ?: $errors[] = 'Degree is required.';
      $inst = $_POST['institution'] ?: $errors[] = 'Institution is required.';
      $yr = $_POST['year'] ?: $errors[] = 'Year is required.';
      if (empty($errors)) {
        $stmt = $conn->prepare(
          "INSERT INTO education (user_id, degree, institution, year)
           VALUES (?,?,?,?)"
        );
        $stmt->bind_param("isss", $user_id, $deg, $inst, $yr);
        $stmt->execute();
        header("Location: account.php");
        exit();
      }
      break;

    case 'skill':
      $skill = $_POST['name'] ?: $errors[] = 'Skill name is required.';
      if (empty($errors)) {
        $stmt = $conn->prepare(
          "INSERT INTO skills (user_id, name) VALUES (?,?)"
        );
        $stmt->bind_param("is", $user_id, $skill);
        $stmt->execute();
        header("Location: account.php");
        exit();
      }
      break;

    case 'resume':
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['resume']['tmp_name'];
        // Sanitize the basename to prevent directory traversal and other issues
        $original_filename = basename($_FILES['resume']['name']);
        // Replace spaces or special characters in filename if desired, though time() prefix helps uniqueness
        $safe_original_filename = preg_replace("/[^a-zA-Z0-9._-]/", "_", $original_filename);
        $new_resume_filename = time() . '_' . $safe_original_filename;

        // Define the target directory relative to the current script (add_info.php)
        // If add_info.php is in /pathfinderfinal/main/, this will be /pathfinderfinal/main/uploads/resumes/
        $target_directory = __DIR__ . '/uploads/resumes/'; // __DIR__ gives the directory of the current file

        
        if (!file_exists($target_directory)) {
            
            if (!mkdir($target_directory, 0755, true)) {
                $errors[] = 'Failed to create resume upload directory. Please check server permissions.';
               
            }
        }

        if (empty($errors)) { // Proceed only if directory creation was successful or not needed
            $target_file_path = $target_directory . $new_resume_filename;

            if (move_uploaded_file($file_tmp_name, $target_file_path)) {
                // File moved successfully, now update the database
                $stmt = $conn->prepare("UPDATE users SET resume = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $new_resume_filename, $user_id);
                    if ($stmt->execute()) {
                        $_SESSION['upload_success'] = "Resume uploaded successfully!"; // Optional success message
                        header("Location: account.php");
                        exit();
                    } else {
                        $errors[] = 'Failed to update resume record in database: ' . $stmt->error;
                        // Potentially remove the uploaded file if DB update fails to prevent orphaned files
                        // unlink($target_file_path);
                    }
                    $stmt->close();
                } else {
                    $errors[] = 'Failed to prepare database statement: ' . $conn->error;
                }
            } else {
                $errors[] = 'Failed to move uploaded resume file. Check server permissions and path.';
                // You can get more detailed error information about the file upload here if needed
                // switch ($_FILES['resume']['error']) { ... }
            }
        }

    } elseif (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other specific upload errors if UPLOAD_ERR_NO_FILE is not the case
        $errors[] = 'There was an error with the resume upload. Error code: ' . $_FILES['resume']['error'];
    } else {
        $errors[] = 'Please choose a resume file to upload.';
    }
    break;

    case 'basic':
      $full = $_POST['fullname'] ?: $errors[] = 'Full name is required.';
      $email = $_POST['email'] ?: $errors[] = 'Email is required.';
      if (empty($errors)) {
        $stmt = $conn->prepare(
          "UPDATE users SET fullname=?, email=? WHERE id=?"
        );
        $stmt->bind_param("ssi", $full, $email, $user_id);
        $stmt->execute();
        header("Location: account.php");
        exit();
      }
      break;

    default:
      die("Invalid type.");
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add <?= ucfirst($type) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <h3 class="mb-4">Add <?= ucfirst($type) ?></h3>
    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
      </div>
    <?php endif; ?>

    <form method="POST" <?= $type==='resume' ? 'enctype="multipart/form-data"' : '' ?>>
      <?php if ($type==='experience'): ?>
        <div class="mb-3">
          <label>Position</label>
          <input type="text" name="position" class="form-control" />
        </div>
        <div class="mb-3">
          <label>Company</label>
          <input type="text" name="company" class="form-control" />
        </div>
        <div class="row">
          <div class="col">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" />
          </div>
          <div class="col">
            <label>End Date (optional)</label>
            <input type="date" name="end_date" class="form-control" />
          </div>
        </div>

      <?php elseif ($type==='education'): ?>
        <div class="mb-3">
          <label>Degree</label>
          <input type="text" name="degree" class="form-control" />
        </div>
        <div class="mb-3">
          <label>Institution</label>
          <input type="text" name="institution" class="form-control" />
        </div>
        <div class="mb-3">
          <label>Year</label>
          <input type="number" name="year" min="1900" max="<?= date('Y') ?>" class="form-control" />
        </div>

      <?php elseif ($type==='skill'): ?>
        <div class="mb-3">
          <label>Skill Name</label>
          <input type="text" name="name" class="form-control" />
        </div>

      <?php elseif ($type==='resume'): ?>
        <div class="mb-3">
          <label>Upload Resume (PDF/DOCX)</label>
          <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" />
        </div>

      <?php elseif ($type==='basic'): ?>
        <?php
          // load existing values
          $u = $conn->query("SELECT fullname,email FROM users WHERE id=$user_id")->fetch_assoc();
        ?>
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($u['fullname']) ?>" />
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" />
        </div>

      <?php endif; ?>

      <button class="btn btn-primary">Save</button>
      <a href="account.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</body>
</html>
