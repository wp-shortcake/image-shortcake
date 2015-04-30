<?php
/**
 * All methods dealing with migrating data into and out of the shortcode format
 * used by this plugin.
 *
 */

class Img_Shortcode_Data_Migration {

	/**
	 * Find all `<img>` tags in a post that can be replaced.
	 *
	 * @param int|object Post ID or post object
	 * @return array Array of found <img> tags
	 */
	public static function find_img_tags_for_replacement( $post ) {

		if ( ! $post = self::maybe_get_post_from_id( $post ) ) {
			return false;
		}

		$replacements = array();

		$img_shortcode_regex =
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

		preg_match_all(
			"/$img_shortcode_regex/s",
			$post->post_content,
			$matches,
			PREG_SET_ORDER
		);

		if ( 0 === count( $matches ) ) {
			return false;
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
		$shortcode_attrs = array(
			'size' => esc_attr( $attributes['size'] ),
			'attachment' => esc_attr( $attributes['attachment'] ),
			'align' => esc_attr( $attributes['align'] ),
			'alt' => esc_attr( $attributes['alt'] ),
			'width' => esc_attr( $attributes['width'] ),
			'height' => esc_attr( $attributes['height'] ),
		);

		// If this isn't a WP attachment, we'll just use its existing src attribute
		if ( empty( $shortcode_attrs['attachment'] ) ) {
			$shortcode_attrs['src'] = esc_attr( esc_url( $attributes['src'] ) );
		}

		if ( ! empty( $attributes['href'] ) ) {

			if ( ! empty( $shortcode_attrs['attachment'] ) ) {
				$attachment_src = wp_get_attachment_image_src( $attributes['attachment'], 'full' );

				if ( get_permalink( (int) $attributes['attachment'] ) === $attributes['href'] ) {
					$shortcode_attrs['linkto'] = 'attachment';
				} else if ( $attachment_src[0] === $attributes['href'] // link to full size image
						|| $attributes['src'] === $attributes['href'] // link the same as image src
						) {
					if ( $attachment_src[0] !== $attributes['href'] ) var_dump( $attachment_src[0], $attributes['href'] );
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
			$shortcode .= sanitize_key( $attr_name ) . '="' . $attr_value . '" ';
		}

		$shortcode .= '/]';

		return $shortcode;
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
