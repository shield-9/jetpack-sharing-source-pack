<?php

class JPSSP_Sharing_Service {
	static $instance;

	static function init() {
		if( !self::$instance ) {
			self::$instance = new JPSSP_Sharing_Service;
		}

		return self::$instance;
	}

	function __construct() {
		add_filter( 'sharing_services', array( &$this, 'add_sharing_services' ) );
	}

	function add_sharing_services( $services ) {
		include_once JPSSP__PLUGIN_DIR . 'class.sharing-sources.php';

		if( !array_key_exists( 'feedly', $services ) ) {
			$services['feedly'] = 'Share_Feedly';
		}
		if( !array_key_exists( 'line', $services ) ) {
			$services['line'] = 'Share_LINE';
		}
		if( !array_key_exists( 'delicious', $services ) ) {
			$services['delicious'] = 'Share_Delicious';
		}
		if( !array_key_exists( 'instapaper', $services ) ) {
			$services['instapaper'] = 'Share_Instapaper';
		}
		if( !array_key_exists( 'hatena', $services ) ) {
			$services['hatena'] = 'Share_Hatena';
		}
		if( !array_key_exists( 'google-plus-1', $services ) || 'Share_GooglePlus1' == $services['google-plus-1'] ) {
			$services['google-plus-1'] = 'Share_Google';
		}

		return $services;
	}
}
