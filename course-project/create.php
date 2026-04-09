<?php
session_start();
include "db.php";

// Only logged-in users can create resumes
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors  = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // reCAPTCHA validation
    $recaptcha_secret   = "6LeeeXAsAAAAALFtCDQ_3BgYPcCy4P19aminhs_i";
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $verify             = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $captcha_success    = json_decode($verify);

    if (!$captcha_success->success) {
        $errors[] = "Please complete the reCAPTCHA.";
    }

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

    // Handle file upload
    $resume_file = null;
    if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        $file_type     = mime_content_type($_FILES['resume_file']['tmp_name']);
        $max_size      = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only PDF, JPG, PNG, or GIF files are allowed.";
        } elseif ($_FILES['resume_file']['size'] > $max_size) {
            $errors[] = "File size must be under 5MB.";
        } else {
            $ext        = pathinfo($_FILES['resume_file']['name'], PATHINFO_EXTENSION);
            $resume_file = uniqid('resume_', true) . '.' . $ext;
            $upload_dir = 'uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!move_uploaded_file($_FILES['resume_file']['tmp_name'], $upload_dir . $resume_file)) {
                $errors[] = "File upload failed. Please try again.";
                $resume_file = null;
            }
        }
    }

    // Insert into database using prepared statement
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO resumes 
            (first_name, last_name, current_position, skills, email, phone, bio, resume_file)
            VALUES (:first_name, :last_name, :current_position, :skills, :email, :phone, :bio, :resume_file)");

        $stmt->execute([
            ':first_name'       => $first_name,
            ':last_name'        => $last_name,
            ':current_position' => $current_position,
            ':skills'           => $skills,
            ':email'            => $email,
            ':phone'            => $phone,
            ':bio'              => $bio,
            ':resume_file'      => $resume_file,
        ]);

        $success = "Resume created successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder - Create</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
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
        <h1 class="mb-4">Create Resume</h1>
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

        <form method="POST" action="create.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" maxlength="50"
                    value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" maxlength="50"
                    value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="current_position" class="form-label">Current Position</label>
                <input type="text" class="form-control" id="current_position" name="current_position" maxlength="100"
                    value="<?php echo isset($_POST['current_position']) ? htmlspecialchars($_POST['current_position']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Skills (separate with commas)</label>
                <input type="text" class="form-control" id="skills" name="skills"
                    value="<?php echo isset($_POST['skills']) ? htmlspecialchars($_POST['skills']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" minlength="10" maxlength="15"
                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Short Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="4" minlength="10" required><?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="resume_file" class="form-label">Upload Resume / CV <span class="text-muted">(PDF, JPG, PNG — max 5MB, optional)</span></label>
                <input type="file" class="form-control" id="resume_file" name="resume_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
            </div>

            <!-- Google reCAPTCHA -->
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="6LeeeXAsAAAAAI56x1t8OGGppJGVFmaQL-rlBQgY"></div>
            </div>

            <button type="submit" class="btn btn-primary">Save Resume</button>
        </form>
    </div>

    <!-- Bootstrap JS: https://getbootstrap.com/docs/5.3/getting-started/download/ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>