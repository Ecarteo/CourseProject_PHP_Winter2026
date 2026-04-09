<?php
session_start();
include "db.php";

// Only logged-in users can delete resumes
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id'])) {

    $id = (int)$_GET['id'];

    // Fetch the resume file name before deleting
    $stmt = $pdo->prepare("SELECT resume_file FROM resumes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if ($row) {
        // Delete the uploaded file from the server if it exists
        if ($row['resume_file'] && file_exists('uploads/' . $row['resume_file'])) {
            unlink('uploads/' . $row['resume_file']);
        }

        // Delete the resume record using a prepared statement
        $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    header("Location: index.php");
    exit();

} else {
    echo "No resume ID provided.";
}
?>