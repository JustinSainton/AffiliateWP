<?php

class Affiliate_WP_Settings {

	private $options;

	public function __construct() {


		$this->options = get_option( 'affwp_settings', array() );

		add_action( 'admin_init', array( $this, 'register_settings' ) );

	}

	public function get( $key, $default ) {
		$value = ! empty( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
		return $value;
	}

	/**
	 * Add all settings sections and fields
	 *
	 * @since 1.0
	 * @return void
	*/
	function register_settings() {

		if ( false == get_option( 'affwp_settings' ) ) {
			add_option( 'affwp_settings' );
		}

		foreach( $this->get_registered_settings() as $tab => $settings ) {

			add_settings_section(
				'affwp_settings_' . $tab,
				__return_null(),
				'__return_false',
				'affwp_settings_' . $tab
			);

			foreach ( $settings as $key => $option ) {

				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'affwp_settings[' . $key . ']',
					$name,
					is_callable( array( $this, $option['type'] . '_callback' ) ) ? array( $this, $option['type'] . '_callback' ) : array( $this, 'missing_callback' ),
					'affwp_settings_' . $tab,
					'affwp_settings_' . $tab,
					array(
						'id'      => $key,
						'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'    => isset( $option['name'] ) ? $option['name'] : null,
						'section' => $tab,
						'size'    => isset( $option['size'] ) ? $option['size'] : null,
						'options' => isset( $option['options'] ) ? $option['options'] : '',
						'std'     => isset( $option['std'] ) ? $option['std'] : ''
					)
				);
			}

		}

		// Creates our settings in the options table
		register_setting( 'affwp_settings', 'affwp_settings', array( $this, 'sanitize_settings' ) );

	}

	/**
	 * Retrieve the array of plugin settings
	 *
	 * @since 1.0
	 * @return array
	*/
	function sanitize_settings( $input = array() ) {

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		parse_str( $_POST['_wp_http_referer'], $referrer );

		$settings = $this->get_registered_settings();
		$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

		$input = $input ? $input : array();
		$input = apply_filters( 'affwp_settings_' . $tab . '_sanitize', $input );

		// Loop through each setting being saved and pass it through a sanitization filter
		foreach ( $input as $key => $value ) {

			// Get the setting type (checkbox, select, etc)
			$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

			if ( $type ) {
				// Field type specific filter
				$input[$key] = apply_filters( 'affwp_settings_sanitize_' . $type, $value, $key );
			}

			// General filter
			$input[$key] = apply_filters( 'affwp_settings_sanitize', $value, $key );
		}

		add_settings_error( 'affwp-notices', '', __( 'Settings updated.', 'affiliate-wp' ), 'updated' );

		return $input;

	}

	/**
	 * Retrieve the array of plugin settings
	 *
	 * @since 1.0
	 * @return array
	*/
	function get_registered_settings() {

		$settings = array(
			/** General Settings */
			'general' => apply_filters( 'affwp_settings_general',
				array(
					'pages' => array(
						'name' => '<strong>' . __( 'Pages', 'affiliate-wp' ) . '</strong>',
						'desc' => '',
						'type' => 'header'
					),
					'affiliates_page' => array(
						'name' => __( 'Affiliate Area', 'affiliate-wp' ),
						'desc' => __( 'This is the page where affiliates will manage this affiliate account.', 'affiliate-wp' ),
						'type' => 'select',
						'options' => affwp_get_pages()
					),
					'currency_settings' => array(
						'name' => '<strong>' . __( 'Currency Settings', 'affiliate-wp' ) . '</strong>',
						'desc' => __( 'Configure the currency options', 'affiliate-wp' ),
						'type' => 'header'
					),
					'currency' => array(
						'name' => __( 'Currency', 'affiliate-wp' ),
						'desc' => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'affiliate-wp' ),
						'type' => 'select',
						'options' => affwp_get_currencies()
					),
					'currency_position' => array(
						'name' => __( 'Currency Position', 'affiliate-wp' ),
						'desc' => __( 'Choose the location of the currency sign.', 'affiliate-wp' ),
						'type' => 'select',
						'options' => array(
							'before' => __( 'Before - $10', 'affiliate-wp' ),
							'after' => __( 'After - 10$', 'affiliate-wp' )
						)
					),
					'thousands_separator' => array(
						'name' => __( 'Thousands Separator', 'affiliate-wp' ),
						'desc' => __( 'The symbol (usually , or .) to separate thousands', 'affiliate-wp' ),
						'type' => 'text',
						'size' => 'small',
						'std' => ','
					),
					'decimal_separator' => array(
						'name' => __( 'Decimal Separator', 'affiliate-wp' ),
						'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'affiliate-wp' ),
						'type' => 'text',
						'size' => 'small',
						'std' => '.'
					),
					'uninstall_on_delete' => array(
						'name' => __( 'Remove Data on Uninstall?', 'affiliate-wp' ),
						'desc' => __( 'Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'affiliate-wp' ),
						'type' => 'checkbox'
					)
				)
			),
			/** Misc Settings */
			'misc' => apply_filters('affwp_settings_misc',
				array(
					'not_real' => array(
						'name' => __( 'Not a Real Option', 'affiliate-wp' ),
						'desc' => __( 'Just a place holder for now.', 'affiliate-wp' ),
						'type' => 'checkbox'
					),
				)
			)
		);

		return $settings;
	}


	/**
	 * Header Callback
	 *
	 * Renders the header.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @return void
	 */
	function header_callback( $args ) {
		echo '<hr/>';
	}

	/**
	 * Checkbox Callback
	 *
	 * Renders checkboxes.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function checkbox_callback( $args ) {

		$checked = isset($this->options[$args['id']]) ? checked(1, $this->options[$args['id']], false) : '';
		$html = '<input type="checkbox" id="affwp_settings[' . $args['id'] . ']" name="affwp_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
		$html .= '<label for="affwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	/**
	 * Multicheck Callback
	 *
	 * Renders multiple checkboxes.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function multicheck_callback( $args ) {

		if ( ! empty( $args['options'] ) ) {
			foreach( $args['options'] as $key => $option ) {
				if( isset( $this->options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
				echo '<input name="affwp_settings[' . $args['id'] . '][' . $key . ']" id="affwp_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
				echo '<label for="affwp_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
			}
			echo '<p class="description">' . $args['desc'] . '</p>';
		}
	}

	/**
	 * Radio Callback
	 *
	 * Renders radio boxes.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function radio_callback( $args ) {

		foreach ( $args['options'] as $key => $option ) :
			$checked = false;

			if ( isset( $this->options[ $args['id'] ] ) && $this->options[ $args['id'] ] == $key )
				$checked = true;
			elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $this->options[ $args['id'] ] ) )
				$checked = true;

			echo '<input name="affwp_settings[' . $args['id'] . ']"" id="affwp_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
			echo '<label for="affwp_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		endforeach;

		echo '<p class="description">' . $args['desc'] . '</p>';
	}

	/**
	 * Text Callback
	 *
	 * Renders text fields.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function text_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="affwp_settings[' . $args['id'] . ']" name="affwp_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<label for="affwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	/**
	 * Number Callback
	 *
	 * Renders number fields.
	 *
	 * @since 1.9
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function number_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$max  = isset( $args['max'] ) ? $args['max'] : 999999;
		$min  = isset( $args['min'] ) ? $args['min'] : 0;
		$step = isset( $args['step'] ) ? $args['step'] : 1;

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="affwp_settings[' . $args['id'] . ']" name="affwp_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
		$html .= '<label for="affwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	/**
	 * Textarea Callback
	 *
	 * Renders textarea fields.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function textarea_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<textarea class="large-text" cols="50" rows="5" id="affwp_settings[' . $args['id'] . ']" name="affwp_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
		$html .= '<label for="affwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	/**
	 * Password Callback
	 *
	 * Renders password fields.
	 *
	 * @since 1.3
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function password_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="password" class="' . $size . '-text" id="affwp_settings[' . $args['id'] . ']" name="affwp_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
		$html .= '<label for="affwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	/**
	 * Missing Callback
	 *
	 * If a function is missing for settings callbacks alert the user.
	 *
	 * @since 1.3.1
	 * @param array $args Arguments passed by the setting
	 * @return void
	 */
	function missing_callback($args) {
		printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'affiliate-wp' ), $args['id'] );
	}

	/**
	 * Select Callback
	 *
	 * Renders select fields.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @return void
	 */
	function select_callback($args) {

		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$html = '<select id="affwp_settings[' . $args['id'] . ']" name="affwp_settings[' . $args['id'] . ']"/>';

		foreach ( $args['options'] as $option => $name ) :
			$selected = selected( $option, $value, false );
			$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
		endforeach;

		$html .= '</select>';
		$html .= '<label for="affwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}

	/**
	 * Rich Editor Callback
	 *
	 * Renders rich editor fields.
	 *
	 * @since 1.0
	 * @param array $args Arguments passed by the setting
	 * @global $this->options Array of all the EDD Options
	 * @global $wp_version WordPress Version
	 */
	function rich_editor_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) )
			$value = $this->options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		ob_start();
		wp_editor( stripslashes( $value ), 'affwp_settings[' . $args['id'] . ']', array( 'textarea_name' => 'affwp_settings[' . $args['id'] . ']' ) );
		$html = ob_get_clean();

		$html .= '<br/><label for="affwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}


}