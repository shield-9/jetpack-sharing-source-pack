jQuery(document).ready(function($) {
	if ('undefined' != typeof WPCOM_sharing_counts) {
		for (var url in WPCOM_sharing_counts) {
			get_feedly_counts(url);
		}
	}
	function get_feedly_counts(url) {
		feedly_api = 'http://cloud.feedly.com/v3/feeds/' + encodeURI('feed/' + feed_URL);
		$.getJSON(feedly_api)
			.done(function(data) {
				if ( 'undefined' != typeof data.subscribers && ( data.subscribers * 1 ) > 0 ) {
					
					WPCOMSharing.inject_share_count('sharing-feedly-' + WPCOM_sharing_counts[ data.url ], data.count);
				}
			});
	}
});

