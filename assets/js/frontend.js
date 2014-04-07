jQuery(document).ready( function($) {
	$( '#affwp-generate-ref-url' ).submit( function() {
		var url    = $( '#affwp-url' ).val(),
		    refVar = $( '#affwp-referral-var' ).val(),
		    affId  = $( '#affwp-affiliate-id' ).val();

		if ( url.indexOf( '?' ) < 0 ) {
			refVar = '?' + refVar;
		} else {
			refVar = '&' + refVar;
		}

		url = url + refVar + '=' + affId;

		$( '.affwp-referral-url-wrap' ).slideDown();
		$( '#affwp-referral-url' ).val( url ).focus();

		return false;
	});
});