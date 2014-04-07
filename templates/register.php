<?php affiliate_wp()->register->print_errors(); ?>

<form id="affwp_register_form" class="affwp_form" action="" method="post">
	<?php do_action( 'affwp_affiliate_register_form_top' ); ?>

	<fieldset>
		<legend><?php _e( 'Register a New Affiliate Account', 'affiliate-wp' ); ?></legend>

		<?php do_action( 'affwp_register_fields_before' ); ?>

		<?php if ( ! is_user_logged_in() ) : ?>

			<p>
				<label for="affwp_user_name"><?php _e( 'Your Name', 'affiliate-wp' ); ?></label>
				<input id="affwp_user_name" class="required" type="text" name="affwp_user_name" title="<?php esc_attr_e( 'Your Name', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp_user_login"><?php _e( 'Username', 'affiliate-wp' ); ?></label>
				<input id="affwp_user_login" class="required" type="text" name="affwp_user_login" title="<?php esc_attr_e( 'Username', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp_user_email"><?php _e( 'Account Email', 'affiliate-wp' ); ?></label>
				<input id="affwp_user_email" class="required" type="email" name="affwp_user_email" title="<?php esc_attr_e( 'Email Address', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp_payment_email"><?php _e( 'Payment Email (if different)', 'affiliate-wp' ); ?></label>
				<input id="affwp_payment_email" type="email" name="affwp_payment_email" title="<?php esc_attr_e( 'Payment Email Address', 'affiliate-wp' ); ?>" />
			</p>

			<p>
				<label for="affwp_user_pass"><?php _e( 'Password', 'affiliate-wp' ); ?></label>
				<input id="affwp_user_pass" class="password required" type="password" name="affwp_user_pass" />
			</p>

			<p>
				<label for="affwp_user_pass2"><?php _e( 'Confirm Password', 'affiliate-wp' ); ?></label>
				<input id="affwp_user_pass2" class="password required" type="password" name="affwp_user_pass2" />
			</p>

		<?php endif; ?>

		<p>
			<label for="affwp_tos">
				<input id="affwp_tos" class="required" type="checkbox" name="affwp_tos" />
				<?php printf( __( 'Agree to our <a href="%s" target="_blank">Terms of Use</a>', 'affiliate-wp' ), esc_url( get_permalink( affiliate_wp()->settings->get( 'terms_of_use' ) ) ) ); ?>
			</label>
		</p>

		<p>
			<input type="hidden" name="affwp_register_nonce" value="<?php echo wp_create_nonce( 'affwp-register-nonce' ); ?>" />
			<input type="hidden" name="affwp_action" value="affiliate_register" />
			<input id="affwp_register_submit" class="button affwp_submit" type="submit" value="<?php esc_attr_e( 'Register', 'affiliate-wp' ); ?>" />
		</p>

		<?php do_action( 'affwp_register_fields_after' ); ?>
	</fieldset>

	<?php do_action( 'affwp_affiliate_register_form_bottom' ); ?>
</form>
