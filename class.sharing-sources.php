<?php

if(!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

class Share_Feedly extends Sharing_Source {
	public $shortname = 'feedly';

	function __construct($id, array $settings) {
		parent::__construct( $id, $settings );

		if ( 'official' == $this->button_style )
			$this->smart = true;
		else
			$this->smart = false;
	}

	function get_name() {
		return __('Feedly', 'jpssp');
	}

	function get_display($post) {
		if ( apply_filters( 'jetpack_register_post_for_share_counts', true, $post->ID, 'feedly' ) ) {
			sharing_register_post_for_share_counts( $post->ID );
		}

		return $this->get_link(
			get_permalink( $post->ID ),
			_x( 'Feedly', 'share to', 'jpssp' ),
			__( 'Subscribe on Feedly', 'jpssp' ),
			'share=feedly',
			'sharing-feedly-' . $post->ID
		);
	}

	function display_header() {
		wp_enqueue_style('jpssp');
	}

	function display_footer() {
		$this->js_dialog( $this->shortname );
	}

	function process_request( $post, array $post_data ) {
		$feed_url = get_bloginfo('rss2_url');
		$feedly_url = $this->http() . '://feedly.com/#subscription%2Ffeed%2F' . rawurlencode( $feed_url );

		// Redirect to Feedly
		wp_redirect( $feedly_url );
		die();
	}

}
