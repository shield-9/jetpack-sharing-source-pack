<?php

class JPSSP {
	static $instance;

	private $data;

	static function init() {
		if( !self::$instance ) {
			if( did_action( 'plugins_loaded' ) ) {
				self::plugin_textdomain();
			} else {
				add_action( 'plugins_loaded', array( __CLASS__, 'plugin_textdomain' ) );
			}

			self::$instance = new JPSSP;
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts',     array( &$this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts',  array( &$this, 'admin_menu_assets' ) );

		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
	}

	function register_assets() {
		if( get_option( 'sharedaddy_disable_resources' ) ) {
			return;
		}

		if( !Jetpack::is_module_active( 'sharedaddy' ) ) {
			return;
		}

		wp_enqueue_script( 'jpssp', JPSSP__PLUGIN_URL . 'count.js', array( 'jquery','sharing-js' ), JPSSP__VERSION, true );
		wp_enqueue_style( 'jpssp', JPSSP__PLUGIN_URL . 'style.css', array(), JPSSP__VERSION );
	}

	function admin_menu_assets( $hook ) {
		if( $hook == 'settings_page_sharing' ) {
			wp_enqueue_style( 'jpssp', JPSSP__PLUGIN_URL . 'style.css', array( 'sharing', 'sharing-admin' ), JPSSP__VERSION );
		}
	}

	static function require_services() {
		if( !Jetpack::is_module_active( 'sharedaddy' ) ) {
			return;
		}

		require_once( JPSSP__PLUGIN_DIR . 'class.sharing-services.php' );

		JPSSP_Sharing_Service::init();
	}

	static function register_rest_api() {
		Feedly_API::init();
		Google_API::init();
	}

	static function plugin_textdomain() {
		load_plugin_textdomain( 'jpssp', false, dirname( plugin_basename( JPSSP__PLUGIN_FILE ) ) . '/languages/' );
	}

	function plugin_row_meta( $links, $file ) {
		if( plugin_basename( __FILE__ ) === $file ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( 'https://www.extendwings.com/donate/' ),
				__( 'Donate', 'jpssp' )
			);
		}
		return $links;
	}
}
