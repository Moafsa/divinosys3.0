(function($) {
  "use strict"; // Start of use strict

  // Check if we're on mobile
  var isMobile = function() {
    return $(window).width() < 768;
  };

  // Function to handle sidebar state
  var handleSidebarState = function() {
    if (isMobile()) {
      $(".sidebar").addClass("toggled");
      $("body").addClass("sidebar-toggled");
      $('.sidebar .collapse').collapse('hide');
    }
  };

  // Initial state check
  handleSidebarState();

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    e.preventDefault();
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    
    // Store the state in localStorage
    localStorage.setItem('sidebarToggled', $(".sidebar").hasClass("toggled"));

    if ($(".sidebar").hasClass("toggled")) {
      $('.sidebar .collapse').collapse('hide');
    }
  });

  // Handle window resize
  $(window).resize(function() {
    handleSidebarState();
  });

  // Restore sidebar state from localStorage on page load
  $(document).ready(function() {
    var sidebarState = localStorage.getItem('sidebarToggled');
    if (sidebarState === 'true' || isMobile()) {
      $(".sidebar").addClass("toggled");
      $("body").addClass("sidebar-toggled");
      $('.sidebar .collapse').collapse('hide');
    }
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    e.preventDefault();
  });

})(jQuery); // End of use strict
