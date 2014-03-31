<?php affiliate_wp()->login->print_errors(); ?>
<form id="affwp_login_form" class="affwp_form" action="" method="post">
	<?php do_action( 'affwp_affiliate_login_form_top' ); ?>
	<fieldset>
		<span><legend><?php _e( 'Log into Your Account', 'affiliate-wp' ); ?></legend></span>
		<?php do_action( 'affwp_login_fields_before' ); ?>
		<p>
			<label for="affwp_user_login"><?php _e( 'Username', 'affiliate-wp' ); ?></label>
			<input name="affwp_user_login" id="affwp_user_login" class="required" type="text" title="<?php _e( 'Username', 'affiliate-wp' ); ?>"/>
		</p>
		<p>
			<label for="affwp_user_pass"><?php _e( 'Password', 'affiliate-wp' ); ?></label>
			<input name="affwp_user_pass" id="affwp_user_pass" class="password required" type="password"/>
		</p>
		<p>
			<label for="affwp_user_remember"><?php _e( 'Remember Me', 'affiliate-wp' ); ?></label>
			<input name="affwp_user_remember" id="affwp_user_remember" type="checkbox" value="1"/>
		</p>
		<p>
			<input type="hidden" name="affwp_login_nonce" value="<?php echo wp_create_nonce( 'affwp-login-nonce' ); ?>"/>
			<input type="hidden" name="affwp_action" value="user_login"/>
			<input id="affwp_login_submit" type="submit" class="affwp_submit" value="<?php _e( 'Login', 'affiliate-wp' ); ?>"/>
		</p>
		<p class="affwp-lost-password">
			<a href="<?php echo wp_lostpassword_url(); ?>" title="<?php _e( 'Lost Password', 'affiliate-wp' ); ?>">
				<?php _e( 'Lost Password?', 'affiliate-wp' ); ?>
			</a>
		</p>
		<?php do_action( 'affwp_login_fields_after' ); ?>
	</fieldset>
	<?php do_action( 'affwp_affiliate_login_form_bottom' ); ?>
</form>