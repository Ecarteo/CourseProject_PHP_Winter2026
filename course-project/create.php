<?php
// Connect to the database
include "db.php";

// Variables declaration
$errors = [];
$success = "";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Server-side reCAPTCHA validation
    $recaptcha_secret = "6LeeeXAsAAAAALFtCDQ_3BgYPcCy4P19aminhs_i";
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $errors[] = "Please complete the reCAPTCHA.";
    }

    // Server-side validation: check that no field is empty
    // Added mysqli_real_escape_stringg since adding apostrophes would break the form.
    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $current_position = mysqli_real_escape_string($conn, trim($_POST['current_position']));
    $skills = mysqli_real_escape_string($conn, trim($_POST['skills']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $bio = mysqli_real_escape_string($conn, trim($_POST['bio']));

    // Possible errors
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($current_position)) $errors[] = "Current position is required.";
    if (empty($skills)) $errors[] = "Skills are required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($bio)) $errors[] = "Bio is required.";

    // If no errors, insert into database
    if (empty($errors)) {
        $query = "INSERT INTO resumes (first_name, last_name, current_position, skills, email, phone, bio) 
                  VALUES ('$first_name', '$last_name', '$current_position', '$skills', '$email', '$phone', '$bio')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Resume created successfully!";
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder - Create</title>
    <!-- Bootstrap CSS: https://getbootstrap.com/docs/5.3/getting-started/download/ -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    
    <!-- Website structure -->
    <div class="container mt-5">
        <h1 class="mb-4">Edit Resume</h1>
        <a href="index.php" class="btn btn-secondary mb-3">Back to List</a>

        <!-- Show errors if any -->
        <?php if (!empty($errors)) { ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($errors as $error) { ?>
                        <li><?php echo $error; ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <!-- Show success message -->
        <?php if ($success) { ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php } ?>

        <!-- Resume Form -->
        <!-- Elements will be re-added after form submission using isset. -->
        <form method="POST" action="create.php">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="current_position" class="form-label">Current Position</label>
                <input type="text" class="form-control" id="current_position" name="current_position" value="<?php echo isset($_POST['current_position']) ? $_POST['current_position'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Skills (separate with commas)</label>
                <input type="text" class="form-control" id="skills" name="skills" value="<?php echo isset($_POST['skills']) ? $_POST['skills'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Short Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="4" required><?php echo isset($_POST['bio']) ? $_POST['bio'] : ''; ?></textarea>
            </div>

            <!-- Google reCAPTCHA -->
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="6LeeeXAsAAAAAI56x1t8OGGppJGVFmaQL-rlBQgY"></div>
            </div>
            <!-- Submitting button -->
            <button type="submit" class="btn btn-primary">Save Resume</button>
        </form>
    </div> 

    <!-- Bootstrap JS: https://getbootstrap.com/docs/5.3/getting-started/download/ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>