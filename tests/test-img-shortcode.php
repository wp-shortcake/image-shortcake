<?php

class Test_Img_Shortcode extends WP_UnitTestCase {

	function test_construct_ui() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}


	/*
	 * Simplest case: An [img] shortcode with a url passed as a src argument
	 * should just render an image with that src.
	 */
	function test_img_shortcode_with_src_tag() {
		$str = '[img src="http://example.com/example.jpg" align="left" /]';
		$content = apply_filters( 'the_content', $str );

		$this->assertContains( '<img class="size-full alignleft" src="http://example.com/example.jpg" />', $content );
	}
}

