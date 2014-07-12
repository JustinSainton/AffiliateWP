<?php
if( ! empty( $_GET['affiliate_id'] ) && is_array( $_GET['affiliate_id'] ) ) {

	$to_delete = array_map( 'absint', $_GET['affiliate_id'] );

} else {

	$to_delete = ! empty( $_GET['affiliate_id'] ) ? array( absint( $_GET['affiliate_id'] ) ) : array();

}
?>
<div class="wrap">

	<h2><?php _e( 'Delete Affiliates', 'affiliate-wp' ); ?></h2>

	<form method="post" id="affwp_delete_affiliate">

		<?php do_action( 'affwp_delete_affiliate_top', $to_delete ); ?>

		<p><?php _e( 'You have specified these affiliates for deletion:' , 'affiliate-wp' ); ?></p>

		<ul>
		<?php foreach( $to_delete as $affiliate_id ) : ?>

			<li>
				<?php printf( _x( 'ID #%d: %s', 'Affiliate ID, affiliate name', 'affiliate-wp' ), $affiliate_id, affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id ) ); ?>
				<input type="hidden" name="affwp_affiliate_ids[]" value="<?php echo esc_attr( $affiliate_id ); ?>"/>
			</li> 

		<?php endforeach; ?>
		</ul>

		<p><?php _e( 'Deleting these affiliates will also delete their referral and visit data.' , 'affiliate-wp' ); ?></p>

		<?php if( current_user_can( 'delete_users' ) ) :?>
		<p>
			<label for="affwp_delete_users_too">
				<input type="checkbox" name="affwp_delete_users_too" id="affwp_delete_users_too" value="1" />
				<?php _e( 'Delete affiliate\'s user accounts as well?', 'affiliate-wp' ); ?>
			</label>
		</p>
		<?php endif; ?>

		<?php do_action( 'affwp_delete_affiliate_bottom', $to_delete ); ?>

		<input type="hidden" name="affwp_action" value="delete_affiliates" />
		<?php echo wp_nonce_field( 'affwp_delete_affiliates_nonce', 'affwp_delete_affiliates_nonce' ); ?>

		<?php submit_button( __( 'Delete Affiliates', 'affiliate-wp' ) ); ?>

	</form>

</div>
