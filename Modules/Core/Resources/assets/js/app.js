// Core JS
import './notifications.js';

console.log('Core module initialized');

// Set DataTables defaults
if (window.APP_LOCALE === 'ar' && typeof $.fn.dataTable !== 'undefined') {
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            url: window.DT_LANG_URL
        },
        direction: 'rtl'
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('topnav-hamburger-icon');
    if (hamburger) {
        hamburger.addEventListener('click', function () {
            document.body.classList.toggle('vertical-sidebar-enable');
        });
    }
});
