jQuery(document).ready(function($) {
	
	// Show referral export form
	$('.affwp-referrals-export-toggle').click(function() {
		$('.affwp-referrals-export-toggle').toggle();
		$('#affwp-referrals-export-form').slideToggle();
	});

	$('#affwp-referrals-export-form').submit(function() {
		if( ! confirm( affwp_vars.confirm ) ) {
			return false;
		}
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

	// select image for creative
	var file_frame;
	$('body').on('click', '.upload_image_button', function(e) {

		e.preventDefault();

		var formfield = $(this).prev();

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
			file_frame.open();
			return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			frame: 'select',
			title: 'Choose Image',
			multiple: false,
			library: {
				type: 'image'
			},
			button: {
				text: 'Use Image'
			}
		});

		file_frame.on( 'menu:render:default', function(view) {
	        // Store our views in an object.
	        var views = {};

	        // Unset default menu items
	        view.unset('library-separator');
	        view.unset('gallery');
	        view.unset('featured-image');
	        view.unset('embed');

	        // Initialize the views in our view object.
	        view.set(views);
	    });

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {

			var attachment = file_frame.state().get('selection').first().toJSON();
			formfield.val(attachment.url);
			
			if ( $('#preview_image img') ) {
				$('#preview_image').show();

				var img = $('<img />'); 
				img.attr('src', attachment.url);
				img.appendTo('#preview_image');
			}
			else {
				$('#preview_image').show().find('img').attr('src', attachment.url);
			}
		});

		// Finally, open the modal
		file_frame.open();
	});
});