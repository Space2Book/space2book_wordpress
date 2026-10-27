/**
 * Space2Book frontend script.
 *
 * Progressively enhances booking buttons (`[data-space2book-mode="modal"]`)
 * into buttons that open the booking form in an accessible modal dialog.
 *
 * Integration point for the official Space2Book Embed SDK:
 *
 *     window.Space2BookWP.registerDriver( {
 *         // Optional. Return true when the driver opened the booking form itself.
 *         openModal: function ( context ) { return false; },
 *         // Optional. Return true when the driver mounted the inline form itself.
 *         mountInline: function ( element, context ) { return false; },
 *     } );
 *
 * `context` contains: venue, language, url (embed URL) and element.
 * Without a registered driver the built-in iframe driver is used.
 */
( function ( window, document ) {
	'use strict';

	var config = window.space2bookConfig || {};
	var i18n = config.i18n || {};
	var driver = null;
	var dialog = null;
	var frame = null;
	var lastTrigger = null;

	function contextFor( element ) {
		return {
			venue: element.getAttribute( 'data-space2book-venue' ) || '',
			language: element.getAttribute( 'data-space2book-language' ) || '',
			url:
				element.getAttribute( 'data-space2book-embed-url' ) ||
				element.getAttribute( 'href' ) ||
				'',
			element: element,
		};
	}

	function supportsDialog() {
		return typeof window.HTMLDialogElement === 'function';
	}

	function createDialog() {
		var closeButton;
		var sentinel;

		dialog = document.createElement( 'dialog' );
		dialog.className = 'space2book-modal';
		dialog.setAttribute( 'aria-label', i18n.dialogLabel || 'Booking form' );

		closeButton = document.createElement( 'button' );
		closeButton.type = 'button';
		closeButton.className = 'space2book-modal__close';
		closeButton.setAttribute( 'aria-label', i18n.close || 'Close' );
		closeButton.innerHTML = '<span aria-hidden="true">&times;</span>';
		closeButton.addEventListener( 'click', closeModal );

		frame = document.createElement( 'iframe' );
		frame.className = 'space2book-modal__frame';
		frame.title = i18n.iframeTitle || 'Booking form';
		frame.setAttribute( 'referrerpolicy', 'strict-origin-when-cross-origin' );
		frame.setAttribute( 'allow', 'payment' );

		// Keeps keyboard focus inside the modal: tabbing out of the iframe
		// lands on this sentinel, which moves focus back to the close button.
		sentinel = document.createElement( 'span' );
		sentinel.tabIndex = 0;
		sentinel.className = 'space2book-modal__sentinel';
		sentinel.addEventListener( 'focus', function () {
			closeButton.focus();
		} );

		// Shift+Tab on the close button moves focus into the booking form.
		closeButton.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Tab' && event.shiftKey ) {
				event.preventDefault();
				frame.focus();
			}
		} );

		dialog.appendChild( closeButton );
		dialog.appendChild( frame );
		dialog.appendChild( sentinel );

		// Clicking the backdrop (the dialog element itself) closes the modal.
		dialog.addEventListener( 'click', function ( event ) {
			if ( event.target === dialog ) {
				closeModal();
			}
		} );

		// Escape is handled natively by <dialog>; restore state afterwards.
		dialog.addEventListener( 'close', onClosed );

		document.body.appendChild( dialog );
	}

	function openModal( trigger ) {
		var context = contextFor( trigger );

		if ( driver && typeof driver.openModal === 'function' && driver.openModal( context ) === true ) {
			return true;
		}

		if ( ! supportsDialog() || ! context.url ) {
			return false;
		}

		if ( ! dialog ) {
			createDialog();
		}

		lastTrigger = trigger;

		if ( frame.getAttribute( 'src' ) !== context.url ) {
			frame.setAttribute( 'src', context.url );
		}

		document.documentElement.classList.add( 'space2book-modal-open' );
		dialog.showModal();
		dialog.querySelector( '.space2book-modal__close' ).focus();

		return true;
	}

	function closeModal() {
		if ( dialog && dialog.open ) {
			dialog.close();
		}
	}

	function onClosed() {
		document.documentElement.classList.remove( 'space2book-modal-open' );

		if ( lastTrigger && typeof lastTrigger.focus === 'function' ) {
			lastTrigger.focus();
		}
		lastTrigger = null;
	}

	function enhanceButton( button ) {
		if ( button.getAttribute( 'data-space2book-ready' ) ) {
			return;
		}
		button.setAttribute( 'data-space2book-ready', '1' );
		button.setAttribute( 'role', 'button' );
		button.setAttribute( 'aria-haspopup', 'dialog' );

		button.addEventListener( 'click', function ( event ) {
			if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
				return;
			}
			if ( openModal( button ) ) {
				event.preventDefault();
			}
		} );

		// Links react to Enter only; buttons also react to Space.
		button.addEventListener( 'keydown', function ( event ) {
			if ( event.key === ' ' || event.key === 'Spacebar' ) {
				event.preventDefault();
				if ( ! openModal( button ) ) {
					window.location.href = button.href;
				}
			}
		} );
	}

	function mountInline( element ) {
		if ( element.getAttribute( 'data-space2book-ready' ) ) {
			return;
		}
		if ( driver && typeof driver.mountInline === 'function' ) {
			var iframe = element.querySelector( 'iframe' );
			var context = contextFor( element );
			context.url = iframe ? iframe.getAttribute( 'src' ) : '';

			if ( driver.mountInline( element, context ) === true ) {
				element.setAttribute( 'data-space2book-ready', '1' );
			}
		}
	}

	function init( root ) {
		var scope = root || document;
		Array.prototype.forEach.call( scope.querySelectorAll( '[data-space2book-mode="modal"]' ), enhanceButton );
		Array.prototype.forEach.call( scope.querySelectorAll( '[data-space2book-mode="inline"]' ), mountInline );
	}

	window.Space2BookWP = {
		/**
		 * Registers the driver that connects the official Embed SDK.
		 *
		 * @param {Object} newDriver Driver with optional openModal / mountInline methods.
		 */
		registerDriver: function ( newDriver ) {
			driver = newDriver || null;
			init();
		},
		/**
		 * (Re)initialises booking elements, e.g. after content was loaded dynamically.
		 *
		 * @param {Element} [root] Element to scan. Defaults to the document.
		 */
		init: init,
		open: openModal,
		close: closeModal,
	};

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			init();
		} );
	} else {
		init();
	}

	document.dispatchEvent( new window.CustomEvent( 'space2book:ready', { detail: window.Space2BookWP } ) );
} )( window, document );
