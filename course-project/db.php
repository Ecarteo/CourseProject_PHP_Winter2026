<?php
// Database connection settings
$host = "sql213.infinityfree.com";
$user = "if0_41192534";
$password = "0w9Lye6zrpSdf2w";
$database = "if0_41192534_resume_builder";

// Create connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>