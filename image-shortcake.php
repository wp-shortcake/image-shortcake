<?php
/**
 * Plugin Name: Image-shortcake
 * Version: 0.2.0-alpha
 * Description: Provides a shortcode for image elements. Use with the Shortcake plugin for a preview of images
 * Author: fusionengineering, goldenapples
 * Author URI: https://github.com/fusioneng
 * Plugin URI: https://github.com/fusioneng/image-shortcake
 * Text Domain: image-shortcake
 * Domain Path: /languages
 */

define( 'IMAGE_SHORTCAKE_VERSION', '0.2.0-alpha' );

class Image_Shortcake {

	private static $instance;

	/**
	 * Activate the plugin as a singleton.
	 *
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::require_files();

			self::$instance = new Image_Shortcake;
			self::$instance->load_textdomain();
			self::$instance->register_shortcode();
			self::$instance->setup_filters();
			self::$instance->enqueue_assets();
		}

		return self::$instance;
	}

	/**
	 * Load translations
	 *
	 * @return void
	 */
	private static function load_textdomain() {
		load_plugin_textdomain( 'image-shortcake', false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	/**
	 * Require the plugin's shortcode class file.
	 *
	 */
	private static function require_files() {
		require_once( dirname( __FILE__ ) . '/inc/class-img-shortcode.php' );

		require_once( dirname( __FILE__ ) . '/inc/class-img-shortcode-data-migration.php' );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( dirname( __FILE__ ) . '/inc/class-wp-cli-img-shortcode-command.php' );
		}
	}


	/**
	 * Register the [img] shortcode and the UI for it..
	 *
	 */
	private function register_shortcode() {

		add_shortcode( 'img', 'Img_Shortcode::callback' );

		if ( function_exists( 'shortcode_ui_register_for_shortcode' ) ) {
			shortcode_ui_register_for_shortcode( 'img', Img_Shortcode::get_shortcode_ui_args() );
		} else {
			add_action( 'admin_notices', array( $this, 'action_admin_notices_warning' ) );
		}
	}


	/**
	 * Set up filters to integrate this shortcode with the media library output.
	 *
	 */
	private function setup_filters() {
		add_filter( 'media_send_to_editor', 'Img_Shortcode::filter_media_send_to_editor', 15, 3 );
	}


	/**
	 * Enqueue scripts and styles for editor admin area
	 *
	 * Defines the callback function which is run on chanes to any of the
	 * shortcode attributes through the UI.
	 */
	public function enqueue_assets() {
		add_action( 'enqueue_shortcode_ui', array( $this, 'action_enqueue_shortcode_ui' ) );
	}


	/**
	 * Enqueues the attribute event handler functions on edit page
	 * Adds some localized text
	 *
	 */
	public function action_enqueue_shortcode_ui() {
		wp_enqueue_script( 'image-shortcake-admin', plugin_dir_url( __FILE__ ) . 'assets/js/image-shortcake-admin.js', false, IMAGE_SHORTCAKE_VERSION );
		$translation_array = array(
			'caption' => __( 'Caption', 'image-shortcake' ),
			'warning' => __( 'Double quotes and HTML tags are not allowed in captions', 'image-shortcake' ),
		);
		wp_localize_script( 'image-shortcake-admin', 'image_shortcake_strings', $translation_array );
	}


	/**
	 * Output a warning notice to authorized users if shortcake is not active.
	 *
	 * if Shortcode UI plugin is not active, the UI for the [img] shortcode
	 * will not be able to be registered.
	 *
	 * @action admin_notices
	 */
	public function action_admin_notices_warning() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="error message"><p>' .
				esc_html__( 'Shortcode UI plugin is not active. No UI will be available for the image shortcode.', 'image-shortcake' ) .
				'</p></div>';
		}
	}

}

add_action( 'init', 'Image_Shortcake::get_instance' );
