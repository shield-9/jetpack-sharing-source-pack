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

	function get_link_addr( $url, $query = '' ) {
		$url = apply_filters( 'sharing_display_link', $url );
		if ( !empty( $query ) ) {
			if ( stripos( $url, '?' ) === false )
				$url .= '?'.$query;
			else
				$url .= '&amp;'.$query;
		}

		return $url;
	}

	function get_name() {
		return __('Feedly', 'jpssp');
	}

	function has_custom_button_style() {
		return $this->smart;
	}

	function get_display($post) {
		if ( apply_filters( 'jetpack_register_post_for_share_counts', true, $post->ID, 'feedly' ) ) {
			sharing_register_post_for_share_counts( $post->ID );
		}

		$feed_id = 'feed/'.get_bloginfo('rss2_url');

		if ( $this->smart ) {
			$button = '';
			$button .= '<div class="feedly_button"><div class="feedly">';
			$button .= '<table cellpadding="0" cellspacing="0"><tr>';
			$button .= '<td class="button-wrap">';

			$button .= '<div data-scribe="component:button">';
			$button .= sprintf(
					'<a rel="nofollow" href="%s" class="share-feedly">%s</a>',
					esc_url($this->get_link_addr( get_permalink( $post->ID ), 'share=feedly' )),
					'<i></i>'
				);
			$button .= '</div>';

			$button .= '</td>';
			$button .= '<td class="count-wrap">';

			$button .= '<div data-scribe="component:count">';
			$button .= '<div class="count-number">';
			$button .= sprintf(
					'<span data-feed-id="%s">-</span>',
					rawurlencode( $feed_id )
				);
			$button .= '</div>';
			$button .= '<div class="count-arrow">';
			$button .= '<s></s>';
			$button .= '<i></i>';
			$button .= '</div>';
			$button .= '</div>';

			$button .= '</td>';
			$button .= '</tr></table>';
			$button .= '</div></div>';
			
			return $button;
		} else {
			return $this->get_link(
					get_permalink( $post->ID ),
					_x( 'Feedly', 'share to', 'jpssp' ),
					__( 'Subscribe on Feedly', 'jpssp' ),
					'share=feedly',
					'sharing-feedly-' . $post->ID
				);
		}
	}

	function display_header() {
		wp_enqueue_style('jpssp', JPSSP__PLUGIN_URL .'style.css', array('sharedaddy'), JPSSP__VERSION);
	}

	function display_footer() {
		global $post;
	?>
		<script>
			var post_id = <?php echo $post->ID; ?>;
			var feedly_api = '<?php echo home_url(JPSSP_API::API_ENDPOINT.'/'); ?>';
			<?php if($this->smart): ?>
			var feedly_smart = true;
			<?php else: ?>
			var feedly_smart = false;
			<?php endif; ?>
		</script>
	<?php
		wp_enqueue_script('jpssp', JPSSP__PLUGIN_URL .'count.js', array('jquery'), JPSSP__VERSION, true);
		$this->js_dialog( $this->shortname, array( 'width' => 1024, 'height' => 576 ) );
	}

	function process_request( $post, array $post_data ) {
		$feed_url = get_bloginfo('rss2_url');
		$feedly_url = $this->http() . '://feedly.com/#' . rawurlencode( 'subscription/'.$feed_url );

		// Redirect to Feedly
		wp_redirect( $feedly_url );
		die();
	}
}
