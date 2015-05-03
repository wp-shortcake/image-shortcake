<?php

if ( class_exists( 'WP_CLI' ) ) :

// Hack of WP-CLI's Logger because I wanted to use private methods.
require_once dirname( __FILE__ ) . '/CLI-Logger.php';

/**
 * Migrate post content to use image shortcodes.
 *
 */
class Img_Shortcode_Command extends \WP_CLI\CommandWithDBObject {

	protected $obj_type = 'post';
	protected $obj_fields = array(
		'ID',
		'post_title',
		'post_name',
		'post_date',
		'post_status'
	);

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
	 * [--log-file=<filename>]
	 * : Specify a file to log script progress to.
	 *
	 * [--quiet]
	 * : No output at all. YOLO!
	 *
	 * [--dry-run]
	 * : Only show the content which is to be changed.
	 *
	 * ## EXAMPLES
	 *
	 *     ## Migrate all Posts to the Image Shortcake syntax
	 *     wp img-shortcode migrate `wp post list --post_type=post` --ids`
	 *
	 *     ## Converts images to shortcodes on one post, preserving a log to rollback in case of errors.
	 *     wp img-shortcode migrate 123 --log-file="potential-oops.txt"
	 *
	 */
	public function update( $args, $assoc_args ) {

		if ( isset( $assoc_args['log-file'] ) ) {
			$handle = fopen( $assoc_args['log-file'], 'w+' );
			$logger = new \WP_CLI\Loggers\Image_Shortcake( false );
		} else {
			$handle = STDOUT;
			$logger = ( isset( $assoc_args['quiet'] ) ) ?
				new \WP_CLI\Loggers\Quiet :
				new \WP_CLI\Loggers\Image_Shortcake( true ); // in_color :: XXX :: hack
		}

		foreach( $args as $post_ID ) {

			$post = get_post( intval( $post_ID ) );

			if ( ! $post ) {
				continue;
			}

			$_content = $content_before = $post->post_content;

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

			$logger->_line( 'Image shortcode replacements for post ' . $post->ID, '', null, $handle );

			foreach ( $replacements as $del => $ins ) {
				$logger->_line( $del, '- ', '%C', $handle );
				$logger->_line( $ins, '+ ', '%G', $handle );
			}

			if ( isset( $assoc_args['dry-run'] ) ) {
				continue;
			}

			$post->post_content = $_content;

			wp_update_post( $post );

			$logger->success( 'Updated post ' . $post->ID );
		}

	}

}


WP_CLI::add_command( 'img-shortcode', 'Img_Shortcode_Command' );

endif; // class_exists( 'WP_CLI' )
