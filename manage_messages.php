<?php
/**
 * Manage Messages
 * Displays a list of all contact messages and provides options to view or delete messages
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

try {
    // Fetch messages from the database
    // Check if contact_messages table exists, otherwise use messages table
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");
    if (mysqli_num_rows($check_table) > 0) {
        $query = "SELECT * FROM contact_messages ORDER BY id DESC";
    } else {
        $query = "SELECT * FROM messages ORDER BY id DESC";
    }
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Error fetching messages: " . mysqli_error($conn));
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
    <title>Manage Messages - Admin Panel</title>
    <?php require('includes/links.php'); ?>
    <link rel="stylesheet" href="styles/admin.css">
    <style>
        .btn i {
            vertical-align: middle;
            margin-right: 3px;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php require('includes/admin_sidebar.php'); ?>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="container-fluid">
                <h2 class="fw-bold h-font mb-4">Manage Messages</h2>

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
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Subject</th>
                                                <th>Message</th>
                                                <th>Date</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($message = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($message['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                                    <td>
                                                        <?php
                                                            $msg_text = htmlspecialchars($message['message']);
                                                            echo (strlen($msg_text) > 50) ? substr($msg_text, 0, 50) . '...' : $msg_text;
                                                        ?>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($message['created_at'])); ?></td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <a href="read_message.php?email=<?php echo urlencode($message['email']); ?>"
                                                               class="btn btn-sm btn-primary"
                                                               title="Read all messages from this user">
                                                                <i class="bi bi-book"></i> Read
                                                            </a>
                                                            <a href="delete_message.php?id=<?php echo $message['id']; ?>"
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Are you sure you want to delete this message?');">
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
                                    No messages found. When visitors send messages through the contact form, they will appear here.
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