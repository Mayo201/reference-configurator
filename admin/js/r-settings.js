(function ($){
    var settings = (function(){

       var _init = function() {
           _paginatePdfsList();
           _removePdf();
       };

       var _paginatePdfsList = function(){
           var items = $('.references__list').children();

           var numItems = items.length;
           var perPage = 20;

           items.slice(perPage).hide();

           $('.pagination').pagination({
               items: numItems,
               itemsOnPage: perPage,
               cssStyle: 'light-theme',
               onPageClick: function(pageNumber) {
                   var showFrom = perPage * (pageNumber -1);
                   var showTo = showFrom + perPage;
                   items.hide().slice(showFrom, showTo).show();
               }
           });
       };

       var _removePdf = function() {
            $('.remove-pdf').click(function(e){
                e.preventDefault();
                var item = $(this);
                var name = item.data('name');
                if(name) {
                    if (confirm('Are you sure you want to remove this item?')) {
                        var data = {action: 'do_remove', filename: name};
                        jQuery.ajax({
                            url: ajax_object.ajax_url,
                            type: 'POST',
                            dataType: 'json',
                            data: data,
                            success: function (response) {
                                if(response.flag){
                                    item.parent().remove();
                                    _paginatePdfsList();
                                }
                                else{
                                    $('.info').html(function () {
                                        return '<div style="color: red;">Error!</div>';
                                    });
                                }
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                $('.info').html(function () {
                                    return '<div style="color: red;">Error!</div>';
                                });
                            }
                        });
                    } else {

                    }

                }

            });
       };

       return {
           init: _init
       }
    })();
    settings.init();
})(jQuery);