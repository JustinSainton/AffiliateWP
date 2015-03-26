jQuery(document).ready( function($) {

	$( '#affwp-generate-ref-url' ).submit( function() {

		var url       = $( '#affwp-url' ).val(),
		    refVar    = $( '#affwp-referral-var' ).val(),
		    affId     = $( '#affwp-affiliate-id' ).val(),
		    refPretty = affwp_vars.pretty_affiliate_urls,
		    refFormat = affwp_vars.referral_format

		
		if ( refPretty ) {
			// pretty affiliate URLs

			if ( url.indexOf( '?' ) < 0 ) {
				// no query strings

				// add trailing slash if missing
				if ( ! url.match( /\/$/ ) ) {
				    url += '/';
				}

				url = url + refVar + '/' + affId;

			} else {
				// has query strings

				// split query string at first occurance of ?
				var pieces = url.split('?');

				// set url back to first piece
				url = pieces[0];

				// add trailing slash if missing
				if ( ! url.match( /\/$/ ) ) {
				    url += '/';
				}

				url = url + refVar + '/' + affId + '/?' + pieces[1]; 
			}

		} else {
			// non-pretty URLs
			if ( url.indexOf( '?' ) < 0 ) {
				refVar = '?' + refVar;
			} else {
				refVar = '&' + refVar;
			}

			url = url + refVar + '=' + affId;
		}

		// clearn URL to remove any instances of multiple slashes
		url = url.replace(/([^:])(\/\/+)/g, '$1/');

		$( '.affwp-referral-url-wrap' ).slideDown();
		$( '#affwp-referral-url' ).val( url ).focus();

		return false;
	});

});