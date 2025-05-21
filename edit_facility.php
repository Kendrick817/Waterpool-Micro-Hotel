<?php
/**
 * Edit Facility
 * Allows admin to edit an existing facility
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

// Initialize variables
$facility = null;
$error_message = null;

// Check if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Fetch facility details
        $query = "SELECT * FROM facilities WHERE facility_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Facility not found");
        }

        $facility = $result->fetch_assoc();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
} else {
    $error_message = "No facility ID provided";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$error_message) {
    try {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];

        // Validate input
        if (empty($name) || empty($description)) {
            throw new Exception("Facility name and description are required");
        }

        // Handle image upload
        $image_name = $facility['icon']; // Keep the existing image by default

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

            // Upload the new image
            if (move_uploaded_file($image_tmp_name, $image_folder)) {
                // Delete the old image if it exists and is not the default image
                if ($facility['icon'] != 'default_feature.png' && file_exists("images/features/" . $facility['icon'])) {
                    unlink("images/features/" . $facility['icon']);
                }
            } else {
                throw new Exception("Failed to upload image");
            }
        }

        // Update facility in the database
        $query = "UPDATE facilities SET name = ?, description = ?, icon = ? WHERE facility_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("sssi", $name, $description, $image_name, $id);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Facility updated successfully!";
            header('Location: manage_facilities.php');
            exit;
        } else {
            throw new Exception("Failed to update facility: " . $stmt->error);
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
    <title>Edit Facility - Admin Panel</title>
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
                    <h2 class="fw-bold h-font">Edit Facility</h2>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                    <a href="manage_facilities.php" class="btn btn-outline-dark">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </a>
                <?php elseif ($facility): ?>
                    <div class="row justify-content-center mt-4">
                        <div class="col-lg-8 bg-white rounded shadow p-4">
                            <form action="edit_facility.php?id=<?php echo $facility['facility_ID']; ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $facility['facility_ID']; ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Facility Name</label>
                                    <input type="text" name="name" class="form-control shadow-none"
                                        value="<?php echo htmlspecialchars($facility['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <textarea name="description" class="form-control shadow-none" rows="4" required><?php echo htmlspecialchars($facility['description']); ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Facility Image</label>
                                    <?php if (!empty($facility['icon']) && file_exists("images/features/" . $facility['icon'])): ?>
                                        <div class="border p-3 mb-3">
                                            <p class="text-muted mb-2">Current Image:</p>
                                            <img src="images/features/<?php echo htmlspecialchars($facility['icon']); ?>"
                                                alt="Facility Image" class="img-fluid d-block mb-2" style="max-height: 200px;">
                                        </div>
                                    <?php endif; ?>

                                    <input type="file" name="image" accept="image/*" class="form-control shadow-none">
                                </div>



                                <div class="d-flex">
                                    <button type="submit" class="btn btn-dark shadow-none me-2">
                                        <i class="bi bi-save"></i> Update Facility
                                    </button>
                                    <a href="manage_facilities.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>