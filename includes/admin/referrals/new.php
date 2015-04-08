<div class="wrap">

	<h2><?php _e( 'New Referral', 'affiliate-wp' ); ?></h2>
	
	<form method="post" id="affwp_add_referral">

		<?php do_action( 'affwp_new_referral_top' ); ?>

		<p><?php _e( 'Use this screen to manually create a new referral record for an affiliate.', 'affiliate-wp' ); ?></p>

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="user_name"><?php _e( 'Affiliate', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<span class="affwp-ajax-search-wrap">
						<input type="text" name="user_name" id="user_name" class="affwp-user-search" autocomplete="off" />
						<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
					</span>
					<div id="affwp_user_search_results"></div>
					<p class="description"><?php _e( 'Enter the name of the affiliate or enter a partial name or email to perform a search.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="amount"><?php _e( 'Amount', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="amount" id="amount" />
					<p class="description"><?php _e( 'The amount of the referral, such as 15.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="description"><?php _e( 'Description', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="description" id="description" />
					<p class="description"><?php _e( 'Enter a description for this referral.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="reference"><?php _e( 'Reference', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="reference" id="reference" />
					<p class="description"><?php _e( 'Enter a reference for this referral (optional). Usually this would be the transaction ID of the associated purchase.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="context"><?php _e( 'Context', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="context" id="context" />
					<p class="description"><?php _e( 'Enter a context for this referral (optional). Usually this is used to help identify the payment system that was used for the transaction.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="status"><?php _e( 'Status', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<select name="status" id="status">
						<option value="unpaid"><?php _e( 'Unpaid', 'affiliate-wp' ); ?></option>
						<option value="paid"><?php _e( 'Paid', 'affiliate-wp' ); ?></option>
						<option value="pending"><?php _e( 'Pending', 'affiliate-wp' ); ?></option>
						<option value="rejected"><?php _e( 'Rejected', 'affiliate-wp' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Select the status of the referral.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

		</table>

		<?php do_action( 'affwp_new_referral_bottom' ); ?>

		<?php echo wp_nonce_field( 'affwp_add_referral_nonce', 'affwp_add_referral_nonce' ); ?>
		<input type="hidden" name="user_id" id="user_id" value="" />
		<input type="hidden" name="affwp_action" value="add_referral" />

		<?php submit_button( __( 'Add Referral', 'affiliate-wp' ) ); ?>

	</form>

</div>
