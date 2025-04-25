$(document).ready(function() {
    // Cache DOM elements
    const $mobileMenu = $('.mobile-menu');
    const $mobileMenuOverlay = $('.mobile-menu-overlay');
    const $sidebarToggleTop = $('#sidebarToggleTop');
    const $mobileMenuClose = $('.mobile-menu-close');
    const $navLinks = $('.mobile-menu .nav-link');
    const $collapseItems = $('.mobile-menu .collapse');

    // Initialize Bootstrap tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Handle menu toggle
    function toggleMobileMenu() {
        $mobileMenu.toggleClass('show');
        $mobileMenuOverlay.toggleClass('show');
        $('body').toggleClass('overflow-hidden');
    }

    // Event listeners
    $sidebarToggleTop.on('click', function(e) {
        e.preventDefault();
        toggleMobileMenu();
    });

    $mobileMenuClose.on('click', function(e) {
        e.preventDefault();
        toggleMobileMenu();
    });

    $mobileMenuOverlay.on('click', function() {
        toggleMobileMenu();
    });

    // Handle submenu toggles
    $navLinks.on('click', function(e) {
        const $this = $(this);
        const $target = $($this.data('target'));
        
        if ($target.length) {
            e.preventDefault();
            
            // Close other open submenus
            $collapseItems.not($target).collapse('hide');
            
            // Toggle current submenu
            $target.collapse('toggle');
        }
    });

    // Close mobile menu on window resize if screen becomes larger than mobile breakpoint
    $(window).on('resize', function() {
        if (window.innerWidth >= 769 && $mobileMenu.hasClass('show')) {
            toggleMobileMenu();
        }
    });

    // Handle escape key press
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $mobileMenu.hasClass('show')) {
            toggleMobileMenu();
        }
    });

    // Prevent scrolling when mobile menu is open
    function preventScroll(e) {
        e.preventDefault();
    }

    $mobileMenu.on('show.bs.collapse', function() {
        document.body.addEventListener('touchmove', preventScroll, { passive: false });
    });

    $mobileMenu.on('hide.bs.collapse', function() {
        document.body.removeEventListener('touchmove', preventScroll);
    });

    // Handle active state
    const currentPath = window.location.pathname;
    $('.mobile-menu .nav-link, .mobile-menu .collapse-item').each(function() {
        const $link = $(this);
        const href = $link.attr('href');
        
        if (href && currentPath.includes(href)) {
            $link.addClass('active');
            
            // If it's a submenu item, expand the parent menu
            const $parentCollapse = $link.closest('.collapse');
            if ($parentCollapse.length) {
                $parentCollapse.addClass('show');
                $(`[data-target="#${$parentCollapse.attr('id')}"]`).addClass('active');
            }
        }
    });
}); 