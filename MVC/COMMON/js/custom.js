// Mobile Menu Functionality
$(document).ready(function() {
    const $mobileMenu = $('.mobile-menu');
    const $mobileMenuOverlay = $('.mobile-menu-overlay');
    const $sidebarToggleTop = $('#sidebarToggleTop');
    const $mobileMenuClose = $('.mobile-menu-close');
    
    // Toggle mobile menu
    $sidebarToggleTop.on('click', function(e) {
        e.preventDefault();
        toggleMobileMenu();
    });
    
    // Close mobile menu
    $mobileMenuClose.on('click', function(e) {
        e.preventDefault();
        closeMobileMenu();
    });
    
    // Close menu when clicking overlay
    $mobileMenuOverlay.on('click', function(e) {
        e.preventDefault();
        closeMobileMenu();
    });
    
    // Handle submenu toggles
    $('.mobile-menu .nav-link[data-toggle="collapse"]').on('click', function(e) {
        e.preventDefault();
        const $this = $(this);
        const $parent = $this.closest('.nav-item');
        const $icon = $this.find('.submenu-icon');
        
        // Close other open submenus
        $('.mobile-menu .nav-item').not($parent).find('.collapse').collapse('hide');
        $('.mobile-menu .nav-link[data-toggle="collapse"]').not($this).attr('aria-expanded', 'false');
        $('.mobile-menu .submenu-icon').not($icon).css('transform', 'rotate(0deg)');
        
        // Toggle current submenu
        const isExpanded = $this.attr('aria-expanded') === 'true';
        $this.attr('aria-expanded', !isExpanded);
        $icon.css('transform', isExpanded ? 'rotate(0deg)' : 'rotate(90deg)');
    });
    
    // Close mobile menu on window resize if screen becomes larger
    $(window).on('resize', function() {
        if (window.innerWidth >= 768) {
            closeMobileMenu();
        }
    });
    
    // Helper functions
    function toggleMobileMenu() {
        $mobileMenu.toggleClass('show');
        $mobileMenuOverlay.toggleClass('show');
        $('body').toggleClass('mobile-menu-open');
    }
    
    function closeMobileMenu() {
        $mobileMenu.removeClass('show');
        $mobileMenuOverlay.removeClass('show');
        $('body').removeClass('mobile-menu-open');
        
        // Close all open submenus
        $('.mobile-menu .collapse').collapse('hide');
        $('.mobile-menu .nav-link[data-toggle="collapse"]').attr('aria-expanded', 'false');
        $('.mobile-menu .submenu-icon').css('transform', 'rotate(0deg)');
    }
    
    // Handle escape key press
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $mobileMenu.hasClass('show')) {
            closeMobileMenu();
        }
    });
}); 