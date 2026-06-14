=== Shortlist - Wishlist for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, wishlist, save for later, accessibility, ajax
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight, accessible WooCommerce wishlist. Guests and customers, AJAX toggle, My Account tab and shortcode. No jQuery, no layout shift.

== Description ==

Shortlist adds an accessible "Add to wishlist" button to your WooCommerce shop loop and product pages. Visitors save products to a list and revisit them later from a My Account tab or anywhere via the `[shortlist]` shortcode.

Guests get a wishlist too — it is kept in a cookie and merged into their account automatically when they log in. Logged-in customers' lists are stored in a custom table keyed to their user id.

= Built for speed and accessibility =

* **No jQuery** in the plugin's own front-end code — the script is vanilla JS, deferred, and loaded in the footer.
* **No layout shift (CLS).** The toggle button reserves its space, so switching between add/remove states never reflows the page.
* **Keyboard and screen-reader friendly.** The toggle is a real `<button>` with `aria-pressed`, and every button for a product stays in sync after a toggle.
* **AJAX toggle.** Adding or removing happens in place over admin-ajax, with no full page reload.

= Where it appears =

* The single product page (after the summary).
* The shop and archive product loops (on each card).
* A "Wishlist" tab in the WooCommerce My Account area (with an optional saved-item count).
* Anywhere, via the `[shortlist]` shortcode.
* In the block editor, via the **Shortlist Wishlist** block.

Each placement can be toggled from the settings screen.

= Settings =

A WooCommerce-capability settings page (Shortlist menu) lets you:

* Enable or disable the wishlist.
* Allow or block guest wishlists.
* Choose where the button appears (single, loop, My Account).
* Show or hide the saved-item count on the My Account menu.
* Set the add and remove button labels.
* Control the wishlist list: heading, intro and empty-list text, column count, and which product details (image, name, price, add-to-cart, remove button) appear.

= Engine =

The wishlist orchestration (guest cookie, ownership, AJAX toggle, My Account endpoint, guest-to-customer transfer, asset enqueue, markup hooks) is provided by the shared, namespace-neutral `wppoland/storefront-kit` Wishlist engine; this plugin is a thin adapter that supplies the text domain, options, asset URLs, templates and custom-table storage.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/shortlist`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be active.
3. Visit the **Shortlist** menu in wp-admin to configure placement and labels.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. Shortlist requires an active WooCommerce installation.

= Can guests use the wishlist? =

Yes, if you allow it in settings. A guest's list lives in a cookie and is merged into their account the next time they log in.

= Does it use jQuery? =

No. The plugin's own front-end script is vanilla JavaScript with no jQuery dependency.

= How do I show the wishlist on a page? =

Use the `[shortlist]` shortcode, or rely on the "Wishlist" tab added to the WooCommerce My Account area.

== Screenshots ==

1. The add-to-wishlist button on a product card.
2. The wishlist in the My Account area.
3. The Shortlist settings screen.

== Changelog ==

= 0.3.0 =
* New: **Wishlist page** — pick an existing page or create one from settings; auto-inject the `[shortlist]` list when the page has no shortcode yet.
* New: **Variation-aware saves** — on variable products the button tracks the selected variation; configurable hint when no variation is chosen.
* Improved: settings screen groups wishlist page, variation hint and existing placement controls.

= 0.2.0 =
* Polish: refreshed, themeable storefront styles (heart icon, dark-mode, CLS-safe grid) and a modern, card-based settings screen with an accessible "?" help popover on every option.
* Accessibility: wishlist changes are now announced to screen readers, and the My Account count updates live without a page reload.
* Robustness: friendly empty-state with a "Browse products" link, clear failure messaging, and defensive guards against missing product data.
* New: **Shortlist Wishlist** block for the block editor (server-rendered, matches the `[shortlist]` shortcode).
* New: optional saved-item count next to the My Account "Wishlist" menu label.
* New: full control over the wishlist list — heading, intro and empty-list text, column count, and which product details (image, name, price, add-to-cart, remove button) appear.
* New: uninstall cleanup removes the wishlist table and plugin options on delete.
* i18n: added Domain Path and a `languages` directory for translations.

= 0.1.0 =
* Initial release: accessible AJAX wishlist for WooCommerce with guest support, a My Account tab, a shortcode, and a settings page for placement and labels.
