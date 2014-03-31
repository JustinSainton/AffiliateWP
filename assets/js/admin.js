jQuery(document).ready(function($) {
	
	// Show referral export form
	$('.affwp-referrals-export-toggle').click(function() {
		$('.affwp-referrals-export-toggle').toggle();
		$('#affwp-referrals-export-form').slideToggle();
	});

	// datepicker
	if( $('.affwp-datepicker').length ) {
		$('.affwp-datepicker').datepicker();
	}

	// ajax user search
	$('.affwp-user-search').keyup(function() {
		var user_search = $(this).val();
		$('.affwp-ajax').show();
		data = {
			action: 'affwp_search_users',
			user_name: user_search
		};

		$.ajax({
			type: "POST",
			data: data,
			dataType: "json",
			url: ajaxurl,
			success: function (search_response) {

				$('.affwp-ajax').hide();

				$('#affwp_user_search_results').html('');

				$(search_response.results).appendTo('#affwp_user_search_results');
			}
		});
	});
	$('body').on('click.rcpSelectUser', '#affwp_user_search_results a', function(e) {
		e.preventDefault();
		var login = $(this).data('login'), id = $(this).data('id');
		$('#user_name').val(login);
		$('#user_id').val(id);
		$('#affwp_user_search_results').html('');
	});
});