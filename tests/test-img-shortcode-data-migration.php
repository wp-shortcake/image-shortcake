<?php

class Test_Img_Shortcode_Data_Migration extends WP_UnitTestCase {

	private $attachment_id;
	private $image_src;
	private $image_path;
	private $image_tag_from_attachment;
	private $image_tag_from_src;

	// @codingStandardsIgnoreStart
	public function setUp() {

		parent::setUp();

		$attachment_id = $this->insert_attachment( null,
			dirname( __FILE__ ) . '/data/fusion_image_placeholder_16x9_h2000.png',
			array(
				'post_title'     => 'Post',
				'post_content'   => 'Post Content',
				'post_date'      => '2014-10-01 17:28:00',
				'post_status'    => 'publish',
				'post_type'      => 'attachment',
			)
		);

		$this->attachment_id = $attachment_id;

		$upload_dir = wp_upload_dir();

		$this->image_src = $upload_dir['url'] . '/fusion_image_placeholder_16x9_h2000.png';
		$this->image_path = $upload_dir['path'] . '/fusion_image_placeholder_16x9_h2000.png';

		$this->image_tag_from_attachment =
			'<img src="' . $this->image_src . '" ' .
				'alt="This is the alt attribute." ' .
				'width="1024" height="540" ' .
				'class="size-large wp-image-' . $this->attachment_id . ' aligncenter" />';

		$this->image_tag_from_src =
			'<a href="http://go.to/thislink/">' .
				'<img class="aligncenter" ' .
				'src="' . $this->image_src . '" ' .
				'alt="This is the alt attribute." ' .
				'width="1024" height="540" />' .
			'</a>';

		$this->regex_test_1 =
			'[caption id="attachment_9" align="alignnone" width="2448"]'.
			'<a href="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664.jpg">' .
			'<img src="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664.jpg"' .
			' alt="Alt text" width="2448" height="2448" class="size-full wp-image-9" />' .
			'</a> Caption text[/caption]';

		$this->regex_test_2 =
			'[caption id="attachment_9" align="alignleft" width="300"]' .
			'<a href="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664.jpg">' .
			'<img src="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664-300x300.jpg" ' .
			'alt="Alt text" width="300" height="300" class="size-medium wp-image-9" /></a> Caption text[/caption]';

		$this->regex_test_3 =
			'[caption id="attachment_9" align="alignright" width="660"]' .
			'<a href="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664.jpg">' .
			'<img src="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664-1024x1024.jpg" ' .
			'alt="Alt text" width="660" height="660" class="size-large wp-image-9" /></a> Caption text[/caption]';

		$this->regex_test_4 =
			'<a href="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664.jpg">' .
			'<img src="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664-1024x1024.jpg" ' .
			'alt="Alt text" width="660" height="660" class="size-large wp-image-9 alignright" /></a>';

		$this->regex_test_5 =
			'<img src="http://vip.local/wp-content/uploads/sites/3/2015/08/IMG_1664-1024x1024.jpg" ' .
			'alt="Alt text" width="660" height="660" class="size-large wp-image-9 alignleft" />';

		$this->caption_regex = Img_Shortcode_Data_Migration::caption_shortcode_regex();
		$this->img_regex = Img_Shortcode_Data_Migration::img_tag_regex();
	}

	public function tearDown() {
		parent::tearDown();

		unlink( $this->image_path );
	}
	// @codingStandardsIgnoreEnd


	/**
	 * Test our regex functions directly
	 *
	 */
	function test_img_caption_regexes() {

		$regex_test_1_matches = preg_match(
			"/$this->caption_regex/s",
			$this->regex_test_1,
			$matches
		);
		$this->assertEquals( 1, $regex_test_1_matches );

		$regex_test_2_matches = preg_match(
			"/$this->caption_regex/s",
			$this->regex_test_2,
			$matches
		);
		$this->assertEquals( 1, $regex_test_2_matches );

		$regex_test_3_matches = preg_match(
			"/$this->caption_regex/s",
			$this->regex_test_3,
			$matches
		);
		$this->assertEquals( 1, $regex_test_3_matches );

		$regex_test_4_matches = preg_match(
			"/$this->img_regex/s",
			$this->regex_test_4,
			$matches
		);
		$this->assertEquals( 1, $regex_test_4_matches );

		$regex_test_5_matches = preg_match(
			"/$this->img_regex/s",
			$this->regex_test_5,
			$matches
		);
		$this->assertEquals( 1, $regex_test_5_matches );

	}

	/**
	 * Case: <img> tags where the src is an attachment
	 *
	 */
	function test_img_tag_from_attachment() {
		$img_tag = $this->image_tag_from_attachment;
		$post_content = "blah blah blah\r\n\r\n{$this->image_tag_from_attachment}";
		$post_id = wp_insert_post( array( 'post_content' => $post_content ) );

		$replacements = Img_Shortcode_Data_Migration::find_img_tags_for_replacement_on_post( $post_id );

		$this->assertContains( $img_tag, array_keys( $replacements ) );

		$this->assertContains( 'attachment="' . $this->attachment_id .'"', $replacements[ $img_tag ] );
		$this->assertNotContains( 'src="', $replacements[ $img_tag ] );
	}

	/**
	 * Case: [img] shortcode conversion to <img>
	 *
	 */
	function test_img_shortcode_conversion_to_img() {

		$attachment_id = $this->attachment_id;
		$upload_dir = wp_upload_dir();
		$expected_src_attr = $upload_dir['url'] . '/fusion_image_placeholder_16x9_h2000.png';

		// Test vanilla shortcode
		$shortcode = '[img attachment="' . $attachment_id . '" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$this->assertContains( '<img class="size-full alignnone" src="' . $expected_src_attr . '" width="2000" height="1125" />' , $conversion );

		// Test link href: linkto="file"
		$shortcode = '[img attachment="' . $attachment_id . '" linkto="file" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$this->assertContains( 'href="' . $expected_src_attr . '"', $conversion );

		// Test link href: linkto="attachment"
		$shortcode = '[img attachment="' . $attachment_id . '" linkto="attachment" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$expected_href_attr = get_permalink( $attachment_id );
		$this->assertContains( 'href="' . $expected_href_attr . '"', $conversion );

		// Test caption attribute (it should always get a width)
		$caption = <<<EOL
This is a "<em>caption</em>". It should contain <abbr>HTML</abbr> and <span class="icon">markup</span>.
EOL;
		$shortcode = '[img attachment="' . $attachment_id . '" caption="' . esc_attr( $caption ) . '" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$expected_caption = esc_html( $caption );
		$this->assertContains( '[caption id="attachment_' . $attachment_id . '" width="2000" align="alignnone"]', $conversion );
		$this->assertContains( $expected_caption . '[/caption]', $conversion );

		// Test caption width with a different size image
		$caption = <<<EOL
This is a "<em>caption</em>". It should contain <abbr>HTML</abbr> and <span class="icon">markup</span>.
EOL;
		$shortcode = '[img attachment="' . $attachment_id . '" caption="' . esc_attr( $caption ) . '" size="full" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$expected_caption = esc_html( $caption );
		$this->assertContains( '[caption id="attachment_' . $attachment_id . '" width="2000" align="alignnone"]', $conversion );
		$this->assertContains( $expected_caption . '[/caption]', $conversion );

		// Test no attachment
		$shortcode = '[img caption="' . esc_attr( $caption ) . '" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$expected = '[caption ' .
			'width="600" align="alignnone"]<img class="size-large alignnone" />' .
			$expected_caption .
			'[/caption]';
		$this->assertContains( $expected, $conversion );

		// Test invalid attachment
		$shortcode = '[img attachment="9999999" caption="' . esc_attr( $caption ) . '" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$expected = '[caption id="attachment_9999999" ' .
			'width="600" align="alignnone"]<img class="size-large alignnone" data-shortcode-attachment="9999999" />' .
			$expected_caption .
			'[/caption]';
		$this->assertContains( $expected, $conversion );

		// Test cadillac 1
		$shortcode = '[img attachment="' . $attachment_id . '" linkto="attachment" size="full" caption="' . esc_attr( $caption ) . '" align="alignright" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$expected = '[caption id="attachment_' .
			$attachment_id .
			'" width="2000" align="alignright"]<a href="' .
			$expected_href_attr .
			'" ><img class="size-full alignright" src="' .
			$expected_src_attr .
			'" width="2000" height="1125" /></a>' .
			$expected_caption .
			'[/caption]';

		$this->assertContains( $expected, $conversion );

		// Test cadillac 2
		$shortcode = '[img attachment="' . $attachment_id . '" linkto="attachment" size="medium" caption="' . esc_attr( $caption ) . '" align="alignnone" /]';
		$conversion = Img_Shortcode_Data_Migration::convert_img_shortcode_to_tag( $shortcode );
		$expected_src = wp_get_attachment_image_src( $attachment_id, 'medium' );
		$expected_src_attr = $expected_src[0];
		$expected = '[caption id="attachment_' .
			$attachment_id .
			'" width="300" align="alignnone"]<a href="' .
			$expected_href_attr .
			'" ><img class="size-medium alignnone" src="' .
			$expected_src_attr .
			'" width="300" height="169" /></a>' .
			$expected_caption .
			'[/caption]';

		$this->assertContains( $expected, $conversion );

	}

	/**
	 * Case: <img> tags with an external src
	 *
	 */
	function test_img_tag_from_src() {
		$img_tag =
			'<a href="http://go.to/thislink/">' .
				'<img src="' . $this->image_src . '" ' .
				'alt="This is the alt attribute." ' .
				'width="1024" height="540" ' . 
				'class="aligncenter" />' .
			'</a>';

		$post_id = wp_insert_post( array( 'post_content' => "\r\n\r\n$img_tag\r\nblah blah blah" ) );

		$replacements = Img_Shortcode_Data_Migration::find_img_tags_for_replacement_on_post( $post_id );

		$this->assertNotContains( 'attachment="', $replacements[ $img_tag ] );
		$this->assertContains( 'src="' . $this->image_src .'"', $replacements[ $img_tag ] );

	}

	/**
	 * Case: <img> tags wrapped in links
	 *
	 */
	public function test_img_tags_wrapped_in_links() {
		$img_tag = $this->image_tag_from_attachment;

		$img_tag_link_custom =
			'<a href="http://go.to/thislink/">' . $img_tag . '</a>';

		$img_tag_link_file =
			'<a href="' . $this->image_src . '">' . $img_tag . '</a>';

		$img_tag_link_attachment =
			'<a href="' . get_permalink( $this->attachment_id ) . '">' . $img_tag . '</a>';

		$post_content = "$img_tag\r\n$img_tag_link_custom\r\n$img_tag_link_file\r\n$img_tag_link_attachment";

		$post_id = wp_insert_post( array( 'post_content' => $post_content ) );

		$replacements = Img_Shortcode_Data_Migration::find_img_tags_for_replacement_on_post( $post_id );

		foreach ( array( $img_tag, $img_tag_link_custom, $img_tag_link_file, $img_tag_link_attachment ) as $should_be_matched ) {
			$this->assertContains( $should_be_matched, array_keys( $replacements ) );
		}

		$this->assertContains( 'attachment="' . $this->attachment_id .'"', $replacements[ $img_tag ] );
		$this->assertNotContains( 'src="', $replacements[ $img_tag ] );

		$this->assertContains( 'href="http://go.to/thislink/"', $replacements[ $img_tag_link_custom ] );
		$this->assertNotContains( 'linkto=', $replacements[ $img_tag_link_custom ] );

		$this->assertNotContains( 'href=', $replacements[ $img_tag_link_file ] );
		$this->assertContains( 'linkto="file"', $replacements[ $img_tag_link_file ] );

		$this->assertNotContains( 'href=', $replacements[ $img_tag_link_attachment ] );
		$this->assertContains( 'linkto="attachment"', $replacements[ $img_tag_link_attachment ] );
	}


	/**
	 * Case: [caption] shortcodes containing any of the above items
	 *
	 */
	public function test_replace_caption_shortcodes() {
		$caption_no_link = '[caption]' . $this->image_tag_from_src . ' Caption of image without attachment[/caption]';

		$caption_with_link = '[caption width="1024"]' .
			'<a href="' . get_permalink( $this->attachment_id ) . '">' . $this->image_tag_from_attachment . '</a>' .
			' Caption of image linked to attachment page' .
			'[/caption]';

		$post_content = "Post content.\r\n\r\n$caption_no_link\r\n\r\n$caption_with_link";

		$post_id = wp_insert_post( array( 'post_content' => $post_content ) );

		$replacements = Img_Shortcode_Data_Migration::find_caption_shortcodes_for_replacement_on_post( $post_id );

		$this->assertCount( 2, $replacements );
		$this->assertContains( 'caption="Caption of image without attachment"', $replacements[ $caption_no_link ] );
		$this->assertContains( 'caption="Caption of image linked to attachment page"', $replacements[ $caption_with_link ] );
	}


	/**
	 * Helper function: insert an attachment to test properties of.
	 *
	 * @param int $parent_post_id
	 * @param str path to image to use
	 * @param array $post_fields Fields, in the format to be sent to `wp_insert_post()`
	 * @return int Post ID of inserted attachment
	 */
	private function insert_attachment( $parent_post_id = 0, $image = null, $post_fields = array() ) {

		$filename = rand_str().'.jpg';
		$contents = rand_str();

		if ( $image ) {
			// @codingStandardsIgnoreStart
			$filename = basename( $image );
			$contents = file_get_contents( $image );
			// @codingStandardsIgnoreEnd
		}

		$upload = wp_upload_bits( $filename, null, $contents );
		$this->assertTrue( empty( $upload['error'] ) );

		$type = '';
		if ( ! empty( $upload['type'] ) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ( $mime ) {
				$type = $mime['type'];
			}
		}

		$attachment = wp_parse_args( $post_fields,
			array(
				'post_title' => basename( $upload['file'] ),
				'post_content' => 'Test Attachment',
				'post_type' => 'attachment',
				'post_parent' => $parent_post_id,
				'post_mime_type' => $type,
				'guid' => $upload['url'],
			)
		);

		// Save the data
		$id = wp_insert_attachment( $attachment, $upload['file'], $parent_post_id );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $id;
	}

}

