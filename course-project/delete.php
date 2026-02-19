<?php
// Connect to the database
include "db.php";

// Delete resume from the database
$query = "DELETE FROM resumes WHERE id = $id";

if (mysqli_query($conn, $query)) {
    // Redirect back to the index after deleting resume
    header("Location: index.php");
    exit();
} else {
    echo "Something went wrong. Please try again.";
}
?>