<?php
// Database Configuration File
// This file connects to the MySQL database using both MySQLi and PDO

// Database login information
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'hotelwaterpool_websitedb';

// Create MySQLi connection
$conn = mysqli_connect($host, $user, $password, $database);

// Check MySQLi connection
if (!$conn) {
    die("MySQLi Database Connection Failed: " . mysqli_connect_error());
}

// Set character set to UTF-8
mysqli_set_charset($conn, "utf8");

// Create PDO connection
try {
    $pdo_database = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);

    // Set error mode to exceptions
    $pdo_database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Use prepared statements by default
    $pdo_database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Return associative arrays by default
    $pdo_database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("PDO Database Connection Failed: " . $e->getMessage());
}

// This function makes database inputs safe by escaping special characters
// It helps prevent SQL injection attacks
function escape_value($value) {
    global $conn;
    return mysqli_real_escape_string($conn, $value);
}
?>