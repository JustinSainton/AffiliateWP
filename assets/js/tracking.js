jQuery(document).ready( function($) {

    var ref_cookie = $.cookie( 'affwp_ref' );
    var visit_cookie = $.cookie( 'affwp_ref_visit_id' );
    var campaign_cookie = $.cookie( 'affwp_campaign' );

    var credit_last = AFFWP.referral_credit_last;

    if( '1' != credit_last && ref_cookie ) {
        return;
    }

    var ref = affwp_get_query_vars()[AFFWP.referral_var];
    var campaign = affwp_get_query_vars()['campaign'];

    if( typeof ref == 'undefined' ) {

        // See if we are using a pretty affiliate URL
        var path = window.location.pathname.split( '/' );

        $.each( path, function( key, value ) {
            if( AFFWP.referral_var == value ) {
                ref = path[ key + 1 ];
            }
        });

    }


    if( typeof ref != 'undefined' && ! $.isNumeric( ref ) ) {

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

                    if( '1' == credit_last && ref_cookie ) {
                        $.removeCookie( 'affwp_ref' );
                    }

                    affwp_track_visit( response.data.affiliate_id, campaign );
                }
            }

        }).fail(function (response) {
            if ( window.console && window.console.log ) {
                console.log( response );
            }
        });

    } else {

        // If a referral var is present and a referral cookie is not already set
        if( ref && ! ref_cookie ) {
            affwp_track_visit( ref, campaign );
        } else if( '1' == credit_last && ref && ref_cookie && ref !== ref_cookie ) {
            $.removeCookie( 'affwp_ref' );
            affwp_track_visit( ref, campaign );
        }

    }

    function affwp_track_visit( affiliate_id, url_campaign ) {

        // Set the cookie and expire it after 24 hours
        $.cookie( 'affwp_ref', affiliate_id, { expires: AFFWP.expiration, path: '/' } );

        // Fire an ajax request to log the hit
        $.ajax({
            type: "POST",
            data: {
                action: 'affwp_track_visit',
                affiliate: affiliate_id,
                campaign: url_campaign,
                url: document.URL,
                referrer: document.referrer
            },
            url: affwp_scripts.ajaxurl,
            success: function (response) {
                $.cookie( 'affwp_ref_visit_id', response, { expires: AFFWP.expiration, path: '/' } );
                $.cookie( 'affwp_campaign', url_campaign, { expires: AFFWP.expiration, path: '/' } );
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

            var key = typeof hash[1] == 'undefined' ? 0 : 1;

            // Remove fragment identifiers
            var n = hash[key].indexOf('#');
            hash[key] = hash[key].substring(0, n != -1 ? n : hash[key].length);
            vars[hash[0]] = hash[key];
        }
        return vars;
    }
});
