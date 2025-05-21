<?php
/**
 * Initialize Admin User
 * This script creates or updates the default admin user
 */

require('includes/db_config.php');

try {
    // Default admin credentials
    $username = 'admin';
    $password = md5('admin123'); // Using MD5 for password hashing to match login system

    // Check if admin user already exists
    $check_query = "SELECT * FROM admin WHERE username = ?";
    $check_stmt = $conn->prepare($check_query);

    if (!$check_stmt) {
        throw new Exception("Error preparing query: " . $conn->error);
    }

    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Admin user already exists, update the password
        $update_query = "UPDATE admin SET password = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_query);

        if (!$update_stmt) {
            throw new Exception("Error preparing update query: " . $conn->error);
        }

        $update_stmt->bind_param("ss", $password, $username);

        if ($update_stmt->execute()) {
            echo "<div style='font-family: \"Poppins\", sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;'>";
            echo "<h2 style='color: #4CAF50;'>Success!</h2>";
            echo "<p>Admin user updated successfully.</p>";
            echo "<p>You can now login with the following credentials:</p>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> admin</li>";
            echo "<li><strong>Password:</strong> admin123</li>";
            echo "</ul>";
            echo "<p><a href='admin_login.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Go to Admin Login</a></p>";
            echo "</div>";
        } else {
            throw new Exception("Error updating admin user: " . $update_stmt->error);
        }
    } else {
        // Insert default admin user
        $insert_query = "INSERT INTO admin (username, password) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);

        if (!$insert_stmt) {
            throw new Exception("Error preparing insert query: " . $conn->error);
        }

        $insert_stmt->bind_param("ss", $username, $password);

        if ($insert_stmt->execute()) {
            echo "<div style='font-family: \"Poppins\", sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;'>";
            echo "<h2 style='color: #4CAF50;'>Success!</h2>";
            echo "<p>Admin user created successfully.</p>";
            echo "<p>You can now login with the following credentials:</p>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> " . htmlspecialchars($username) . "</li>";
            echo "<li><strong>Password:</strong> " . htmlspecialchars($plain_password) . "</li>";
            echo "</ul>";
            echo "<p><a href='admin_login.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Go to Admin Login</a></p>";
            echo "</div>";
        } else {
            throw new Exception("Error creating admin user: " . $insert_stmt->error);
        }
    }
} catch (Exception $e) {
    echo "<div style='font-family: \"Poppins\", sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #fff0f0;'>";
    echo "<h2 style='color: #f44336;'>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><a href='index.php' style='display: inline-block; padding: 10px 15px; background-color: #f44336; color: white; text-decoration: none; border-radius: 4px;'>Go to Homepage</a></p>";
    echo "</div>";
} finally {
    // Close the database connection
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>