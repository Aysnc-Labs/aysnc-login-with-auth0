import { test, expect, RequestUtils, Admin } from "@wordpress/e2e-test-utils-playwright";
import type { Page } from '@playwright/test';

test.describe(
	'Auth0 Options',
	async () => {
		test.beforeAll( async ( { requestUtils }: { requestUtils: RequestUtils } ) => {
			await requestUtils.activatePlugin( 'aysnc-auth0-login' );
		} );

		test(
			'Settings added via WP Admin',
			async ( { admin, page }: { admin: Admin, page: Page } ) => {
				await admin.visitAdminPage( 'options-permalink.php' );
				await page.locator( '#permalink-input-post-name' ).click();
				await page.locator( 'input[type="submit"]' ).click();

				await admin.visitAdminPage( 'options-general.php?page=aysnc_auth0_options' );
				await expect( page.getByRole('heading', { name: 'Auth0 Settings', level: 2 } ) ).toBeVisible();
				await expect( page.locator( '#aysnc_auth0_secret_login_token' ) ).toBeEnabled();
				await expect( page.locator( '#aysnc_auth0_domain' ) ).toBeEnabled();
				await expect( page.locator( '#aysnc_auth0_client_id' ) ).toBeEnabled();
				await expect( page.locator( '#aysnc_auth0_client_secret' ) ).toBeEnabled();

				await page.fill( '#aysnc_auth0_secret_login_token', 'secret-token' );
				await page.fill( '#aysnc_auth0_domain', 'domain' );
				await page.fill( '#aysnc_auth0_client_id', 'client-id' );
				await page.fill( '#aysnc_auth0_client_secret', 'client-secret' );
				await page.locator( 'input[type="submit"]' ).click();

				await expect( page.locator( '#setting-error-settings_updated' ) ).toContainClass( 'notice-success' );

				await expect( page.locator( '#aysnc_auth0_secret_login_token' ) ).toHaveValue( 'secret-token' );
				await expect( page.locator( '#aysnc_auth0_domain' ) ).toHaveValue( 'domain' );
				await expect( page.locator( '#aysnc_auth0_client_id' ) ).toHaveValue( 'client-id' );
				await expect( page.locator( '#aysnc_auth0_client_secret' ) ).toHaveValue( 'client-secret' );
			}
		);

		test.afterAll( async ( { requestUtils }: { requestUtils: RequestUtils } ) => {
			await requestUtils.deactivatePlugin( 'aysnc-auth0-login' );
		} );
	}
);
