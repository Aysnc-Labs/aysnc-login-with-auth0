<?php
/**
 * Plugin Name: Aysnc Login with Auth0
 * Description: Auth0 Login for WordPress from Aysnc.
 * Author: Aysnc
 * Author URI: https://aysnc.com.au
 * Version: 1.0.0
 * Requires PHP: 8.2
 * License: MIT
 *
 * @package aysnc/aysnc-login-with-auth0
 */

namespace Aysnc\Auth0Login;

// Check for bundled autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Register activation hook.
register_activation_hook( __FILE__, [ 'Aysnc\Auth0Login\SecretLoginLink', 'flush_permalinks' ] );

// Load modules.
add_action( 'plugins_loaded', [ 'Aysnc\Auth0Login\Admin', 'bootstrap' ] );
add_action( 'plugins_loaded', [ 'Aysnc\Auth0Login\SecretLoginLink', 'bootstrap' ] );
add_action( 'plugins_loaded', [ 'Aysnc\Auth0Login\Auth0Login', 'bootstrap' ] );
