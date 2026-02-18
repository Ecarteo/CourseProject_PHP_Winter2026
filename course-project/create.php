<?php
// Connect to the database
include "db.php";

// Variables declaration
$errors = [];
$success = "";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Server-side validation: check that no field is empty
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $current_position = trim($_POST['current_position']);
    $skills = trim($_POST['skills']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);

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

        <!-- Resume Form -->
        <form method="POST" action="create.php">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="current_position" class="form-label">Current Position</label>
                <input type="text" class="form-control" id="current_position" name="current_position" required>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Skills (separate with commas)</label>
                <input type="text" class="form-control" id="skills" name="skills" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="mb-3">
                <label for="bio" class="form-label">Short Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Resume</button>
        </form>
    </div> 

    <!-- Bootstrap JS: https://getbootstrap.com/docs/5.3/getting-started/download/ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>