<?php
/**
 * All methods dealing with migrating data into and out of the shortcode format
 * used by this plugin.
 *
 */

class Img_Shortcode_Data_Migration {

	private static function img_shortcode_regex() {
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

		return $img_shortcode_regex;
	}


	private static function caption_shortcode_regex() {
		$caption_shortcode_regex =
			'\[caption' .
				'[^\]]*' .  '\]\]?' .
				self::img_shortcode_regex() .
				'(?: (?P<caption>[^\]]*))' .
			'\[\[?\/caption\]\]?';
		return $caption_shortcode_regex;
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
