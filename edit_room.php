<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}
require('includes/db_config.php');

// Get the room ID from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $room_id = $_GET['id'];

    try {
        // Fetch room details - use correct column name (room_ID instead of room_id)
        $query = "SELECT * FROM rooms WHERE room_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database query preparation failed: " . $conn->error);
        }

        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $room = $result->fetch_assoc();
        } else {
            throw new Exception("Room not found!");
        }
    } catch (Exception $e) {
        echo "<script>alert('" . $e->getMessage() . "'); window.location.href='manage_rooms.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid Room ID!'); window.location.href='manage_rooms.php';</script>";
    exit;
}

// Update room details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate and sanitize input
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $features = trim($_POST['features']);
        $max_guests = intval($_POST['max_guests']);

        // Validate required fields
        if (empty($name) || empty($features) || $price <= 0 || $max_guests <= 0) {
            throw new Exception("Please fill all required fields with valid values.");
        }

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Only JPG, JPEG, PNG and GIF files are allowed.");
            }

            // Get the original filename
            $original_filename = basename($_FILES['image']['name']);
            $image_tmp_name = $_FILES['image']['tmp_name'];

            // Create directory if it doesn't exist
            $upload_dir = "images/rooms/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Check if this is the same image that's already being used
            if ($original_filename == $room['image']) {
                // If it's the same filename, just keep using the existing image
                $image_name = $room['image'];
                $image_path = $upload_dir . $image_name;
                $file_already_exists = true;
            }
            // Check if the image already exists in the folder
            else if (file_exists($upload_dir . $original_filename)) {
                // If it exists, use the existing filename without saving again
                $image_name = $original_filename;
                $image_path = $upload_dir . $image_name;

                // Delete the old image if it's different from the one we're using now
                if (!empty($room['image']) && $room['image'] != $original_filename && file_exists($upload_dir . $room['image'])) {
                    unlink($upload_dir . $room['image']);
                }

                $file_already_exists = true;
            } else {
                // If it doesn't exist, create a unique filename
                $image_name = time() . "_" . $original_filename;
                $image_path = $upload_dir . $image_name;
                $file_already_exists = false;

                // Delete the old image if it exists
                if (!empty($room['image']) && file_exists($upload_dir . $room['image'])) {
                    unlink($upload_dir . $room['image']);
                }
            }

            // Only move the file if it doesn't already exist
            if ($file_already_exists || move_uploaded_file($image_tmp_name, $image_path)) {

                // Update the room details along with the image - use correct column names
                $update_query = "UPDATE rooms SET name = ?, price = ?, max_guests = ?, features = ?, image = ? WHERE room_ID = ?";
                $stmt = $conn->prepare($update_query);

                if (!$stmt) {
                    throw new Exception("Database query preparation failed: " . $conn->error);
                }

                $stmt->bind_param("sdissi", $name, $price, $max_guests, $features, $image_name, $room_id);
            } else {
                throw new Exception("Failed to upload image. Please try again.");
            }
        } else {
            // Update the room details without changing the image - use correct column names
            $update_query = "UPDATE rooms SET name = ?, price = ?, max_guests = ?, features = ? WHERE room_ID = ?";
            $stmt = $conn->prepare($update_query);

            if (!$stmt) {
                throw new Exception("Database query preparation failed: " . $conn->error);
            }

            $stmt->bind_param("sdisi", $name, $price, $max_guests, $features, $room_id);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Room updated successfully!'); window.location.href='manage_rooms.php';</script>";
            exit;
        } else {
            throw new Exception("Error updating room: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room - Admin Panel</title>
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
                <div class="mb-4">
                    <h2 class="fw-bold h-font">Edit Room</h2>
                </div>

        <div class="row justify-content-center mt-4">
            <div class="col-lg-8 bg-white rounded shadow p-4">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Room Name</label>
                            <input type="text" name="name" class="form-control shadow-none"
                                value="<?php echo htmlspecialchars($room['name']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Price /per night</label>
                            <input type="number" name="price" min="0" step="0.01" class="form-control shadow-none"
                                value="<?php echo htmlspecialchars($room['price']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Max Guests</label>
                            <input type="number" name="max_guests" min="1" class="form-control shadow-none"
                                value="<?php echo isset($room['max_guests']) ? htmlspecialchars($room['max_guests']) : '2'; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Features</label>
                        <textarea name="features" class="form-control shadow-none" rows="3" required
                            placeholder="Enter room features (e.g., King Size Bed, Private Bathroom, Ocean View)"><?php echo htmlspecialchars($room['features']); ?></textarea>
                    </div>



                    <div class="mb-4">
                        <label class="form-label fw-bold">Room Image</label>
                        <?php if (!empty($room['image'])): ?>
                            <div class="border p-3 mb-3">
                                <p class="text-muted mb-2">Current Image:</p>
                                <img src="images/rooms/<?php echo htmlspecialchars($room['image']); ?>" alt="Room Image"
                                    class="img-fluid d-block mb-2" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>

                        <input type="file" name="image" accept="image/*" class="form-control shadow-none">
                    </div>



                    <div class="d-flex">
                        <button type="submit" class="btn btn-dark shadow-none me-2">
                            <i class="bi bi-save"></i> Update Room
                        </button>
                        <a href="manage_rooms.php" class="btn btn-outline-secondary">Cancel</a>
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