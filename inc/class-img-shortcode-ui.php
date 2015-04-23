<?php
/**
 * Shortcode UI for [img] shortcode
 *
 * This class is only used for the static function which builds the array of
 * shortcode UI attributes. To modify the UI fields, attach a filter to
 * `image_shortcake_ui_attrs`, which receives the entire array of attributes
 * available as its only parameter.
 *
 */

class Img_Shortcode_UI {

	/**
	 * Shortcode UI attributes.
	 *
	 * This is the only function in this class. It is responsible for
	 * generating the Shortcake attributes necessary for rendering UI on the
	 * Add Post Element screen.
	 *
	 * To modify these attributes (for example to limit this shortcode to certain
	 * post types), this object can be filtered with the `image_shortcake_ui_attrs`
	 * filter.
	 */
	public static function shortcode_ui_attrs() {

		$shortcode_ui_attrs = array(

				'label' => 'Image',

				'listItemImage' => 'dashicons-format-image',

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
			);

		$shortcode_ui_attrs = apply_filters( 'image_shortcake_ui_attrs', $shortcode_ui_attrs );

		return $shortcode_ui_attrs;

	}
}
