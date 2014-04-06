<div class="wrap">

	<h2><?php _e( 'New Affiliate', 'affiliate-wp' ); ?></h2>
	
	<form method="post" id="affwp_add_affiliate">

		<?php do_action( 'affwp_new_affiliate_top' ); ?>

		<p><?php printf( __( 'Use this screen to register a new affiliate. Each affiliate is tied directly to a user account, so if the user account for the affiliate does not yet exist, <a href="%s" target="_blank">create one</a>.', 'affiliate-wp' ), admin_url( 'user-new.php' ) ); ?></p>

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="user_name"><?php _e( 'User', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<span class="affwp-ajax-search-wrap">
						<input type="text" name="user_name" id="user_name" class="affwp-user-search" autocomplete="off" />
						<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
					</span>
					<div id="affwp_user_search_results"></div>
					<div class="description"><?php _e( 'Begin typing the name of the affiliate to perform a search for their associated user account.', 'affiliate-wp' ); ?></div>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="rate"><?php _e( 'Rate', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="rate" id="rate" />
					<div class="description"><?php _e( 'Referral rate, such as 20 for 20%. If left blank, the site default will be used.', 'affiliate-wp' ); ?></div>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="payment_email"><?php _e( 'Payment Email', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="payment_email" id="payment_email" />
					<div class="description"><?php _e( 'Affiliate\'s payment email for systems such as PayPal, Moneybookers, or others. Leave blank to use the affiliate\'s user email', 'affiliate-wp' ); ?></div>
				</td>

			</tr>

		</table>

		<?php do_action( 'affwp_new_affiliate_bottom' ); ?>

		<input type="hidden" name="user_id" id="user_id" value="" />
		<input type="hidden" name="affwp_action" value="add_affiliate" />

		<?php submit_button( __( 'Add Affiliate', 'affiliate-wp' ) ); ?>

	</form>

</div>
