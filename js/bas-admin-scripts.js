(function( $ ) {
	'use strict';

	$(document).ready(function() {

        var mediaFrame,
            images = [],
            imagesIdArr = [],
            carouselOptions = {
                animation: "slide",
                controlNav: false,
                animationLoop: false,
                prevText: baSliderI18N.PREV,
                nextText: baSliderI18N.NEXT,
                slideshow: false,
                itemWidth: 150,
                itemMargin: 5,
                asNavFor: '#slider'
            },
            sliderOptions = {
                animation: "slide",
                controlNav: false,
                directionNav: false,
                animationLoop: false,
                slideshow: false,
                sync: "#carousel"
            };

        function initSlide() {
            $('#carousel').flexslider(carouselOptions);
            $('#slider').flexslider(sliderOptions);
        }

        function createSlideObj(slideId, imgUrl, type) {
            var obj  = {},
                li   = {},
                a    = {},
                span = {},
                img  = {};

            if ( 'carousel' == type ) {
                li = $('<li/>', {
                    id: 'slide-thumb-' + slideId,
                    class: 'bas-dynamic-slide'
                });

                a = $('<a/>', {
                    'href': 'javascript:;',
                    'class': 'bas-delete-slide-btn bas-delete-slide',
                    'data-slideid': slideId,
                });

                span = $('<span/>', {
                    class: 'fas fa-times',
                    html: '&nbsp;'
                });

                img = $('<img/>', {
                    class: 'tab-slide-thumb',
                    src: imgUrl,
                    alt: '',
                });

                obj = li.append(a.append(span)).append(img);

            } else {
                li = $('<li/>', {
                    id: 'slide-' + slideId,
                    class: ''
                });

                img = $('<img/>', {
                    class: 'tab-slide-thumb',
                    src: imgUrl,
                    alt: '',
                });

                obj = li.append(img);
            }

            return obj;
        }

        $('#bas-multi-upload').off('click').on('click', function(e) {
            e.preventDefault();

            // if the media mediaFrame already exists, reopen it.
            if ( mediaFrame ) {
              mediaFrame.open();
              return;
            }

            // create a new media mediaFrame
            mediaFrame = wp.media({
                title: baSliderI18N.TITLE_ADD_TO_SLIDER,
                button: {
                    text: baSliderI18N.BUTTON_ADD_TO_SLIDER
                },
                multiple: true
            });

            mediaFrame.on('select', function() {
                images = mediaFrame.state().get("selection").models;
                $.each(images, function(key, val) {
                    imagesIdArr.push(val.id);
                });

                $.ajax({
                    url: baSliderI18N.AJAX_URL,
                    data: {
                        action: 'bas_add_image_to_slider',
                        security: baSliderI18N.WPNONCE,
                        images: imagesIdArr.join(','),
                        post_id: baSliderI18N.POST_ID
                    },
                    dataType: 'json',
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            var carousel = $('#carousel').data('flexslider');
                            var slider   = $('#slider').data('flexslider');

                            $.each(response.images, function(key, img) {
                                var slideObjCar    = createSlideObj(img['slide_id'], img['carousel_img'], 'carousel');
                                var slideObjSlider = createSlideObj(img['slide_id'], img['slider_img'], 'slider');
                                carousel.addSlide( slideObjCar, 0 );
                                slider.addSlide( slideObjSlider, 0 );
                            });

                            $('#carousel ul.slides li:first-child').trigger('click');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // return error message
                        console.log('error', errorThrown);
                    }
                });
            });

            mediaFrame.open();

        });

        // hack required to unjam dynamically added slides; due to a FlexSlider bug?
        $(document).on('click', '.bas-dynamic-slide', function() {
            var elementNum = 1369*($(this).prevAll("li").length);
            $("#slider .slides").css("transform", "translate3d(-"+elementNum+"px, 0px, 0px)");
        });

        $('#bas-video-url').off('keyup').on('keyup', function() {
            var videoUrl = $(this).val();
            setTimeout(function() {
                $('#bas-preview-video').BaSlider('getVideo', {
                    wpnonce: baSliderI18N.WPNONCE,
                    post_id: baSliderI18N.POST_ID,
                    url: videoUrl,
                    args: {
                        width: 600,
                        height: 400
                    }
                });
            }, 1000);
        });

        $(document).off('click', '.bas-delete-slide').on('click', '.bas-delete-slide', function(e) {
            e.preventDefault();

            if ( confirm(baSliderI18N.WARN_DELETE_SLIDE) ) {
                var slideId = $(this).data('slideid');
                $.ajax({
                    url: baSliderI18N.AJAX_URL,
                    data: {
                        action: 'bas_delete_slide',
                        security: baSliderI18N.WPNONCE,
                        attach_id: slideId,
                        post_id: baSliderI18N.POST_ID
                    },
                    type: 'POST',
                    success: function(response) {
                        if (response) {
                            // remove slide
                            var carousel = $('#carousel').data('flexslider');
                            var slider   = $('#slider').data('flexslider');

                            carousel.removeSlide( $('#slide-thumb-' + slideId) );
                            slider.removeSlide( $('#slide-' + slideId) );
                            carousel.flexslider(0);
                            slider.flexslider(0);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        // return error message
                        console.log('error', errorThrown);
                    }
                });
            }

        });

        $('.bas-toggle-menu').off('click').on('click', function(e) {
            var slideId = $(this).data('slideid');
            e.preventDefault();
            $('#bas-context-menu-' + slideId).fadeToggle();
        });


        /* slide captions */

        $('.bas-add-caption').off('click').on('click', function(e) {
            e.preventDefault();
            $.fn.BaSlider('addCaption', {
                slideId: $(this).data('slideid')
            });
        });

        $(document).mouseup(function (e) {
            var menu = $('.bas-context-menu');
            if ( ! menu.is(e.target) && menu.has(e.target).length === 0 ) {
                menu.fadeOut();
            }
        });

        $(document).off('click', '.bas-delete-caption').on('click', '.bas-delete-caption', function(e) {
            e.preventDefault();

            if ( confirm(baSliderI18N.WARN_DELETE_CAPTION) ) {
                $(this).closest('.bas-slide-caption').remove();
            }
        });

        $('#carousel ul.slides').sortable({
            items: "li:not(.bas-sort-disabled)",
            placeholder: "bas-ui-state-highlight",
            stop: function(event, ui) {
                var slide_order_arr = [];
                var sortedItems = $('#carousel ul.slides').sortable('toArray');

                $.each(sortedItems, function(key, val) {
                    var slideId = val.replace('slide-thumb-', '');
                    slide_order_arr.push({
                        attachment_id: slideId,
                        order: key
                    });
                });

                $.ajax({
                    url: baSliderI18N.AJAX_URL,
                    data: {
                        action: 'bas_update_slides_order',
                        security: baSliderI18N.WPNONCE,
                        slides_order: slide_order_arr,
                        post_id: baSliderI18N.POST_ID
                    },
                    type: 'POST',
                    success: function(response) {
                        window.location.reload(true);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log('error', errorThrown);
                    }
                });
            }
        });

        initSlide();
    });

})( jQuery );
