jQuery(document).ready(function($) {
	if ('undefined' != typeof WPCOM_sharing_counts) {
		for (var url in WPCOM_sharing_counts) {
			get_feedly_count(url);
		}
	}

	function get_feedly_count(url) {
		$.ajaxSetup({
			cache: true
		});
		$.getScript(feedly_api);
	}
});

function update_feedly_count(data) {
	if ( 'undefined' != typeof data.subscribers && ( data.subscribers * 1 ) > 0 ) {
		WPCOMSharing.inject_share_count('sharing-feedly-' + post_id, data.subscribers);
	}
}
