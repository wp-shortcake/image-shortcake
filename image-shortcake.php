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

require_once( 'inc/class-img-shortcode.php' );
require_once( 'inc/class-img-shortcode-ui.php' );

class Image_Shortcake {

	private static $instance;


	/**
	 * Activate the plugin as a singleton.
	 *
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new Image_Shortcake;
			self::$instance->register_shortcodes();
		}
		return self::$instance;
	}

	private function register_shortcodes() {

		if ( ! function_exists( 'shortcode_ui_register_for_shortcode' ) ) {
			add_action( 'admin_notices', function(){
				if ( current_user_can( 'activate_plugins' ) ) {
					echo '<div class="error message"><p>Shortcode UI plugin must be active for Image Shortcake plugin to function.</p></div>';
				}
			});
			return;
		}

		add_shortcode( 'img',
			array( $this, 'shortcake_image_shortcode' )
		);

		shortcode_ui_register_for_shortcode( 'img', Image_Shortcode_UI::shortcode_ui_attrs() );
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
	public function shortcake_image_shortcode( $attr, $content = '' ) {
		$img_shortcode = new Image_Shortcode( $attr );
		return $img_shortcode->render();
	}

}


function _image_shortcake() {
	return Image_Shortcake::get_instance();
}
add_action( 'init', '_image_shortcake' );

