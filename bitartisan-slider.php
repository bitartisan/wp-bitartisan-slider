<?php
/*
Plugin Name: BitArtisan Slider
Plugin URI: https://bitartisan.com/plugins/slider/
Description: Because why not another WordPress slider plugin?
Version: 0.0.1
Author: BitArtisan
Author URI: https://bitartisan.com/
License: GPLv2
Text Domain: bitartisanslider
*/

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

require_once 'class-bitartisan-slider-admin.php';
require_once 'class-bitartisan-slider-front.php';

class BitArtisanSlider {

    protected $namespace;
    protected $plugin_path;
    protected $plugin_url;
    protected $version;
    protected $i18n = Array();

    public function __construct() {
        $this->version   = '0.0.1';
        $this->namespace = 'bitartisanslider';
        $this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugins_url() . '/' . $this->namespace . '/';

        $this->bas_add_actions();
        $this->i18n = $this->bas_i18n();
    }

    public function bas_get_slides($post_id) {

        // attempt getting ordered attachments
        $slides_order = get_post_meta($post_id, 'bas_slides_order', true);

        if ( ! is_array($slides_order) || sizeof($slides_order) == 0 ) {
            $args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'post_parent' => $post_id,
                'order' => 'DESC',
                'nopaging' => true,
            );

            $query = new WP_Query( $args );
            $attachments = $query->posts;
        } else {
            $attachments = $slides_order;
        }

        $upload_dir_arr = wp_upload_dir();

        foreach ($attachments as &$slide) {
            $slide_id = ( is_array($slide) ? $slide['attachment_id'] : $slide->ID );
            $attachment_meta = wp_get_attachment_metadata( $slide_id );
            $attachment_meta['ID'] = $slide_id;
            $attachment_meta['url'] = esc_url( $upload_dir_arr['url'] ) . '/';
            $attachment_meta['baseurl'] = esc_url( $upload_dir_arr['baseurl'] ) . '/';
            unset($attachment_meta['image_meta']);
            $slide = $attachment_meta;
        }

        // append default empty slide
        $default_slide_arr = array(
            'ID' => -1,
            'file' => ''
        );

        array_push($attachments, $default_slide_arr);

        return $attachments;
    }

    protected function bas_add_actions() {
        add_action( 'init', array($this, 'bas_load_textdomain') );
	}

    function bas_load_textdomain() {
        load_plugin_textdomain($this->namespace, false, $this->plugin_path . 'languages');
	}

    function bas_i18n() {
        return array(
            'NAMESPACE' => $this->namespace,
            'TITLE_ADD_TO_SLIDER' => __('Add image(s) to Slider', $this->namespace),
            'BUTTON_ADD_TO_SLIDER' => __('Add to Slider', $this->namespace),
            'PREV' => __('Previous', $this->namespace),
            'NEXT' => __('Next', $this->namespace),
            'ADD' => __('Add', $this->namespace),
            'REMOVE' => __('Remove', $this->namespace),
            'CANCEL' => __('Cancel', $this->namespace),
            'WARN_DELETE_SLIDE' => __('Are you sure you want to remove this slide?', $this->namespace),
            'WARN_DELETE_CAPTION' => __('Are you sure you want to remove this caption?', $this->namespace),
        );
	}
}
