<?php
// Connect to the database
include "db.php";

// Variables declaration
$errors = [];
$success = "";

// Get the resume ID from the URL
$id = $_GET['id'];

// Fetch the existing resume data from the database
$query = "SELECT * FROM resumes WHERE id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Server-side validation: check that no field is empty
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

    // If no errors, update the database
    if (empty($errors)) {
        $query = "UPDATE resumes SET
            first_name='$first_name', 
            last_name='$last_name', 
            current_position='$current_position', 
            skills='$skills', 
            email='$email', 
            phone='$phone', 
            bio='$bio' 
            WHERE id=$id";

        if (mysqli_query($conn, $query)) {
            $success = "Resume updated successfully!";
            // Refresh the row data after update
            $row = ['first_name' => $first_name, 'last_name' => $last_name,
                    'current_position' => $current_position, 'skills' => $skills,
                    'email' => $email, 'phone' => $phone, 'bio' => $bio];
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
    <title>Resume Builder - Update</title>
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

        <!-- Resume Form pre-filled with existing data -->
        <form method="POST" action="update.php?id=<?php echo $id; ?>">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" maxlength="50" value="<?php echo $row['first_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" maxlength="50" value="<?php echo $row['last_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="current_position" class="form-label">Current Position</label>
                <input type="text" class="form-control" id="current_position" name="current_position" maxlength="100" value="<?php echo $row['current_position']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Skills (separate with commas)</label>
                <input type="text" class="form-control" id="skills" name="skills" value="<?php echo $row['skills']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['email']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" minlength="10" maxlength="15" value="<?php echo $row['phone']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Short Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="4" minlength="10" required><?php echo $row['bio']; ?></textarea>
            </div>

            <!-- Updating button -->
            <button type="submit" class="btn btn-primary">Update Resume</button>
        </form>
    </div>

    <!-- Bootstrap JS: https://getbootstrap.com/docs/5.3/getting-started/download/ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>