<?php
/**
 * Wishlist body for the My Account tab and the [shortlist] shortcode.
 *
 * Rendered by the storefront-kit WishlistEngine via the host renderAccount
 * closure; output is buffered and returned as a string.
 *
 * @var list<\WC_Product>     $shortlist_products
 * @var array<string, mixed>  $shortlist_settings
 *
 * @package Shortlist/Templates
 */

declare(strict_types=1);

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Variables are local to the template include scope.

defined('ABSPATH') || exit;

$shortlist_title        = (string) ($shortlist_settings['account_title'] ?? __('My wishlist', 'plogins-shortlist'));
$shortlist_intro        = (string) ($shortlist_settings['account_intro_text'] ?? '');
$shortlist_empty        = (string) ($shortlist_settings['empty_text'] ?? __('Your wishlist is empty.', 'plogins-shortlist'));
$shortlist_columns      = max(1, (int) ($shortlist_settings['grid_columns'] ?? 3));
$shortlist_show_title   = (bool) ($shortlist_settings['show_list_title'] ?? true);
$shortlist_show_image   = (bool) ($shortlist_settings['show_product_image'] ?? true);
$shortlist_show_name    = (bool) ($shortlist_settings['show_product_name'] ?? true);
$shortlist_show_price   = (bool) ($shortlist_settings['show_price'] ?? true);
$shortlist_show_cart    = (bool) ($shortlist_settings['show_add_to_cart'] ?? true);
$shortlist_show_remove  = (bool) ($shortlist_settings['show_remove_button'] ?? true);
$shortlist_remove_label = (string) ($shortlist_settings['button_remove_text'] ?? __('Remove from wishlist', 'plogins-shortlist'));
?>
<div class="shortlist-wishlist-account">
    <?php if ($shortlist_show_title) : ?>
        <h2><?php echo esc_html($shortlist_title); ?></h2>
    <?php endif; ?>

    <?php if ($shortlist_intro !== '') : ?>
        <div class="shortlist-wishlist-account__intro">
            <?php echo wp_kses_post(wpautop($shortlist_intro)); ?>
        </div>
    <?php endif; ?>

    <?php if ($shortlist_products === []) : ?>
        <div class="shortlist-wishlist-account__empty">
            <p><?php echo esc_html($shortlist_empty); ?></p>
            <?php
            $shortlist_shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';
            if (is_string($shortlist_shop_url) && $shortlist_shop_url !== '') :
                ?>
                <a class="button shortlist-wishlist-account__continue" href="<?php echo esc_url($shortlist_shop_url); ?>">
                    <?php esc_html_e('Browse products', 'plogins-shortlist'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <ul
            class="products columns-<?php echo esc_attr((string) $shortlist_columns); ?> shortlist-wishlist-grid"
            style="--shortlist-columns:<?php echo esc_attr((string) $shortlist_columns); ?>;"
        >
            <?php foreach ($shortlist_products as $shortlist_product) : ?>
                <?php if (! $shortlist_product instanceof \WC_Product) { continue; } ?>
                <li class="product shortlist-wishlist-item">
                    <a href="<?php echo esc_url(get_permalink($shortlist_product->get_id()) ?: ''); ?>">
                        <?php if ($shortlist_show_image) : ?>
                            <?php echo $shortlist_product->get_image('woocommerce_thumbnail'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce returns escaped image markup. ?>
                        <?php endif; ?>
                        <?php if ($shortlist_show_name) : ?>
                            <h3><?php echo esc_html($shortlist_product->get_name()); ?></h3>
                        <?php endif; ?>
                    </a>
                    <?php if ($shortlist_show_price && $shortlist_product->get_price_html() !== '') : ?>
                        <span class="price"><?php echo $shortlist_product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce returns escaped price markup. ?></span>
                    <?php endif; ?>
                    <?php if ($shortlist_show_cart && $shortlist_product->is_purchasable() && $shortlist_product->is_in_stock() && $shortlist_product->supports('ajax_add_to_cart')) : ?>
                        <a
                            href="<?php echo esc_url($shortlist_product->add_to_cart_url()); ?>"
                            data-quantity="1"
                            class="button add_to_cart_button ajax_add_to_cart"
                            data-product_id="<?php echo esc_attr((string) $shortlist_product->get_id()); ?>"
                            data-product_sku="<?php echo esc_attr($shortlist_product->get_sku()); ?>"
                            aria-label="<?php echo esc_attr(wp_strip_all_tags($shortlist_product->add_to_cart_description())); ?>"
                            rel="nofollow"
                        >
                            <?php echo esc_html($shortlist_product->add_to_cart_text()); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($shortlist_show_remove) : ?>
                        <button
                            type="button"
                            class="button shortlist-wishlist-button shortlist-wishlist-button--loop is-active"
                            data-shortlist-wishlist-button
                            data-product-id="<?php echo esc_attr((string) $shortlist_product->get_id()); ?>"
                            aria-pressed="true"
                        >
                            <?php echo esc_html($shortlist_remove_label); ?>
                        </button>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
