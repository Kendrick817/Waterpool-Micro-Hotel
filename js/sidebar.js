/**
 * Main Website Sidebar JavaScript
 * Handles sidebar toggle and other functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const sidebar = document.querySelector('.main-sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content');
    
    // Function to open sidebar
    function openSidebar() {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
        
        // For desktop view
        if (window.innerWidth >= 992) {
            mainContent.classList.add('shifted');
        }
    }
    
    // Function to close sidebar
    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
        
        // For desktop view
        if (window.innerWidth >= 992) {
            mainContent.classList.remove('shifted');
        }
    }
    
    // Toggle sidebar when clicking the toggle button
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            if (sidebar.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }
    
    // Close sidebar when clicking the close button
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    // Close sidebar when clicking the overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    // Close sidebar when pressing Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            // Desktop view
            mainContent.classList.add('shifted');
            sidebarOverlay.classList.remove('active');
        } else {
            // Mobile view
            mainContent.classList.remove('shifted');
            if (sidebar.classList.contains('active')) {
                sidebarOverlay.classList.add('active');
            }
        }
    });
    
    // Initialize sidebar state based on screen size
    if (window.innerWidth >= 992) {
        // Desktop view - sidebar is visible by default
        sidebar.classList.add('active');
        mainContent.classList.add('shifted');
    }
});
