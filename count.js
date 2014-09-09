jQuery(document).ready(function($) {
	if ('undefined' != typeof WPCOM_sharing_counts) {
		// Feedly
		if(!feedly_smart ) {
			for (var url in WPCOM_sharing_counts) {
				get_feedly_count(url);
			}
		} else {
			get_feedly_count(url);
		}

		// Hatena
		for (var url in WPCOM_sharing_counts) {
			get_hatena_count(url);
		}
	}

	function get_feedly_count(url) {
		var request_url = feedly_api;

		if(feedly_smart) {
			request_url += 'smart/';
		}
		
		request_url += '?url=' + encodeURI(url) + '&callback=JPSSP_Sharing.update_feedly_count';

		get_script(request_url);
	}

	function get_hatena_count(url) {
		var request_url = 'http://api.b.st-hatena.com/entry.counts?url='+encodeURIComponent(url)+'&callback=JPSSP_Sharing.update_hatena_count';

		get_script(request_url);
	}
	
	function get_script(url) {
		$.ajaxSetup({
			cache: true
		});

		$.getScript(url);
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
