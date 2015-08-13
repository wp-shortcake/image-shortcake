<?php
/**
 * All methods dealing with migrating data into and out of the shortcode format
 * used by this plugin.
 *
 */

class Img_Shortcode_Data_Migration {

	private static function img_tag_regex() {
		$img_tag_regex =
			'(?:<a[^>]+' .
					'href="(?P<href>[^"]*)"' .
					'[^>]*>)?' .
				'<img[^>]*' .
					'class="' .
						'(?|size-(?P<size>\w+))?' . ' ?' .
						'(?|wp-image-(?P<attachment>\d+))?' . ' ?' .
						'(?|(?P<align>align[\w-]+))?' . ' ?' .
						'[^"]*" ' . // end of class attribute
					'src="(?P<src>[^"]*)" ?' .
					'(?:alt="(?P<alt>[^"]*)" ?)?' .
					'(?:width="(?P<width>[^"]*)" ?)?' .
					'(?:height="(?P<height>[^"]*)" ?)?' .
					'[^>]*>' .
			'(?:<\/a>)?';

		return $img_tag_regex;
	}

	private static function caption_shortcode_regex() {
		$caption_shortcode_regex =
			'\[caption' .
				'[^\]]*' .  '\]\]?' .
				self::img_tag_regex() .
				'(?: (?P<caption>[^\]]*))' .
			'\[\[?\/caption\]\]?';
		return $caption_shortcode_regex;
	}

	private static function img_shortcode_regex() {
		$img_shortcode_regex = '\[img ' .  '[^\]]*]';
		return $img_shortcode_regex;
	}

	public static function find_img_tags_for_replacement_on_post( $post ) {
		if ( ! $post = self::maybe_get_post_from_id( $post ) ) {
			return false;
		}

		return self::find_img_tags_for_replacement( $post->post_content );
	}

	/**
	 * Find all `<img>` tags in a string that can be replaced.
	 *
	 * @param string String containing <img> tags, for example post content
	 * @return array Array of found <img> tags => [img] replacements
	 */
	public static function find_img_tags_for_replacement( $post_content ) {

		$replacements = array();

		$img_tag_regex = self::img_tag_regex();

		preg_match_all(
			"/$img_tag_regex/s",
			$post_content,
			$matches,
			PREG_SET_ORDER
		);

		if ( 0 === count( $matches ) ) {
			return array();
		}

		foreach ( $matches as $matched_pattern ) {
			$replacements[ $matched_pattern[0] ] = self::convert_img_tag_to_shortcode(
				$matched_pattern[0],
				$matched_pattern
			);
		}

		return $replacements;
	}


	public static function find_caption_shortcodes_for_replacement_on_post( $post ) {
		if ( ! $post = self::maybe_get_post_from_id( $post ) ) {
			return false;
		}

		return self::find_caption_shortcodes_for_replacement( $post->post_content );
	}

	/**
	 * Find all [caption] shortcodes in a string that can be replaced with [img] shortcodes.
	 *
	 * @param string String containing <img> tags, for example post content
	 * @return array Array of found caption shortcodes => [img] replacements
	 */
	public static function find_caption_shortcodes_for_replacement( $post_content ) {

		$replacements = array();

		$caption_shortcode_regex = self::caption_shortcode_regex();

		preg_match_all(
			"/$caption_shortcode_regex/s",
			$post_content,
			$matches,
			PREG_SET_ORDER
		);

		if ( 0 === count( $matches ) ) {
			return array();
		}

		foreach ( $matches as $matched_pattern ) {
			$replacements[ $matched_pattern[0] ] = self::convert_img_tag_to_shortcode(
				$matched_pattern[0],
				$matched_pattern
			);
		}

		return $replacements;
	}


	/**
	 * Convert an <img> tag to its shortcode equivalent.
	 *
	 * @param string HTML `<img>` element
	 * @return string An `[img]` shortcode element.
	 */
	public static function convert_img_tag_to_shortcode( $img_tag, $attributes ) {

		// Whitelist a few attributes that we can take in as they are
		$shortcode_attrs = array_intersect_key( $attributes,
			array(
				'size' => null,
				'attachment' => null,
				'align' => null,
				'alt' => null,
				'caption' => null,
				'width' => null,
				'height' => null,
			)
		);

		// If this isn't a WP attachment, we'll just use its existing src attribute
		if ( empty( $shortcode_attrs['attachment'] ) ) {
			$shortcode_attrs['src'] = esc_url( $attributes['src'] );
		}

		// If there's a link, check whether its a link to file, attachment, or custom
		if ( ! empty( $attributes['href'] ) ) {

			if ( ! empty( $shortcode_attrs['attachment'] ) ) {
				$attachment_src = wp_get_attachment_image_src( $attributes['attachment'], 'full' );

				if ( get_permalink( (int) $attributes['attachment'] ) === $attributes['href'] ) {
					$shortcode_attrs['linkto'] = 'attachment';
				} else if ( $attachment_src[0] === $attributes['href'] // link to full size image
						|| $attributes['src'] === $attributes['href'] // link the same as image src
						) {
					$shortcode_attrs['linkto'] = 'file';
				} else {
					$shortcode_attrs['href'] = $attributes['href'];
				}
			} else {
				$shortcode_attrs['href'] = $attributes['href'];
			}
		}

		$shortcode_attrs = array_filter( $shortcode_attrs );

		$shortcode = '[img ';

		foreach ( $shortcode_attrs as $attr_name => $attr_value ) {
			$shortcode .= sanitize_key( $attr_name ) . '="' . esc_attr( $attr_value ) . '" ';
		}

		$shortcode .= '/]';

		return $shortcode;
	}


	/**
	 * Get all [img] shortcodes in a string for replacement.
	 *
	 * Used in converting data added by this plugin back to the default format
	 * of <img> tags and [caption] shortcodes.
	 *
	 */
	public static function find_img_shortcodes_for_replacement( $post_content ) {

		$replacements = array();

		$img_shortcode_regex = self::img_shortcode_regex();

		preg_match_all(
			"/$img_shortcode_regex/s",
			$post_content,
			$matches,
			PREG_SET_ORDER
		);

		if ( 0 === count( $matches ) ) {
			return array();
		}

		foreach ( $matches as $matched_pattern ) {
			$replacements[ $matched_pattern[0] ] = self::convert_img_shortcode_to_tag(
				$matched_pattern[0]
			);
		}

		return $replacements;

	}


	/**
	 * Convert an [img] shortcode as inserted by this plugin to the WP default
	 * representation.
	 *
	 * @param string `[img]` shortcode element
	 * @return string A `[caption]` shortcode element, or an <img> tag
	 */
	public static function convert_img_shortcode_to_tag( $img_shortcode ) {
		$atts = shortcode_parse_atts( $img_shortcode );

		$caption = isset( $atts['caption'] ) ? $atts['caption'] : '';
		unset( $atts['caption'] );

		$width = isset( $atts['width'] ) ? $atts['width'] : null;

		$align = isset( $atts['align'] ) ? $atts['align'] : 'alignnone';

		// Use a size if set.
		// If valid attachment, full is ok
		// If not valid, use medium so we can provide width
		if ( isset( $atts['size'] ) ) {
			$size = $atts['size'];
		} else {
			if ( isset( $atts['attachment'] ) && get_permalink( $atts['attachment'] ) ) {
				$size = $atts['size'] = 'full';
			} else {
				$size = $atts['size'] = 'large';
			}
		}

		$content = Img_Shortcode::callback( $atts );

		if ( ! isset( $width ) && isset( $atts['attachment'] ) && get_permalink( $atts['attachment'] ) ) {
			$attachment = wp_get_attachment_image_src(
				(int) $atts['attachment'], $size
			);
			$width = intval( $attachment[1] );
		} else {
			/* If there's no width set and no valid attachment to get full/custom size dimensions from, fallback to large width */
			$width = '600';
		}

		if ( isset( $atts['attachment'] ) && $caption ) {
			$id_string = 'id="attachment_' . esc_attr( sanitize_html_class( $atts['attachment'] ) ). '" ';
		} else {
			$id_string = '';
		}

		if ( $caption ) {
			$content =
				'[caption ' .
					$id_string .
					'width="' . $width . '" ' .
					'align="' . $align . '"' .
					']' .
				$content .
				$caption .
				'[/caption]';
		}

		return $content;
	}

	/**
	 * Get a post from a Post ID or post object.
	 *
	 * Simple utility function which allows the other functions in this class
	 * to accept either Post IDs or Post objects as arguments.
	 *
	 * @param int|WP_Post $post
	 * @return false|WP_Post Post object, or false if one can't be found.
	 */
	private static function maybe_get_post_from_id( $post ) {

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		return ( $post instanceof WP_Post ) ? $post : false;
	}

}
