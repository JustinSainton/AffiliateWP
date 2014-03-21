<div class="wrap">

	<h2><?php _e( 'New Affiliate', 'affiliate-wp' ); ?></h2>
	
	<form method="post" id="affwp_add_affiliate">

		<?php do_action( 'affwp_new_affiliate_top' ); ?>

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="user_id"><?php _e( 'User ID', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="user_id" id="user_id" />
					<div class="description"><?php _e( 'Enter the ID of the user to register as an affiliate. To search for a user\'s ID, enter the user\'s login name, first name, or last name to perform a search.', 'affiliate-wp' ); ?></div>
				</td>

			</tr>

			<tr class="form-row">

				<td colspan="2"><?php _e( 'If the user account for this affiliate does not exist, create one first.', 'affiliate-wp' ); ?></td>

			</tr>

		</table>

		<?php do_action( 'affwp_new_affiliate_bottom' ); ?>

		<input type="hidden" name="affwp_action" value="add_affiliate" />

		<?php submit_button( __( 'Add Affiliate', 'affiliate-wp' ) ); ?>

	</form>

</div>
