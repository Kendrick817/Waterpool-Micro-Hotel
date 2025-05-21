<?php
/**
 * Admin Sidebar
 * Provides navigation for the admin panel
 */

// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Admin Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-container text-center">
            <a href="admin.php" title="Go to Dashboard">
                <div class="logo-circle">
                    <img src="images/logo/logo.png" alt="Waterpool Hotel Logo" class="sidebar-logo">
                </div>
            </a>
        </div>
        <h3>
            <a href="index.php" class="hotel-name-link" title="View Website" target="_blank">
                Waterpool Hotel
                <i class="bi bi-box-arrow-up-right hotel-link-icon"></i>
            </a>
        </h3>
        <div class="sidebar-admin-info">
            <?php if (isset($_SESSION['admin_username'])): ?>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">
                <a href="admin.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'manage_rooms.php') ? 'active' : ''; ?>">
                <a href="manage_rooms.php">
                    <i class="bi bi-door-closed-fill"></i>
                    <span>Rooms</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'manage_bookings.php') ? 'active' : ''; ?>">
                <a href="manage_bookings.php">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'manage_facilities.php') ? 'active' : ''; ?>">
                <a href="manage_facilities.php">
                    <i class="bi bi-tools"></i>
                    <span>Aminities & Facilities</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'manage_messages.php') ? 'active' : ''; ?>">
                <a href="manage_messages.php">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Messages</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'all_payments.php') ? 'active' : ''; ?>">
                <a href="all_payments.php">
                    <i class="bi bi-credit-card-fill"></i>
                    <span>Payments</span>
                </a>
            </li>
            <li class="sidebar-divider"></li>
            <li>
                <a href="logout.php?admin=1" class="logout-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Mobile Toggle Button -->
<div class="sidebar-toggle">
    <button id="sidebarToggle" class="btn">
        <i class="bi bi-list"></i>
    </button>
</div>
