<?php affiliate_wp()->register->print_errors(); ?>

<form id="affwp-register-form" class="affwp-form" action="" method="post">
	<?php do_action( 'affwp_affiliate_register_form_top' ); ?>

	<fieldset>
		<legend><?php _e( 'Register a New Affiliate Account', 'affiliate-wp' ); ?></legend>

		<?php do_action( 'affwp_register_fields_before' ); ?>

		<?php if ( ! is_user_logged_in() ) : ?>

			<p>
				<label for="affwp-user-name"><?php _e( 'Your Name', 'affiliate-wp' ); ?></label>
				<input id="affwp-user-name" class="required" type="text" name="affwp_user_name" title="<?php esc_attr_e( 'Your Name', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp-user-login"><?php _e( 'Username', 'affiliate-wp' ); ?></label>
				<input id="affwp-user-login" class="required" type="text" name="affwp_user_login" title="<?php esc_attr_e( 'Username', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp-user-email"><?php _e( 'Account Email', 'affiliate-wp' ); ?></label>
				<input id="affwp-user-email" class="required" type="email" name="affwp_user_email" title="<?php esc_attr_e( 'Email Address', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp-payment-email"><?php _e( 'Payment Email (if different)', 'affiliate-wp' ); ?></label>
				<input id="affwp-payment-email" type="email" name="affwp_payment_email" title="<?php esc_attr_e( 'Payment Email Address', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp-user-pass"><?php _e( 'Password', 'affiliate-wp' ); ?></label>
				<input id="affwp-user-pass" class="password required" type="password" name="affwp_user_pass" />
			</p>

			<p>
				<label for="affwp-user-pass2"><?php _e( 'Confirm Password', 'affiliate-wp' ); ?></label>
				<input id="affwp-user-pass2" class="password required" type="password" name="affwp_user_pass2" />
			</p>

		<?php endif; ?>

		<?php do_action( 'affwp_register_fields_before_tos' ); ?>

		<?php $terms_of_use =  affiliate_wp()->settings->get( 'terms_of_use' ); ?>
		<?php if( !empty( $terms_of_use ) ): ?>
			<p>
				<label class="affwp-tos" for="affwp-tos">
					<input id="affwp-tos" class="required" type="checkbox" name="affwp_tos" />
					<?php printf( __( 'Agree to our <a href="%s" target="_blank">Terms of Use</a>', 'affiliate-wp' ), esc_url( get_permalink( affiliate_wp()->settings->get( 'terms_of_use' ) ) ) ); ?>
				</label>
			</p>
		<?php endif;?>

		<?php do_action( 'affwp_register_fields_before_submit' ); ?>

		<p>
			<input type="hidden" name="affwp_honeypot" value="" />
			<input type="hidden" name="affwp_register_nonce" value="<?php echo wp_create_nonce( 'affwp-register-nonce' ); ?>" />
			<input type="hidden" name="affwp_action" value="affiliate_register" />
			<input class="button" type="submit" value="<?php esc_attr_e( 'Register', 'affiliate-wp' ); ?>" />
		</p>

		<?php do_action( 'affwp_register_fields_after' ); ?>
	</fieldset>

	<?php do_action( 'affwp_affiliate_register_form_bottom' ); ?>
</form>
