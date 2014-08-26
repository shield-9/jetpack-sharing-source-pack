<?php

if(!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if(did_action('jetpack_modules_loaded')) {
	JPSSP_Sharing_Service::init();
} else {
	add_action('jetpack_modules_loaded', array('JPSSP_Sharing_Service', 'init'));
}

class JPSSP_Sharing_Service {
	static $instance;

	static function init() {
		if(!Jetpack::is_module_active('sharedaddy')) {
			return false;
		}

		if(!self::$instance) {
			self::$instance = new JPSSP_Sharing_Service;
		}

		return self::$instance;
	}

	function __construct() {
		add_filter('sharing_services', array(&$this, 'add_sharing_services'));
	}

	function add_sharing_services($services) {
		include_once JPSSP__PLUGIN_DIR . 'class.sharing-sources.php';

		if(!array_key_exists('feedly', $services)) {
			$services['feedly'] = 'Share_Feedly';
		}
		if(!array_key_exists('line', $services)) {
			$services['line'] = 'Share_LINE';
		}
		
		return $services;
	}
}
