<?php

/**
 * @group  webfonts
 * @covers WP_Webfonts_Test
 */
class WP_Webfonts_Test extends WP_UnitTestCase {

	/**
	 * @covers wp_register_webfonts
	 * @covers WP_Webfonts::register_font
	 * @covers WP_Webfonts::get_fonts
	 * @covers WP_Webfonts::get_font_id
	 */
	public function test_get_fonts() {
		$fonts = array(
			array(
				'provider'     => 'local',
				'font-family'  => 'Source Serif Pro',
				'font-style'   => 'normal',
				'font-weight'  => '200 900',
				'font-stretch' => 'normal',
				'src'          => 'https://example.com/assets/fonts/source-serif-pro/SourceSerif4Variable-Roman.ttf.woff2',
				'font-display' => 'fallback',
			),
			array(
				'provider'     => 'local',
				'font-family'  => 'Source Serif Pro',
				'font-style'   => 'italic',
				'font-weight'  => '200 900',
				'font-stretch' => 'normal',
				'src'          => 'https://example.com/assets/fonts/source-serif-pro/SourceSerif4Variable-Italic.ttf.woff2',
				'font-display' => 'fallback',
			),
		);

		$expected = array(
			'source-serif-pro-200-900-normal-local' => $fonts[0],
			'source-serif-pro-200-900-italic-local' => $fonts[1],
		);

		wp_register_webfonts( $fonts );
		$this->assertEquals( $expected, wp_webfonts()->get_fonts() );
	}

	/**
	 * @covers wp_register_webfont
	 * @covers WP_Webfonts::register_provider
	 * @covers WP_Webfonts::get_providers
	 */
	public function test_get_providers() {
		wp_register_webfont_provider( 'test-provider', 'Test_Provider' );
		$this->assertEquals(
			array(
				'local'         => 'WP_Webfonts_Provider_Local',
				'test-provider' => 'Test_Provider',
			),
			wp_get_webfont_providers()
		);
	}

	/**
	 * @covers WP_Webfonts::validate_font
	 */
	public function test_validate_font() {
		// Test empty array.
		$this->assertFalse( wp_webfonts()->validate_font( array() ) );

		$font = array(
			'font-family' => 'Test Font 1',
			'src'         => 'https://example.com/assets/fonts/source-serif-pro/SourceSerif4Variable-Roman.ttf.woff2',
		);

		// Test missing provider fallback to local.
		$this->assertEquals( 'local', wp_webfonts()->validate_font( $font )['provider'] );

		// Test missing font-weight fallback to 400.
		$this->assertEquals( '400', wp_webfonts()->validate_font( $font )['font-weight'] );

		// Test missing font-style fallback to normal.
		$this->assertEquals( 'normal', wp_webfonts()->validate_font( $font )['font-style'] );

		// Test missing font-display fallback to fallback.
		$this->assertEquals( 'fallback', wp_webfonts()->validate_font( $font )['font-display'] );

		// Test local font with missing "src".
		$this->assertFalse( wp_webfonts()->validate_font( array( 'font-family' => 'Test Font 2' ) ) );

		// Test malformatted src.
		$invalid_src_values = array(
			'',                                           // Empty string.
			array(),                                      // EMpty array.
			10,                                           // Not a string or array.
			array( '', 'https://example.com/font.woff2' ), // Array containing an empty string.
			'invalid-url',                                // Not a valid URL.
		);
		foreach ( $invalid_src_values as $invalid_src_value ) {
			$font['src'] = $invalid_src_value;
			$this->assertFalse( wp_webfonts()->validate_font( $font ) );
		}

		// Test valid src URL, without a protocol.
		$font['src'] = '//example.com/SourceSerif4Variable-Roman.ttf.woff2';
		$this->assertEquals( wp_webfonts()->validate_font( $font )['src'], $font['src'] );

		// Test valid font.
		$this->assertNotEmpty( wp_webfonts()->validate_font( $font ) );

		// Test font-style.
		$font['font-style'] = 'invalid';
		$this->assertFalse( wp_webfonts()->validate_font( $font ) );
		$font['font-style'] = 'italic';
		$this->assertNotEmpty( wp_webfonts()->validate_font( $font ) );

		// Test font-weight.
		$font_weights = array(
			'invalid' => array( 'invalid', '', '100-900' ),
			'valid'   => array( 100, '100', '100 900', 'normal' ),
		);
		foreach ( $font_weights['invalid'] as $value ) {
			$font['font-weight'] = $value;
			$this->assertFalse( wp_webfonts()->validate_font( $font ) );
		}
		foreach ( $font_weights['valid'] as $value ) {
			$font['font-weight'] = $value;
			$this->assertEquals( wp_webfonts()->validate_font( $font )['font-weight'], $value );
		}

		// Test that invalid keys get removed from the font.
		$font['invalid-key'] = 'invalid';
		$this->assertArrayNotHasKey( 'invalid-key', wp_webfonts()->validate_font( $font ) );
	}

	/**
	 * @covers WP_Webfonts::generate_styles
	 */
	public function test_generate_styles() {
		$this->assertEquals(
			wp_webfonts()->generate_styles(),
			'@font-face{font-family:"Source Serif Pro";font-style:normal;font-weight:200 900;font-display:fallback;font-stretch:normal;src:local("Source Serif Pro"), url(\'https://example.com/assets/fonts/source-serif-pro/SourceSerif4Variable-Roman.ttf.woff2\') format(\'woff2\');}@font-face{font-family:"Source Serif Pro";font-style:italic;font-weight:200 900;font-display:fallback;font-stretch:normal;src:local("Source Serif Pro"), url(\'https://example.com/assets/fonts/source-serif-pro/SourceSerif4Variable-Italic.ttf.woff2\') format(\'woff2\');}'
		);
	}
}
