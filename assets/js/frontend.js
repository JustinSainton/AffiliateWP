jQuery(document).ready(function($) {
	$('#affwp_generate_ref_url').submit(function() {
		var url = $('#affwp_url').val(),
			ref_var = $('#affwp_referral_var').val(),
			aff_id = $('#affwp_affiliate_id').val();

		if( url.indexOf( "?" ) < 0 ) {
			ref_var = '?' + ref_var;
		} else {
			ref_var = '&' + ref_var;
		}

		url = url + ref_var + '=' + aff_id;
		$('#affwp_referral_url_wrap').slideDown();
		$('#affwp_referral_url').val( url ).focus();
		return false;
	})
});