<?php
/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

Copyright 2018 BitArtisan.
*/

if ( ! defined('WPINC') ) {
    exit;
}

class BitArtisanSliderAdmin extends BitArtisanSlider {

    function __construct() {
        parent::__construct();
        $this->bas_add_actions();
    }

    protected function bas_add_actions() {
        add_action( 'init', array($this, 'bas_create_slider_post_type') );
        add_action( 'admin_enqueue_scripts', array($this, 'bas_enqueue_scripts') );
        add_action( 'add_meta_boxes', array($this, 'bas_register_meta_boxes') );
        add_action( 'after_setup_theme', array( $this, 'bas_add_theme_support' ) );

        add_action( 'wp_ajax_bas_add_image_to_slider', array($this, 'bas_add_image_to_slider') );
        add_action( 'wp_ajax_bas_delete_slide', array($this, 'bas_delete_slide') );
        add_action( 'wp_ajax_bas_update_slides_order', array($this, 'bas_update_slides_order') );
        add_action( 'wp_ajax_bas_get_embed_video', array($this, 'bas_get_embed_video') );
    }

    function bas_create_slider_post_type() {
        $labels = array(
          'name'               => __('Slider', $this->namespace),
          'singular_name'      => __('Slider', $this->namespace),
          'menu_name'          => __('Slider', $this->namespace),
          'name_admin_bar'     => __('Slider', $this->namespace),
          'add_new'            => __('Add New Slider', $this->namespace),
          'add_new_item'       => __('Add New Slider', $this->namespace),
          'new_item'           => __('New Slider', $this->namespace),
          'edit_item'          => __('Edit Slider', $this->namespace),
          'view_item'          => __('View Slider', $this->namespace),
          'all_items'          => __('Slider Details', $this->namespace),
          'search_items'       => __('Search Sliders', $this->namespace),
          'not_found'          => __('No Sliders found.', $this->namespace),
          'not_found_in_trash' => __('No Sliders found in Trash.', $this->namespace)
      );

        $args = array(
          'labels'             => $labels,
          'description'        => __('Description.', $this->namespace),
          'public'             => false,
          'publicly_queryable' => false,
          'show_ui'            => true,
          'show_in_menu'       => true,
          'query_var'          => true,
          'exclude_from_search'=> true,
          'rewrite'            => array( 'slug' => $this->namespace ),
          'capability_type'    => 'page',
          'has_archive'        => false,
          'hierarchical'       => false,
          'menu_position'      => null,
          'menu_icon'          => 'dashicons-welcome-view-site',
          'supports'           => array( 'title' ),
       );

        register_post_type($this->namespace, $args);
    }

    function bas_register_meta_boxes() {
        add_meta_box(
            $this->namespace . '-slides',
            __( 'Slides', $this->namespace ),
            array($this, 'bas_render_metabox'),
            $this->namespace,
            'advanced',
            'high',
            array('meta_box' => 'slides')
        );

        add_meta_box(
            $this->namespace . '-settings',
            __( 'Slider Settings', $this->namespace ),
            array($this, 'bas_render_metabox'),
            $this->namespace,
            'side',
            'low',
            array('meta_box' => 'settings')
        );
    }

    function bas_render_metabox($post, $params) {
        $box = strtoupper($params['args']['meta_box']);
        switch ($box) {
            case 'SLIDES':
                $this->bas_render_slides($post, $params['args']);
                break;
            case 'SETTINGS':
                $this->bas_render_settings($post, $params['args']);
                break;
        }
    }

    function bas_render_slides($post, $box_arr=array()) {
        $slides_arr = $this->bas_get_slides($post->ID);
        ?>
        <!-- nav control for the slides -->
        <div id="carousel" class="flexslider">
            <ul class="slides">
                <?php foreach ($slides_arr as $key => $slide) : ?>
                    <li id="slide-thumb-<?php echo $slide['ID']; ?>" class="bas-ui-state-default<?php echo ( $slide['ID'] == -1 ? ' bas-sort-disabled' : '' ); ?>">
                        <?php if ( $slide['ID'] > 0 ) : ?>
                            <a href="javascript:;" title="Delete this slide" class="bas-delete-slide-btn bas-delete-slide" data-slideid="<?php echo $slide['ID']; ?>">
                                <span class="fas fa-times">&nbsp;</span>
                            </a>
                            <img class="tab-slide-thumb" src="<?php echo $slide['url'] . $slide['sizes']['slider-thumb']['file']; ?>" alt="" />
                        <?php else : ?>
                            <span class="fas fa-plus-circle bas-new-slide" title="<?php echo __('Add New Slide', $this->namespace); ?>"></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach ; ?>
            </ul>
        </div>
        <!-- render the slides -->
        <div id="slider" class="flexslider">
            <ul class="slides">
                <?php foreach ($slides_arr as $key => $slide) :

                    echo "<pre>";
                    print_r($slide);
                    echo "</pre>";
                    die(1);

                    ?>
                    <li id="slide-<?php echo $slide['ID']; ?>" class="bas-slide" <?php echo ( $slide['ID'] > 0 ? 'style="background: transparent url(' . $slide['baseurl'] . $slide['file'] . ') no-repeat; background-size: cover;"' : ''); ?>>
                        <?php if ( $slide['ID'] > 0 ) : ?>
                            <ul id="bas-slide-menu">
                                <li>
                                    <a href="javascript:;" title="<?php echo __('Save Slide', $this->namespace); ?>" class="bas-save-slide bas-btn-disabled" data-slideid="<?php echo $slide['ID']; ?>">
                                        <span class="fas fa-save"></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" title="<?php echo __('Add Caption', $this->namespace); ?>" class="bas-add-caption" data-slideid="<?php echo $slide['ID']; ?>">
                                        <span class="fas fa-plus-circle"></span>
                                    </a>
                                </li>
                            </ul>
                            <img class="slide-img" src="<?php echo $slide['url'] . $slide['sizes']['slider-thumb']['file']; ?>" width="1" height="1" alt="" style="display: none;" />
                        <?php else : ?>
                            <?php $this->bas_render_add_slides_btn(); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach ; ?>
            </ul>
        </div>
        <?php
    }

    function bas_add_image_to_slider() {
        $response = array(
            'success' => false,
            'msg'     => array(),
            'images'  => array(),
        );

        if ( check_ajax_referer( 'bas-ajax-process-media', 'security', false ) ) {
            $parent_post_id = intval($_POST['post_id']);
            $images_arr = explode(',', $_POST['images']);
            $upload_dir_arr = wp_upload_dir();

            foreach ( $images_arr as $attachment_id ) {
                $attachment = array(
                    'ID'          => $attachment_id,
                    'post_parent' => $parent_post_id,
                    'post_type'   => 'attachment',
                    'post_status' => 'inherit',
                );

                $post_id = wp_update_post( $attachment, true );

                if ( ! is_wp_error($post_id) ) {
                    $attach_meta = wp_get_attachment_metadata( $attachment_id );
                    $slide_arr = array(
                        'slide_id'     => $attachment_id,
                        'carousel_img' => esc_url( $upload_dir_arr['url'] ) . '/' . $attach_meta['sizes']['slider-thumb']['file'],
                        'slider_img'   => esc_url( $upload_dir_arr['baseurl'] ) . '/' . $attach_meta['file'],
                    );
                    array_push( $response['images'], $slide_arr );
                    $response['success'] = true;
                } else {
                    $errors = $post_id->get_error_messages();
                    foreach ($errors as $error) {
                        array_push( $response['msg'], $error );
                    }
                }
            }
        }

        echo json_encode( $response );
        wp_die();
    }

    function bas_update_slides_order() {
        if ( check_ajax_referer( 'bas-ajax-process-media', 'security', false ) ) {
            $parent_post_id = intval($_POST['post_id']);
            $slides_order_arr = $_POST['slides_order'];
            update_post_meta( $parent_post_id, 'bas_slides_order', $slides_order_arr );
        }
        echo 1;
        wp_die();
    }

    function bas_delete_slide() {
        $success = false;

        if ( check_ajax_referer( 'bas-ajax-process-media', 'security', false ) ) {
            $parent_post_id = intval($_POST['post_id']);
            $attachment_id  = intval($_POST['attach_id']);

            $attachment = array(
                'ID'          => $attachment_id,
                'post_parent' => 0,
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
            );

            wp_update_post( $attachment );
            wp_delete_attachment( $attachment_id, true );

            $success = true;
        }

        echo $success;
        wp_die();
    }

    function bas_get_embed_video() {

        $response = array('success' => false);

        if ( check_ajax_referer( 'bas-ajax-process-media', 'security', false ) ) {
            $parent_post_id = intval($_POST['post_id']);
            $video_url      = esc_url( $_POST['video_url'] );
            $iframe         = wp_oembed_get( $video_url, array('width' => 600, 'height' => 400) );

            $video_service_arr = array('YOUTUBE', 'VIMEO');
            foreach ( $video_service_arr as $service) {
                $match = preg_match('/(' . $service . ')/i', $video_url, $matches);
                if ( $match && $iframe ) {
                    $response['success'] = true;
                    switch ( $service ) {
                        case 'YOUTUBE':
                            $iframe              = str_replace('?feature=oembed', '?feature=oembed&showinfo=0&controls=0&modestbranding=1&rel=0', $iframe);
                            $response['iframe']  = $iframe;
                            $video_url_arr       = explode('?v=', $video_url);
                            $video_id            = $video_url_arr[1];
                            $response['thumb']   = 'https://img.youtube.com/vi/' . $video_id . '/0.jpg';
                            break;
                        case 'VIMEO':
                            $video_url_arr       = explode('/', $video_url);
                            $video_id            = end($video_url_arr);
                            $video_info          = json_decode( file_get_contents('http://vimeo.com/api/v2/video/' . $video_id . '.json') );
                            $iframe              = preg_replace('/src=\"(.*)\"/', 'src="https://player.vimeo.com/video/' . $video_id . '?title=0&byline=0&portrait=0" width="600" height="338"', $iframe);
                            $response['iframe']  = $iframe;
                            $response['thumb']   = $video_info[0]->thumbnail_large;
                            break;
                    }
                }
            }
        }

        echo json_encode( $response );
        wp_die();
    }

    function bas_render_add_slides_btn() {
        ?>
        <div id="bas-add-slide">
            <h2><?php echo __('Add Slide', $this->namespace); ?></h2>
            <div id="bas-add-slide-controls">
                <a href="javascript:;" id="bas-multi-upload">
                    <span class="fas fa-images"></span>
                    <?php echo __('Upload Image(s)', $this->namespace); ?>
                </a>
                <span class="bas-label bas-center"><?php echo __('or insert video', $this->namespace); ?></span>
                <input type="text" name="bas_video_url" placeholder="Video URL (ex: https://www.youtube.com/watch?v=op07UzSCu4c)" id="bas-video-url" />
                <div id="bas-preview-video"></div>
            </div>
        </div>
        <?php
    }

    function bas_render_settings($post, $box_arr=array()) {
        ?>
        <div id="slide-settings">
            <p>Settings</p>
        </div>
        <?php
    }

    function bas_add_theme_support() {
        add_image_size( 'slider-thumb', 150, 150, array('center', 'center') );
    }

    function bas_enqueue_scripts() {
        global $post;

        if ( ($post && $post->post_type == $this->namespace) || (isset($_GET['post_type']) && $_GET['post_type'] == $this->namespace) ) {
            // styles
            wp_enqueue_style( $this->namespace . '-fontawesome', $this->plugin_url . 'css/fontawesome.min.css' );
            wp_enqueue_style( $this->namespace . '-flexslider', $this->plugin_url . 'css/flexslider.min.css' );
            wp_enqueue_style( $this->namespace . '-styles', $this->plugin_url . 'css/bas-admin-styles.css' );
            wp_enqueue_style( 'jquery-ui-theme', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/themes/cupertino/jquery-ui.css' );

            // scripts
            wp_enqueue_script( 'jquery-ui-core');
            wp_enqueue_script( 'jquery-ui-draggable');
            wp_enqueue_script( 'jquery-ui-resizable');
            wp_enqueue_script( 'jquery-ui-sortable');

            wp_enqueue_script( $this->namespace . '-flexslider', $this->plugin_url . 'js/jquery.flexslider-min.js', array('jquery') );
            wp_enqueue_script( $this->namespace . '-slider', $this->plugin_url . 'js/bas-slider.js', array('jquery'), '', true );
            wp_register_script( $this->namespace . '-scripts', $this->plugin_url . 'js/bas-admin-scripts.js', array($this->namespace . '-slider'), '', true );

            $data_global_arr = $this->i18n;
            $data_global_arr['AJAX_URL'] = admin_url('admin-ajax.php');
            $data_global_arr['WPNONCE'] = wp_create_nonce('bas-ajax-process-media');

            if ( $post ) {
                $data_global_arr['POST_ID'] = $post->ID;
            }

            wp_localize_script( $this->namespace . '-scripts', 'baSliderI18N', $data_global_arr );
            wp_enqueue_script( $this->namespace . '-scripts' );
            wp_enqueue_media();
        }
    }
}

new BitArtisanSliderAdmin();
