jQuery(document).ready( function($) {

	$( '#affwp-generate-ref-url' ).submit( function() {

		var url                 = $( this ).find( '#affwp-url' ).val(),
		    campaign            = $( this ).find( '#affwp-campaign' ).val(),
		    refVar              = $( this ).find( 'input[type="hidden"].affwp-referral-var' ).val(),
		    affId               = $( this ).find( 'input[type="hidden"].affwp-affiliate-id' ).val(),
		    prettyAffiliateUrls = affwp_vars.pretty_affiliate_urls,
		    add                 = '';

		// URL has fragment
		if ( url.indexOf( '#' ) > 0 ) {
			var fragment = url.split('#');
		}

		// if fragment, remove it, we'll append it later
		if ( fragment ) {
			url = fragment[0];
		}

		if ( prettyAffiliateUrls ) {
			// pretty affiliate URLs

			if ( url.indexOf( '?' ) < 0 ) {
				// no query strings

				// add trailing slash if missing
				if ( ! url.match( /\/$/ ) ) {
				    url += '/';
				}

				if( campaign.length ) {

					campaign = '?campaign=' + campaign;

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

				if( campaign.length ) {

					campaign = '&campaign=' + campaign;

				}

			}

			// build URL
			url = url + refVar + '/' + affId + add + campaign;

		} else {

			// non-pretty URLs

			if( campaign.length ) {

				campaign = '&campaign=' + campaign;

			}


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
			url = url + '?' + refVar + '=' + affId + add + campaign;

		}

		// if there's a fragment, add it to the end of the URL
		if ( fragment) {
			url += '#' + fragment[1];
		}

		// clean URL to remove any instances of multiple slashes
		url = url.replace(/([^:])(\/\/+)/g, '$1/');

		$( this ).find( '.affwp-referral-url-wrap' ).slideDown();
		$( this ).find( '#affwp-referral-url' ).val( url ).focus();

		return false;
	});

});