(function ($) {
  'use strict';

  var referenceConfigurator = (function(){
    var _posts = {};
    var _messages = [];

    var _initPosts = function() {
        _posts = ajax_object.posts;
        _messages = ajax_object.translations;
    };

    var _init = function() {
      _initPosts();
      _openLightbox();
      _addDirectToDroppable();
      _dragAndDrop();
      _removeProject();
      _searchAfterClick();
      _searchBySelect('search__category');
      _searchBySelect('search__country');
      _searchByInput();
      _removeAllConfiguration();
      _createPdf();
      _countDraggable();
      _paginateProjects();
      _sendPDF();
    };

    var _openLightbox = function() {
        $('.fancybox').click(function(e){
          e.preventDefault();
          var postId = $(this).data('id');
          for(var i=0; i < _posts.length; i++)
          {
            if(postId  == _posts[i].id) {
                $.fancybox.open('<div class="project__details">' + _posts[i].details + '</div>');
                //console.log(_posts[i].details);
            }
          }
        });
        return false;
    };


    var _addDirectToDroppable = function() {
        $('.icon-add').click(function(e) {
            e.preventDefault();
            var id = $(e.currentTarget.offsetParent).data('guid');
            var project = $('#guid-'+id);
            project.children('.icon-ok').show();
            project.children('.icon-add').hide();

            project.clone().appendTo($('.project__droppable'));
            project.draggable('disable');
            $('.downloads').html(function () {
                return '<div></div>';
            });
            _removeProject();
            _openLightbox();
            _countDraggable();
        });
    };

    var _removeProject = function() {
        $('.icon-remove').unbind('click');
        $('.icon-remove').click(function(e) {
            e.preventDefault();
            var id = $(e.currentTarget.offsetParent).data('guid');
            var project = $('#guid-'+id);
            project.draggable('enable');
            project.children('.icon-ok').hide();
            project.children('.icon-add').show();

            $(e.currentTarget.offsetParent).remove();
            _countDraggable();
        });
    };

    var _dragAndDrop = function() {

        $(".draggable").draggable({
            cursor: 'move',
            helper: 'clone',
            revert: 'invalid',
            classes: {
                "ui-droppable-active": "ui-state-active",
                "ui-droppable-hover": "ui-state-hover"
            },
            stop: function(event, ui) {
                $('.icon-remove').unbind('click');
                $('.icon-add').unbind('click');
                $('.fancybox').unbind('click');
                _openLightbox();
                _addDirectToDroppable();
                _removeProject();
            }
        });
        $(".droppable").droppable({
            accept: ":not(.ui-sortable-helper)",
            drop: function( event, ui ) {
                ui.draggable.clone().appendTo($(this));
                ui.draggable.draggable('disable');
                ui.draggable.children('.icon-ok').show();
                ui.draggable.children('.icon-add').hide();

                $('.icon-remove').unbind('click');
                $('.fancybox').unbind('click');
                $('.icon-add').unbind('click');
                $('.downloads').html(function () {
                    return '<div></div>';
                });
                _openLightbox();
                _removeProject();
                _countDraggable();
            }
        }).sortable({
            sort: function() { }
        });

    };

    var _countDraggable = function(){
        var treshold = 1;

        var mobileViewport = window.matchMedia("screen and (max-width: 700px)");
        if(mobileViewport.matches) {
            treshold = 1;
        } else {
            treshold= 3;
        }

        mobileViewport.addListener(function(mq) {
            if(mq.matches) {
                treshold = 1;
            } else {
                treshold= 3;
            }
        });

        var counter = $('.project__droppable .project__container').length;
        if(counter > treshold) $('.project__droppable').removeClass('empty');
        else {
            $('.project__droppable').addClass('empty')
        }
        if(counter  > 1) {
            $('.project__info').html('<p><strong>'+ counter+ ' </strong>' + _messages.count_references + '</p>');
        }
        else {
            $('.project__info').html('<p><strong>'+ counter+ ' </strong>' + _messages.count_reference + '</p>');
        }
    };

    var _search = function() {
        var filter, items, item, title, i;
        var category = document.getElementById('search__category');
        var filter_category = category.options[category.selectedIndex].value.toUpperCase();
        var country = document.getElementById('search__country');
        var filter_country = country.options[country.selectedIndex].value.toUpperCase();
        var input = document.getElementById('search__input');
        var filter_input = input.value.toUpperCase();

        items = document.getElementById("projects");
        item = items.getElementsByClassName('project__container');
        for (i = 0; i < item.length; i++) {
            title = item[i].getElementsByTagName('h4')[0];
            title = title.innerHTML + ' ' + title.getAttribute('data-categories') + ' ' + title.getAttribute('data-countries');

            if ((title.toUpperCase().indexOf(filter_category) > -1) && (title.toUpperCase().indexOf(filter_country) >-1) && (title.toUpperCase().indexOf(filter_input) >-1)) {
                item[i].style.display = "";
            } else {
                item[i].style.display = "none";
            }
        }
        _paginateProjects();
    };

    var _searchAfterClick = function(){
        $('.search__button').click(function(e){
            e.preventDefault();
            _search();
        });
    };

    var _searchBySelect = function(_selectID) {
        $('#'+ _selectID).change(function(e) {
            e.preventDefault();
            _search();
        });
    };

    var _searchByInput = function() {
        $('#search__input').keyup(function(e) {
            e.preventDefault();
            _search();
        });
    };

    var _removeAllConfiguration= function(){
        $('#config-remove').click(function(e) {
            e.preventDefault();
            var activeProjects = $('.project__droppable');
            //var projects = $('.projects');
            var countProjects = activeProjects.children().length;
            $('.project__form').fadeOut();

            if(countProjects > 0) {
                for(var i=0; i < countProjects; i++)
                {
                    var id = $('.project__droppable').children()[i].getAttribute('data-guid');
                    $('#guid-'+ id).draggable('enable');
                    $('#guid-'+ id).children('.icon-ok').hide();
                    $('#guid-'+ id).children('.icon-add').show();

                }
                activeProjects.children().remove();
                $('.downloads').html(function () {
                    return '';
                });
            }
            _countDraggable();
        });
    };

    var _getProjectsFromDroppable = function() {
        var _projects= [];
        var activeProjects = $('.project__droppable');

        var countProjects = activeProjects.children().length;
        var id, i, j, title, details, guid;

        if(countProjects > 0) {
            for(i=0; i < countProjects; i++)
            {
                id = $('.project__droppable').children()[i].getAttribute('data-guid');
                for(j = 0; j< _posts.length; j++)
                {
                    if(_posts[j].id == id) {
                        _projects.push({guid: _posts[j].id, title: _posts[j].title, details: _posts[j].details});
                    }
                }
            }
        }

        return _projects;
    };

    var _createPdf = function() {
        $('#pdf-create').click(function(e){
            e.preventDefault();
            var projects = _getProjectsFromDroppable();
            $('.project__form').fadeOut();
            $('.ajax-loader').show();

            if(projects.length > 0 ) {
                var data = {action: 'do_action', projects: projects};
                jQuery.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function (response) {
                        $('.ajax-loader').hide();
                        $('.downloads').html(function () {
                            //console.log(response);
                            return '<div><h2>'+_messages.download+'</h2><a href="' + (response.path) + '" target="_blank">' + response.name + '</a></div>';
                        });
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        $('.ajax-loader').hide();
                        $('.downloads').html(function () {
                            return '<div style="color: red;">'+_messages.message_error+'</div>';
                        });
                    }
                });
            }
            else {
                $('.ajax-loader').hide();
                $('.downloads').html(function () {
                    return '<div><h2>0 '+_messages.count_reference+'</h2></div>';
                });
            }
        });
    };

    var _sendPDF = function() {
        $('#pdf-send').click(function(e){
            e.preventDefault();
            var projects = _getProjectsFromDroppable();
            if(projects.length > 0 ) {
                $('.downloads').html(function () {
                    return ' ';
                });
                $('.project__form').fadeIn();
            }
            else {
                $('.downloads').html(function () {
                    return '<div><h2>0 '+_messages.count_reference+'</h2></div>';
                });
            }
        });

        $('.send-pdf-form').validate({
            rules:{
                pdf_email: {
                    email: true,
                    required: true
                },
                pdf_subject: {
                    required: true
                }
            },
            submitHandler: function () {
                var projects = _getProjectsFromDroppable();
                var pdf_email =  $('#pdf_email').val();
                var pdf_subject= $('#pdf_subject').val();
                var pdf_message= $('#pdf_message').val();
                $('.mail-loader').show();

                if(projects.length > 0 ) {
                    var data = {action: 'do_action', projects: projects, email: pdf_email, subject: pdf_subject, message: pdf_message};
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function (response) {
                            $('.mail-loader').hide();
                            if(response.debug) {
                                $('.send-pdf-form').each(function () {
                                    this.reset();
                                });
                                $('.project__form').fadeOut();
                                $('.downloads').html(function () {
                                    return '<div>'+_messages.message_ok+'</div>';
                                });
                                $('.form_info').html(function () {
                                    return '<div></div>';
                                });
                            }
                            else{
                                $('.form_info').html(function () {
                                    return '<div style="color: red;">'+_messages.message_error+'</div>';
                                });
                            }
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            $('.mail-loader').hide();
                            $('.downloads').html(function () {
                                return '<div style="color: red;">'+_messages.message_error+'</div>';
                            });
                        }
                    });
                }
                else {
                    $('.ajax-loader').hide();
                    $('.downloads').html(function () {
                        return '<div><h2>0 '+_messages.count_reference+'</h2></div>';
                    });
                }
            }
        });
    };

    var _paginateProjects = function() {
        //var items = $('.projects .project__container:not([style*="display: none"])');
        var items = $('.projects').children('.project__container:not([style*="display: none"])');

        var numItems = items.length;
        var perPage = 6;

        items.slice(perPage).hide();

        $('.pagination').pagination({
          items: numItems,
          itemsOnPage: perPage,
          cssStyle: 'light-theme',
          prevText: _messages.prev,
          nextText: _messages.next,
          onPageClick: function(pageNumber) {
              var showFrom = perPage * (pageNumber -1);
              var showTo = showFrom + perPage;
              items.hide().slice(showFrom, showTo).show();
          }
        });

        $('.project__droppable').children('.project__container').show();

        if(numItems == 0) {
            $('#projects .info').removeClass('hide');
        }
        else {
            $('#projects .info').addClass('hide');
        }
    };

    return {
      posts: _posts,
      init: _init
    }
  })();

  referenceConfigurator.init();

})(jQuery);
