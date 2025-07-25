<?php
/**
 * Auth0: Admin.
 *
 * @package aysnc/aysnc-auth0-login
 */

namespace Aysnc\Auth0Login;

/**
 * Admin class.
 */
class Admin {

	/**
	 * Option group name.
	 *
	 * @var string
	 */
	public static string $option_group = 'aysnc_auth0_options';

	/**
	 * Bootstrap this module.
	 *
	 * @return void
	 */
	public static function bootstrap(): void {
		// Admin stuff.
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
		add_action( 'admin_menu', [ __CLASS__, 'add_options_page' ] );
		add_action( 'admin_init', [ __CLASS__, 'handle_flush_permalinks_on_save' ] );
		add_action( 'admin_init', [ __CLASS__, 'handle_flush_permalinks' ] );
		add_action( 'admin_notices', [ __CLASS__, 'show_permalink_notice' ] );
		add_action( 'admin_head', [ __CLASS__, 'add_required_field_styles' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_scripts' ] );

		// Flush permalinks when login token setting is updated.
		add_action( 'add_option_aysnc_auth0_secret_login_token', [ __CLASS__, 'flush_permalinks_on_save' ], 10, 2 );
		add_action( 'update_option_aysnc_auth0_secret_login_token', [ __CLASS__, 'flush_permalinks_on_save' ], 10, 2 );
	}

	/**
	 * Check if all Auth0 config values are defined in wp-config.php.
	 *
	 * @return bool True if all values are defined, false otherwise.
	 */
	public static function all_wp_config_values_defined(): bool {
		return defined( 'AYSNC_AUTH0_DOMAIN' )
			&& defined( 'AYSNC_AUTH0_CLIENT_ID' )
			&& defined( 'AYSNC_AUTH0_CLIENT_SECRET' )
			&& defined( 'AYSNC_AUTH0_SECRET_LOGIN_TOKEN' );
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		// Register settings.
		register_setting(
			self::$option_group,
			'aysnc_auth0_domain',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);

		register_setting(
			self::$option_group,
			'aysnc_auth0_client_id',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);

		register_setting(
			self::$option_group,
			'aysnc_auth0_client_secret',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);

		register_setting(
			self::$option_group,
			'aysnc_auth0_secret_login_token',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			]
		);

		// Register settings section.
		add_settings_section(
			'aysnc_auth0_section',
			__( 'Auth0 Settings', 'aysnc-auth0-login' ),
			[ __CLASS__, 'settings_section_callback' ],
			self::$option_group
		);

		// Register settings fields.
		add_settings_field(
			'aysnc_auth0_secret_login_token',
			__( 'Secret Login Token', 'aysnc-auth0-login' ) . '<span class="required">*</span>',
			[ __CLASS__, 'secret_login_token_field_callback' ],
			self::$option_group,
			'aysnc_auth0_section',
			[
				'label_for' => 'aysnc_auth0_secret_login_token',
			]
		);

		add_settings_field(
			'aysnc_auth0_domain',
			__( 'Domain', 'aysnc-auth0-login' ) . '<span class="required">*</span>',
			[ __CLASS__, 'domain_field_callback' ],
			self::$option_group,
			'aysnc_auth0_section',
			[
				'label_for' => 'aysnc_auth0_domain',
			]
		);

		add_settings_field(
			'aysnc_auth0_client_id',
			__( 'Client ID', 'aysnc-auth0-login' ) . '<span class="required">*</span>',
			[ __CLASS__, 'client_id_field_callback' ],
			self::$option_group,
			'aysnc_auth0_section',
			[
				'label_for' => 'aysnc_auth0_client_id',
			]
		);

		add_settings_field(
			'aysnc_auth0_client_secret',
			__( 'Client Secret', 'aysnc-auth0-login' ) . '<span class="required">*</span>',
			[ __CLASS__, 'client_secret_field_callback' ],
			self::$option_group,
			'aysnc_auth0_section',
			[
				'label_for' => 'aysnc_auth0_client_secret',
			]
		);
	}

	/**
	 * Add options page.
	 *
	 * @return void
	 */
	public static function add_options_page(): void {
		add_options_page(
			__( 'Auth0 Settings', 'aysnc-auth0-login' ),
			__( 'Auth0', 'aysnc-auth0-login' ),
			'manage_options',
			self::$option_group,
			[ __CLASS__, 'render_options_page' ]
		);
	}

	/**
	 * Settings section callback.
	 *
	 * @return void
	 */
	public static function settings_section_callback(): void {
		// Only show the notice if not all config values are defined.
		if ( ! self::all_wp_config_values_defined() ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<strong><?php echo esc_html__( 'Recommended: Define Auth0 credentials in wp-config.php', 'aysnc-auth0-login' ); ?></strong>
					<br>
					<?php echo esc_html__( 'For better security, we recommend defining your Auth0 credentials directly in your wp-config.php file instead of storing them in the database.', 'aysnc-auth0-login' ); ?>
				</p>
				<p>
					<?php echo esc_html__( 'Add the following lines to your wp-config.php file:', 'aysnc-auth0-login' ); ?>
					<br><br>
					<code>define( 'AYSNC_AUTH0_SECRET_LOGIN_TOKEN', 'your-secret-token' );</code>
					<br>
					<code>define( 'AYSNC_AUTH0_DOMAIN', 'your-domain.auth0.com' );</code>
					<br>
					<code>define( 'AYSNC_AUTH0_CLIENT_ID', 'your-client-id' );</code>
					<br>
					<code>define( 'AYSNC_AUTH0_CLIENT_SECRET', 'your-client-secret' );</code>
				</p>
			</div>
			<?php
		endif;

		// If we have a token (either from wp-config.php or database), display the login URL.
		if ( ! empty( SecretLoginLink::get_token() ) ) :
			// Get the login URL.
			$login_url = SecretLoginLink::get_link();
			?>
			<div class="notice notice-warning inline" style="margin-top: 10px; margin-bottom: 0; padding-bottom: 12px;">
				<p><strong><?php echo esc_html__( 'Your WordPress login URL is now:', 'aysnc-auth0-login' ); ?></strong></p>
				<p>
					<code id="aysnc-auth0-login-url"><?php echo esc_url( $login_url ); ?></code>
					<button type="button" class="button button-small button-secondary aysnc-copy-button" data-clipboard-target="#aysnc-auth0-login-url" aria-label="<?php esc_attr_e( 'Copy login URL to clipboard', 'aysnc-auth0-login' ); ?>">
						<?php esc_html_e( 'Copy', 'aysnc-auth0-login' ); ?>
					</button>
				</p>
				<p><strong><?php echo esc_html__( 'Important:', 'aysnc-auth0-login' ); ?></strong>
				<?php echo esc_html__( 'Save this URL in a secure location. You will need it to access your WordPress login page, as the standard wp-login.php is now disabled.', 'aysnc-auth0-login' ); ?></p>

				<?php if ( defined( 'AYSNC_AUTH0_SECRET_LOGIN_TOKEN' ) ) : ?>
					<hr>
					<p><?php esc_html_e( 'If you have changed the Secret Login Token in wp-config.php, click the button below to update the login URL.', 'aysnc-auth0-login' ); ?></p>
					<form method="post">
						<?php wp_nonce_field( 'aysnc_flush_permalinks', 'aysnc_flush_permalinks_nonce' ); ?>
						<input type="hidden" name="action" value="aysnc_flush_permalinks">
						<?php submit_button( __( 'Flush Permalinks', 'aysnc-auth0-login' ), 'secondary', 'flush_permalinks', false ); ?>
					</form>
				<?php endif; ?>
			</div>
			<?php
		endif;
	}

	/**
	 * Secret Login Token field callback.
	 *
	 * @return void
	 */
	public static function secret_login_token_field_callback(): void {
		$value      = get_option( 'aysnc_auth0_secret_login_token', '' );
		$is_defined = defined( 'AYSNC_AUTH0_SECRET_LOGIN_TOKEN' );

		if ( ! is_string( $value ) ) {
			$value = '';
		}

		echo '<input type="text" id="aysnc_auth0_secret_login_token" name="aysnc_auth0_secret_login_token" value="' . esc_attr( $value ) . '" class="regular-text"' . ( $is_defined ? ' disabled' : ' required' ) . '>';

		if ( $is_defined ) {
			echo '<p class="description">' . esc_html__( 'This value is defined in wp-config.php', 'aysnc-auth0-login' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'Enter a unique, random string to use as your secret login token. This will be used to create a special login URL.', 'aysnc-auth0-login' ) . '</p>';
		}
	}

	/**
	 * Domain field callback.
	 *
	 * @return void
	 */
	public static function domain_field_callback(): void {
		$value      = get_option( 'aysnc_auth0_domain', '' );
		$is_defined = defined( 'AYSNC_AUTH0_DOMAIN' );

		if ( ! is_string( $value ) ) {
			$value = '';
		}

		echo '<input type="text" id="aysnc_auth0_domain" name="aysnc_auth0_domain" value="' . esc_attr( $value ) . '" class="regular-text"' . ( $is_defined ? ' disabled' : ' required' ) . '>';

		if ( $is_defined ) {
			echo '<p class="description">' . esc_html__( 'This value is defined in wp-config.php', 'aysnc-auth0-login' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'Enter your Auth0 domain (e.g., your-tenant.auth0.com).', 'aysnc-auth0-login' ) . '</p>';
		}
	}

	/**
	 * Client ID field callback.
	 *
	 * @return void
	 */
	public static function client_id_field_callback(): void {
		$value      = get_option( 'aysnc_auth0_client_id', '' );
		$is_defined = defined( 'AYSNC_AUTH0_CLIENT_ID' );

		if ( ! is_string( $value ) ) {
			$value = '';
		}

		echo '<input type="password" id="aysnc_auth0_client_id" name="aysnc_auth0_client_id" value="' . esc_attr( $value ) . '" class="regular-text"' . ( $is_defined ? ' disabled' : ' required' ) . '>';

		if ( $is_defined ) {
			echo '<p class="description">' . esc_html__( 'This value is defined in wp-config.php', 'aysnc-auth0-login' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'Enter your Auth0 application Client ID.', 'aysnc-auth0-login' ) . '</p>';
		}
	}

	/**
	 * Client Secret field callback.
	 *
	 * @return void
	 */
	public static function client_secret_field_callback(): void {
		$value      = get_option( 'aysnc_auth0_client_secret', '' );
		$is_defined = defined( 'AYSNC_AUTH0_CLIENT_SECRET' );

		if ( ! is_string( $value ) ) {
			$value = '';
		}

		echo '<input type="password" id="aysnc_auth0_client_secret" name="aysnc_auth0_client_secret" value="' . esc_attr( $value ) . '" class="regular-text"' . ( $is_defined ? ' disabled' : ' required' ) . '>';

		if ( $is_defined ) {
			echo '<p class="description">' . esc_html__( 'This value is defined in wp-config.php', 'aysnc-auth0-login' ) . '</p>';
		} else {
			echo '<p class="description">' . esc_html__( 'Enter your Auth0 application Client Secret.', 'aysnc-auth0-login' ) . '</p>';
		}
	}

	/**
	 * Render options page.
	 *
	 * @return void
	 */
	public static function render_options_page(): void {
		$all_config_defined = self::all_wp_config_values_defined();
		$plugin_dir_url     = plugin_dir_url( __DIR__ );
		$auth0_logo_url     = $plugin_dir_url . 'assets/auth0-logo.svg';
		$auth0_site_url     = 'https://auth0.com/?utm_source=wordpress&utm_medium=plugin&utm_campaign=aysnc-auth0-login';
		?>
		<div class="wrap">
			<div style="display: flex; align-items: center; margin-bottom: 20px;">
				<a href="<?php echo esc_url( $auth0_site_url ); ?>" target="_blank" rel="noopener noreferrer">
					<img src="<?php echo esc_url( $auth0_logo_url ); // phpcs:ignore ?>" alt="Auth0" style="height: 40px; width: auto;">
				</a>
			</div>

			<form action="options.php" method="post">
				<?php
				settings_fields( self::$option_group );
				do_settings_sections( self::$option_group );

				// Only show the Save Changes button if not all config values are defined
				if ( ! $all_config_defined ) {
					submit_button();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handle permalink flushing on save.
	 *
	 * @return void
	 */
	public static function handle_flush_permalinks_on_save(): void {
		if ( '1' === get_transient( 'aysnc_flush_permalinks' ) ) {
			self::flush_permalinks();
		}
	}

	/**
	 * Handle permalink flushing.
	 *
	 * @return void
	 */
	public static function handle_flush_permalinks(): void {
		if (
			isset( $_POST['action'] )
			&& 'aysnc_flush_permalinks' === $_POST['action']
			&& isset( $_POST['aysnc_flush_permalinks_nonce'] )
			&& is_string( $_POST['aysnc_flush_permalinks_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aysnc_flush_permalinks_nonce'] ) ), 'aysnc_flush_permalinks' ) // @phpstan-ignore-line
		) {
			// First, flush permalinks.
			self::flush_permalinks();

			// Set a transient to show admin notice.
			set_transient( 'aysnc_auth0_permalinks_flushed', true, 60 );

			// Redirect to prevent form resubmission.
			wp_safe_redirect( (string) wp_get_referer() );
			exit;
		}
	}

	/**
	 * Actually flush the permalinks.
	 * This is separated to allow it to be hooked to wp_loaded.
	 *
	 * @return void
	 */
	public static function flush_permalinks(): void {
		SecretLoginLink::flush_permalinks();
		delete_transient( 'aysnc_flush_permalinks' );
	}

	/**
	 * Show admin notice when permalinks are flushed.
	 *
	 * @return void
	 */
	public static function show_permalink_notice(): void {
		// Check if we should show the notice
		if ( get_transient( 'aysnc_auth0_permalinks_flushed' ) ) {
			// Delete the transient
			delete_transient( 'aysnc_auth0_permalinks_flushed' );

			// Show the notice.
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Login URL has been updated successfully. The secret login link should now work with your current configuration.', 'aysnc-auth0-login' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Flush permalinks when settings are updated.
	 *
	 * @param string $old_value The old value of the option.
	 * @param string $new_value The new value of the option.
	 *
	 * @return void
	 */
	public static function flush_permalinks_on_save( string $old_value = '', string $new_value = '' ): void {
		if ( $old_value !== $new_value ) {
			// It is too late to do wp_flush_permalinks() here.
			// So set a transient, and let the admin page flush the permalinks instead.
			set_transient( 'aysnc_flush_permalinks', 1 );
		}
	}

	/**
	 * Add styles for required fields.
	 *
	 * @return void
	 */
	public static function add_required_field_styles(): void {
		// Only add styles on our settings page
		$screen = get_current_screen();
		if ( ! $screen || 'settings_page_' . self::$option_group !== $screen->id ) {
			return;
		}

		// Add CSS for required field asterisk
		echo '<style>
			.form-table th .required {
				color: #dc3232;
				font-weight: bold;
				margin-left: 3px;
			}
			.aysnc-copy-button {
				margin-left: 10px;
				vertical-align: middle;
			}
		</style>';
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public static function enqueue_admin_scripts( string $hook ): void {
		// Only enqueue on our settings page
		if ( 'settings_page_' . self::$option_group !== $hook ) {
			return;
		}

		$plugin_dir_url = plugin_dir_url( __DIR__ );
		$script_url     = $plugin_dir_url . 'assets/js/admin-copy-url.js';
		$version        = '1.0.0';

		// Register and enqueue the script
		wp_register_script(
			'aysnc-auth0-admin-copy-url',
			$script_url,
			[],
			$version,
			true
		);

		// Localize the script with translation strings.
		wp_localize_script(
			'aysnc-auth0-admin-copy-url',
			'aysnc_auth0_admin',
			[
				'copied_text' => __( 'Copied!', 'aysnc-auth0-login' ),
			]
		);

		// Enqueue the script.
		wp_enqueue_script( 'aysnc-auth0-admin-copy-url' );
	}
}
