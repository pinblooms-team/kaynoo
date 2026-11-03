var config = {
    map: {
        '*': {
            'jquerypopper': "Sm_Destino/js/popper",
            'jquerybootstrap': "Sm_Destino/js/bootstrap.min",
            'owlcarousel': "Sm_Destino/js/owl.carousel",
            'jqueryfancyboxpack': "Sm_Destino/js/jquery.fancybox.pack",

            'fancybuttons': "Sm_Destino/js/helpers/jquery.fancybox-buttons",
            'fancymedia': "Sm_Destino/js/helpers/jquery.fancybox-media",
            'fancythumbs': "Sm_Destino/js/helpers/jquery.fancybox-thumbs",

            'jqueryunveil': "Sm_Destino/js/jquery.unveil",
            'yttheme': "Sm_Destino/js/yttheme"
        }
    },

    shim: {
        'jquerypopper': {
            'deps': ['jquery'],
            'exports': 'Popper'
        },
        'jquerybootstrap': {
            'deps': ['jquery', 'jquerypopper']
        },
        'owlcarousel': {
            'deps': ['jquery']
        },
        'jqueryfancyboxpack': {
            'deps': ['jquery']
        },
        'yttheme': {
            'deps': ['jquery']
        }
    }
};