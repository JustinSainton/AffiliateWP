<?php
/**
 * Creatives
 *
 * This class handles the asset management of affiliate banners/HTML/links etc
 *
 * @package     AffiliateWP
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

class Affiliate_WP_Creatives {

	/**
	 * The [affiliate_creative] shortcode
	 *
	 * @since  1.2
	 * @return string
	 */
	public function affiliate_creative( $args = array() ) {
		
		$id = isset( $args['id'] ) ? $args['id'] : '';

		$defaults = array(
			'id'            => '',
			'description'   => affiliate_wp()->creatives->get_column( 'description', $id ),
			'link'          => affiliate_wp()->creatives->get_column( 'url', $id ),
			'text'          => affiliate_wp()->creatives->get_column( 'text', $id ),
			'image_id'      => '',
			'image_link'	=> affiliate_wp()->creatives->get_column( 'image', $id ),
			'preview'       => 'yes'
		);

		$args = wp_parse_args( $args, $defaults );

		// if no link is specified, use the current site URL
		$link = ! empty( $args['link'] ) ? $args['link'] : get_site_url();

		// if no text is specified, use the site name
		$text = ! empty( $args['text'] ) ? $args['text'] : get_bloginfo( 'name' );

		// get the image attributes from image_id
		$attributes = ! empty( $args['image_id'] ) ? wp_get_attachment_image_src( $args['image_id'], 'full' ) : '';

		// description for creative
		$desc = ! empty( $args['description'] ) ? $args['description'] : '';

		// load the HTML required for the creative
		return $this->html( $id, $args['link'], $args['image_link'], $attributes, $args['preview'], $args['text'], $desc );

	}

	/**
	 * The [affiliate_creatives] shortcode
	 *
	 * @since  1.2
	 * @return string
	 */
	public function affiliate_creatives( $args = array() ) {
		
		$defaults = array(
			'preview' => 'yes',
			'status'  => 'active'
		);

		$args = wp_parse_args( $args, $defaults );

		ob_start();

		$creatives = affiliate_wp()->creatives->get_creatives( $args );

		if ( $creatives ) {
			foreach ( $creatives as $creative ) {

				$url   = $creative->url;
				$image = $creative->image;
				$text  = $creative->text;
				$desc  = ! empty( $creative->description ) ? $creative->description : '';

				echo $this->html( $creative->creative_id, $url, $image, '', $args['preview'], $text, $desc );	
			}
		}

		return ob_get_clean();
	}

	/**
	 * Returns the referral link to append to the end of a URL
	 *
	 * @since  1.2
	 * @return string Affiliate's referral link
	 */
	public function ref_link( $url = '' ) {
		return affwp_get_affiliate_referral_url( array( 'base_url' => $url ) );
	}

	/**
	 * Shortcode HTML
	 *
	 * @since  1.2
	 * @param  $image the image URL. Either the URL from the image column in DB or external URL of image.
	 * @return string
	 */
	public function html( $id = '', $url, $image_link, $image_attributes, $preview, $text, $desc = '' ) {
		
		$id_class = $id ? ' creative-' . $id : '';
		ob_start();
	?>
		<div class="affwp-creative<?php echo esc_attr( $id_class ); ?>">

			<?php if ( ! empty( $desc ) ) : ?>
				<p class="affwp-creative-desc"><?php echo $desc; ?></p>
			<?php endif; ?>

			<?php if ( $preview != 'no' ) : ?>

				<?php 
				// Image preview - using ID of image from media library
				if ( $image_attributes ) : ?> 
				<p>
					<a href="<?php echo esc_url( $this->ref_link( $url ) ); ?>" title="<?php echo esc_attr( $text ); ?>">
						<img src="<?php echo esc_attr( $image_attributes[0] ); ?>" width="<?php echo esc_attr( $image_attributes[1] ); ?>" height="<?php echo esc_attr( $image_attributes[2] ); ?>" alt="<?php echo esc_attr( $text ); ?>">
					</a>
				</p>
				
				<?php
				// Image preview - External image URL or picked from media library
				elseif ( $image_link ) :
					$image      = $image_link;
					$image_size = getimagesize( $image ); // get the image's dimensions
				?>
					<p>
						<a href="<?php echo esc_url( $this->ref_link( $url ) ); ?>" title="<?php echo esc_attr( $text ); ?>">
							<img src="<?php echo esc_attr( $image ); ?>" <?php echo $image_size[3]; ?> alt="<?php echo esc_attr( $text ); ?>">
						</a>
					</p>

				<?php else : // text link preview ?>
					<p>
						<a href="<?php echo esc_url( $this->ref_link( $url ) ); ?>" title="<?php echo esc_attr( $text ); ?>"><?php echo esc_attr( $text ); ?></a>
					</p>
				<?php endif; ?>

			<?php endif; ?>

			<?php
				echo apply_filters( 'affwp_affiliate_creative_text', '<p>' . __( 'Copy and paste the following:', 'affiliate-wp' ) . '</p>' );

				// Image - media library
				if ( $image_attributes ) {
					$image_or_text = '<img src="' . esc_attr( $image_attributes[0] ) . '" alt="' . esc_attr( $text ) .'" />';
				}
				// Image - External URL
				elseif ( $image_link ) {
					$image_or_text = '<img src="' . esc_attr( $image_link ) . '" alt="' . esc_attr( $text ) .'" />';
				}
				// Show site name when no image
				else {
					$image_or_text = esc_attr( $text );
				}
			?>
			
			<?php 
				$creative = '<a href="' . esc_url( $this->ref_link( $url ) ) .'" title="' . esc_attr( $text ) . '">' . $image_or_text . '</a>';
				echo '<pre><code>' . esc_html( $creative ) . '</code></pre>'; 
			?>
			
		</div>

		<?php 
		$html = ob_get_clean();
		return apply_filters( 'affwp_affiliate_creative_html', $html, $url, $image_link, $image_attributes, $preview, $text );
	}
	
}