<?php
/**
 * Test suite for Secret Login Link.
 *
 * @package aysnc/aysnc-login-with-auth0
 */

namespace Aysnc\Auth0Login;

use WP;
use WP_Rewrite;
use WP_UnitTestCase;

/**
 * Class TestSecretLoginLink.
 */
class TestSecretLoginLink extends WP_UnitTestCase {

	/**
	 * Test bootstrap.
	 *
	 * @covers SecretLoginLink::bootstrap()
	 * @covers SecretLoginLink::get_token()
	 *
	 * @return void
	 */
	public function test_bootstrap(): void {
		// Test without token.
		$this->assertEquals( '', SecretLoginLink::get_token() );
		$this->assertFalse( has_action( 'init', [ SecretLoginLink::class, 'register_rewrite_endpoint' ] ) );
		$this->assertFalse( has_action( 'wp', [ SecretLoginLink::class, 'handle_secret_link' ] ) );
		$this->assertFalse( has_action( 'init', [ SecretLoginLink::class, 'disable_admin_login' ] ) );

		// Add token to options.
		update_option( 'aysnc_auth0_secret_login_token', 'abc' );
		$this->assertEquals( 'abc', SecretLoginLink::get_token() );

		// Test hooks now.
		SecretLoginLink::bootstrap();
		$this->assertEquals(
			10,
			has_action( 'init', [ SecretLoginLink::class, 'register_rewrite_endpoint' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'wp', [ SecretLoginLink::class, 'handle_secret_link' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'init', [ SecretLoginLink::class, 'disable_admin_login' ] )
		);

		// Add token to config.
		define( 'AYSNC_AUTH0_SECRET_LOGIN_TOKEN', 'abcdef' );
		$this->assertEquals( 'abcdef', SecretLoginLink::get_token( force: true ) );
	}

	/**
	 * Test getting link.
	 *
	 * @covers SecretLoginLink::get_link()
	 *
	 * @return void
	 */
	public function test_get_link(): void {
		$this->assertEquals( 'http://test.aysncwordpress.com/abcdef', SecretLoginLink::get_link() );
	}

	/**
	 * Test register_rewrite_endpoint method.
	 *
	 * @covers SecretLoginLink::register_rewrite_endpoint()
	 *
	 * @return void
	 */
	public function test_register_rewrite_endpoint(): void {
		// Get rewrite object.
		global $wp_rewrite;
		$this->assertTrue( $wp_rewrite instanceof WP_Rewrite );

		// Assert that rule does not exist.
		$regex = '^(' . SecretLoginLink::get_token() . ')/?$';
		$this->assertFalse( array_key_exists( $regex, $wp_rewrite->extra_rules_top ) );

		// Add rewrite rules.
		SecretLoginLink::register_rewrite_endpoint();

		// Assert rule now exists.
		$this->assertTrue( array_key_exists( $regex, $wp_rewrite->extra_rules_top ) );
	}

	/**
	 * Test handle_secret_link method.
	 *
	 * @covers SecretLoginLink::handle_secret_link()
	 *
	 * @return void
	 */
	public function test_handle_secret_link(): void {
		// Prepare variables.
		$wp           = new WP();
		$action_fired = false;

		// Add a listener for the hook.
		add_action(
			'aysnc_secret_link_visited',
			function () use ( &$action_fired ) {
				$action_fired = true;
			}
		);

		// Test with no query vars.
		SecretLoginLink::handle_secret_link( $wp );
		$this->assertFalse( $action_fired );

		// Test with wrong query var.
		$wp->query_vars['aysnc_secret_login_token'] = 'test';
		SecretLoginLink::handle_secret_link( $wp );
		$this->assertFalse( $action_fired ); // @phpstan-ignore-line

		// Test with correct query var.
		$wp->query_vars['aysnc_secret_login_token'] = SecretLoginLink::get_token();
		SecretLoginLink::handle_secret_link( $wp );
		$this->assertTrue( $action_fired ); // @phpstan-ignore-line
	}

	/**
	 * Test disable_admin_login method.
	 *
	 * @covers SecretLoginLink::disable_admin_login()
	 *
	 * @return void
	 */
	public function test_disable_admin_login(): void {
		// Prepare variables.
		$filter_fired    = false;
		$filter_function = function () use ( &$filter_fired ) {
			$filter_fired = true;
			return false;
		};

		// Add a listener for the hook.
		add_filter( 'wp_redirect', $filter_function );

		// Does not redirect by default.
		SecretLoginLink::disable_admin_login();
		$this->assertFalse( $filter_fired );

		// Does not redirect any random URL.
		$_SERVER['REQUEST_URI'] = 'test';
		SecretLoginLink::disable_admin_login();
		$this->assertFalse( $filter_fired ); // @phpstan-ignore-line

		// Does redirect if wp-login.php is found in the URL.
		$_SERVER['REQUEST_URI'] = '/wp-login.php';
		SecretLoginLink::disable_admin_login();
		$this->assertTrue( $filter_fired ); // @phpstan-ignore-line

		// Does redirect if wp-login.php is found in the URL, and if action is anything.
		$filter_fired           = false;
		$_GET['action']         = 'something';
		$_SERVER['REQUEST_URI'] = '/wp-login.php';
		SecretLoginLink::disable_admin_login();
		$this->assertTrue( $filter_fired ); // @phpstan-ignore-line

		// Does not redirect if wp-login.php is found in the URL, and if action is logout.
		$filter_fired           = false;
		$_GET['action']         = 'logout';
		$_SERVER['REQUEST_URI'] = '/wp-login.php';
		SecretLoginLink::disable_admin_login();
		$this->assertFalse( $filter_fired ); // @phpstan-ignore-line

		// Clean up.
		remove_filter( 'wp_redirect', $filter_function );
	}

	/**
	 * Test flush_permalinks method.
	 *
	 * @covers SecretLoginLink::flush_permalinks()
	 *
	 * @return void
	 */
	public function test_flush_permalinks(): void {
		// Get global rewrite.
		global $wp_rewrite;
		$this->assertTrue( $wp_rewrite instanceof WP_Rewrite );

		// First let's assert that the previous test rules have carried over.
		$this->assertTrue( SecretLoginLink::$rewrite_rules_registered );
		$regex = '^(' . SecretLoginLink::get_token() . ')/?$';
		$this->assertTrue( array_key_exists( $regex, $wp_rewrite->extra_rules_top ) );

		// Let's reset everything.
		SecretLoginLink::$rewrite_rules_registered = false;
		unset( $wp_rewrite->extra_rules_top[ $regex ] );
		$this->assertFalse( array_key_exists( $regex, $wp_rewrite->extra_rules_top ) );

		// Flush permalink and test again.
		SecretLoginLink::flush_permalinks();
		$this->assertTrue( SecretLoginLink::$rewrite_rules_registered );
		$this->assertTrue( array_key_exists( $regex, $wp_rewrite->extra_rules_top ) );
	}
}
