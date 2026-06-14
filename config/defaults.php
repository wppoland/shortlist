<?php
/**
 * Default settings, merged under the option key `shortlist_settings`.
 *
 * The feature ships enabled. The merchant tunes the button labels, where the
 * add-to-wishlist button renders (single product page and/or shop loop), and the
 * My Account / shortcode list appearance. All wishlist logic lives in the
 * storefront-kit WishlistEngine; these values are passed through to it as the
 * resolved settings.
 *
 * @package Shortlist
 *
 * @return array<string, mixed>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    'enabled' => true,

    // Who can build a wishlist.
    'allow_guests' => true,

    // Where the add-to-wishlist button appears.
    'show_on_single'  => true,
    'show_on_loop'    => true,
    'show_in_account' => true,

    // Show the saved-item count next to the My Account "Wishlist" menu label.
    'show_account_count' => true,

    // Button labels (toggle state).
    'button_add_text'    => 'Add to wishlist',
    'button_remove_text' => 'Remove from wishlist',

    // My Account / shortcode list.
    'account_label'      => 'Wishlist',
    'account_title'      => 'My wishlist',
    'account_intro_text' => '',
    'empty_text'         => 'Your wishlist is empty.',
    'grid_columns'       => 3,
    'show_list_title'    => true,
    'show_product_image' => true,
    'show_product_name'  => true,
    'show_price'         => true,
    'show_add_to_cart'   => true,
    'show_remove_button' => true,

    // Runtime strings (front-end script / AJAX handler).
    'login_required_text'    => 'Please log in to use your wishlist.',
    'product_not_found_text' => 'Product not found.',
    'variation_required_text' => 'Choose product options before adding to your wishlist.',

    // Dedicated wishlist page (optional).
    'wishlist_page_id'        => 0,
    'inject_wishlist_on_page' => true,
];
