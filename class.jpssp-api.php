<?php

abstract class JPSSP_API {
	const API_NS = 'jpssp/v1';
	const API_ENDPOINT = 'jpssp';
	const API_PATH = self::API_NS . '/' . self::API_ENDPOINT;

	static $instance;

	public static function init() {
		$name = get_called_class();

		if( !isset( static::$instance[ $name ] ) ) {
			static::$instance[ $name ] = new static();
		}

		return static::$instance;
	}

	public function __construct() {
		register_rest_route( static::API_NS, static::API_ENDPOINT, array(
			'methods'  => 'GET',
			'callback' => array( &$this, 'get_item' ),
			'args'     => array(
				'url' => array(
					'required' => false,
				),
			),
		) );
	}

	protected function remote_request( $transient_name, $url, $method = 'get', $args = array() ) {
		$response = get_transient( 'jpssp_' . $transient_name );

		if( $response !== false ) {
			return $response;
		}

		$default_args = array( 'httpversion' => '1.1' );
		$args = array_merge( $default_args, $args );

		switch( $method ) {
			case 'post':
				$response = wp_remote_post( $url, $args );
				break;

			case 'get':
			default:
				$response = wp_remote_get( $url, $args );
		}

		$status = wp_remote_retrieve_response_code( $response );

		if( !is_wp_error( $response ) && $status == 200 ) {
			set_transient( $transient_name, $response, HOUR_IN_SECONDS );
		}

		return $response;
	}

	abstract public function get_item();
}

class Feedly_API extends JPSSP_API {
	const API_ENDPOINT = 'feedly';

	public function get_item() {
		$feed_url       = get_bloginfo('rss2_url');
		$feedly_url     = 'https://cloud.feedly.com/v3/feeds/' . rawurlencode( 'feed/' . $feed_url );
		$transient_name = 'feedly_' . hash( 'crc32b', $feedly_url );

		$response = $this->remote_request( $transient_name, $feedly_url );
		$status   = wp_remote_retrieve_response_code( $response );

		if( is_wp_error( $response ) || $status != 200 ) {
			return new WP_Error( 'jpssp_gateway_error', get_status_header_desc( $status ), array( 'status' => $status ) );
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}
}

class Google_API extends JPSSP_API {
	const API_ENDPOINT = 'google';

	public function get_item() {

		if( !empty( $_GET['url'] ) ) {
			$url = $_GET['url'];
		} else {
			$url = home_url();
		}

		$transient_name = 'google_' . hash( 'crc32b', $url );

		$response = $this->remote_request( $transient_name, 'https://clients6.google.com/rpc', 'post', array(
			'body'    => wp_json_encode( array(
				array(
					'method'     => 'pos.plusones.get',
					'id'         => 'p',
					'params'     => array(
						'nolog'   => true,
						'id'      => $url,
						'source'  => 'widget',
						'userId'  => '@viewer',
						'groupId' => '@self',
					),
					'jsonrpc'    => '2.0',
					'key'        => 'p',
					'apiVersion' => 'v1',
				),
			) ),
			'headers' => array( 'Content-Type' => 'application/json' ),
		) );
		$status = wp_remote_retrieve_response_code( $response );

		if( is_wp_error( $response ) || $status != 200 ) {
			return new WP_Error( 'jpssp_gateway_error', get_status_header_desc( $status ), array( 'status' => $status ) );
		}

		$result = json_decode( wp_remote_retrieve_body( $response ), true );
		$result = $result[0];

		$data = array(
			'url'   => $url,
			'count' => 0,
		);

		if( !isset( $result['error'] ) && isset( $result['result']['metadata']['globalCounts']['count'] ) ) {
			$data['count'] = intval( $result['result']['metadata']['globalCounts']['count'] );
		}

		return $data;
	}
}
