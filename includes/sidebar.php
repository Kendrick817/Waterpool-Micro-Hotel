<?php
/**
 * Main Website Sidebar
 * Provides navigation for the main website
 */

// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Only start a session if one doesn't already exist
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Main Website Sidebar -->
<div class="main-sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <img src="images/logo/logo.png" alt="Waterpool Hotel" class="sidebar-logo">
            <span>Waterpool Hotel</span>
        </a>
        <button class="sidebar-close" id="sidebarClose">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    
    <div class="sidebar-user">
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
            <div class="user-info">
                <i class="bi bi-person-circle"></i>
                <span><?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'Guest'; ?></span>
            </div>
            <div class="user-actions">
                <a href="my_bookings.php" class="btn btn-sm btn-outline-light">My Bookings</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
            </div>
        <?php else: ?>
            <div class="guest-info">
                <i class="bi bi-person"></i>
                <span>Welcome, Guest</span>
            </div>
            <div class="guest-actions">
                <a href="login.php" class="btn btn-sm btn-outline-light">Login</a>
                <a href="register.php" class="btn btn-sm btn-outline-light">Register</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="bi bi-house-fill"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'rooms.php') ? 'active' : ''; ?>">
                <a href="rooms.php">
                    <i class="bi bi-door-closed-fill"></i>
                    <span>Rooms</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'facilities.php') ? 'active' : ''; ?>">
                <a href="facilities.php">
                    <i class="bi bi-stars"></i>
                    <span>Aminities & Facilities</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">
                <a href="contact.php">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Contact Us</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">
                <a href="about.php">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>About</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div class="social-icons">
            <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
        </div>
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> Waterpool Hotel
        </div>
    </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar Toggle Button -->
<div class="sidebar-toggle">
    <button id="sidebarToggle" class="btn">
        <i class="bi bi-list"></i>
    </button>
</div>
