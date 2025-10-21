<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'portofolio_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session
session_start();

// Base URL
define('BASE_URL', 'http://localhost/portfolio/');

// Upload directory
define('UPLOAD_DIR', 'uploads/');

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function to sanitize input
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}
?>