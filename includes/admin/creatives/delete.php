<?php
if ( ! empty( $_GET['creative_id'] ) && is_array( $_GET['creative_id'] ) ) {
	$to_delete = array_map( 'absint', $_GET['creative_id'] );
} else {
	$to_delete = ! empty( $_GET['creative_id'] ) ? array( absint( $_GET['creative_id'] ) ) : array();
}
?>
<div class="wrap">

	<h2><?php _e( 'Delete Creative', 'affiliate-wp' ); ?></h2>

	<form method="post" id="affwp_delete_creative">

		<?php do_action( 'affwp_delete_creative_top', $to_delete ); ?>

		<p><?php _e( 'Are you sure you want to delete this creative?', 'affiliate-wp' ); ?></p>

		<ul>
		<?php foreach ( $to_delete as $creative_id ) : ?>
			<li>
				<?php printf( _x( 'Creative ID #%d: %s', 'Creative ID, creative name', 'affiliate-wp' ), $creative_id, affiliate_wp()->creatives->get_column( 'name', $creative_id ) ); ?>
				<input type="hidden" name="affwp_creative_ids[]" value="<?php echo esc_attr( $creative_id ); ?>"/>
			</li> 
		<?php endforeach; ?>
		</ul>

		<?php do_action( 'affwp_delete_creative_bottom', $to_delete ); ?>

		<input type="hidden" name="affwp_action" value="delete_creatives" />
		<?php echo wp_nonce_field( 'affwp_delete_creatives_nonce', 'affwp_delete_creatives_nonce' ); ?>

		<?php submit_button( __( 'Delete Creative', 'affiliate-wp' ) ); ?>

	</form>

</div>