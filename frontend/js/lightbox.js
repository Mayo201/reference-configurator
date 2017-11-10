(function($) {
    var lightbox = {
        posts: {},
        initPosts: function () {
            posts = {}
        },
        init: function () {
            $(document).ready(function () {
                console.log('Lightbox init');
            });
        }
    };
    lightbox.init();
    console.log('test');

})(jQuery);