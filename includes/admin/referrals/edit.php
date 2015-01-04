<?php
$referral = affwp_get_referral( absint( $_GET['referral_id'] ) );
?>
<div class="wrap">

	<h2><?php _e( 'Edit Referral', 'affiliate-wp' ); ?></h2>

	<form method="post" id="affwp_edit_referral">

		<?php do_action( 'affwp_edit_referral_top', $referral ); ?>

		<table class="form-table">


			<tr class="form-row form-required">

				<th scope="row">
					<label for="affiliate_id"><?php _e( 'Affiliate ID', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input class="small-text" type="text" name="affiliate_id" id="affiliate_id" value="<?php echo esc_attr( $referral->affiliate_id ); ?>" disabled="disabled"/>
					<p class="description"><?php _e( 'The affiliate\'s ID this referral belongs to. This value cannot be changed.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="amount"><?php _e( 'Amount', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="amount" id="amount" value="<?php echo esc_attr( $referral->amount ); ?>" />
					<p class="description"><?php _e( 'The amount of the referral, such as 15.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="description"><?php _e( 'Description', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="description" id="description" value="<?php echo esc_attr( $referral->description ); ?>" />
					<p class="description"><?php _e( 'Enter a description for this referral.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="reference"><?php _e( 'Reference', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="reference" id="reference" value="<?php echo esc_attr( $referral->reference ); ?>" />
					<p class="description"><?php _e( 'Enter a reference for this referral (optional). Usually this would be the transaction ID of the associated purchase.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="context"><?php _e( 'Context', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="context" id="context" value="<?php echo esc_attr( $referral->context ); ?>" />
					<p class="description"><?php _e( 'Enter a context for this referral (optional). Usually this is used to help identify the payment system that was used for the transaction.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="status"><?php _e( 'Status', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<select name="status" id="status">
						<option value="unpaid"<?php selected( 'unpaid', $referral->status ); ?>><?php _e( 'Unpaid', 'affiliate-wp' ); ?></option>
						<option value="paid"<?php selected( 'paid', $referral->status ); ?>><?php _e( 'Paid', 'affiliate-wp' ); ?></option>
						<option value="pending"<?php selected( 'pending', $referral->status ); ?>><?php _e( 'Pending', 'affiliate-wp' ); ?></option>
						<option value="rejected"<?php selected( 'rejected', $referral->status ); ?>><?php _e( 'Rejected', 'affiliate-wp' ); ?></option>
					</select>
					<p class="description"><?php _e( 'Select the status of the referral.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

		</table>

		<?php do_action( 'affwp_edit_referral_bottom', $referral ); ?>

		<?php echo wp_nonce_field( 'affwp_edit_referral_nonce', 'affwp_edit_referral_nonce' ); ?>
		<input type="hidden" name="referral_id" value="<?php echo absint( $referral->referral_id ); ?>" />
		<input type="hidden" name="affwp_action" value="process_update_referral" />

		<?php submit_button( __( 'Update Referral', 'affiliate-wp' ) ); ?>

	</form>

</div>
