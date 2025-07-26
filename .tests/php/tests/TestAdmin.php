<?php
/**
 * Test suite for Admin.
 *
 * @package aysnc/aysnc-auth0-login
 */

namespace Aysnc\Auth0Login;

use WP_UnitTestCase;

/**
 * Class TestAdmin.
 */
class TestAdmin extends WP_UnitTestCase {

	/**
	 * Test bootstrap.
	 *
	 * @covers Admin::bootstrap()
	 *
	 * @return void
	 */
	public function test_bootstrap(): void {
		$this->assertEquals(
			10,
			has_action( 'admin_init', [ Admin::class, 'register_settings' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'admin_menu', [ Admin::class, 'add_options_page' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'admin_init', [ Admin::class, 'handle_flush_permalinks_on_save' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'admin_init', [ Admin::class, 'handle_flush_permalinks' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'admin_notices', [ Admin::class, 'show_permalink_notice' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'admin_head', [ Admin::class, 'add_required_field_styles' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'admin_enqueue_scripts', [ Admin::class, 'enqueue_admin_scripts' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'add_option_aysnc_auth0_secret_login_token', [ Admin::class, 'flush_permalinks_on_save' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'update_option_aysnc_auth0_secret_login_token', [ Admin::class, 'flush_permalinks_on_save' ] )
		);
	}
}
