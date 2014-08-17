<?php
/*
 * Plugin Name: Jetpack Sharing Source Pack
 * Plugin URI: http://wordpress.org/plugins/jpssp/
 * Description: Add more services to Jepack Sharing
 * Version: 0.1.0-dev
 * Author: Daisuke Takahashi(Extend Wings)
 * Author URI: http://www.extendwings.com
 * License: AGPLv3 or later
 * Text Domain: jpssp
 * Domain Path: /languages/
*/

if(!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if(version_compare(get_bloginfo('version'), '3.8', '<')) {
	require_once(ABSPATH.'wp-admin/includes/plugin.php');
	deactivate_plugins(__FILE__);
}

define('JPSSP__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JPSSP__PLUGIN_FILE', __FILE__);

add_action('init', array('Jetpack_Sharing_Source_Pack', 'init'));

class Jetpack_Sharing_Source_Pack {
	static $instance;

	const VERSION = '0.1.0-dev';
	
	private $data;

	static function init() {
		if(!self::$instance) {
			if(did_action('plugins_loaded')) {
				self::plugin_textdomain();
			} else {
				add_action('plugins_loaded', array(__CLASS__, 'plugin_textdomain'));
			}

			self::$instance = new Jetpack_Sharing_Source_Pack;
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('wp_loaded', array(&$this, 'register_assets'));
		if(did_action('plugins_loaded')) {
			$this->require_services();
		} else {
			add_action('plugins_loaded', array(&$this, 'require_services'));
		}
		add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);
	}

	function register_assets() {
	//	if(!wp_style_is('genericons', 'registered'))
	//		wp_register_style('genericons', plugin_dir_url(__FILE__).'css/genericons.css', false, '3.0.3');
	}

	function require_services() {
		if(class_exists('Jetpack')) {
			require JPSSP__PLUGIN_DIR . 'class.sharing-services.php';
		}
	}

	static function plugin_textdomain() {
		load_plugin_textdomain('jpssp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	function plugin_row_meta($links, $file) {
		if(plugin_basename(__FILE__) === $file) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url('http://www.extendwings.com/donate/'),
				__('Donate', 'jpssp')
			);
		}
		return $links;
	}
}
