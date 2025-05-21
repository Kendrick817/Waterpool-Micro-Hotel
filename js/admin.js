/**
 * Admin Panel JavaScript
 * Handles sidebar toggle and other admin panel functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const adminContent = document.querySelector('.admin-content');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            // Toggle sidebar visibility on mobile
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-visible');
            } else {
                // Toggle sidebar collapse on desktop
                sidebar.classList.toggle('collapsed');
                adminContent.classList.toggle('expanded');
            }
        });
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnToggle) {
                sidebar.classList.remove('mobile-visible');
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-visible');
        }
    });
});
