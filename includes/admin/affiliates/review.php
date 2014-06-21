<?php
$affiliate 			= affwp_get_affiliate( absint( $_GET['affiliate_id'] ) );
$affiliate_id 		= $affiliate->affiliate_id;
$user_info 			= get_userdata( $affiliate_id );
$user_url			= $user_info->user_url;
$name 				= affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id );
$promotion_method 	= get_user_meta( $affiliate_id, 'affwp_promotion_method', true );
?>
<div class="wrap">

	<h2><?php _e( 'Review Affiliate', 'affiliate-wp' ); ?> <a href="<?php echo admin_url( 'admin.php?page=affiliate-wp-affiliates' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'affiliate-wp' ); ?></a></h2>

	<form method="post" id="affwp_review_affiliate">

		<?php do_action( 'affwp_review_affiliate_top', $affiliate ); ?>

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="affiliate_id"><?php _e( 'Name', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<?php echo esc_attr( $name ); ?>
				</td>

			</tr>

			<tr class="form-row form-required">

				<th scope="row">
					<label for="user_id"><?php _e( 'Website URL', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<a href="<?php echo esc_url( $user_url ); ?>" title="<?php _e( 'Affiliate\'s Website URL', 'affiliate-wp' ); ?>" target="blank"><?php echo esc_url( $user_url ); ?></a>
				</td>

			</tr>

			<?php if ( $promotion_method ) : ?>
				<tr class="form-row form-required">

					<th scope="row">
						<label for="rate_type"><?php _e( 'Promotion Method', 'affiliate-wp' ); ?></label>
					</th>

					<td>
						<?php echo esc_html( $promotion_method ); ?>
					</td>

				</tr>
			<?php endif; ?>
		</table>

		<?php do_action( 'affwp_review_affiliate_bottom', $affiliate ); ?>

		<a href="<?php echo add_query_arg( array( 'affwp_notice' => 'affiliate_accepted', 'action' => 'accept', 'affiliate_id' => $affiliate_id ) ); ?>" class="button button-primary"><?php _e( 'Accept Affiliate', 'affiliate-wp' ); ?></a>
		<a href="<?php echo add_query_arg( array( 'affwp_notice' => 'affiliate_rejected', 'action' => 'reject', 'affiliate_id' => $affiliate_id ) ); ?>" class="button button-secondary"><?php _e( 'Reject Affiliate', 'affiliate-wp' ); ?></a>
	</form>

</div>