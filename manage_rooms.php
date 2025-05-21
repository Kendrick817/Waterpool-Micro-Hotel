<?php
// This is the manage rooms page
// It shows a list of all rooms and lets admins add, edit, or delete rooms

// Start a session to check if admin is logged in
session_start();

// If admin is not logged in, send them to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Connect to the database
require('includes/db_config.php');

try {
    // Get all rooms from the database, sorted by ID
    $query = "SELECT * FROM rooms ORDER BY room_ID";
    $result = mysqli_query($conn, $query);

    // Check if the query was successful
    if (!$result) {
        throw new Exception("Error fetching rooms: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    // Save error message if something went wrong
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Admin Panel</title>
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
                <h2 class="fw-bold h-font mb-4">Manage Rooms</h2>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    echo $_SESSION['success_msg'];
                    unset($_SESSION['success_msg']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                    echo $_SESSION['error_msg'];
                    unset($_SESSION['error_msg']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="row mt-4">
                <div class="col-12">
                    <a href="add_room.php" class="btn btn-dark mb-3 shadow-none">
                        <i class="bi bi-plus-circle"></i> Add New Room
                    </a>

                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Max Guests</th>
                                        <th>Features</th>
                                        <th>Image</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Loop through each room in the database
                                    while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <!-- Show room ID -->
                                            <td><?php echo htmlspecialchars($row['room_ID']); ?></td>

                                            <!-- Show room name -->
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>

                                            <!-- Show room price with peso sign -->
                                            <td>₱<?php echo htmlspecialchars($row['price']); ?></td>

                                            <!-- Show maximum number of guests -->
                                            <td>
                                                <?php echo isset($row['max_guests']) ? htmlspecialchars($row['max_guests']) . ' persons' : '2 persons'; ?>
                                            </td>

                                            <!-- Show room features (shortened if too long) -->
                                            <td>
                                                <?php
                                                    $features = htmlspecialchars($row['features']);
                                                    // If features text is longer than 100 characters, cut it off with "..."
                                                    echo (strlen($features) > 100) ? substr($features, 0, 100) . '...' : $features;
                                                ?>
                                            </td>

                                            <!-- Show room image thumbnail -->
                                            <td>
                                                <?php if (!empty($row['image'])): ?>
                                                    <img src="images/rooms/<?php echo htmlspecialchars($row['image']); ?>"
                                                        alt="Room Image" class="img-fluid" style="max-height: 50px;">
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- Edit and Delete buttons -->
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <!-- Edit button - links to edit_room.php with room ID -->
                                                    <a href="edit_room.php?id=<?php echo $row['room_ID']; ?>"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </a>

                                                    <!-- Delete button - links to delete_room.php with room ID -->
                                                    <!-- Shows confirmation dialog when clicked -->
                                                    <a href="delete_room.php?id=<?php echo $row['room_ID']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this room?');">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; // End of room loop ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No rooms found. Click "Add New Room" to create your first room.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>