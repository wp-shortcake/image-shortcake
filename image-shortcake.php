<?php
/*
Plugin Name: Image-shortcake
Version: 0.1-alpha
Description: Provides a shortcode for image elements. Use with the Shortcake plugin for a preview of images
Author: goldenapples
Author URI: https://github.com/fusioneng
Plugin URI: https://github.com/fusioneng/image-shortcake
Text Domain: image-shortcake
Domain Path: /languages
*/

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
			self::$instance->register_shortcode();
		}

		return self::$instance;
	}


	/**
	 * Require the plugin's shortcode class file.
	 *
	 */
	private static function require_files() {
		require_once( dirname( __FILE__ ) . '/inc/class-img-shortcode.php' );
	}


	/**
	 * Register the [img] shortcode and the UI for it..
	 *
	 */
	private function register_shortcode() {

		add_shortcode( 'img',
			array( $this, 'shortcake_img_shortcode' )
		);

		if ( function_exists( 'shortcode_ui_register_for_shortcode' ) ) {

			shortcode_ui_register_for_shortcode( 'img', Img_Shortcode_UI::shortcode_ui_attrs() );

		} else {

			add_action( 'admin_notices', function(){
				if ( current_user_can( 'activate_plugins' ) ) {
					echo '<div class="error message"><p>' .
						esc_html__( 'Shortcode UI plugin must be active for Image Shortcake plugin to function.', 'image-shortcake' ) .
						'</p></div>';
				}
			});

		}
	}


	/**
	 * Output a warning notice to authorized users if shortcake is not active.
	 *
	 * if Shortcode UI plugin is not active, the UI for the [img] shortcode
	 * will not be able to be registered.
	 *
	 * @action admin_notices
	 */
	public function admin_notices_warning() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="error message"><p>' .
				esc_html__( 'Shortcode UI plugin must be active for Image Shortcake plugin to function.', 'image-shortcake' ) .
				'</p></div>';
		}
	}


	/**
	 * Build the HTML output for the shortcode.
	 *
	 * This should handle all markup generation, and include filters for
	 * theme-specific overrides.
	 *
	 * @shortcode img
	 * @param $attr
	 */
	public function shortcake_img_shortcode( $attr, $content = '' ) {
		$img_shortcode = new Img_Shortcode( $attr );
		return $img_shortcode->render();
	}

}


function _image_shortcake() {
	return Image_Shortcake::get_instance();
}
add_action( 'init', '_image_shortcake' );

