<?php
/**
 * Test Config.
 */

// Modify the $_SERVER global for testing environment.
$_SERVER['HTTP_HOST'] = 'test.aysncwordpress.com';

// Define database constants.
define( 'DB_NAME', 'aysnc-tests' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', '0.0.0.0' );

// Defined required constants for testing environment.
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_TESTS_DOMAIN', 'localhost' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WP_TESTS_MULTISITE', false );
define( 'WP_TESTS', true );
define( 'WP_RUN_CORE_TESTS', false );

// Define Site URL.
if ( ! defined( 'WP_SITEURL' ) ) {
	define( 'WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST'] );
}

// Define Home URL.
if ( ! defined( 'WP_HOME' ) ) {
	define( 'WP_HOME', 'http://' . $_SERVER['HTTP_HOST'] );
}

// Define Path & URL for Content.
define( 'WP_CONTENT_DIR', dirname( __DIR__ ) . '/../wp-content' );
define( 'WP_CONTENT_URL', WP_HOME . '/wp-content' );

// Create "uploads" needed for tests.
if ( ! is_dir( WP_CONTENT_DIR . '/uploads' ) ) {
	mkdir( WP_CONTENT_DIR . '/uploads' ); // phpcs:ignore
}

// Somehow WP is not creating directories recursively.
// Add Month and Year directories.
if ( ! is_dir( WP_CONTENT_DIR . '/uploads/' . gmdate( 'Y' ) ) ) {
	mkdir( WP_CONTENT_DIR . '/uploads/' . gmdate( 'Y' ) ); // phpcs:ignore
}

// Add Month directory.
if ( ! is_dir( WP_CONTENT_DIR . '/uploads/' . gmdate( 'Y' ) . '/' . gmdate( 'm' ) ) ) {
	mkdir( WP_CONTENT_DIR . '/uploads/' . gmdate( 'Y' ) . '/' . gmdate( 'm' ) ); // phpcs:ignore
}

// Prevent editing of files through WP Admin.
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', true );

// Absolute path to the WordPress directory.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/../wp/' );
}
