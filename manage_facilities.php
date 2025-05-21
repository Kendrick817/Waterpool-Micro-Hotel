<?php
/**
 * Manage Facilities
 * Displays a list of all facilities and provides options to add, edit, or delete facilities
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

try {
    // Fetch facilities from the database
    $query = "SELECT * FROM facilities ORDER BY facility_ID";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Error fetching facilities: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Facilities - Admin Panel</title>
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
                <h2 class="fw-bold h-font mb-4">Manage Amenities & Facilities</h2>

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
                            <a href="add_facility.php" class="btn btn-dark mb-3 shadow-none">
                                <i class="bi bi-plus-circle"></i> Add New Facility
                            </a>

                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['facility_ID']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td>
                                                        <?php
                                                            $description = htmlspecialchars($row['description']);
                                                            echo (strlen($description) > 100) ? substr($description, 0, 100) . '...' : $description;
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <a href="edit_facility.php?id=<?php echo $row['facility_ID']; ?>"
                                                               class="btn btn-sm btn-warning">
                                                                <i class="bi bi-pencil-square"></i> Edit
                                                            </a>
                                                            <a href="delete_facility.php?id=<?php echo $row['facility_ID']; ?>"
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Are you sure you want to delete this facility?');">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    No facilities found. Click "Add New Facility" to create your first facility.
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