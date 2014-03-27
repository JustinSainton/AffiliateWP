jQuery(document).ready(function($) {
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

				if(search_response.id == 'found') {
					$(search_response.results).appendTo('#affwp_user_search_results');
				} else if(search_response.id == 'fail') {
					$('#affwp_user_search_results').text(search_response.msg);
				}
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