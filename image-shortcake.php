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

		add_shortcode( 'shortcake_image',
			array( $this, 'shortcake_image_shortcode' )
		);

		shortcode_ui_register_for_shortcode(
			'shortcake_image',
			array(

				// Display label. String. Required.
				'label' => 'Image',

				// Icon/attachment for shortcode. Optional. src or dashicons-$icon. Defaults to carrot.
				'listItemImage' => 'dashicons-format-image',

				'inner_content' => array(
					'label' => 'Image',
				),

				'post_type'     => array( 'post' ),

				'attrs' => array(

					array(
						'label' => 'Choose Attachment',
						'attr'  => 'attachment',
						'type'  => 'attachment',
						'libraryType' => array( 'image' ),
						'addButton'   => 'Select Image',
						'frameTitle'  => 'Select Image',
					),

					array(
						'label'       => 'Image size',
						'attr'        => 'size',
						'type'        => 'select',
						'options' => array(
							'thumbnail' => 'Thumbnail',
							'small'     => 'Small',
							'medium'    => 'Medium',
							'large'     => 'Large',
						),
					),

					array(
						'label' => 'Alt',
						'attr'  => 'alt',
						'type'  => 'text',
						'placeholder' => 'Alt text for the image',
					),

					array(
						'label'       => 'Caption',
						'attr'        => 'caption',
						'type'        => 'text',
						'placeholder' => 'Caption for the image',
					),

					array(
						'label'       => 'Alignment',
						'attr'        => 'align',
						'type'        => 'select',
						'options' => array(
							'alignleft'   => 'Float left',
							'alignright'  => 'Float right',
							'aligncenter' => 'Float center',
							'alignnone'   => 'None (inline)',
						),
					),

					array(
						'label'       => 'Link to',
						'attr'        => 'linkto',
						'type'        => 'select',
						'options' => array(
							'none'       => 'None (no link)',
							'attachment' => 'Link to attachment file',
							'file'       => 'Link to file',
							'custom'     => 'Custom link',
						),
					),

				),
			)
		);
	}


	/**
	 * Build the HTML output for the shortcode.
	 *
	 * This should handle all markup generation, and include filters for
	 * theme-specific overrides.
	 *
	 * @shortcode shortcake_image
	 * @param $attr
	 */
	public function shortcake_image_shortcode( $attr, $content = '' ) {

		$attr = wp_parse_args( $attr, array(
			'attachment' => 0,
			'size'       => 'full',
			'alt'        => '',
			'caption'    => '',
			'align'      => 'none',
			'linkto'     => '',
		) );

		// XXX Actually build html here
		$image_html = '<img ';

		$image_attr = array(
			'classes' => explode( ',', $attr['classes'] ),
			'alt' => $attr['alt'],
		);

		$image_attr['classes'][] = 'size-' . $attr['size'];
		$image_attr['classes'][] = $attr['align'];

		if ( isset( $attr['attachment'] ) &&
			$attachment = wp_get_attachment_image_src( (int) $attr['attachment'], $attr['size'] ) ) {
			$image_attr['src'] = $attachment[0];
			$image_attr['width'] = $attachment[1];
			$image_attr['height'] = $attachment[2];
		} else {
			$image_attr['src'] = $attr['src'];
		}

		foreach ( $image_attr as $attr_name => $attr_value ) {
			if ( ! empty( $attr_value ) ) {
				$image_html .= $attr_name . '="' .
					esc_attr( $attr_value ) . '" ';
			}
		}

		$image_html .= '/>';

		return $image_html;

	}

}


function _image_shortcake() {
	return Image_Shortcake::get_instance();
}
add_action( 'init', '_image_shortcake' );

