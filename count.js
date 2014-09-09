jQuery(document).ready(function($) {
	// Feedly
	if (!feedly_smart && 'undefined' != typeof WPCOM_sharing_counts) {
		for (var url in WPCOM_sharing_counts) {
			get_feedly_count(url);
		}
	} else if(feedly_smart) {
		get_feedly_count(url);
	}

	function get_feedly_count(url) {
		$.ajaxSetup({
			cache: true
		});

		if(feedly_smart) {
			feedly_api += 'smart/';
		}
		
		feedly_api += encodeURI(url);

		$.getScript(feedly_api);
	}

});

function update_feedly_count(data) {
	if(data.smart) {
		jQuery('.sd-social-official .feedly_button .count-number span').text(data.subscribers);
		jQuery('.sd-social-official .feedly_button .count-wrap').show();
	} else {
		if ( 'undefined' != typeof data.subscribers && ( data.subscribers * 1 ) > 0 ) {
			WPCOMSharing.inject_share_count('sharing-feedly-' + WPCOM_sharing_counts[ data.url ], data.subscribers);
		}
	}
}
