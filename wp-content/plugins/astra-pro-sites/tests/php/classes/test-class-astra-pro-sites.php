<?php
/**
 * Class Astra Pro Sites
 *
 * @package Astra_Pro_Sites
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Astra Pro Sites.
 */
class Test_Astra_Pro_Sites extends TestCase {

	/**
	 * Tests actions and filters.
	 */
	public function test_hooks() {

		// Test actions with priority.
		$this->assertSame( 1, has_action( 'admin_init', array( Astra_Pro_Sites::set_instance(), 'admin_notices' ) ) );
		$this->assertSame( 10, has_action( 'plugins_loaded', array( Astra_Pro_Sites::set_instance(), 'load_textdomain' ) ) );

		// Test filters with priority.
		$this->assertSame( 10, has_filter( 'astra_sites_localize_vars', array( Astra_Pro_Sites::set_instance(), 'update_vars' ) ) );
		$this->assertSame( 10, has_filter( 'astra_sites_render_localize_vars', array( Astra_Pro_Sites::set_instance(), 'update_vars' ) ) );
		$this->assertSame( 10, has_filter( 'astra_sites_api_params', array( Astra_Pro_Sites::set_instance(), 'api_request_params' ) ) );
		$this->assertSame( 10, has_filter( 'astra_sites_menu_page_title', array( Astra_Pro_Sites::set_instance(), 'page_title' ) ) );

	}
}
