jQuery(document).ready( function($) {

    function affwp_get_query_vars() {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }


    var cookie = $.cookie( 'affwp_ref' );
    var ref = affwp_get_query_vars()[AFFWP.referral_var];

    // If a referral var is present and a referral cookie is not already set
    if( ref && ! cookie ) {
        var cookie_value = ref;

        // Set the cookie and expire it after 24 hours
        $.cookie( 'affwp_ref', cookie_value, { expires: AFFWP.expiration, path: '/' } );

        // Fire an ajax request to log the hit
        $.ajax({
            type: "POST",
            data: {
                action: 'affwp_track_visit',
                affiliate: ref,
                url: document.URL,
                referrer: document.referrer
            },
            url: affwp_scripts.ajaxurl,
            success: function (response) {
                $.cookie( 'affwp_ref_visit_id', response, { expires: AFFWP.expiration, path: '/' } );
            }

        }).fail(function (response) {
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        }).done(function (response) {
        });
    }
});