<?php

$instance = JPSSP_API::init();

class JPSSP_API {
	static $instance;

	const OPTION_NAME_ACTIVATED = 'jpssp-api_activated';
	const API_ENDPOINT = null;

	static function init() {
		if( !self::$instance ) {
			self::$instance = new __CLASS__;
		}
		return self::$instance;
	}

	function __construct() {
		add_filter( 'force_ssl',         array( &$this, 'force_ssl' ),     10, 3 );
		add_action( 'init',              array( &$this, 'add_rewrite_endpoint' ) );
		add_action( 'delete_option',     array( $this,  'delete_option' ), 10, 1 );
		add_filter( 'query_vars',        array( $this,  'query_vars' ) );
		add_action( 'template_redirect', array( $this,  'template_redirect' ) );
	}

	function force_ssl( $force_ssl, $post_id = 0, $url = '' ) {
		global $wp_query;
		if( is_object( $wp_query ) && isset( $wp_query->query[ self::API_ENDPOINT ] ) && $url == set_url_scheme( $url, 'https' ) ) {
			$force_ssl = true;
		}
		return $force_ssl;
	}

	static function activation(){
		update_option( self::OPTION_NAME_ACTIVATED, true );
		flush_rewrite_rules();
	}

	static function deactivation(){
		delete_option( self::OPTION_NAME_ACTIVATED, true );
		flush_rewrite_rules();
	}
	public function delete_option( $option ){
		if( 'rewrite_rules' === $option && get_option( self::OPTION_NAME_ACTIVATED ) ) { 
			$this->add_rewrite_endpoint();
		}
	}

	public function add_rewrite_endpoint() {
		add_rewrite_endpoint( self::API_ENDPOINT, EP_ROOT );
	}

	public function query_vars( $vars ) {
		$vars[] = self::API_ENDPOINT;
		return $vars;
	}

	abstract function template_redirect();
}

class Feedly_API extends JPSSP_API {
	static $instance;

	const API_ENDPOINT = 'feedly-api';

	public function template_redirect() {
		global $wp_query;
		if( is_object( $wp_query ) && isset( $wp_query->query[ self::API_ENDPOINT ] ) ) {
			$feed_url       = get_bloginfo('rss2_url');
			$feedly_url     = 'https://cloud.feedly.com/v3/feeds/' . rawurlencode( 'feed/' . $feed_url );
			$transient_name = 'jpssp-feedly-api_' . hash( 'crc32b', $feedly_url );

			if( ( $response = get_transient( $transient_name ) ) === false ) {
				$response = wp_remote_get( $feedly_url, array( 'httpversion' => '1.1' ) );
				$status   = wp_remote_retrieve_response_code( $response );

				if( !is_wp_error( $response ) && $status == 200 ) {
					set_transient( $transient_name, $response, HOUR_IN_SECONDS );
				}
			} else {
				$status = wp_remote_retrieve_response_code( $response );
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			nocache_headers();
			header('Content-Type: application/javascript; charset=UTF-8');

			$callback = 'update_feedly_count';
			if( !empty( $_GET['callback'] ) ) {
				$callback = esc_js( $_GET['callback'] );
			}

			if( !empty( $_GET['url'] ) ) {
				$body->{'url'} = esc_js( $_GET['url'] );
			}

			switch( $wp_query->query[ self::API_ENDPOINT ] ) {
				case 'smart':
					$body->{'smart'} = true;
					break;
				default:
					$body->{'smart'} = false;
			}

			echo $callback . '(';
			if( !is_wp_error( $response ) && $status == 200 ) {
				echo json_encode( $body, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE );
			} else {
				status_header( $status );
				echo json_encode( array(
					'meta' => array(
						'code'    => $status,
						'message' => wp_remote_retrieve_response_message( $response ),
					),
				) );
			}
			echo ');';
			exit;
		}
	}
}
