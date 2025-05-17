<?php
session_start();
require 'connection.php';
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'client') {
  header("Location: loginpage.php");
  exit();
}

$user_id = $_SESSION['user'];
$type    = $_GET['type'] ?? '';
$id      = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errors  = [];

// Fetch existing data if editing
$existing = [];
if ($id) {
  switch ($type) {
    case 'experience':
      $tbl = 'experiences'; break;
    case 'education':
      $tbl = 'education'; break;
    case 'skill':
      $tbl = 'skills'; break;
    case 'basic':
    case 'resume':
      // basic/resume come from users table
      $tbl = 'users'; break;
    default:
      die("Invalid type.");
  }
  // only fetch if it's not resume (no file prefill)
  if ($type !== 'resume') {
    $stmt = $conn->prepare("SELECT * FROM {$tbl} WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc() ?: [];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  switch ($type) {
    case 'experience':
      $pos   = trim($_POST['position']) ?: $errors[] = 'Position is required.';
      $comp  = trim($_POST['company'])  ?: $errors[] = 'Company is required.';
      $start = $_POST['start_date']     ?: $errors[] = 'Start date is required.';
      $end   = $_POST['end_date']       ?: null;
      if (empty($errors)) {
        if ($id) {
          // UPDATE
          $sql = "UPDATE experiences 
                    SET position=?, company=?, start_date=?, end_date=?
                  WHERE id=? AND user_id=?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("ssssii", $pos, $comp, $start, $end, $id, $user_id);
        } else {
          // INSERT
          $sql = "INSERT INTO experiences (user_id, position, company, start_date, end_date)
                  VALUES (?,?,?,?,?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("issss", $user_id, $pos, $comp, $start, $end);
        }
        $stmt->execute();
        header("Location: account.php");
        exit();
      }
      break;

    case 'education':
      $deg  = trim($_POST['degree'])      ?: $errors[] = 'Degree is required.';
      $inst = trim($_POST['institution']) ?: $errors[] = 'Institution is required.';
      $yr   = $_POST['year']              ?: $errors[] = 'Year is required.';
      if (empty($errors)) {
        if ($id) {
          $sql = "UPDATE education 
                    SET degree=?, institution=?, year=?
                  WHERE id=? AND user_id=?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("ssiii", $deg, $inst, $yr, $id, $user_id);
        } else {
          $sql = "INSERT INTO education (user_id, degree, institution, year)
                  VALUES (?,?,?,?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("isss", $user_id, $deg, $inst, $yr);
        }
        $stmt->execute();
        header("Location: account.php");
        exit();
      }
      break;

    case 'skill':
      $skill = trim($_POST['name']) ?: $errors[] = 'Skill name is required.';
      if (empty($errors)) {
        if ($id) {
          $sql = "UPDATE skills SET name=? WHERE id=? AND user_id=?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("sii", $skill, $id, $user_id);
        } else {
          $sql = "INSERT INTO skills (user_id, name) VALUES (?,?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("is", $user_id, $skill);
        }
        $stmt->execute();
        header("Location: account.php");
        exit();
      }
      break;

    case 'resume':
      if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['resume']['tmp_name'];
        $name = time() . '_' . basename($_FILES['resume']['name']);
        move_uploaded_file($tmp, "uploads/resumes/$name");
        $sql = "UPDATE users SET resume=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
        header("Location: account.php");
        exit();
      } else {
        $errors[] = 'Please choose a resume file to upload.';
      }
      break;

    case 'basic':
      $full  = trim($_POST['fullname']) ?: $errors[] = 'Full name is required.';
      $email = trim($_POST['email'])    ?: $errors[] = 'Email is required.';
      if (empty($errors)) {
        $sql = "UPDATE users SET fullname=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
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
  <title><?= $id ? 'Edit' : 'Add' ?> <?= ucfirst($type) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <h3 class="mb-4"><?= $id ? 'Edit' : 'Add' ?> <?= ucfirst($type) ?></h3>
    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
      </div>
    <?php endif; ?>

    <form method="POST" <?= $type === 'resume' ? 'enctype="multipart/form-data"' : '' ?>>
      <?php if ($type === 'experience'): ?>
        <div class="mb-3">
          <label>Position</label>
          <input type="text" name="position" class="form-control"
                 value="<?= htmlspecialchars($existing['position'] ?? '') ?>" />
        </div>
        <div class="mb-3">
          <label>Company</label>
          <input type="text" name="company" class="form-control"
                 value="<?= htmlspecialchars($existing['company'] ?? '') ?>" />
        </div>
        <div class="row">
          <div class="col">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control"
                   value="<?= htmlspecialchars($existing['start_date'] ?? '') ?>" />
          </div>
          <div class="col">
            <label>End Date (optional)</label>
            <input type="date" name="end_date" class="form-control"
                   value="<?= htmlspecialchars($existing['end_date'] ?? '') ?>" />
          </div>
        </div>

      <?php elseif ($type === 'education'): ?>
        <div class="mb-3">
          <label>Degree</label>
          <input type="text" name="degree" class="form-control"
                 value="<?= htmlspecialchars($existing['degree'] ?? '') ?>" />
        </div>
        <div class="mb-3">
          <label>Institution</label>
          <input type="text" name="institution" class="form-control"
                 value="<?= htmlspecialchars($existing['institution'] ?? '') ?>" />
        </div>
        <div class="mb-3">
          <label>Year</label>
          <input type="number" name="year" min="1900" max="<?= date('Y') ?>" class="form-control"
                 value="<?= htmlspecialchars($existing['year'] ?? '') ?>" />
        </div>

      <?php elseif ($type === 'skill'): ?>
        <div class="mb-3">
          <label>Skill Name</label>
          <input type="text" name="name" class="form-control"
                 value="<?= htmlspecialchars($existing['name'] ?? '') ?>" />
        </div>

      <?php elseif ($type === 'resume'): ?>
        <div class="mb-3">
          <label>Upload Resume (PDF/DOCX)</label>
          <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" />
        </div>

      <?php elseif ($type === 'basic'): ?>
        <?php
          $u = $existing ?: $conn->query("SELECT fullname,email FROM users WHERE id=$user_id")
                             ->fetch_assoc();
        ?>
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" name="fullname" class="form-control"
                 value="<?= htmlspecialchars($u['fullname']) ?>" />
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($u['email']) ?>" />
        </div>
      <?php endif; ?>

      <button class="btn btn-primary"><?= $id ? 'Save Changes' : 'Save' ?></button>
      <a href="account.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</body>
</html>
