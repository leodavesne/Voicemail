<?php

class VO_Plugin_Settings {

	private $vo_setting;
	/*
	 * Construct me
	 */
	public function __construct() {
		$this->vo_setting = get_option( 'vo_setting', '' );

		// register the checkbox
		add_action('admin_init', array( $this, 'register_settings' ) );
	}

	/*
	 * Setup the settings
	 * Add a single checkbox setting for Active/Inactive and a text field
	 * just for the sake of our demo
	 */
	public function register_settings() {
		register_setting( 'vo_setting', 'vo_setting', array( $this, 'vo_validate_settings' ) );

		add_settings_section(
			'vo_settings_section',         // ID used to identify this section and with which to register options
			__( "Enable VO Templates", 'vobase' ),                  // Title to be displayed on the administration page
			array($this, 'vo_settings_callback'), // Callback used to render the description of the section
			'vo-plugin-base'                           // Page on which to add this section of options
		);

		add_settings_field(
			'vo_opt_in',                      // ID used to identify the field throughout the theme
			__( "Active: ", 'vobase' ),                           // The label to the left of the option interface element
			array( $this, 'vo_opt_in_callback' ),   // The name of the function responsible for rendering the option interface
			'vo-plugin-base',                          // The page on which this option will be displayed
			'vo_settings_section'         // The name of the section to which this field belongs
		);

		add_settings_field(
			'vo_sample_text',                      // ID used to identify the field throughout the theme
			__( "VO Sample: ", 'vobase' ),                           // The label to the left of the option interface element
			array( $this, 'vo_sample_text_callback' ),   // The name of the function responsible for rendering the option interface
			'vo-plugin-base',                          // The page on which this option will be displayed
			'vo_settings_section'         // The name of the section to which this field belongs
		);
	}

	public function vo_settings_callback() {
		echo _e( "Enable me", 'vobase' );
	}

	public function vo_opt_in_callback() {
		$enabled = false;
		$out = '';
		$val = false;

		// Check if checkbox is checked
		if(! empty( $this->vo_setting ) && isset ( $this->vo_setting['vo_opt_in'] ) ) {
			$val = true;
		}

		if($val) {
			$out = '<input type="checkbox" id="vo_opt_in" name="vo_setting[vo_opt_in]" CHECKED  />';
		} else {
			$out = '<input type="checkbox" id="vo_opt_in" name="vo_setting[vo_opt_in]" />';
		}

		echo $out;
	}

	public function vo_sample_text_callback() {
		$out = '';
		$val = '';

		// check if checkbox is checked
		if(! empty( $this->vo_setting ) && isset ( $this->vo_setting['vo_sample_text'] ) ) {
			$val = $this->vo_setting['vo_sample_text'];
		}

		$out = '<input type="text" id="vo_sample_text" name="vo_setting[vo_sample_text]" value="' . $val . '"  />';

		echo $out;
	}

	/*
	 * Helper Settings function if you need a setting from the outside.
	 * Keep in mind that in our demo the Settings class is initialized in a specific environment and if you
	 * want to make use of this function, you should initialize it earlier (before the base class)
	 * @return boolean is enabled
	 */
	public function is_enabled() {
		if(! empty( $this->vo_setting ) && isset ( $this->vo_setting['vo_opt_in'] ) ) {
			return true;
		}

		return false;
	}

	/*
	 * Validate Settings
	 * Filter the submitted data as per your request and return the array
	 * @param array $input
	 */
	public function vo_validate_settings( $input ) {
		return $input;
	}
}
