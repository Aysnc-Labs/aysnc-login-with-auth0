<?php
/**
 * Test suite for Auth0.
 *
 * @package aysnc/aysnc-auth0-login
 */

namespace Aysnc\Auth0Login;

use Auth0\SDK\Auth0;
use WP_UnitTestCase;

/**
 * Class TestAuth0SSO.
 */
class TestAuth0SSO extends WP_UnitTestCase {

	/**
	 * Test bootstrap.
	 *
	 * @covers Auth0Login::bootstrap()
	 *
	 * @return void
	 */
	public function test_bootstrap(): void {
		$this->assertEquals(
			10,
			has_action( 'aysnc_secret_link_visited', [ Auth0Login::class, 'handle_secret_link_visit' ] )
		);
		$this->assertEquals(
			10,
			has_action( 'allowed_redirect_hosts', [ Auth0Login::class, 'add_auth0_to_safe_hosts' ] )
		);
	}

	/**
	 * Test get_client method.
	 *
	 * @covers Auth0Login::get_client()
	 * @covers Auth0Login::get_cookie_secret()
	 * @covers Auth0Login::get_domain()
	 * @covers Auth0Login::get_client_id()
	 * @covers Auth0Login::get_client_secret()
	 * @covers Auth0Login::add_auth0_to_safe_hosts()
	 * @covers Auth0Login::handle_secret_link_visit()
	 * @covers Auth0Login::reset()
	 *
	 * @return void
	 */
	public function test_get_client(): void {
		// Test default values.
		$this->assertNull( Auth0Login::get_client() );
		$this->assertNull( Auth0Login::get_domain() );
		$this->assertNull( Auth0Login::get_client_id() );
		$this->assertNull( Auth0Login::get_client_secret() );
		$this->assertEquals( AUTH_KEY, Auth0Login::get_cookie_secret() );
		$this->assertEquals( [ 'test' ], Auth0Login::add_auth0_to_safe_hosts( [ 'test' ] ) );

		// Prepare variables.
		$redirected_home      = false;
		$redirected_to_auth_0 = false;
		$filter_function      = function ( string $url = '' ) use ( &$redirected_home, &$redirected_to_auth_0 ) {
			if ( str_contains( $url, 'auth0' ) ) {
				$redirected_to_auth_0 = true;
				$redirected_home      = false;
			} else {
				$redirected_to_auth_0 = false;
				$redirected_home      = true;
			}
			return false;
		};
		$action_function      = function () use ( &$redirected_to_auth_0 ) {
			$redirected_to_auth_0 = true;
		};

		// Add a listeners for the hooks.
		add_filter( 'wp_redirect', $filter_function );
		add_action( 'aysnc_auth0_sso_secret_link_error', $action_function );

		// Test with just auth key.
		Auth0Login::reset();
		$this->assertNull( Auth0Login::get_client() );
		Auth0Login::handle_secret_link_visit();
		$this->assertTrue( $redirected_home );
		$this->assertFalse( $redirected_to_auth_0 );

		// Update values.
		// But add an invalid domain.
		update_option( 'aysnc_auth0_domain', 'domain' );
		update_option( 'aysnc_auth0_client_id', 'client-id' );
		update_option( 'aysnc_auth0_client_secret', 'client-secret' );
		Auth0Login::reset();
		$this->assertEquals( 'domain', Auth0Login::get_domain() );
		$this->assertEquals( 'client-id', Auth0Login::get_client_id() );
		$this->assertEquals( 'client-secret', Auth0Login::get_client_secret() );
		$this->assertNull( Auth0Login::get_client() );
		$this->assertEquals( [ 'test', 'domain' ], Auth0Login::add_auth0_to_safe_hosts( [ 'test' ] ) );
		$redirected_home      = false;
		$redirected_to_auth_0 = false;
		Auth0Login::handle_secret_link_visit();
		$this->assertTrue( $redirected_home ); // @phpstan-ignore-line
		$this->assertFalse( $redirected_to_auth_0 ); // @phpstan-ignore-line

		// Update domain to a valid one.
		update_option( 'aysnc_auth0_domain', 'dev-my-app.us.auth0.com' );
		Auth0Login::reset();
		$this->assertEquals( 'dev-my-app.us.auth0.com', Auth0Login::get_domain() );
		$this->assertTrue( Auth0Login::get_client() instanceof Auth0 ); // @phpstan-ignore-line
		$this->assertEquals( [ 'test', 'dev-my-app.us.auth0.com' ], Auth0Login::add_auth0_to_safe_hosts( [ 'test' ] ) );
		$redirected_home      = false;
		$redirected_to_auth_0 = false;
		Auth0Login::handle_secret_link_visit();
		$this->assertFalse( $redirected_home ); // @phpstan-ignore-line
		$this->assertTrue( $redirected_to_auth_0 ); // @phpstan-ignore-line

		// Delete options.
		delete_option( 'aysnc_auth0_domain' );
		delete_option( 'aysnc_auth0_client_id' );
		delete_option( 'aysnc_auth0_client_secret' );
		Auth0Login::reset();
		$this->assertNull( Auth0Login::get_domain() );
		$this->assertNull( Auth0Login::get_client_id() );
		$this->assertNull( Auth0Login::get_client_secret() );
		$this->assertNull( Auth0Login::get_client() );
		$this->assertEquals( [ 'test' ], Auth0Login::add_auth0_to_safe_hosts( [ 'test' ] ) );

		// Try with WP Config.
		define( 'AYSNC_AUTH0_DOMAIN', 'dev-my-app.us.auth0.com' );
		define( 'AYSNC_AUTH0_CLIENT_ID', 'client-id' );
		define( 'AYSNC_AUTH0_CLIENT_SECRET', 'client-secret' );
		Auth0Login::reset();
		$this->assertEquals( 'dev-my-app.us.auth0.com', Auth0Login::get_domain() );
		$this->assertEquals( 'client-id', Auth0Login::get_client_id() );
		$this->assertEquals( 'client-secret', Auth0Login::get_client_secret() );
		$this->assertTrue( Auth0Login::get_client() instanceof Auth0 ); // @phpstan-ignore-line
		$this->assertEquals( [ 'test', 'dev-my-app.us.auth0.com' ], Auth0Login::add_auth0_to_safe_hosts( [ 'test' ] ) );
		$redirected_home      = false;
		$redirected_to_auth_0 = false;
		Auth0Login::handle_secret_link_visit();
		$this->assertFalse( $redirected_home ); // @phpstan-ignore-line
		$this->assertTrue( $redirected_to_auth_0 ); // @phpstan-ignore-line

		// Clean up.
		remove_filter( 'wp_redirect', $filter_function );
		remove_action( 'aysnc_auth0_sso_secret_link_error', $action_function );
	}
}
