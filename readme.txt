=== Aysnc Login with Auth0 ===
Contributors: aysnc, junaidbhura
Tags: auth0, login, sso, single-sign-on
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.2
Stable tag: 1.0.0
License: MIT

A simple, secure way to log in to WordPress using Auth0. Hides the default login screen with a redirect to Auth0.

== Description ==

[❤️ Contribute on GitHub](https://github.com/Aysnc-Labs/aysnc-login-with-auth0)

Auth0 Login for WordPress provides a simple, secure way to log in to WordPress using Auth0. It hides the default login screen with a redirect to Auth0.

Watch this video to see how to configure your Auth0 application and hook it up to WordPress:

[vimeo https://vimeo.com/1104092077]

= Features =

* Very easy to set up
* Replaces the standard WordPress login URL with a secret link for additional security
* Authenticates users via Auth0 using OAuth 2.0 / OIDC

= Configuration =

You can configure the plugin either through the WordPress admin interface or by defining constants in your wp-config.php file (recommended for enhanced security).

= Option 1: WordPress Admin Configuration (Basic) =

1. Go to WordPress Admin > Settings > Auth0
2. Enter the following required information:
   * Secret Login Token: A unique, random string that will be used for your custom login URL
   * Domain: Your Auth0 domain (e.g., your-tenant.auth0.com)
   * Client ID: Your Auth0 application Client ID
   * Client Secret: Your Auth0 application Client Secret
3. Save changes

= Option 2: wp-config.php Configuration (Recommended) =

For enhanced security, add the following lines to your wp-config.php file:

`
// Auth0 Configuration
define( 'AYSNC_AUTH0_SECRET_LOGIN_TOKEN', 'your-secret-token' );
define( 'AYSNC_AUTH0_DOMAIN', 'your-tenant.auth0.com' );
define( 'AYSNC_AUTH0_CLIENT_ID', 'your-client-id' );
define( 'AYSNC_AUTH0_CLIENT_SECRET', 'your-client-secret' );
`

This method prevents your Auth0 credentials from being stored in the database.

= Important: Don't lose your login URL! =

After configuring the plugin:

1. Your WordPress login URL will change to: https://your-wordpress-site.com/your-secret-token
2. The standard wp-login.php page will be disabled
3. Save your new login URL in a secure location - you'll need it to access your WordPress admin area

If you change your Secret Login Token in wp-config.php, use the "Update Login URL" button in the Auth0 settings page to refresh the login URL.

== External Services ==

This plugin connects to Auth0, a third-party authentication service, to provide secure user authentication for your WordPress site.

= What is Auth0 and what is it used for? =

Auth0 is a cloud-based identity and access management platform that provides authentication and authorization services. This plugin uses Auth0 to authenticate users instead of using WordPress's built-in authentication system.

= What data is sent and when? =

The following data is transmitted to Auth0:
* When a user attempts to log in: The user is redirected to Auth0's servers for authentication
* During the OAuth flow: Auth0 receives user credentials and returns authentication tokens and user profile information (email, name, etc.) back to your WordPress site
* User profile information such as email address is retrieved from Auth0 to match or create WordPress user accounts

= Service Information =

**Service Provider:** Auth0, Inc.  
**Service Website:** https://auth0.com/  
**Terms of Service:** https://auth0.com/terms  
**Privacy Policy:** https://auth0.com/privacy  

**Important:** You must configure your own Auth0 application and are responsible for reviewing and complying with Auth0's terms of service and privacy policy. The data processing depends on your Auth0 configuration and the information users provide during authentication.

== Installation ==

1. Upload 'aysnc-wordpress-auth0' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin through Settings > Auth0 or by adding constants to wp-config.php

== Frequently Asked Questions ==

= What happens if I lose my Secret Login Token? =

If you've defined it in wp-config.php, you can retrieve it from there. If you've only set it in the WordPress admin, you may need to access your database directly to retrieve it.

= Can I still use the standard WordPress login? =

No, the standard wp-login.php is disabled for security reasons. You must use the custom login URL.

= Does this plugin work with multisite? =

Yes, the plugin is compatible with WordPress multisite installations.

= What if I need to update my Auth0 credentials? =

You can update them either in the WordPress admin interface or in your wp-config.php file, depending on how you initially configured the plugin.

== Screenshots ==

1. WordPress Admin Settings
2. WordPress Config Settings

== Changelog ==

= 1.0.0 =
* First stable release.
