<?php
session_start();
require('includes/db_config.php'); // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validate form data
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error'] = "All fields are required.";
        header('Location: contact.php');
        exit();
    }

    // Insert message into the database
    $stmt = $pdo_database->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $subject, $message])) {
        $_SESSION['success'] = "Your message has been sent successfully!";
        header('Location: contact.php');
        exit();
    } else {
        $_SESSION['error'] = "An error occurred. Please try again.";
        header('Location: contact.php');
        exit();
    }
}
?>