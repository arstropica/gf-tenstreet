<?php

/*
 Plugin Name: Gravity Forms TenStreet Addon
 Plugin URI: https://github.com/arstropica/gf-tenstreet.git
 Description: This plugin sends Gravity Forms submissions to the TenStreet API.
 Version: 1.03
 Author: arstropica
 Author URI: http://arstropica.com
 Author Email: aowilliams@arstropica.com
 License: GPL-2.0+
 License URI: http://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: gf-tenstreet
 Domain Path: /lang
 
 */

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/classes/class.gf_tenstreet.php';

global $gf_tenstreet;

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 * 
 * @return void
 * 
 */
function init_gf_tenstreet() {
    
    global $gf_tenstreet;
    
    $gf_tenstreet = new GF_TenStreet();
    
}

function pre_deactivation_gf_tenstreet() {
    flush_rewrite_rules();
    delete_option('gf_tenstreet_first_run');
}

register_deactivation_hook( __FILE__, 'pre_deactivation_gf_tenstreet');
init_gf_tenstreet();
