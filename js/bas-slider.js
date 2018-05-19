(function( $ ) {
    "use strict";

    $.fn.BaSlider = function(options, params) {

        this.addCaption = function(params) {

            var container = $('li#slide-' + params.slideId);

            // get container image average color
            var invRGB = this.invertRGB({
                image: container.find('img').get(0)
            });

            var zIndex    = 100 + ( $('.bas-slide-caption').length );
            var caption   = $('<div/>', {
                id: 'bas-slide-caption-' + params.slideId + '-' + zIndex,
                class: 'bas-slide-caption',
                'data-slideId': params.slideId,
                style: 'z-index: ' + zIndex + '; color: rgb('
                        + invRGB + '); background-color: rgb('
                        + invRGB + ',0.5); border: 1px dashed rgb('
                        + invRGB + ',0.7);'
            });

            $('<a/>', {
                href: 'javascript:;',
                id: 'bas-delete-caption-' + params.slideId + '-' + zIndex,
                class: 'bas-delete-caption bas-delete-caption-btn',
                html: '<span class="fas fa-times">&nbsp;</span>'
            }).appendTo(caption);

            container.prepend(caption);

            caption.resizable({
                containment: 'li#slide-' + params.slideId
            });
            caption.draggable({
                containment: 'li#slide-' + params.slideId,
                scroll: false
            });
        }

        this.getVideo = function(params) {
            var $this = this;

            $.ajax({
                url: baSliderI18N.AJAX_URL,
                data: {
                    action: 'bas_get_embed_video',
                    security: baSliderI18N.WPNONCE,
                    args: params,
                    post_id: baSliderI18N.POST_ID
                },
                type: 'POST',
                success: function(response) {
                    return $this.html(response);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // return error message
                    console.log('error', errorThrown);
                }
            });
        }

        // https://stackoverflow.com/questions/2541481/get-average-color-of-image-via-javascript
        this.invertRGB = function(params) {

            var imgEl = params.image;

            var blockSize = 5, // only visit every 5 pixels
                defaultRGB = [0,0,0], // for non-supporting envs
                canvas = document.createElement('canvas'),
                context = canvas.getContext && canvas.getContext('2d'),
                data, width, height,
                i = -4,
                length,
                rgb = [0,0,0],
                count = 0;

            if (!context) {
                return defaultRGB;
            }

            height = canvas.height = imgEl.naturalHeight || imgEl.offsetHeight || imgEl.height;
            width  = canvas.width = imgEl.naturalWidth || imgEl.offsetWidth || imgEl.width;

            context.drawImage(imgEl, 0, 0);

            try {
                data = context.getImageData(0, 0, width, height);
            } catch(e) {
                /* security error, img on diff domain */
                return defaultRGB;
            }

            length = data.data.length;

            while ( (i += blockSize * 4) < length ) {
                ++count;
                rgb[0] += data.data[i];
                rgb[1] += data.data[i+1];
                rgb[2] += data.data[i+2];
            }

            // ~~ used to floor values
            rgb[0] = ~~(rgb[0]/count);
            rgb[1] = ~~(rgb[1]/count);
            rgb[2] = ~~(rgb[2]/count);

            // invert rgb
            // https://gist.github.com/Xordal/9bf24bc6cbc5a39f62cd
            var rgb = rgb.join(",").replace(/rgb\(|\)|rgba\(|\)|\s/gi, '').split(',');
            for (var i = 0; i < rgb.length; i++) rgb[i] = (i === 3 ? 1 : 255) - rgb[i];

            // return inverted RGB string
            return rgb.join(',');
        }

        if(typeof(this[options]) == 'function') {
            return this[options](params);
        }

        // run default actions ...
    }

}( jQuery ));
