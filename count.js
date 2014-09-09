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

		var request_url = feedly_api;

		if(feedly_smart) {
			request_url += 'smart/';
		}
		
		request_url += '?callback=JPSSP_Sharing.update_feedly_count&url=' + encodeURI(url);

		$.getScript(request_url);
	}

	// Hatena
	if ('undefined' != typeof WPCOM_sharing_counts) {
		for (var url in WPCOM_sharing_counts) {
			get_hatena_count(url);
		}
	} else if(feedly_smart) {
		get_hatena_count(url);
	}

	function get_hatena_count(url) {
		$.ajaxSetup({
			cache: true
		});

		var request_url = 'http://api.b.st-hatena.com/entry.counts?url='+encodeURIComponent(url)+'&callback=JPSSP_Sharing.update_hatena_count';

		$.getScript(request_url);
	}
});

var JPSSP_Sharing = {
	update_feedly_count: function(data) {
		if(data.smart) {
			jQuery('.sd-social-official .feedly_button .count-number span').text(data.subscribers);
			jQuery('.sd-social-official .feedly_button .count-wrap').show();
		} else {
			if ( 'undefined' != typeof data.subscribers && ( data.subscribers * 1 ) > 0 ) {
				WPCOMSharing.inject_share_count('sharing-feedly-' + WPCOM_sharing_counts[ data.url ], data.subscribers);
			}
		}
	},
	update_hatena_count: function(data) {
		if ( 'undefined' != typeof data ){
			for (var url in data) {
				if( ( data[url] * 1 ) > 0 ) {
					WPCOMSharing.inject_share_count('sharing-hatena-' + WPCOM_sharing_counts[ url ], data[ url ]);
				}
			}
		}
	}
};
