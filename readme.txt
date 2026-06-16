=== Shortlist - Wishlist for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, wishlist, product wishlist, save for later, favourites
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 0.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce wishlist and save-for-later list for guests and customers: AJAX toggle, My Account tab, shortcode and block.

== Description ==

Shortlist adds an "Add to wishlist" button to your WooCommerce shop loop and product pages. Shoppers save products, favourites and save-for-later items, then come back from a "Wishlist" tab in My Account, a page of their own, or anywhere you drop the `[shortlist]` shortcode.

Guests can save products before they log in. A guest list lives in a cookie; the next time that visitor signs in, their saved items move onto their account, so nothing is lost at the login step. Logged-in customers' lists are stored in a custom database table keyed to their user id.

The plugin is written for stores that care about front-end weight and accessibility:

* The front-end script is vanilla JavaScript with no jQuery dependency. It is deferred and loaded in the footer.
* The toggle button reserves its space, so switching between the add and remove states does not reflow the page (no CLS).
* The toggle is a real `<button>` with `aria-pressed`. When a product appears more than once on a page, every button for it updates together after a save, and the change is announced to screen readers through a polite live region.
* Saving and removing happen over admin-ajax with no page reload.

On variable products the button follows the selected variation, so a customer saves the exact size or colour they chose rather than the parent product. Until they pick options the button stays disabled, with a hint you can word yourself.

The source lives on GitHub at https://github.com/wppoland/shortlist — that's the place for bug reports and patches.

= Where the button and list can appear =

* The single product page, below the add-to-cart area.
* Product cards in the shop, category and tag loops.
* A "Wishlist" tab in WooCommerce My Account, optionally showing a saved-item count like "Wishlist (3)".
* A dedicated page you pick or create from the settings screen.
* Any post or page, via the `[shortlist]` shortcode.
* The block editor, via the **Shortlist Wishlist** block (server-rendered, so the editor preview matches the front end).

Each placement is a separate switch on the settings screen.

= Settings =

The Shortlist menu in wp-admin opens to shop managers (it uses the `manage_woocommerce` capability), not only administrators. From there you can:

* Turn the wishlist on or off, and decide whether guests may use it.
* Choose where the button shows up: single product, shop loop, My Account, and a dedicated page.
* Show or hide the saved-item count on the My Account menu.
* Set the add and remove button labels, and the variation hint.
* Shape the list itself: heading, intro and empty-list text, how many columns the grid uses, and which details (image, name, price, add-to-cart, remove button) each saved product shows.

Every setting has a "?" next to it that opens a short explanation of what it does.

Shortlist only loads its stylesheet and script on the pages where the wishlist actually appears, so the rest of your store stays untouched.

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

= Does it work with variable products? =

Yes. On variable products the wishlist button follows the selected variation, so the saved item can include the chosen size or colour.

= Can I create a dedicated wishlist page? =

Yes. Choose an existing page or create one from the Shortlist settings screen. The plugin can inject the `[shortlist]` list automatically.

= Is the wishlist accessible? =

Yes. The wishlist button is a real button with `aria-pressed`, screen-reader announcements and no layout shift when the saved state changes.

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
