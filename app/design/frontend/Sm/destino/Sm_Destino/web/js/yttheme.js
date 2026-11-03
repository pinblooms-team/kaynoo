define(["jquery","jqueryfancyboxpack"], function ($) {
    $(document).ready(function () {
        // Fix hover on IOS
        $('body').bind('touchstart', function () {
        });
         // instagrams
        $(".fancybox").fancybox({
            openEffect  : 'none',
            closeEffect : 'none'
         });
        // Go to top
        $('#yt-totop').click(function () {
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });

        // Menu ontop

        $window = $(window);
        if ($('.ontop_element').length) {
            menu_offset_top = $('.ontop_element').offset().top;
            function processScroll() {
                var scrollTop = $window.scrollTop();
                if (scrollTop >= menu_offset_top) {
                    $('.ontop_element').addClass('menu-on-top');
                    $('body').addClass('body-on-top');
                } else if (scrollTop <= menu_offset_top) {
                    $('.ontop_element').removeClass('menu-on-top');
                    $('body').removeClass('body-on-top');
                }
            }

            processScroll();
            $window.scroll(function () {
                processScroll();
            });
        }

        // Show more item categories vertical
        var limit;
        limit = 6;
        var i;
        i = 0;
        var items;
        items = $('.block-menu .block-content ul > li').length;

        $('.block-menu .block-content ul > li.all-categories > a.show_more').click(function () {
            $('.block-menu .block-content ul > li.hidden-item').slideDown(200).addClass('active');
            $(this).css({'display': 'none'});
            $('.block-menu .block-content ul > li.all-categories > a.close_more').css({'display': 'block'})
        });

        $('.block-menu .block-content ul > li.all-categories > a.close_more').click(function () {
            $('.block-menu .block-content ul > li.hidden-item').slideUp(200).removeClass('active');
            $(this).css({'display': 'none'});
            $('.block-menu .block-content ul > li.all-categories > a.show_more').css({'display': 'block'})
        });

        /* $('.block-menu .btn-showsub').click(function () {
         if($(this).hasClass('active')){
         $(this).removeClass('active').prev('.submenu-wrap').slideUp(200);
         return;
         } else {
         $(this).addClass('active').prev('.submenu-wrap').slideDown(200);
         return;
         }

         }); */
        ///////////////////////

        $('#btn-header-sidebar').click(function () {
            $('body').toggleClass('active-header-sidebar');
        });
    });

});

