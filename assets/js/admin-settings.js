/**
 * Shortlist - admin settings help affordances.
 *
 * The "?" help buttons use the native Popover API (popovertarget) when
 * available - no JS needed for those. This script is a graceful fallback for
 * browsers without Popover support: it toggles a panel on click and closes it
 * on Escape or outside-click, with correct aria-expanded state. Loaded with
 * defer/in_footer.
 */
( function () {
	'use strict';

	// Native Popover API present - the markup already wires everything up.
	if ( window.HTMLElement && 'popover' in window.HTMLElement.prototype ) {
		return;
	}

	var open = null;

	function close() {
		if ( ! open ) {
			return;
		}
		open.panel.hidden = true;
		open.toggle.setAttribute( 'aria-expanded', 'false' );
		open = null;
	}

	document.addEventListener( 'click', function ( event ) {
		var toggle = event.target.closest( '.shortlist-help__toggle' );

		if ( ! toggle ) {
			close();
			return;
		}

		event.preventDefault();

		var panelId = toggle.getAttribute( 'popovertarget' );
		var panel = panelId ? document.getElementById( panelId ) : null;

		if ( ! panel ) {
			return;
		}

		if ( open && open.panel === panel ) {
			close();
			return;
		}

		close();
		panel.hidden = false;
		toggle.setAttribute( 'aria-expanded', 'true' );
		open = { toggle: toggle, panel: panel };
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' && open ) {
			var toggle = open.toggle;
			close();
			toggle.focus();
		}
	} );
} )();
