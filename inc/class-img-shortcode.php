<?php
/**
 * An image shortcode.
 *
 * Creates and renders an [img] shortcode.
 */

class Img_Shortcode {


	/**
	 * Shortcode UI attributes.
	 *
	 * To modify these attributes (for example to limit this shortcode to certain
	 * post types), this object can be filtered with the `image_shortcode_ui_args`
	 * filter.
	 */
	public static function get_shortcode_ui_args() {

		$shortcode_ui_args = array(

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
							'left'   => 'Float left',
							'right'  => 'Float right',
							'center' => 'Center',
							'none'   => 'None (inline)',
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

		$shortcode_ui_args = apply_filters( 'image_shortcode_ui_args', $shortcode_ui_args );

		return $shortcode_ui_args;
	}


	/**
	 * Take content containing existing image tags or [caption] shortcodes,
	 * turn it into the shortcodes we want, so all images can be processed in
	 * the same way.
	 *
	 */
	public static function reversal( $content ) {
		/**
		 * TODO: detect images in content. If we can, try and replace them with
		 * the [img] shortcode.
		 */
		return $content;
	}


	/**
	 * Render output from this shortcode.
	 *
	 * @param array $attrs Shortcode attributes. See definition in
	 *                     @function `get_shortcode_ui_args()`
	 * @return string
	 */
	public static function callback( $attr ) {

		$attr = wp_parse_args( $attr, array(
			'attachment' => 0,
			'size'       => 'full',
			'alt'        => '',
			'classes'    => '',
			'caption'    => '',
			'align'      => 'none',
			'linkto'     => '',
		) );

		$attr = apply_filters( 'image_shortcode_attrs', $attr );

		/**
		 * Filter the default shortcode output.
		 *
		 * If the filtered output isn't empty, it will be used instead of generating
		 * the default image template.
		 *
		 * @param string $output  The image output. Default empty.
		 * @param array  $attr    Attributes of the image shortcode.
		 * @param string $content The image element, possibly wrapped in a hyperlink.
		 */
		$output = apply_filters( 'image_shortcode_output', '', $attr );

		if ( $output !== '' ) {
			return $output;
		}

		$image_html = '<img ';

		$image_classes = explode( ' ', $attr['classes'] );
		$image_classes[] = 'size-' . $attr['size'];
		$image_classes[] = 'align' . $attr['align'];

		$image_attr = array(
			'alt' => $attr['alt'],
			'class' => trim( implode( ' ', $image_classes ) ),
		);

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
				$image_html .= $attr_name . '="' . esc_attr( $attr_value ) . '" ';
			}
		}

		$image_html .= '/>';

		return $image_html;
	}

}
