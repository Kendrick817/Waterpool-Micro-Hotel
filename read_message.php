<?php
/**
 * Read Message
 * Displays all messages from a specific user
 */

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require('includes/db_config.php');

// Initialize variables
$messages = [];
$user_info = null;
$error_message = null;

// Check if email is provided
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email = $_GET['email'];

    try {
        // Check which table exists
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'contact_messages'");
        $table_name = mysqli_num_rows($check_table) > 0 ? 'contact_messages' : 'messages';

        // Get user information from the first message
        $user_query = "SELECT name, email FROM $table_name WHERE email = ? ORDER BY created_at DESC LIMIT 1";
        $user_stmt = $conn->prepare($user_query);

        if (!$user_stmt) {
            throw new Exception("Error preparing user query: " . $conn->error);
        }

        $user_stmt->bind_param("s", $email);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $user_info = $user_result->fetch_assoc();

            // Fetch all messages from this user
            $messages_query = "SELECT * FROM $table_name WHERE email = ? ORDER BY created_at DESC";
            $messages_stmt = $conn->prepare($messages_query);

            if (!$messages_stmt) {
                throw new Exception("Error preparing messages query: " . $conn->error);
            }

            $messages_stmt->bind_param("s", $email);
            $messages_stmt->execute();
            $messages_result = $messages_stmt->get_result();

            while ($message = $messages_result->fetch_assoc()) {
                $messages[] = $message;
            }
        } else {
            $error_message = "No messages found for this email address.";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
} else {
    $error_message = "No email address provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Messages - Admin Panel</title>
    <?php require('includes/links.php'); ?>
    <link rel="stylesheet" href="styles/admin.css">
    <style>
        .message-card {
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .message-body {
            padding: 15px;
            background-color: #fff;
        }
        .message-date {
            color: #6c757d;
            font-size: 0.85rem;
        }
        .message-subject {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .user-info {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .user-email {
            font-weight: bold;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <?php require('includes/admin_sidebar.php'); ?>

        <!-- Main Content -->
        <div class="admin-content">
            <div class="container-fluid p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold h-font">Read Messages</h2>
                </div>

                <?php if (isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_msg'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php elseif ($user_info): ?>
                    <div class="user-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?php echo htmlspecialchars($user_info['name']); ?></h4>
                                <p class="user-email">
                                    <i class="bi bi-envelope"></i>
                                    <?php echo htmlspecialchars($user_info['email']); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-3">Message History (<?php echo count($messages); ?>)</h4>

                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message-card">
                                <div class="message-header">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="message-date">
                                                <i class="bi bi-calendar"></i>
                                                <?php echo date('F j, Y, g:i a', strtotime($message['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="message-body">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>

                                    <div class="text-end mt-3">
                                        <?php
                                        $msg_reply_subject = "Re: " . htmlspecialchars($message['subject']);
                                        $msg_gmail_url = "https://mail.google.com/mail/?view=cm&fs=1&to=" . urlencode($message['email']) . "&su=" . urlencode($msg_reply_subject);
                                        ?>
                                        <a href="<?php echo $msg_gmail_url; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="bi bi-google"></i> Reply to this message
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No messages found for this user.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
