jQuery(document).ready( function($) {

	$( '#affwp-generate-ref-url' ).submit( function() {

		var url                 = $( '#affwp-url' ).val(),
		    refVar              = $( '#affwp-referral-var' ).val(),
		    affId               = $( '#affwp-affiliate-id' ).val(),
		    prettyAffiliateUrls = affwp_vars.pretty_affiliate_urls,
		    add                 = ''
		
		if ( prettyAffiliateUrls ) {
			// pretty affiliate URLs

			if ( url.indexOf( '?' ) < 0 ) {
				// no query strings

				// add trailing slash if missing
				if ( ! url.match( /\/$/ ) ) {
				    url += '/';
				}

			} else {
				// has query strings

				// split query string at first occurrence of ?
				var pieces = url.split('?');

				// set URL back to first piece
				url = pieces[0];

				// add trailing slash if missing
				if ( ! url.match( /\/$/ ) ) {
				    url += '/';
				}

				// add any query strings to the end
				add = '/?' + pieces[1];
			}

			// build URL
			url = url + refVar + '/' + affId + add;

		} else {
			// non-pretty URLs

			if ( url.indexOf( '?' ) < 0 ) {

				// add trailing slash if missing
				if ( ! url.match( /\/$/ ) ) {
				    url += '/';
				}

			} else {

				// split query string at first occurrence of ?
				var pieces = url.split('?');

				// set url back to first piece
				url = pieces[0];

				// add trailing slash if missing
				if ( ! url.match( /\/$/ ) ) {
				    url += '/';
				}

				// add any query strings to the end
				add = '&' + pieces[1];
			}

			// build URL
			url = url + '?' + refVar + '=' + affId + add;
		}

		// clean URL to remove any instances of multiple slashes
		url = url.replace(/([^:])(\/\/+)/g, '$1/');

		$( '.affwp-referral-url-wrap' ).slideDown();
		$( '#affwp-referral-url' ).val( url ).focus();

		return false;
	});
	
});