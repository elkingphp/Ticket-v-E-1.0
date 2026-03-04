/* Enterprise Documentation JS */
document.addEventListener('DOMContentLoaded', function () {
    // Highlight current page in sidebar
    const currentPath = window.location.pathname.split('/').pop();
    const sidebarLinks = document.querySelectorAll('.doc-sidebar a');

    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || (currentPath === '' && href === 'index.html')) {
            link.classList.add('active');
        }
    });

    // Mobile Navigation Toggle (Optional)
    // Add logic here if a hamburger menu is added in the future
});
