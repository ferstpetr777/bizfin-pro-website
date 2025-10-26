<?php
/**
 * Class Astra Pro Sites
 *
 * @package Astra_Pro_Sites
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Astra Sites Functions.
 */
class Test_Astra_Sites_Functions extends TestCase {

	/**
	 * Tests actions and filters.
	 */
	public function test_validate_urls() {

		// Validate live site host.
		$this->assertTrue( astra_sites_is_valid_url( 'https://websitedemos.net/' ) );

		// Check ONLY host, avoid protocol.
		$this->assertTrue( astra_sites_is_valid_url( 'http://websitedemos.net/' ) );

		// Invalid host.
		$this->assertFalse( astra_sites_is_valid_url( 'https://example.com/' ) );

		// Google User Content Host.
		$this->assertTrue( astra_sites_is_valid_url( 'https://lh3.googleusercontent.com/' ) );
		$this->assertTrue( astra_sites_is_valid_url( 'http://lh3.googleusercontent.com/' ) );

		// Pixabay URL.
		$this->assertTrue( astra_sites_is_valid_url( 'https://pixabay.com/' ) );
		$this->assertTrue( astra_sites_is_valid_url( 'http://pixabay.com/' ) );

	}
}
