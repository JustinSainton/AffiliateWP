<?php
/**
 * Assets
 *
 * This class handles the asset management of affiliate banners/HTML/links etc
 *
 * @package     AffiliateWP
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.3
 */

class Affiliate_WP_Assets {

	/**
	 * HTML for [affiliate_link] shortcode
	 *
	 * @since  1.1.3
	 * @return string
	 */
	function link_html( $attachment_id = null, $url = '', $preview = 'yes', $text = '' ) {

		$image_attributes 	= wp_get_attachment_image_src( $attachment_id, 'full' );
		$url 				= $url ? $url : get_site_url();

		ob_start();
		?>
		<p class="affwp-link">
			<?php if ( $image_attributes && $preview != 'no' ) : ?> 
			<img src="<?php echo $image_attributes[0]; ?>" width="<?php echo $image_attributes[1]; ?>" height="<?php echo $image_attributes[2]; ?>">
			<?php endif; ?>

			<?php
				$image_link = $image_attributes ? '<img src="' . $image_attributes[0] . '" alt="' . esc_attr( $text ) .'" />' : get_bloginfo( 'name' );
				echo apply_filters( 'affwp_affiliate_link_text', '<p>' . __( 'Copy and paste the following HTML:', 'affiliate-wp' ) . '</p>' );
			?>
			
			<textarea rows="3"><a title="<?php echo esc_attr( $text ); ?>" href="<?php echo esc_url( trailingslashit( $url ) ) . '?' . affiliate_wp()->tracking->get_referral_var() . '=' . affwp_get_affiliate_id(); ?>"><?php echo $image_link; ?></a></textarea>
		</p>

	<?php
		$html = ob_get_clean();
		return apply_filters( 'affwp_affiliate_link_html', $html, $url, $image_link, $image_attributes, $text, $url, $preview );
	}
}