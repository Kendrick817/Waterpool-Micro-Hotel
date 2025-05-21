<?php
// This is the manage bookings page
// It shows all bookings and lets admins confirm, cancel, or delete them

// Start a session to check if admin is logged in
session_start();

// If admin is not logged in, send them to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Connect to the database
require('includes/db_config.php');

// Get search term from URL if it exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Set up pagination
$limit = 10; // Show 10 bookings per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Calculate starting position

try {
    // Create SQL query to get booking information
    // We join the bookings table with rooms table to get room names
    $query = "SELECT b.booking_ID, b.room_ID, b.user_email, b.check_in, b.check_out,
              b.adults, b.children, b.status, r.name as room_name
              FROM bookings b
              JOIN rooms r ON b.room_ID = r.room_ID";

    // If user is searching, add search conditions
    if (!empty($search)) {
        // Make search term safe to use in SQL
        $search_safe = mysqli_real_escape_string($conn, $search);
        // Search in user email or room name
        $query .= " WHERE b.user_email LIKE '%$search_safe%' OR r.name LIKE '%$search_safe%'";
    }

    // Sort by booking ID (newest first)
    $query .= " ORDER BY b.booking_ID DESC";

    // Add pagination limits
    $query .= " LIMIT $limit OFFSET $offset";

    // Run the query
    $result = mysqli_query($conn, $query);

    // Check if query was successful
    if (!$result) {
        throw new Exception("Error fetching bookings: " . mysqli_error($conn));
    }

    // Count total bookings for pagination
    $total_query = "SELECT COUNT(*) AS total FROM bookings b JOIN rooms r ON b.room_ID = r.room_ID";
    // Add search conditions to count query too
    if (!empty($search)) {
        $total_query .= " WHERE b.user_email LIKE '%$search_safe%' OR r.name LIKE '%$search_safe%'";
    }
    $total_result = mysqli_query($conn, $total_query);

    // Check if count query was successful
    if (!$total_result) {
        throw new Exception("Error counting bookings: " . mysqli_error($conn));
    }

    // Calculate total pages needed
    $total_row = mysqli_fetch_assoc($total_result);
    $total_pages = ceil($total_row['total'] / $limit); // Round up to get total pages
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
    <title>Manage Bookings - Admin Panel</title>
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
                <h2 class="fw-bold h-font mb-4">Manage Bookings</h2>

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
                    <!-- Search Form -->
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control shadow-none"
                                placeholder="Search email or room name"
                                value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-dark shadow-none">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>

                    <div class="row mt-4">
                        <div class="col-12">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th>ID</th>
                                                <th>Room</th>
                                                <th>User Email</th>
                                                <th>Check-in</th>
                                                <th>Check-out</th>
                                                <th>Guests</th>
                                                <th>Status</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['booking_ID']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['room_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($row['check_in'])); ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($row['check_out'])); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row['adults']); ?> Adults,
                                                        <?php echo htmlspecialchars($row['children']); ?> Children
                                                    </td>
                                                    <td>
                                                        <?php
                                                        // Set the badge color based on booking status
                                                        $status_class = '';
                                                        if ($row['status'] == 'Confirmed') {
                                                            $status_class = 'bg-success'; // Green for confirmed
                                                        } elseif ($row['status'] == 'Pending') {
                                                            $status_class = 'bg-warning text-dark'; // Yellow for pending
                                                        } elseif ($row['status'] == 'Cancelled') {
                                                            $status_class = 'bg-danger'; // Red for cancelled
                                                        }
                                                        ?>
                                                        <!-- Display status badge with appropriate color -->
                                                        <span class="badge <?php echo $status_class; ?>">
                                                            <?php echo htmlspecialchars($row['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                            <!-- Only show Confirm button if booking is not already confirmed -->
                                                            <?php if ($row['status'] != 'Confirmed'): ?>
                                                                <a href="update_booking_status.php?id=<?php echo $row['booking_ID']; ?>&status=Confirmed"
                                                                   class="btn btn-sm btn-success">
                                                                    <i class="bi bi-check-circle"></i> Confirm
                                                                </a>
                                                            <?php endif; ?>

                                                            <!-- Only show Cancel button if booking is not already cancelled -->
                                                            <?php if ($row['status'] != 'Cancelled'): ?>
                                                                <a href="update_booking_status.php?id=<?php echo $row['booking_ID']; ?>&status=Cancelled"
                                                                   class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-x-circle"></i> Cancel
                                                                </a>
                                                            <?php endif; ?>

                                                            <!-- Delete button - always shown -->
                                                            <!-- Shows confirmation dialog when clicked -->
                                                            <a href="delete_booking.php?id=<?php echo $row['booking_ID']; ?>"
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this booking?');">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Links -->
                                <?php if ($total_pages > 1): ?>
                                    <div class="d-flex justify-content-center mt-4">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination">
                                                <?php if ($page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="manage_bookings.php?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                                            <span aria-hidden="true">&laquo;</span>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                        <a class="page-link" href="manage_bookings.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>

                                                <?php if ($page < $total_pages): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="manage_bookings.php?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                                            <span aria-hidden="true">&raquo;</span>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <?php if (!empty($search)): ?>
                                        No bookings found
                                    <?php else: ?>
                                        No bookings found
                                    <?php endif; ?>
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