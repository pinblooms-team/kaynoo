require([
    'jquery',
    'owlcarousel'
], function ($) {
    "use strict";
    // Block - category slider
    jQuery(".category-slides").owlCarousel({
        items: 7,
        autoplay: false,
        loop: false,
        nav: true,
        dots: true,
        autoplayHoverPause: true,
        margin: 10,
        lazyLoad: true,
        responsiveClass: true,
        responsive: {
            0: {
                items: 3,

            },
            600: {
                items: 4,

            },

            768: {
                items: 5,
                dots: false,

            },
            1000: {
                items: 7,

            },

        }
    });

    jQuery(window).scroll(function () {
        if (jQuery(this).scrollTop() > 80) {
            jQuery('.mobile-style .header-top-content').addClass('fixed');
        } else {
            jQuery('.mobile-style .header-top-content').removeClass('fixed');
        }
    });

}
);