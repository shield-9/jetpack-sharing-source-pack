<?php

$instance = JPSSP_API::init();

class JPSSP_API {
	static $instance;

	const OPTION_NAME_ACTIVATED = 'jpssp-api_activated';
	const API_ENDPOINT = 'feedly-api';

	static function init() {
		if(!self::$instance) {
			self::$instance = new JPSSP_API;
		}
		return self::$instance;
	}

	function __construct() {
		add_action('init', array(&$this, 'add_rewrite_endpoint'));
		add_action('delete_option', array($this, 'delete_option'), 10, 1 );
		add_filter('query_vars', array($this, 'query_vars'));
		add_action('template_redirect', array($this, 'template_redirect'));
	}

	static function activation(){
		update_option( self::OPTION_NAME_ACTIVATED, true );
		flush_rewrite_rules();
	}

	static function deactivation(){
		delete_option( self::OPTION_NAME_ACTIVATED, true );
		flush_rewrite_rules();
	}
	public function delete_option($option){
		if ( 'rewrite_rules' === $option && get_option(self::OPTION_NAME_ACTIVATED) ) { 
			$this->add_rewrite_endpoint();
		}
	}

	public function add_rewrite_endpoint() {
		add_rewrite_endpoint(self::API_ENDPOINT, EP_ROOT);
	}

	public function query_vars($vars) {
		$vars[] = self::API_ENDPOINT;
		return $vars;
	}

	public function template_redirect() {
		global $wp_query;
		if (is_object($wp_query) && isset($wp_query->query[self::API_ENDPOINT])) {
			$feed_url = get_bloginfo('rss2_url');
			$feedly_url = 'https://cloud.feedly.com/v3/feeds/' . rawurlencode('feed/' . $feed_url);

			if(($response = get_transient('jpssp-feedly-api')) === false) {
				$response = wp_remote_get($feedly_url, array('httpversion' => '1.1'));
				$status = wp_remote_retrieve_response_code($response);

				if(!is_wp_error($response) && $status == 200) {
					set_transient('jpssp-feedly-api', $response, HOUR_IN_SECONDS);
				}
			} else {
				$status = wp_remote_retrieve_response_code($response);
			}

			nocache_headers();
			header('Content-Type: application/javascript; charset='.get_option('charset'));

			$callback = (!empty($_GET['callback'])) ? esc_js($_GET['callback']) : 'update_feedly_count';
			echo $callback . '(';
			if(!is_wp_error($response) && $status == 200) {
				echo wp_remote_retrieve_body($response);
			} else {
				status_header($status);
				echo json_encode(array(
					'meta' => array(
						'code' => $status,
						'message' => wp_remote_retrieve_response_message($response),
					),
				));
			}
			echo ');';
			exit;
		}
	}
}