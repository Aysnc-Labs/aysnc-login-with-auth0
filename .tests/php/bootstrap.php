<?php
/**
 * Bootstrap for unit tests.
 *
 * @package aysnc/aysnc-auth0-login
 */

namespace Aysnc\Auth0Login;

// Composer Autoloader.
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Load test environment.
 *
 * @return void
 */
function load_environment(): void {
	// Table prefix for test database.
	global $table_prefix;
	$table_prefix = 'wptests_'; // phpcs:ignore

	// Options.
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'blogname', 'Aysnc Labs' );
	update_option( 'admin_email', 'test@aysnc.dev' );

	// Activate plugins.
	$plugins_to_activate = [];

	// Update active plugins.
	update_option( 'active_plugins', $plugins_to_activate );

	// Load this plugin.
	require __DIR__ . '/../../plugin.php';
}

// Load PHPUnit functions.
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';

// Load the test environment.
tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\\load_environment' );

// Bootstrap PHPUnit tests.
require_once getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
