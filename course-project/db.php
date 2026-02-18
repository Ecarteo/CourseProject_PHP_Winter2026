<?php
// Database connection settings
$host = "localhost";
$user = "root";
$password = "";
$database = "resume_builder";

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>