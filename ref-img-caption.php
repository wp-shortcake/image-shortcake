	/**
	 * Builds the image shortcode element.
	 *
	 * Copied from WP core as a starting point. See function
	 * `img_caption_shortcode()` in wp-includes/media.php
	 *
	 * The supported attributes for the shortcode are 'id', 'align', 'width', and
	 * 'caption'.
	 *
	 * @since 2.6.0
	 *
	 * @param array  $attr {
	 *     Attributes of the caption shortcode.
	 *
	 *     @type string $id      ID of the div element for the caption.
	 *     @type string $align   Class name that aligns the caption. Default 'alignnone'. Accepts 'alignleft',
	 *                           'aligncenter', alignright', 'alignnone'.
	 *     @type int    $width   The width of the caption, in pixels.
	 *     @type string $caption The caption text.
	 *     @type string $class   Additional class name(s) added to the caption container.
	 * }
	 * @param string $content Shortcode content.
	 * @return string HTML content to display the caption.
	 */
	function img_caption_shortcode( $attr, $content = null ) {
		// New-style shortcode with the caption inside the shortcode with the link and image tags.
		if ( ! isset( $attr['caption'] ) ) {
			if ( preg_match( '#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches ) ) {
				$content = $matches[1];
				$attr['caption'] = trim( $matches[2] );
			}
		}

		/**
		 * Filter the default caption shortcode output.
		 *
		 * If the filtered output isn't empty, it will be used instead of generating
		 * the default caption template.
		 *
		 * @since 2.6.0
		 *
		 * @see img_caption_shortcode()
		 *
		 * @param string $output  The caption output. Default empty.
		 * @param array  $attr    Attributes of the caption shortcode.
		 * @param string $content The image element, possibly wrapped in a hyperlink.
		 */
		$output = apply_filters( 'img_caption_shortcode', '', $attr, $content );
		if ( $output !== '' ) {
			return $output;
		}

		$atts = shortcode_atts( array(
			'id'	  => '',
			'align'	  => 'alignnone',
			'width'	  => '',
			'caption' => '',
			'class'   => '',
		), $attr, 'caption' );

		$atts['width'] = (int) $atts['width'];
		if ( $atts['width'] < 1 || empty( $atts['caption'] ) ) {
			return $content;
		}

		if ( ! empty( $atts['id'] ) ) {
			$atts['id'] = 'id="' . esc_attr( $atts['id'] ) . '" ';
		}

		$class = trim( 'wp-caption ' . $atts['align'] . ' ' . $atts['class'] );

		if ( current_theme_supports( 'html5', 'caption' ) ) {
			return '<figure ' . $atts['id'] . 'style="width: ' . (int) $atts['width'] . 'px;" class="' . esc_attr( $class ) . '">'
			. do_shortcode( $content ) . '<figcaption class="wp-caption-text">' . $atts['caption'] . '</figcaption></figure>';
		}

		$caption_width = 10 + $atts['width'];

		/**
		 * Filter the width of an image's caption.
		 *
		 * By default, the caption is 10 pixels greater than the width of the image,
		 * to prevent post content from running up against a floated image.
		 *
		 * @since 3.7.0
		 *
		 * @see img_caption_shortcode()
		 *
		 * @param int    $caption_width Width of the caption in pixels. To remove this inline style,
		 *                              return zero.
		 * @param array  $atts          Attributes of the caption shortcode.
		 * @param string $content       The image element, possibly wrapped in a hyperlink.
		 */
		$caption_width = apply_filters( 'img_caption_shortcode_width', $caption_width, $atts, $content );

		$style = '';
		if ( $caption_width ) {
			$style = 'style="width: ' . (int) $caption_width . 'px" ';
		}

		return '<div ' . $atts['id'] . $style . 'class="' . esc_attr( $class ) . '">'
		. do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . '</p></div>';
	}
