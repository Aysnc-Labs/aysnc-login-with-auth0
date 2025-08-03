<?php
/**
 * Auth0: Secret Login Link.
 *
 * @package aysnc/aysnc-login-with-auth0
 */

namespace Aysnc\Auth0Login;

use WP;

/**
 * Secret Login Link class.
 */
class SecretLoginLink {

	/**
	 * The login token.
	 *
	 * @var string $token Login token.
	 */
	protected static string $token = '';

	/**
	 * Flag to keep track of whether rewrite rules are registered.
	 *
	 * @var bool $rewrite_rules_registered Rewrite rules registered.
	 */
	public static bool $rewrite_rules_registered = false;

	/**
	 * Bootstrap this module.
	 *
	 * @return void
	 */
	public static function bootstrap(): void {
		// Bail early if we don't have a token.
		if ( empty( self::get_token() ) ) {
			return;
		}

		// Hooks.
		add_action( 'init', [ __CLASS__, 'register_rewrite_endpoint' ] );
		add_action( 'wp', [ __CLASS__, 'handle_secret_link' ] );
		add_action( 'init', [ __CLASS__, 'disable_admin_login' ] );
	}

	/**
	 * Get token.
	 *
	 * @param bool $force Force get token.
	 *
	 * @return string
	 */
	public static function get_token( bool $force = false ): string {
		// Check if we already have a token.
		if ( empty( self::$token ) || true === $force ) {
			// First check for constant.
			if ( defined( 'AYSNC_AUTH0_SECRET_LOGIN_TOKEN' ) && is_string( AYSNC_AUTH0_SECRET_LOGIN_TOKEN ) ) {
				self::$token = AYSNC_AUTH0_SECRET_LOGIN_TOKEN;
			} else {
				// Fall back to option value.
				$token = get_option( 'aysnc_auth0_secret_login_token', '' );
				if ( is_string( $token ) ) {
					self::$token = $token;
				}
			}
		}

		// Return token.
		return self::$token;
	}

	/**
	 * Get the secret login link URL.
	 *
	 * @return string The secret login link URL.
	 */
	public static function get_link(): string {
		return home_url( self::get_token() );
	}

	/**
	 * Register rewrite endpoint for secret login link.
	 *
	 * @param bool $force Force register rules.
	 *
	 * @return void
	 */
	public static function register_rewrite_endpoint( bool $force = false ): void {
		// Bail early if we don't have a token.
		if (
			( empty( self::get_token() ) || false !== self::$rewrite_rules_registered )
			&& false === $force
		) {
			return;
		}

		add_rewrite_tag( '%aysnc_secret_login_token%', '([^&]+)' );
		add_rewrite_rule( '^(' . self::get_token() . ')/?$', 'index.php?aysnc_secret_login_token=' . self::get_token(), 'top' );
		self::$rewrite_rules_registered = true;
	}

	/**
	 * Handle the secret link visit.
	 *
	 * @param WP $wp The WP object passed by the 'wp' action.
	 *
	 * @return void
	 */
	public static function handle_secret_link( WP $wp ): void {
		// Check if the current request matches our secret token.
		if (
			! empty( $wp->query_vars['aysnc_secret_login_token'] )
			&& self::get_token() === $wp->query_vars['aysnc_secret_login_token']
		) {
			// It does!
			// This module just fires a hook if a secret URL is found!
			do_action( 'aysnc_secret_link_visited' );
		}
	}

	/**
	 * Disable regular WordPress admin login.
	 *
	 * @return void
	 */
	public static function disable_admin_login(): void {
		// Block requests to wp-login.php
		if (
			! empty( $_SERVER['REQUEST_URI'] )
			&& is_string( $_SERVER['REQUEST_URI'] )
			&& str_contains( $_SERVER['REQUEST_URI'], 'wp-login.php' ) // phpcs:ignore
		) {
			// Except if we're not logging out.
			if ( isset( $_GET['action'] ) && 'logout' === $_GET['action'] ) { // phpcs:ignore
				return;
			}

			// Redirect others to the home page.
			$redirected = wp_safe_redirect( home_url() );
			if ( true === $redirected ) {
				exit;
			}
		}
	}

	/**
	 * Flush permalinks by adding custom endpoint first.
	 *
	 * @return void
	 */
	public static function flush_permalinks(): void {
		self::register_rewrite_endpoint( true );
		flush_rewrite_rules();
	}
}
