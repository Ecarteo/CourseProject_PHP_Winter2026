<?php
session_start();
include "db.php";

// Only logged-in users can edit resumes
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors  = [];
$success = "";

// Get resume ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch existing resume
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = :id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    die("Resume not found.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize inputs
    $first_name       = trim($_POST['first_name']);
    $last_name        = trim($_POST['last_name']);
    $current_position = trim($_POST['current_position']);
    $skills           = trim($_POST['skills']);
    $email            = trim($_POST['email']);
    $phone            = trim($_POST['phone']);
    $bio              = trim($_POST['bio']);

    // Validate
    if (empty($first_name))                                           $errors[] = "First name is required.";
    if (empty($last_name))                                            $errors[] = "Last name is required.";
    if (empty($current_position))                                     $errors[] = "Current position is required.";
    if (empty($skills))                                               $errors[] = "Skills are required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = "A valid email is required.";
    if (empty($phone))                                                $errors[] = "Phone number is required.";
    if (empty($bio))                                                  $errors[] = "Bio is required.";

    // Handle file upload (optional on update)
    $resume_file = $row['resume_file']; // Keep existing file by default
    if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $file_type     = mime_content_type($_FILES['resume_file']['tmp_name']);
        $max_size      = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only PDF, JPG, PNG, or GIF files are allowed.";
        } elseif ($_FILES['resume_file']['size'] > $max_size) {
            $errors[] = "File size must be under 5MB.";
        } else {
            $ext      = pathinfo($_FILES['resume_file']['name'], PATHINFO_EXTENSION);
            $new_file = uniqid('resume_', true) . '.' . $ext;
            $upload_dir = 'uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($_FILES['resume_file']['tmp_name'], $upload_dir . $new_file)) {
                // Delete old file if one existed
                if ($row['resume_file'] && file_exists($upload_dir . $row['resume_file'])) {
                    unlink($upload_dir . $row['resume_file']);
                }
                $resume_file = $new_file;
            } else {
                $errors[] = "File upload failed. Please try again.";
            }
        }
    }

    // Update database using prepared statement
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE resumes SET
            first_name       = :first_name,
            last_name        = :last_name,
            current_position = :current_position,
            skills           = :skills,
            email            = :email,
            phone            = :phone,
            bio              = :bio,
            resume_file      = :resume_file
            WHERE id = :id");

        $stmt->execute([
            ':first_name'       => $first_name,
            ':last_name'        => $last_name,
            ':current_position' => $current_position,
            ':skills'           => $skills,
            ':email'            => $email,
            ':phone'            => $phone,
            ':bio'              => $bio,
            ':resume_file'      => $resume_file,
            ':id'               => $id,
        ]);

        $success = "Resume updated successfully!";

        // Refresh row data for the form
        $row = array_merge($row, [
            'first_name'       => $first_name,
            'last_name'        => $last_name,
            'current_position' => $current_position,
            'skills'           => $skills,
            'email'            => $email,
            'phone'            => $phone,
            'bio'              => $bio,
            'resume_file'      => $resume_file,
        ]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder - Update</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <!-- Bootstrap CSS: https://getbootstrap.com/docs/5.3/getting-started/download/ -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Resume Builder</a>
            <div class="ms-auto">
                <span class="navbar-text text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="profile.php" class="btn btn-outline-light btn-sm me-2">My Profile</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Edit Resume</h1>
        <a href="index.php" class="btn btn-secondary mb-3">Back to List</a>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="update.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" maxlength="50"
                    value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" maxlength="50"
                    value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="current_position" class="form-label">Current Position</label>
                <input type="text" class="form-control" id="current_position" name="current_position" maxlength="100"
                    value="<?php echo htmlspecialchars($row['current_position']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Skills (separate with commas)</label>
                <input type="text" class="form-control" id="skills" name="skills"
                    value="<?php echo htmlspecialchars($row['skills']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo htmlspecialchars($row['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" minlength="10" maxlength="15"
                    value="<?php echo htmlspecialchars($row['phone']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Short Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="4" minlength="10" required><?php echo htmlspecialchars($row['bio']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="resume_file" class="form-label">Upload Resume / CV <span class="text-muted">(PDF, JPG, PNG — max 5MB)</span></label>
                <?php if ($row['resume_file']): ?>
                    <div class="mb-2">
                        <small class="text-muted">Current file: 
                            <a href="uploads/<?php echo htmlspecialchars($row['resume_file']); ?>" target="_blank">View uploaded file</a>
                        </small>
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="resume_file" name="resume_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                <div class="form-text">Leave blank to keep the existing file.</div>
            </div>

            <button type="submit" class="btn btn-primary">Update Resume</button>
        </form>
    </div>

    <!-- Bootstrap JS: https://getbootstrap.com/docs/5.3/getting-started/download/ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>