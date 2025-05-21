<?php

//Add facility

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

// Initialize error message
$error_message = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = $_POST['name'];
        $description = $_POST['description'];

        // Validate input
        if (empty($name) || empty($description)) {
            throw new Exception("Facility name and description are required");
        }

        // Handle image upload
        $image_name = 'default_feature.png'; // Default image

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Only JPG, JPEG, PNG and GIF files are allowed");
            }

            // Generate a unique filename
            $image_name = time() . "_" . basename($_FILES['image']['name']);
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_folder = "images/features/" . $image_name;

            // Create directory if it doesn't exist
            if (!is_dir("images/features/")) {
                mkdir("images/features/", 0755, true);
            }

            // Upload the image
            if (!move_uploaded_file($image_tmp_name, $image_folder)) {
                throw new Exception("Failed to upload image");
            }
        }

        // Insert facility into the database
        $query = "INSERT INTO facilities (name, description, icon) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("sss", $name, $description, $image_name);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Facility added successfully!";
            header('Location: manage_facilities.php');
            exit;
        } else {
            throw new Exception("Failed to add facility: " . $stmt->error);
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Facility - Admin Panel</title>
    <?php require('includes/links.php'); ?>
    <link rel="stylesheet" href="styles/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php require('includes/admin_sidebar.php'); ?>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold h-font">Add New Facility</h2>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="row justify-content-center mt-4">
                    <div class="col-lg-8 bg-white rounded shadow p-4">
                        <form action="add_facility.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Facility Name</label>
                                <input type="text" name="name" class="form-control shadow-none" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control shadow-none" rows="4" required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Image</label>
                                <input type="file" name="image" accept="images/*" class="form-control shadow-none">
                            </div>



                            <div class="d-flex">
                                <button type="submit" class="btn btn-dark shadow-none me-2">
                                    <i class="bi bi-plus-circle"></i> Add Facility
                                </button>
                                <a href="manage_facilities.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>