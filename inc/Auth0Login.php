<?php
/**
 * Auth0: Single Sign-On.
 *
 * @package aysnc/aysnc-login-with-auth0
 */

namespace Aysnc\Auth0Login;

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\StateException;
use Exception;
use WP_User;

/**
 * Auth0 Login class.
 */
class Auth0Login {

	/**
	 * Auth0 SDK Instance.
	 *
	 * @var ?Auth0
	 */
	protected static ?Auth0 $client = null;

	/**
	 * Auth0 Domain.
	 *
	 * @var string|null Domain.
	 */
	protected static ?string $domain = null;

	/**
	 * Auth0 Client ID.
	 *
	 * @var string|null Client ID.
	 */
	protected static ?string $client_id = null;

	/**
	 * Auth0 Client Secret.
	 *
	 * @var string|null Client Secret.
	 */
	protected static ?string $client_secret = null;

	/**
	 * Bootstrap this module.
	 *
	 * @return void
	 */
	public static function bootstrap(): void {
		// Hooks.
		add_action( 'aysnc_secret_link_visited', [ __CLASS__, 'handle_secret_link_visit' ] );
		add_filter( 'allowed_redirect_hosts', [ __CLASS__, 'add_auth0_to_safe_hosts' ] );
	}

	/**
	 * Get Auth0 client.
	 *
	 * @return Auth0|null
	 */
	public static function get_client(): ?Auth0 {
		// Check if client is already initialised.
		if ( self::$client instanceof Auth0 ) {
			return self::$client;
		}

		// Get config.
		$domain        = self::get_domain();
		$client_id     = self::get_client_id();
		$client_secret = self::get_client_secret();

		// Bail early if we don't have a client ID, client secret, or domain.
		if (
			! defined( 'AUTH_KEY' )
			|| empty( $domain )
			|| empty( $client_id )
			|| empty( $client_secret )
		) {
			return null;
		}

		// Get or create cookie secret
		$cookie_secret = self::get_cookie_secret();

		// Set client up.
		try {
			$configuration = new SdkConfiguration(
				domain: $domain,
				clientId: $client_id,
				redirectUri: SecretLoginLink::get_link(),
				clientSecret: $client_secret,
				scope: [ 'openid', 'profile', 'email' ],
				cookieSecret: $cookie_secret,
			);

			self::$client = new Auth0( $configuration );
		} catch ( Exception $e ) {
			// We encountered an error, let's reset and trigger an action.
			self::$client = null;
			do_action( 'aysnc_auth0_sso_client_error', $e );
		}

		// Return built client.
		return self::$client;
	}

	/**
	 * Get domain.
	 *
	 * @return string|null
	 */
	public static function get_domain(): ?string {
		if ( ! empty( self::$domain ) ) {
			return self::$domain;
		}

		if ( defined( 'AYSNC_AUTH0_DOMAIN' ) && is_string( AYSNC_AUTH0_DOMAIN ) ) {
			self::$domain = AYSNC_AUTH0_DOMAIN;
		} else {
			$domain = get_option( 'aysnc_auth0_domain', null );
			if ( is_string( $domain ) ) {
				self::$domain = $domain;
			}
		}

		return self::$domain;
	}

	/**
	 * Get client ID.
	 *
	 * @return string|null
	 */
	public static function get_client_id(): ?string {
		if ( ! empty( self::$client_id ) ) {
			return self::$client_id;
		}

		if ( defined( 'AYSNC_AUTH0_CLIENT_ID' ) && is_string( AYSNC_AUTH0_CLIENT_ID ) ) {
			self::$client_id = AYSNC_AUTH0_CLIENT_ID;
		} else {
			$client_id = get_option( 'aysnc_auth0_client_id', null );
			if ( is_string( $client_id ) ) {
				self::$client_id = $client_id;
			}
		}

		return self::$client_id;
	}

	/**
	 * Get client secret.
	 *
	 * @return string|null
	 */
	public static function get_client_secret(): ?string {
		if ( ! empty( self::$client_secret ) ) {
			return self::$client_secret;
		}

		if ( defined( 'AYSNC_AUTH0_CLIENT_SECRET' ) && is_string( AYSNC_AUTH0_CLIENT_SECRET ) ) {
			self::$client_secret = AYSNC_AUTH0_CLIENT_SECRET;
		} else {
			$client_secret = get_option( 'aysnc_auth0_client_secret', null );
			if ( is_string( $client_secret ) ) {
				self::$client_secret = $client_secret;
			}
		}

		return self::$client_secret;
	}

	/**
	 * Add Auth0 domain to safe redirect hosts.
	 *
	 * @param string[] $hosts Array of allowed hosts.
	 *
	 * @return string[]
	 */
	public static function add_auth0_to_safe_hosts( array $hosts = [] ): array {
		$domain = self::get_domain();

		if ( ! empty( $domain ) ) {
			$hosts[] = $domain;
		}

		return $hosts;
	}

	/**
	 * Handle secret link visit.
	 *
	 * @return void
	 */
	public static function handle_secret_link_visit(): void {
		// Check for client.
		$client = self::get_client();
		if ( ! $client instanceof Auth0 ) {
			$redirected = wp_safe_redirect( home_url() );
			if ( true === $redirected ) {
				exit;
			}
			return;
		}

		try {
			// If user is already logged in, redirect to admin.
			if ( is_user_logged_in() ) {
				$redirected = wp_safe_redirect( admin_url() );
				if ( true === $redirected ) {
					exit;
				}
			}

			// Check if this is an Auth0 OAuth callback (has code parameter)
			if ( ! empty( $_GET['code'] ) && ! empty( $_GET['state'] ) ) { // phpcs:ignore
				self::process_auth0_callback();
				return;
			}

			// This is an initial visit - redirect to Auth0 login page.
			$auth_url   = $client->login();
			$redirected = wp_safe_redirect( $auth_url );
			if ( true === $redirected ) {
				exit;
			}
		} catch ( Exception $e ) {
			do_action( 'aysnc_auth0_sso_secret_link_error', $e );
		}
	}

	/**
	 * Get or create a cookie secret for Auth0 SDK.
	 *
	 * @return string The cookie secret.
	 */
	public static function get_cookie_secret(): string {
		// Use WordPress AUTH_KEY by default.
		if ( defined( 'AUTH_KEY' ) ) {
			return (string) AUTH_KEY;
		}

		// No auth key found!
		return '';
	}

	/**
	 * Process Auth0 OAuth callback.
	 *
	 * @return void
	 */
	public static function process_auth0_callback(): void {
		// Check for client.
		$client = self::get_client();
		if ( ! $client instanceof Auth0 ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		try {
			// Exchange authorization code for tokens
			$client->exchange();

			// Get user info from ID token
			$user_info = $client->getUser();

			if ( empty( $user_info ) || empty( $user_info['email'] ) || ! is_string( $user_info['email'] ) ) {
				throw new Exception( 'Unable to get user information from Auth0' );
			}

			// Attempt to get a matching user.
			$user = get_user_by( 'email', $user_info['email'] );

			if ( $user instanceof WP_User ) {
				// We found a user, log them in and redirect to admin.
				do_action( 'aysnc_auth0_sso_success', $user, $user_info );
				wp_set_current_user( $user->ID );
				wp_set_auth_cookie( $user->ID, true );
				wp_safe_redirect( admin_url() );
			} else {
				// Matching user not found, redirect to home page.
				do_action( 'aysnc_auth0_sso_fail', $user_info );
				wp_safe_redirect( home_url() );
			}
		} catch ( StateException $e ) {
			// State validation failed
			do_action( 'aysnc_auth0_sso_error', 'State validation failed: ' . $e->getMessage() );
			wp_safe_redirect( home_url() );
		} catch ( Exception $e ) {
			// There was a problem, redirect to home page.
			do_action( 'aysnc_auth0_sso_error', $e->getMessage() );
			wp_safe_redirect( home_url() );
		}

		// Exit, since we're redirecting.
		exit;
	}

	/**
	 * Reset this class.
	 *
	 * @return void
	 */
	public static function reset(): void {
		self::$client        = null;
		self::$domain        = null;
		self::$client_id     = null;
		self::$client_secret = null;
	}
}
