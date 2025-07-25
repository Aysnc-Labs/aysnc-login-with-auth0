/**
 * Admin clipboard functionality for Auth0 Login plugin.
 */
( function() {
	'use strict';

	// Wait for DOM to be ready
	document.addEventListener( 'DOMContentLoaded', function() {
		// Find all copy buttons
		const copyButtons = document.querySelectorAll( '.aysnc-copy-button' );

		// Add click event listener to each button.
		copyButtons.forEach(function( button ) {
			button.addEventListener( 'click', handleCopyClick );
		} );

		/**
		 * Handle copy button click.
		 *
		 * @param {Event} e Click event
		 */
		function handleCopyClick( e ) {
			const button = e.currentTarget
			const targetId = button.getAttribute( 'data-clipboard-target' )
			const targetElement = document.querySelector( targetId )

			if ( ! targetElement ) {
				return;
			}

			const textToCopy = targetElement.textContent;

			// Try to use the modern Clipboard API.
			if ( navigator.clipboard ) {
				navigator.clipboard.writeText( textToCopy )
					.then( () => showSuccess( button ) )
					.catch( () => fallbackCopyToClipboard( textToCopy, button ) );
			} else {
				// Fallback for browsers that don't support Clipboard API.
				fallbackCopyToClipboard( textToCopy, button );
			}
		}

		/**
		 * Fallback method to copy text to clipboard
		 *
		 * @param {string}  text   Text to copy
		 * @param {Element} button Button element
		 */
		function fallbackCopyToClipboard( text, button ) {
			// Create a temporary textarea element.
			const textarea = document.createElement( 'textarea' );
			textarea.value = text;

			// Make the textarea out of viewport.
			textarea.style.position = 'fixed';
			textarea.style.left     = '-999999px';
			textarea.style.top      = '-999999px';

			document.body.appendChild( textarea );
			textarea.focus();
			textarea.select();

			let success = false;

			try {
				// Execute the copy command.
				success = document.execCommand( 'copy' );
			} catch (err) {
				console.error( 'Failed to copy text: ', err );
			}

			// Remove the temporary textarea.
			document.body.removeChild( textarea );

			if ( success ) {
				showSuccess(button);
			}
		}

		/**
		 * Show success feedback on button.
		 *
		 * @param {Element} button Button element
		 */
		function showSuccess( button ) {
			const originalText = button.textContent;
			const successText = aysnc_auth0_admin.copied_text;

			// Change button text to success message
			button.textContent = successText;
			button.classList.add( 'button-primary' );
			button.classList.remove( 'button-secondary' );

			// Reset button after 2 seconds.
			setTimeout( function() {
				button.textContent = originalText;
				button.classList.remove( 'button-primary' );
				button.classList.add( 'button-secondary' );
			}, 2000 );
		}
	} );
} )();
