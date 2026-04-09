<?php
session_start();
include "db.php";

// Only logged-in users can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors  = [];
$success = "";
$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // --- UPDATE PROFILE ---
    if ($_POST['action'] === 'update') {
        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm  = trim($_POST['confirm_password']);

        if (empty($username) || strlen($username) < 3)                   $errors[] = "Username must be at least 3 characters.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
        if (!empty($password) && strlen($password) < 6)                  $errors[] = "New password must be at least 6 characters.";
        if (!empty($password) && $password !== $confirm)                  $errors[] = "Passwords do not match.";

        // Make sure username/email isn't taken by another user
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
            $stmt->execute([':username' => $username, ':email' => $email, ':id' => $user_id]);
            if ($stmt->fetch()) {
                $errors[] = "Username or email is already taken by another account.";
            }
        }

        if (empty($errors)) {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id");
                $stmt->execute([':username' => $username, ':email' => $email, ':password' => $hashed, ':id' => $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
                $stmt->execute([':username' => $username, ':email' => $email, ':id' => $user_id]);
            }

            $_SESSION['username'] = $username;
            $success = "Profile updated successfully!";

            // Refresh user data for the form
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch();
        }
    }

    // --- DELETE ACCOUNT ---
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);

        session_unset();
        session_destroy();

        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Builder - My Profile</title>
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h1 class="mb-4">My Profile</h1>

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

                <!-- Update Profile Form -->
                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="update">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" minlength="3" maxlength="50"
                            value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-muted">(leave blank to keep current)</span></label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                </form>

                <hr class="my-4">

                <!-- Delete Account -->
                <h5 class="text-danger">Danger Zone</h5>
                <p class="text-muted">Deleting your account is permanent and cannot be undone.</p>
                <form method="POST" action="profile.php" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger w-100">Delete My Account</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>