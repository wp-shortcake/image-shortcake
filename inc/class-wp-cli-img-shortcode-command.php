<?php

/**
 * Migrate post content to use image shortcodes.
 *
 */
class Image_Shortcake_Command extends WP_CLI_Command {

	public function __construct() {
		$this->fetcher = new \WP_CLI\Fetchers\Post;
	}


	/**
	 * Migrate post content from <img> tags to image shortcodes.
	 *
	 * ## OPTIONS
	 *
	 * <id>...
	 * : One or more IDs of posts to update.
	 *
	 * [--dry-run]
	 * : Only show the content which is to be changed, don't update posts.
	 *
	 * ## EXAMPLES
	 *
	 *     ## Migrate all Posts to the Image Shortcake syntax
	 *     wp image-shortcake-shortcode migrate `wp post list --post_type=post` --ids`
	 *
	 *     ## Converts images to shortcodes on one post, preserving a log to rollback in case of errors.
	 *     wp image-shortcake migrate 123 > potential-oops.txt
	 *
	 *
	 * @synopsis <id>... [--dry-run]
	 */
	public function migrate( $args, $assoc_args ) {

		foreach ( array_filter( $args ) as $post_ID ) {

			$post = $this->fetcher->get_check( $post_ID );

			$_content = $post->post_content;

			$caption_replacements = Img_Shortcode_Data_Migration::find_caption_shortcodes_for_replacement( $_content );

			$_content = str_replace(
				array_keys( $caption_replacements ),
				array_values( $caption_replacements ),
				$_content
			);

			$img_tag_replacements = Img_Shortcode_Data_Migration::find_img_tags_for_replacement( $_content );

			$_content = str_replace(
				array_keys( $img_tag_replacements ),
				array_values( $img_tag_replacements ),
				$_content
			);

			$replacements = array_merge( (array) $caption_replacements, (array) $img_tag_replacements );

			WP_CLI::log( '' );

			if ( 0 === count( $replacements ) ) {
				WP_CLI::log( 'Nothing to replace on post ' . $post->ID . '. Skipping.' );
				WP_CLI::log( '' );
				continue;
			}

			$header = 'Image shortcode replacements for post ' . $post->ID;

			WP_CLI::log( $header );
			WP_CLI::log( str_repeat( '=', strlen( $header ) ) );
			WP_CLI::log( '' );

			foreach ( $replacements as $del => $ins ) {
				\WP_CLI::log( \cli\Colors::colorize( '%C-%n' ) . $del, true );
				\WP_CLI::log( \cli\Colors::colorize( '%G+%n' ) . $ins, true );
			}

			WP_CLI::log( '' );

			if ( isset( $assoc_args['dry-run'] ) ) {
				WP_CLI::log( 'Post not updated: --dry-run specifed.' );
				WP_CLI::log( '' );
				continue;
			}

			global $wpdb;

			// @codingStandardsIgnoreStart
			$updated = $wpdb->update( $wpdb->posts, array( 'post_content' => $_content ), array( 'ID' => $post_ID ) );
			// @codingStandardsIgnoreEnd

			if ( 1 === $updated ) {
				clean_post_cache( $post );
				WP_CLI::success( 'Updated post ' . $post->ID . '.' );
			} else {
				WP_CLI::warning( 'There was an unexpected error updating post ' . $post->ID . '.' );
			}
		}

	}

}


WP_CLI::add_command( 'image-shortcake', 'Image_Shortcake_Command' );
