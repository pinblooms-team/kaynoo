define( [ "jquery" ], function ( $ ) {
    $( document ).ready(function() {
        // Fix hover on IOS
        $('body').bind('touchstart', function () {
        });

        // Toogle button sticky
        $(".sticky-bottom .button-sticky-bottom").click(function() {
            $(".sticky-bottom .block-bottom").removeClass("active");

            if($(this).hasClass("active")){
                $("#" + $(this).attr("data-drop")).removeClass("active");
                $(this).removeClass("active");
                $("body").removeClass("overflow-hidden");
                return;
            } else{
                $(".sticky-bottom .button-sticky-bottom").removeClass("active");
                $("#" + $(this).attr("data-drop")).toggleClass("active");
                $(this).addClass("active");
                if($("#" + $(this).attr("data-drop")).hasClass("active")){
                    $("body").addClass("overflow-hidden");;
                } else {
                    $("body").removeClass("overflow-hidden");
                }
            }
        });

        $(".sticky-bottom .close-sticky-bottom").click(function() {
            var el = $(this).attr("data-drop");
            $("#" + el).removeClass("active");
            $(".sticky-bottom .button-sticky-bottom").removeClass("active");
            $("body").removeClass("overflow-hidden");
        });

        // End toogle button sticky

        // Categories header top
        $(".btn-categories").click(function() {
            $(this).toggleClass('active');
            $(".dropdown-categories-header").toggleClass('active');
        });

        $('.block-menu ul > li.all-categories > a.show_more').click(function () {
            $('.block-menu ul > li.hidden-item').slideDown(200).addClass('active');
            $(this).css({'display': 'none'});
            $('.block-menu ul > li.all-categories > a.close_more').css({'display': 'block'})
        });

        $('.block-menu ul > li.all-categories > a.close_more').click(function () {
            $('.block-menu ul > li.hidden-item').slideUp(200).removeClass('active');
            $(this).css({'display': 'none'});
            $('.block-menu ul > li.all-categories > a.show_more').css({'display': 'block'})
        });

        $('.block-menu .btn-showsub').click(function () {
            $(this).parent().toggleClass('parent-active');

            if($(this).hasClass('active')){
                $(this).removeClass('active').prev('.submenu-wrap').slideUp(200);
                return;
            } else {
                $(this).addClass('active').prev('.submenu-wrap').slideDown(200);
                return;
            }

        });
        // End categories header top

        // Menu Sticky bottom
        $('.navigation-mobile > ul li').has('ul').append( '<span class="touch-button"><span>open</span></span>' );

        $('.touch-button').click(function(){
            $(this).prev().slideToggle(200);
            $(this).toggleClass('active');
            $(this).parent().toggleClass('parent-active');
        });
        // End menu Sticky bottom

        // Accordion footer
        $(".title-footer").click(function() {
            $(this).parent(".block-footer").toggleClass('active');
        });
        // End accordion footer

        // Button sidebar trigger
        $(".sidebar-trigger").click(function() {
            if($(this).hasClass('active')){
                $('body').removeClass('overflow-hidden overlay-sidebar')
                $(this).removeClass('active').parent().parent().parent().removeClass('active');
                return;
            } else{
                $('.sidebar-container').removeClass('active')
                $(".sidebar-trigger").removeClass('active')
                $('body').addClass('overflow-hidden overlay-sidebar')
                $(this).addClass('active').parent().parent().parent().addClass('active');
            }

        });
        // End button sidebar trigger

    });
});

