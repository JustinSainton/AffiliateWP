<?php
$affiliate = affwp_get_affiliate( absint( $_GET['affiliate_id'] ) );
$user_info = get_userdata( $affiliate->user_id );
$rate_type = ! empty( $affiliate->rate_type ) ? $affiliate->rate_type : '';
$rate      = ! empty( $affiliate->rate ) ? $affiliate->rate : '';
$email     = ! empty( $affiliate->payment_email ) ? $affiliate->payment_email : '';
?>
<div class="wrap">

	<h2><?php _e( 'Edit Affiliate', 'affiliate-wp' ); ?></h2>

	<form method="post" id="affwp_edit_affiliate">

		<?php do_action( 'affwp_edit_affiliate_top', $affiliate ); ?>

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="affiliate_id"><?php _e( 'Affiliate ID', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input class="small-text" type="text" name="affiliate_id" id="affiliate_id" value="<?php echo esc_attr( $affiliate->affiliate_id ); ?>" disabled="1" />
					<p class="description"><?php _e( 'The affiliate\'s ID. This cannot be changed.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="user_id"><?php _e( 'User ID', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input class="small-text" type="text" name="user_id" id="user_id" value="<?php echo esc_attr( $affiliate->user_id ); ?>" <?php if( ! empty( $affiliate->user_id ) ) { echo 'disabled="1"'; } ?> />
					<p class="description"><?php _e( 'The affiliate\'s user ID. This cannot be changed.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="rate_type"><?php _e( 'Referral Rate Type', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<select name="rate_type" id="rate_type">
						<option value=""><?php _e( 'Site Default', 'affiliate-wp' ); ?></option>
						<?php foreach( affwp_get_affiliate_rate_types() as $key => $type ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $rate_type, $key ); ?>><?php echo esc_html( $type ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'The affiliate\'s referral rate type.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="rate"><?php _e( 'Referral Rate', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input class="small-text" type="text" name="rate" id="rate" value="<?php echo esc_attr( $rate ); ?>"/>
					<p class="description"><?php _e( 'The affiliate\'s referral rate, such as 20 for 20%. If left blank, the site default will be used.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="account-email"><?php _e( 'Account Email', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input class="regular-text" type="text" name="account_email" id="account-email" value="<?php echo $user_info->user_email; ?>" />
					<p class="description"><?php _e( 'The affiliate\'s account email. Updating this will change the email address shown on the user\'s profile page.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="payment_email"><?php _e( 'Payment Email', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input class="regular-text" type="text" name="payment_email" id="payment_email" value="<?php echo esc_attr( $email ); ?>"/>
					<p class="description"><?php _e( 'Affiliate\'s payment email for systems such as PayPal, Moneybookers, or others. Leave blank to use the affiliate\'s user email.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<?php do_action( 'affwp_edit_affiliate_end', $affiliate ); ?>

		</table>

		<?php do_action( 'affwp_edit_affiliate_bottom', $affiliate ); ?>

		<input type="hidden" name="affwp_action" value="update_affiliate" />

		<?php submit_button( __( 'Update Affiliate', 'affiliate-wp' ) ); ?>

	</form>

</div>
