<?php
// Connect to the database
include "db.php";

// Check if ID was provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id = $_GET['id'];

    // Delete the resume from the database
    $query = "DELETE FROM resumes WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        // Redirect back to index after deletion
        header("Location: index.php");
        exit();
    } else {
        echo "Something went wrong. Please try again.";
    }

} else {
    echo "No resume ID provided.";
}
?>