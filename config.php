<?php
/* ============================================
   DATABASE CONFIGURATION FILE
   Used by all pages to connect to MySQL
   ============================================ */

/* ---- DATABASE SETTINGS ---- */
$host = "localhost";      // Change if server is different
$username = "root";       // Your MySQL username
$password = "";           // Your MySQL password (empty in XAMPP)
$database = "employee_forum";  // Database name created in Navicat


/* ---- CREATE CONNECTION ---- */
$conn = new mysqli($host, $username, $password, $database);


/* ---- CHECK CONNECTION ---- */
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}


/* ---- SET CHARACTER ENCODING ---- */
$conn->set_charset("utf8mb4");


/* ============================================
   OPTIONAL: GLOBAL SETTINGS
   ============================================ */

// Set default timezone (important for task deadlines)
date_default_timezone_set('Asia/Kolkata');

?>
