jQuery(document).ready( function($) {

    var cookie = $.cookie( 'affwp_ref' );

    if( cookie ) {
        return;
    }

    var ref = affwp_get_query_vars()[AFFWP.referral_var];

    if( typeof ref == 'undefined' ) {

        // See if we are using a pretty affiliate URL
        var path = window.location.pathname.split( '/' );

        $.each( path, function( key, value ) {
            if( AFFWP.referral_var == value ) {
                ref = path[ key + 1 ];
            }
        });

    }


    if( ! $.isNumeric( ref ) ) {

        // If a username was provided instead of an affiliate ID number, we need to retrieve the ID
        $.ajax({
            type: "POST",
            data: {
                action: 'affwp_get_affiliate_id',
                affiliate: ref
            },
            url: affwp_scripts.ajaxurl,
            success: function (response) {
                if( '1' == response.data.success ) {
                    affwp_track_visit( response.data.affiliate_id );
                }
            }

        }).fail(function (response) {
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        });

    } else {

        // If a referral var is present and a referral cookie is not already set
        if( ref && ! cookie ) {
            affwp_track_visit( ref );
        }

    }

    function affwp_track_visit( affiliate_id ) {

        // Set the cookie and expire it after 24 hours
        $.cookie( 'affwp_ref', affiliate_id, { expires: AFFWP.expiration, path: '/' } );

        // Fire an ajax request to log the hit
        $.ajax({
            type: "POST",
            data: {
                action: 'affwp_track_visit',
                affiliate: affiliate_id,
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
        });

    }

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
});
