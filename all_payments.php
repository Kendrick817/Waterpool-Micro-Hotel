<?php
/**
 * All Payments
 * Displays a list of all payments for admin
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page if not logged in
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10; // Number of records per page
$offset = ($page - 1) * $limit;

try {
    // Start building the main query
    $query = "SELECT p.*, b.user_email, r.name as room_name
              FROM payments p
              JOIN bookings b ON p.booking_ID = b.booking_ID
              JOIN rooms r ON b.room_ID = r.room_ID";

    // Add WHERE if searching
    if (!empty($search)) {
        $search_safe = mysqli_real_escape_string($conn, $search);
        $query .= " WHERE b.user_email LIKE '%$search_safe%' OR r.name LIKE '%$search_safe%' OR p.transaction_id LIKE '%$search_safe%'";
    }

    // Add order
    $query .= " ORDER BY p.payment_date DESC";

    // Count total records for pagination
    $count_query = str_replace("p.*, b.user_email, r.name as room_name", "COUNT(*) as total", $query);
    $count_result = mysqli_query($conn, $count_query);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_records / $limit);

    // Add pagination
    $query .= " LIMIT $limit OFFSET $offset";

    // Execute the query
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Error fetching payments: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    $error_msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Payments - Admin Panel</title>
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
                <h2 class="fw-bold h-font mb-4">All Payments</h2>

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

                <?php if (isset($error_msg)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_msg; ?>
                    </div>
                <?php else: ?>

                <!-- Search Form -->
                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control shadow-none"
                            placeholder="Search by email, room name, or transaction ID"
                            value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-dark shadow-none">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>

                <div class="row mt-4">
                    <div class="col-12">
                        <?php if (isset($result) && mysqli_num_rows($result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th>ID</th>
                                            <th>Booking</th>
                                            <th>User</th>
                                            <th>Room</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Transaction ID</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($payment = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['payment_ID']); ?></td>
                                                <td>
                                                    <a href="manage_bookings.php?search=<?php echo htmlspecialchars($payment['booking_ID']); ?>" class="text-decoration-none">
                                                        #<?php echo htmlspecialchars($payment['booking_ID']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($payment['user_email']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['room_name']); ?></td>
                                                <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                                <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($payment['transaction_id']); ?></span></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($payment['payment_date'])); ?></td>
                                                <td>
                                                    <?php if ($payment['status'] == 'completed'): ?>
                                                        <span class="badge bg-success">Completed</span>
                                                    <?php elseif ($payment['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php elseif ($payment['status'] == 'failed'): ?>
                                                        <span class="badge bg-danger">Failed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($payment['status']); ?></span>
                                                    <?php endif; ?>
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
                                                    <a class="page-link" href="all_payments.php?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="all_payments.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="all_payments.php?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
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
                                    No payments found
                                <?php else: ?>
                                    No payments found
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
