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

// modification number 2
// test

if ( ! defined('WPINC') ) {
    exit;
}

class BitArtisanSliderFront extends BitArtisanSlider {

    public function __construct() {
        parent::__construct();
        $this->bas_add_actions();
    }

    public function bas_add_actions() {
        add_action( 'after_setup_theme', array( $this, 'bas_add_theme_support' ) );
    }

    public function bas_add_theme_support() {
        if ( get_theme_support( 'post-thumbnails' ) ) {
            add_theme_support( 'post-thumbnails', array($this->namespace) );
        }
    }

}

$baSlider = new BitArtisanSliderFront();
