<?php
/**
 * Affiiates Overview
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Overview
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function affwp_affiliates_dashboard() {
?>
	<div class="wrap">
		<div id="affwp-dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				<div class="postbox-container">
					<div class="postbox">
						<h3><?php _e( 'Recent Referrals', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							stuff
						</div>
					</div>
				</div>
				<div class="postbox-container">
					<div class="postbox">
						<h3><?php _e( 'Affiliate Registrations', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							stuff
						</div>
					</div>
				</div>
				<div class="postbox-container">
					<div class="postbox">
						<h3><?php _e( 'Totals', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							stuff
						</div>
					</div>
				</div>
				<div class="postbox-container">
					<div class="postbox">
						<h3><?php _e( 'Recent Referrals', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							stuff
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}