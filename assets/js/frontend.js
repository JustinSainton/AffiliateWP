jQuery(document).ready(function($) {
	$( '#affwp-generate-ref-url' ).submit( function() {
		var url = $( '#affwp-url' ).val(),
			ref_var = $( '#affwp-referral-var' ).val(),
			aff_id = $( '#affwp-affiliate-id' ).val();

		if ( url.indexOf( '?' ) < 0 ) {
			ref_var = '?' + ref_var;
		} else {
			ref_var = '&' + ref_var;
		}

		url = url + ref_var + '=' + aff_id;

		$( '.affwp-referral-url-wrap' ).slideDown();
		$( '#affwp-referral-url' ).val( url ).focus();

		return false;
	});
});