/**
 * Shortlist - accessible WooCommerce wishlist.
 *
 * Vanilla JS, no jQuery. Event delegation so buttons rendered after page load
 * (infinite scroll, quick-view, AJAX filters) keep working. Config is provided
 * by the storefront-kit WishlistEngine via wp_localize_script as
 * `window.shortlistWishlist`. Loaded with defer/in_footer, so the DOM is ready
 * and no DOMContentLoaded wrapper is needed.
 *
 * Feedback is announced to assistive tech through a polite aria-live region and,
 * on failure, surfaced inline so the shopper is never left guessing.
 */
( function () {
	'use strict';

	var BUTTON_SELECTOR = '[data-shortlist-wishlist-button]';
	var liveRegion = null;

	/**
	 * Lazily create the shared visually-hidden status region used to announce
	 * add/remove/failure outcomes to screen-reader users.
	 */
	function announce( message ) {
		if ( ! message ) {
			return;
		}

		if ( ! liveRegion ) {
			liveRegion = document.createElement( 'div' );
			liveRegion.className = 'shortlist-wishlist-status';
			liveRegion.setAttribute( 'role', 'status' );
			liveRegion.setAttribute( 'aria-live', 'polite' );
			document.body.appendChild( liveRegion );
		}

		// Clear first so identical consecutive messages are still announced.
		liveRegion.textContent = '';
		window.requestAnimationFrame( function () {
			liveRegion.textContent = message;
		} );
	}

	/**
	 * Keep the "Wishlist (n)" My Account menu label in sync after a toggle,
	 * without a page reload. Best-effort: silently no-ops if the label is absent.
	 */
	function updateMenuCount( count ) {
		if ( typeof count !== 'number' ) {
			return;
		}

		var link = document.querySelector(
			'.woocommerce-MyAccount-navigation-link--shortlist a'
		);

		if ( ! link ) {
			return;
		}

		var base = link.textContent.replace( /\s*\(\d+\)\s*$/, '' );
		link.textContent = count > 0 ? base + ' (' + count + ')' : base;
	}

	/**
	 * Reflect the new saved/unsaved state across every button for this product
	 * (loop + single buttons can co-exist on one page).
	 */
	function syncButtons( productId, active, label ) {
		document
			.querySelectorAll(
				BUTTON_SELECTOR + '[data-product-id="' + productId + '"]'
			)
			.forEach( function ( el ) {
				el.classList.toggle( 'is-active', active );
				el.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				if ( label ) {
					el.textContent = label;
				}
			} );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( BUTTON_SELECTOR );

		if ( ! button ) {
			return;
		}

		var config = window.shortlistWishlist;

		if ( ! config ) {
			return;
		}

		event.preventDefault();

		if (
			! config.allowGuests &&
			! document.body.classList.contains( 'logged-in' )
		) {
			window.location.href = config.loginUrl;
			return;
		}

		// Guard against double submissions while a request is in flight.
		if ( button.getAttribute( 'aria-busy' ) === 'true' ) {
			return;
		}

		var productId = button.dataset.productId || '';

		if ( ! productId ) {
			return;
		}

		var formData = new FormData();
		formData.append( 'action', config.action );
		formData.append( 'nonce', config.nonce );
		formData.append( 'product_id', productId );

		button.disabled = true;
		button.setAttribute( 'aria-busy', 'true' );

		fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} )
			.then( function ( response ) {
				// Parse the body either way: WooCommerce returns a useful
				// message (e.g. "Please log in") even on 403/404 responses.
				return response.json();
			} )
			.then( function ( payload ) {
				if ( ! payload || ! payload.success || ! payload.data ) {
					var serverMessage =
						payload &&
						payload.data &&
						payload.data.message
							? payload.data.message
							: config.errorText;
					announce( serverMessage );
					return;
				}

				var active = !! payload.data.in_wishlist;
				var label = payload.data.button_text || '';

				syncButtons( productId, active, label );
				updateMenuCount( payload.data.count );

				announce(
					active
						? config.addedText || label
						: config.removedText || label
				);
			} )
			.catch( function () {
				// Network/parse failure: leave button state intact, tell the user.
				announce( config.errorText );
			} )
			.finally( function () {
				button.disabled = false;
				button.removeAttribute( 'aria-busy' );
			} );
	} );
} )();
