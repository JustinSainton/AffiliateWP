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
	 * @todo  Better handling of referral link once we introduce pretty affiliate URLs
	 */
	function link_html( $attachment_id = null, $image_url = '', $url = '', $preview = 'yes', $text = '' ) {
		$image_attributes 	= wp_get_attachment_image_src( $attachment_id, 'full' );
		$url 				= $url ? $url : get_site_url();

		ob_start();
		?>
		<div class="affwp-link">
			<?php 
			// Image preview - media library
			if ( $image_attributes && $preview != 'no' ) : ?> 
			<p><img src="<?php echo $image_attributes[0]; ?>" width="<?php echo $image_attributes[1]; ?>" height="<?php echo $image_attributes[2]; ?>" alt="<?php echo esc_attr( $text ); ?>"></p>
			
			<?php
			// Image preview - External URL
			elseif ( $image_url ) :
				$image_url = esc_url( $image_url );
				$image_size = getimagesize( $image_url ); // get the image's dimensions
			?>
				<p><img src="<?php echo $image_url; ?>" <?php echo $image_size[3]; ?> alt="<?php echo esc_attr( $text ); ?>"></p>
			<?php endif; ?>

			<?php
				echo apply_filters( 'affwp_affiliate_link_text', '<p>' . __( 'Copy and paste the following HTML:', 'affiliate-wp' ) . '</p>' );

				// Image - media library
				if ( $image_attributes ) {
					$image_link = '<img src="' . $image_attributes[0] . '" alt="' . esc_attr( $text ) .'" />';
				}
				// Image - External URL
				elseif ( $image_url ) {
					$image_link = '<img src="' . $image_url . '" alt="' . esc_attr( $text ) .'" />';
				}
				// Show site name when no image
				else {
					$image_link = get_bloginfo( 'name' );
				}
			?>
			
			<?php 
				$text = '<a href="' . esc_url( trailingslashit( $url ) ) . '?' . affiliate_wp()->tracking->get_referral_var() . '=' . affwp_get_affiliate_id() .'" title="' . esc_attr( $text ) . '">' . $image_link . '</a>';
				echo '<p>' . esc_html( $text ) . '</p>'; 
			?>
			
		</div>

	<?php
		$html = ob_get_clean();
		return apply_filters( 'affwp_affiliate_link_html', $html, $url, $image_link, $image_url, $image_attributes, $text, $url, $preview );
	}
}