<?php

/*
 * Plugin Name: Voicemail
 * Description: A WordPress plugin to let your audience record messages and ask questions directly on your website
 * Plugin URI: http://leodavesne.net/voicemail-wordpress-plugin
 * Author: leodavesne
 * Author URI: http://leodavesne.net
 * Version: 0.1
 * Text Domain: voicemail-plugin
 * License: GPL2
 */

/*
 * Constants
 */

define('VOI_VERSION', '0.1');
define('VOI_PATH', dirname(__FILE__));
define('VOI_PATH_INCLUDES', dirname(__FILE__) . '/inc');
define('VOI_FOLDER', basename(VOI_PATH));
define('VOI_URL', plugins_url() . '/' . VOI_FOLDER);
define('VOI_URL_INCLUDES', VOI_URL . '/inc');

/*
 * The plugin base class
 */
class VO_Plugin_Base {

	/*
	 * Assign everything as a call from within the constructor
	 */
	public function __construct() {
		// add script and style calls the WP way
		// it's a bit confusing as styles are called with a scripts hook
		// @blamenacin - http://make.wordpress.org/core/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/
		add_action( 'wp_enqueue_scripts', array( $this, 'vo_add_JS' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'vo_add_CSS' ) );

		// add scripts and styles only available in admin
		add_action( 'admin_enqueue_scripts', array( $this, 'vo_add_admin_JS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'vo_add_admin_CSS' ) );

		// register admin pages for the plugin
		add_action( 'admin_menu', array( $this, 'vo_admin_pages_callback' ) );

		// register meta boxes for Pages (could be replicated for posts and custom post types)
		add_action( 'add_meta_boxes', array( $this, 'vo_meta_boxes_callback' ) );

		// register save_post hooks for saving the custom fields
		add_action( 'save_post', array( $this, 'vo_save_sample_field' ) );

		// Register custom post types and taxonomies
		add_action( 'init', array( $this, 'vo_custom_post_types_callback' ), 5 );
		add_action( 'init', array( $this, 'vo_custom_taxonomies_callback' ), 6 );

		// Register activation and deactivation hooks
		register_activation_hook( __FILE__, 'vo_on_activate_callback' );
		register_deactivation_hook( __FILE__, 'vo_on_deactivate_callback' );

		// Translation-ready
		add_action( 'plugins_loaded', array( $this, 'vo_add_textdomain' ) );

		// Add earlier execution as it needs to occur before admin page display
		add_action( 'admin_init', array( $this, 'vo_register_settings' ), 5 );

		add_action( 'init', array( $this, 'vo_sample_shortcode_voicemail' ) );

		// Add a sample widget
		add_action( 'widgets_init', array( $this, 'vo_sample_widget' ) );

		/*
		 * TODO:
		 * 		template_redirect
		 */

		// Add actions for storing value and fetching URL
		// use the wp_ajax_nopriv_ hook for non-logged users (handle guest actions)
 		add_action( 'wp_ajax_store_ajax_value', array( $this, 'store_ajax_value' ) );
 		add_action( 'wp_ajax_fetch_ajax_url_http', array( $this, 'fetch_ajax_url_http' ) );

		add_action( 'wp_ajax_my_action', 'my_action_callback' );
		add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );
	}

	public function my_action_callback() {
	    // Handle Geo Location Data
	    wp_die(); // this is required to terminate immediately and return a proper response
	}

	/*
	 * Adding JavaScript scripts
	 * Loading existing scripts from wp-includes or adding custom ones
	 */
	public function vo_add_JS() {
		$data = array(
			'ajaxurl' => plugins_url('getAudio.php', __FILE__ ),
			'firstGifUrl' => plugins_url('/gif/RecordingInProgress01.gif', __FILE__ ),
			'imgBaseUrl' => plugins_url('/img/', __FILE__ )
		);

		wp_enqueue_script("recorder", plugins_url("/projects/Recorderjs/dist/recorder.js", __FILE__ ));
	    wp_localize_script("recorder", "variables", $data);

		wp_enqueue_script("voicemail", plugins_url("/js/voicemail.js", __FILE__ ));
	    wp_localize_script("voicemail", "variables", $data);
	}

	/*
	 * Adding JavaScript scripts for the admin pages only
	 * Loading existing scripts from wp-includes or adding custom ones
	 */
	public function vo_add_admin_JS( $hook ) {
		wp_enqueue_script( 'jquery' );
		wp_register_script( 'samplescript-admin', plugins_url( '/js/samplescript-admin.js' , __FILE__ ), array('jquery'), '1.0', true );
		wp_enqueue_script( 'samplescript-admin' );
	}

	/*
	 * Add CSS styles
	 */
	public function vo_add_CSS() {
		wp_register_style( 'samplestyle', plugins_url( '/css/samplestyle.css', __FILE__ ), array(), '1.0', 'screen' );
		wp_enqueue_style( 'samplestyle' );
	}

	/*
	 * Add admin CSS styles - available only on admin
	 */
	public function vo_add_admin_CSS( $hook ) {
		wp_register_style( 'samplestyle-admin', plugins_url( '/css/samplestyle-admin.css', __FILE__ ), array(), '1.0', 'screen' );
		wp_enqueue_style( 'samplestyle-admin' );

		if( 'toplevel_page_vo-plugin-base' === $hook ) {
			wp_register_style('vo_help_page',  plugins_url( '/help-page.css', __FILE__ ) );
			wp_enqueue_style('vo_help_page');
		}
	}

	/*
	 * Callback for registering pages
	 * This demo registers a custom page for the plugin and a subpage
	 */
	public function vo_admin_pages_callback() {
		add_menu_page(__( "Plugin Base Admin", 'vobase' ), __( "Plugin Base Admin", 'vobase' ), 'edit_themes', 'vo-plugin-base', array( $this, 'vo_plugin_base' ) );
		add_submenu_page( 'vo-plugin-base', __( "Base Subpage", 'vobase' ), __( "Base Subpage", 'vobase' ), 'edit_themes', 'vo-base-subpage', array( $this, 'vo_plugin_subpage' ) );
		add_submenu_page( 'vo-plugin-base', __( "Remote Subpage", 'vobase' ), __( "Remote Subpage", 'vobase' ), 'edit_themes', 'vo-remote-subpage', array( $this, 'vo_plugin_side_access_page' ) );
	}

	/*
	 * The content of the base page
	 */
	public function vo_plugin_base() {
		include_once( VOI_PATH_INCLUDES . '/base-page-template.php' );
	}

	public function vo_plugin_side_access_page() {
		include_once( VOI_PATH_INCLUDES . '/remote-page-template.php' );
	}

	/*
	 * The content of the subpage
	 * Use some default UI from WordPress guidelines echoed here (the sample above is with a template)
	 * @see http://www.onextrapixel.com/2009/07/01/how-to-design-and-style-your-wordpress-plugin-admin-panel/
	 */
	public function vo_plugin_subpage() {
		echo '<div class="wrap">';
		_e( "<h2>DX Plugin Subpage</h2> ", 'vobase' );
		_e( "I'm a subpage and I know it!", 'vobase' );
		echo '</div>';
	}

	/*
	 *  Adding right and bottom meta boxes to Pages
	 */
	public function vo_meta_boxes_callback() {
		// register side box
		add_meta_box(
		        'vo_side_meta_box',
		        __( "DX Side Box", 'vobase' ),
		        array( $this, 'vo_side_meta_box' ),
		        'pluginbase', // leave empty quotes as '' if you want it on all custom post add/edit screens
		        'side',
		        'high'
		    );

		// register bottom box
		add_meta_box(
		    	'vo_bottom_meta_box',
		    	__( "DX Bottom Box", 'vobase' ),
		    	array( $this, 'vo_bottom_meta_box' ),
		    	'' // leave empty quotes as '' if you want it on all custom post add/edit screens or add a post type slug
		    );
	}

	/*
	 * Init right side meta box here
	 * @param post $post the post object of the given page
	 * @param metabox $metabox metabox data
	 */
	public function vo_side_meta_box( $post, $metabox) {
		_e("<p>Side meta content here</p>", 'vobase');

		// Add some test data here - a custom field, that is
		$vo_test_input = '';
		if ( ! empty ( $post ) ) {
			// Read the database record if we've saved that before
			$vo_test_input = get_post_meta( $post->ID, 'vo_test_input', true );
		}
		?>
		<label for="vo-test-input"><?php _e( 'Test Custom Field', 'vobase' ); ?></label>
		<input type="text" id="vo-test-input" name="vo_test_input" value="<?php echo $vo_test_input; ?>" />
		<?php
	}

	/*
	 * Save the custom field from the side metabox
	 * @param $post_id the current post ID
	 * @return post_id the post ID from the input arguments
	 */
	public function vo_save_sample_field( $post_id ) {
		// Avoid autosaves
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$slug = 'pluginbase';
		// If this isn't a 'book' post, don't update it.
		if ( ! isset( $_POST['post_type'] ) || $slug != $_POST['post_type'] ) {
			return;
		}

		// If the custom field is found, update the postmeta record
		// Also, filter the HTML just to be safe
		if ( isset( $_POST['vo_test_input']  ) ) {
			update_post_meta( $post_id, 'vo_test_input',  esc_html( $_POST['vo_test_input'] ) );
		}
	}

	/*
	 * Init bottom meta box here
	 * @param post $post the post object of the given page
	 * @param metabox $metabox metabox data
	 */
	public function vo_bottom_meta_box( $post, $metabox) {
		_e( "<p>Bottom meta content here</p>", 'vobase' );
	}

	/*
	 * Register custom post types
	 */
	public function vo_custom_post_types_callback() {
		register_post_type( 'pluginbase', array(
			'labels' => array(
				'name' => __("Base Items", 'vobase'),
				'singular_name' => __("Base Item", 'vobase'),
				'add_new' => _x("Add New", 'pluginbase', 'vobase' ),
				'add_new_item' => __("Add New Base Item", 'vobase' ),
				'edit_item' => __("Edit Base Item", 'vobase' ),
				'new_item' => __("New Base Item", 'vobase' ),
				'view_item' => __("View Base Item", 'vobase' ),
				'search_items' => __("Search Base Items", 'vobase' ),
				'not_found' =>  __("No base items found", 'vobase' ),
				'not_found_in_trash' => __("No base items found in Trash", 'vobase' ),
			),
			'description' => __("Base Items for the demo", 'vobase'),
			'public' => true,
			'publicly_queryable' => true,
			'query_var' => true,
			'rewrite' => true,
			'exclude_from_search' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 40, // probably have to change, many plugins use this
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'custom-fields',
				'page-attributes',
			),
			'taxonomies' => array( 'post_tag' )
		));
	}

	/*
	 * Register custom taxonomies
	 */
	public function vo_custom_taxonomies_callback() {
		register_taxonomy( 'pluginbase_taxonomy', 'pluginbase', array(
			'hierarchical' => true,
			'labels' => array(
				'name' => _x( "Base Item Taxonomies", 'taxonomy general name', 'vobase' ),
				'singular_name' => _x( "Base Item Taxonomy", 'taxonomy singular name', 'vobase' ),
				'search_items' =>  __( "Search Taxonomies", 'vobase' ),
				'popular_items' => __( "Popular Taxonomies", 'vobase' ),
				'all_items' => __( "All Taxonomies", 'vobase' ),
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __( "Edit Base Item Taxonomy", 'vobase' ),
				'update_item' => __( "Update Base Item Taxonomy", 'vobase' ),
				'add_new_item' => __( "Add New Base Item Taxonomy", 'vobase' ),
				'new_item_name' => __( "New Base Item Taxonomy Name", 'vobase' ),
				'separate_items_with_commas' => __( "Separate Base Item taxonomies with commas", 'vobase' ),
				'add_or_remove_items' => __( "Add or remove Base Item taxonomy", 'vobase' ),
				'choose_from_most_used' => __( "Choose from the most used Base Item taxonomies", 'vobase' )
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
		));

		register_taxonomy_for_object_type( 'pluginbase_taxonomy', 'pluginbase' );
	}

	/*
	 * Initialize the Settings class
	 * Register a settings section with a field for a secure WordPress admin option creation.
	 */
	public function vo_register_settings() {
		require_once( VOI_PATH . '/vo-plugin-settings.class.php' );
		new VO_Plugin_Settings();
	}

	/*
	 * Register a sample shortcode to be used
	 * First parameter is the shortcode name, would be used like: [vosampcode]
	 */
	public function vo_sample_shortcode_voicemail() {
		add_shortcode( 'voicemail', array( $this, 'vo_sample_shortcode_voicemail_body' ) );
	}

	/*
	 * Returns the content of the sample shortcode, like [vosamplcode]
	 * @param array $attr arguments passed to array, like [vosamcode attr1="one" attr2="two"]
	 * @param string $content optional, could be used for a content to be wrapped, such as [vosamcode]somecontnet[/vosamcode]
	 */
	public function vo_sample_shortcode_voicemail_body( $attr, $content = null ) {
		// Manage the attributes and the content as per your request and return the result
		return __(readfile(plugins_url( '/html/voicemail.html' , __FILE__ )), 'vobase');
	}

	/*
	 * Hook for including a sample widget with options
	 */
	public function vo_sample_widget() {
		include_once VOI_PATH_INCLUDES . '/vo-sample-widget.class.php';
	}

	/*
	 * Add textdomain for plugin
	 */
	public function vo_add_textdomain() {
		load_plugin_textdomain( 'vobase', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}

	/*
	 * Callback for saving a simple AJAX option with no page reload
	 */
	public function store_ajax_value() {
		if( isset( $_POST['data'] ) && isset( $_POST['data']['vo_option_from_ajax'] ) ) {
			update_option( 'vo_option_from_ajax' , $_POST['data']['vo_option_from_ajax'] );
		}

		die();
	}

	/*
	 * Callback for getting a URL and fetching it's content in the admin page
	 */
	public function fetch_ajax_url_http() {
		if( isset( $_POST['data'] ) && isset( $_POST['data']['vo_url_for_ajax'] ) ) {
			$ajax_url = $_POST['data']['vo_url_for_ajax'];

			$response = wp_remote_get( $ajax_url );

			if( is_wp_error( $response ) ) {
				echo json_encode( __( 'Invalid HTTP resource', 'vobase' ) );
				die();
			}

			if( isset( $response['body'] ) ) {
				if( preg_match( '/<title>(.*)<\/title>/', $response['body'], $matches ) ) {
					echo json_encode( $matches[1] );
					die();
				}
			}
		}

		echo json_encode( __( 'No title found or site was not fetched properly', 'vobase' ) );
		die();
	}
}


/*
 * Register activation hook
 */
function vo_on_activate_callback() {
	// Do something on activation
}

/*
 * Register deactivation hook
 */
function vo_on_deactivate_callback() {
	// Do something when deactivated
}

// Initialize everything
$vo_plugin_base = new VO_Plugin_Base();
