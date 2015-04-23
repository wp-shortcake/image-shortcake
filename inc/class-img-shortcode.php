<?php
/**
 * An image shortcode.
 *
 * Creates and renders an [img] shortcode.
 */

class Img_Shortcode {

	private $attachment;
	private $size;
	private $alt;
	private $classes;
	private $caption;
	private $align;
	private $linkto;

	/**
	 * Initialize the shortcode.
	 *
	 * Reads attrs passed in, merges them with defaults, and creates a
	 * shortcode object which can be rendered.
	 *
	 * @param array $attr Shortcode attributes.
	 */
	public function __construct( $attr ) {

		$attr = wp_parse_args( $attr, array(
			'attachment' => 0,
			'size'       => 'full',
			'alt'        => '',
			'classes'    => '',
			'caption'    => '',
			'align'      => 'none',
			'linkto'     => '',
		) );

		$this->attachment = $attr['attachment'];
		$this->size       = $attr['size'];
		$this->alt        = $attr['alt'];
		$this->classes    = $attr['classes'];
		$this->caption    = $attr['caption'];
		$this->align      = $attr['align'];
		$this->linkto     = $attr['linkto'];

	}

	/**
	 * Gather the shortcode attributes, and allow them to be filtered before
	 * rendering.
	 *
	 * @param none
	 * @return array
	 */
	private function attributes() {
		$attrs = array(
			'attachment' => $this->attachment,
			'size'       => $this->size,
			'alt'        => $this->alt,
			'classes'    => $this->classes,
			'caption'    => $this->caption,
			'align'      => $this->align,
			'linkto'     => $this->linkto,
		);

		return apply_filters( 'image_shortcode_attrs', $attrs );
	}

	/**
	 * Render output from this created shortcode object.
	 *
	 * @param none
	 * @return string
	 */
	public function render() {

		/**
		 * Filter the default shortcode output.
		 *
		 * If the filtered output isn't empty, it will be used instead of generating
		 * the default caption template.
		 *
		 * @param string $output  The caption output. Default empty.
		 * @param array  $attr    Attributes of the caption shortcode.
		 * @param string $content The image element, possibly wrapped in a hyperlink.
		 */
		$output = apply_filters( 'image_shortcake_output', '', $this->attributes() );

		if ( $output !== '' ) {
			return $output;
		}

		$image_html = '<img ';

		$image_classes = explode( ' ', $this->classes );
		$image_classes[] = 'size-' . $this->size;
		$image_classes[] = $this->align;

		$image_attr = array(
			'alt' => $this->alt,
			'class' => implode( ' ', $image_classes ),
		);

		if ( isset( $this->attachment ) &&
				$attachment = wp_get_attachment_image_src( (int) $this->attachment, $this->size ) ) {
			$image_attr['src'] = $attachment[0];
			$image_attr['width'] = $attachment[1];
			$image_attr['height'] = $attachment[2];
		} else {
			$image_attr['src'] = $this->src;
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
