<?php affiliate_wp()->register->print_errors(); ?>
<form id="affwp_register_form" class="affwp_form" action="" method="post">
	<?php do_action( 'affwp_affiliate_register_form_top' ); ?>
	<fieldset>
		<span><legend><?php _e( 'Register a New Affiliate Account', 'affiliate-wp' ); ?></legend></span>
		
		<?php do_action( 'affwp_register_fields_before' ); ?>
		
		<?php if ( ! is_user_logged_in() ) : ?>
			<p>
				<label for="affwp_user_name"><?php _e( 'Your Name', 'affiliate-wp' ); ?></label>
				<input name="affwp_user_name" id="affwp_user_name" class="required" type="text" title="<?php _e( 'Your Name', 'affiliate-wp' ); ?>" />
			</p>
			<p>
				<label for="affwp_user_login"><?php _e( 'Username', 'affiliate-wp' ); ?></label>
				<input name="affwp_user_login" id="affwp_user_login" class="required" type="text" title="<?php _e( 'Username', 'affiliate-wp' ); ?>" />
			</p>
			<p>
				<label for="affwp_user_email"><?php _e( 'Email', 'affiliate-wp' ); ?></label>
				<input name="affwp_user_email" id="affwp_user_email" class="required" type="email" title="<?php _e( 'Email Address', 'affiliate-wp' ); ?>" />
			</p>
			<p>
				<label for="affwp_user_pass"><?php _e( 'Password', 'affiliate-wp' ); ?></label>
				<input name="affwp_user_pass" id="affwp_user_pass" class="password required" type="password" />
			</p>
			<p>
				<label for="affwp_user_pass2"><?php _e( 'Confirm Password', 'affiliate-wp' ); ?></label>
				<input name="affwp_user_pass2" id="affwp_user_pass2" class="password required" type="password" />
			</p>
		<?php endif; ?>

		<p>
			<label for="affwp_tos">
				<input name="affwp_tos" id="affwp_tos" class="required" type="checkbox" />
				<?php printf( __( 'Agree to Our <a href="%s" target="_blank">Terms of Use</a>', 'affiliate-wp' ), get_permalink( affiliate_wp()->settings->get( 'terms_of_use' ) ) ); ?>
			</label>
			
		</p>
		<p>
			<input type="hidden" name="affwp_register_nonce" value="<?php echo wp_create_nonce( 'affwp-register-nonce' ); ?>" />
			<input type="hidden" name="affwp_action" value="affiliate_register" />
			<input id="affwp_register_submit" type="submit" class="affwp_submit" value="<?php _e( 'Register', 'affiliate-wp' ); ?>" />
		</p>
		
		<?php do_action( 'affwp_register_fields_after' ); ?>
	</fieldset>
	<?php do_action( 'affwp_affiliate_register_form_bottom' ); ?>
</form>