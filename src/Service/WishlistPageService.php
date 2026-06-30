<?php

declare(strict_types=1);

namespace Shortlist\Service;

use Shortlist\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Dedicated wishlist page: admin page picker, one-click page creation, and optional
 * auto-injection of the list when the chosen page has no [shortlist] shortcode yet.
 */
final class WishlistPageService implements HasHooks
{
    private const OPTION = 'shortlist_settings';
    private const CREATE_ACTION = 'shortlist_create_wishlist_page';

    public function __construct(
        private readonly ShortlistService $shortlist,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('admin_post_' . self::CREATE_ACTION, [$this, 'handleCreatePage']);
        add_filter('the_content', [$this, 'maybeInjectWishlist'], 20);
    }

    public function handleCreatePage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to create pages.', 'plogins-shortlist'));
        }

        check_admin_referer('shortlist_create_wishlist_page');

        $pageId = wp_insert_post(
            [
                'post_title'   => __('My wishlist', 'plogins-shortlist'),
                'post_name'    => 'my-wishlist',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => "<!-- wp:shortcode -->\n[shortlist]\n<!-- /wp:shortcode -->",
            ],
            true,
        );

        if (! is_wp_error($pageId) && is_int($pageId) && $pageId > 0) {
            $settings = $this->storedSettings();
            $settings['wishlist_page_id'] = $pageId;
            update_option(self::OPTION, $settings);
            set_transient('shortlist_page_created_' . get_current_user_id(), 1, MINUTE_IN_SECONDS);
        }

        wp_safe_redirect(
            add_query_arg(
                [
                    'page' => 'shortlist-settings',
                ],
                admin_url('admin.php'),
            ),
        );
        exit;
    }

    public function maybeInjectWishlist(string $content): string
    {
        if (! is_page() || ! in_the_loop() || ! is_main_query()) {
            return $content;
        }

        if (! $this->shortlist->isEnabled()) {
            return $content;
        }

        $settings = $this->resolvedSettings();

        if (empty($settings['inject_wishlist_on_page'])) {
            return $content;
        }

        $pageId = (int) ($settings['wishlist_page_id'] ?? 0);

        if ($pageId <= 0 || get_queried_object_id() !== $pageId) {
            return $content;
        }

        if (has_shortcode($content, 'shortlist')) {
            return $content;
        }

        $wishlist = $this->shortlist->renderWishlistHtml();

        if ($wishlist === '') {
            return $content;
        }

        return $wishlist . $content;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvedSettings(): array
    {
        /** @var array<string, mixed> $defaults */
        $defaults = require SHORTLIST_DIR . 'config/defaults.php';

        return array_merge($defaults, $this->storedSettings());
    }

    /**
     * @return array<string, mixed>
     */
    private function storedSettings(): array
    {
        $stored = get_option(self::OPTION, []);

        return is_array($stored) ? $stored : [];
    }
}
