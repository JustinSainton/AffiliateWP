<?php
/**
 * Creatives
 *
 * This class handles the asset management of affiliate banners/HTML/links etc
 *
 * @package     AffiliateWP
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.4
 */

class Affiliate_WP_Creatives {

	/**
	 * The [affiliate_creative] shortcode
	 *
	 * @since  1.1.4
	 * @return string
	 */
	public function affiliate_creative( $args = array() ) {
		
		$id = isset( $args['id'] ) ? $args['id'] : '';

		$defaults = array(
			'id'            => '',
			'link'          => '',
			'text'          => '',
			'image_id'      => '',
			'image_link'	=> '',
			'preview'       => 'yes',
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		// if ID is set it will use the parameters set with the creative
		// also makes sure the shortcode parameters cannot override the already set up creative
		if ( $id ) {

			// don't show an inactive creative
			if ( 'inactive' == affiliate_wp()->creatives->get_column( 'status', $id ) )
				return;

			$text       = affiliate_wp()->creatives->get_column( 'text', $id );
			$link       = affiliate_wp()->creatives->get_column( 'url', $id );
			$image_link = affiliate_wp()->creatives->get_column( 'image', $id );
			$image_id   = '';
		} 
		// user is manually creating a banner using the shortcode parameters and does not have one setup in creatives
		else {
			// if no link is specified, use the current site URL
			$link = $link ? $link : get_site_url();

			// if no text is specified, use the site name
			$text = $text ? $text : get_bloginfo( 'name' );
		}

		// get the image attributes from image_id
		$image_attributes = wp_get_attachment_image_src( $image_id, 'full' );

		// load the HTML required for the creative
		return $this->html( $id, $link, $image_link, $image_attributes, $preview, $text );

	}

	/**
	 * The [affiliate_creatives] shortcode
	 *
	 * @since  1.1.4
	 * @return string
	 */
	public function affiliate_creatives( $args = array() ) {
		
		$defaults = array(
			'preview' => 'yes',
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );

		ob_start();

		$creatives = affiliate_wp()->creatives->get_creatives();

		if ( $creatives ) {
			foreach ( $creatives as $creative ) {

				// don't show an inactive creative
				if ( 'inactive' == affiliate_wp()->creatives->get_column( 'status', $creative->creative_id ) )
					continue;

				$url   = $creative->url;
				$image = $creative->image;
				$text  = $creative->text;

				echo $this->html( $creative->creative_id, $url, $image, $image_attributes = '', $preview, $text );	
			}
		}

		return ob_get_clean();
	}

	/**
	 * Returns the referral link to append to the end of a URL
	 *
	 * @since  1.1.4
	 * @return string Affiliate's referral link
	 * @todo  Better handling of referral link once we introduce pretty affiliate URLs
	 */
	public function ref_link() {
		return '?' . affiliate_wp()->tracking->get_referral_var() . '=' .  affwp_get_affiliate_id();
	}

	/**
	 * Shortcode HTML
	 *
	 * @since  1.1.4
	 * @param  $image the image URL. Either the URL from the image column in DB or external URL of image.
	 * @return string
	 */
	public function html( $id = '', $url, $image_link, $image_attributes, $preview, $text ) {
		
		$id_class = $id ? ' creative-' . $id : '';
		ob_start();
	?>
		<div class="affwp-creative<?php echo $id_class; ?>">

			<?php if ( $preview != 'no' ) : ?>

				<?php 
				// Image preview - using ID of image from media library
				if ( $image_attributes ) : ?> 
				<p>
					<a href="<?php echo esc_url( trailingslashit( $url ) ) . $this->ref_link(); ?>" title="<?php echo esc_attr( $text ); ?>">
						<img src="<?php echo $image_attributes[0]; ?>" width="<?php echo $image_attributes[1]; ?>" height="<?php echo $image_attributes[2]; ?>" alt="<?php echo esc_attr( $text ); ?>">
					</a>
				</p>
				
				<?php
				// Image preview - External image URL or picked from media library
				elseif ( $image_link ) :
					$image      = esc_url( $image_link );
					$image_size = getimagesize( $image ); // get the image's dimensions
				?>
					<p>
						<a href="<?php echo esc_url( trailingslashit( $url ) ) . $this->ref_link(); ?>" title="<?php echo esc_attr( $text ); ?>">
							<img src="<?php echo $image; ?>" <?php echo $image_size[3]; ?> alt="<?php echo esc_attr( $text ); ?>">
						</a>
					</p>

				<?php else : // text link preview ?>
					<p>
						<a href="<?php echo esc_url( trailingslashit( $url ) ); ?>" title="<?php echo esc_attr( $text ); ?>"><?php echo esc_attr( $text ); ?></a>
					</p>
				<?php endif; ?>

			<?php endif; ?>

			<?php
				echo apply_filters( 'affwp_affiliate_creative_text', '<p>' . __( 'Copy and paste the following:', 'affiliate-wp' ) . '</p>' );

				// Image - media library
				if ( $image_attributes ) {
					$image_or_text = '<img src="' . $image_attributes[0] . '" alt="' . esc_attr( $text ) .'" />';
				}
				// Image - External URL
				elseif ( $image_link ) {
					$image_or_text = '<img src="' . $image_link . '" alt="' . esc_attr( $text ) .'" />';
				}
				// Show site name when no image
				else {
					$image_or_text = esc_attr( $text );
				}
			?>
			
			<?php 
				$creative = '<a href="' . esc_url( trailingslashit( $url ) ) . $this->ref_link() .'" title="' . esc_attr( $text ) . '">' . $image_or_text . '</a>';
				echo '<p>' . esc_html( $creative ) . '</p>'; 
			?>
			
		</div>

		<?php 
		$html = ob_get_clean();
		return apply_filters( 'affwp_affiliate_creative_html', $html, $url, $image_link, $image_attributes, $preview, $text );
	}
	
}