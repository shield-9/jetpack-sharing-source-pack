<?php
/*
 * Plugin Name: Jetpack Sharing Source Pack
 * Plugin URI: http://wordpress.org/plugins/jpssp/
 * Description: Add more services to Jepack Sharing
 * Version: 0.1.4
 * Author: Daisuke Takahashi (Extend Wings)
 * Author URI: https://www.extendwings.com
 * License: AGPLv3 or later
 * Text Domain: jpssp
 * Domain Path: /languages/
*/

if( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if( version_compare( get_bloginfo( 'version' ), '4.4-beta1', '<' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( __FILE__ );
}

define( 'JPSSP__PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'JPSSP__PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'JPSSP__PLUGIN_FILE', __FILE__ );
define( 'JPSSP__VERSION',     '0.1.4' );

require_once( JPSSP__PLUGIN_DIR . 'class.jpssp.php' );
require_once( JPSSP__PLUGIN_DIR . 'class.jpssp-api.php' );

add_action( 'init',                   array( 'JPSSP', 'init' ) );
add_action( 'jetpack_modules_loaded', array( 'JPSSP', 'require_services' ) );

add_action( 'rest_api_init', array( 'JPSSP', 'register_rest_api' ) );
