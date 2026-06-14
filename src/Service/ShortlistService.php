<?php

declare(strict_types=1);

namespace Shortlist\Service;

use Shortlist\Contract\HasHooks;
use Shortlist\Repository\WishlistTableRepository;
use WPPoland\StorefrontKit\Wishlist\WishlistEngine;

defined('ABSPATH') || exit;

/**
 * Thin adapter over the storefront-kit {@see WishlistEngine}.
 *
 * Injects this plugin's text-domain ('shortlist'), option prefix ('shortlist_'),
 * asset URLs and labels into the namespace-neutral engine, and supplies the two
 * closures the engine needs: one to render the packaged loop/single buttons and
 * one to build the My Account / shortcode list HTML. All wishlist orchestration
 * (cookie, ownership, AJAX toggle, account endpoint, guest->user transfer) lives
 * in the kit; this class wires localisation, option storage, asset paths and the
 * `[shortlist]` shortcode. Storage is delegated to {@see WishlistTableRepository}.
 */
final class ShortlistService implements HasHooks
{
    private const OPTION = 'shortlist_settings';

    private ?WishlistEngine $engine = null;

    public function __construct()
    {
        // The engine ships with storefront-kit >= 1.4.0. When present, wire it
        // with this plugin's text-domain / option prefix / asset URLs. Otherwise
        // leave the service inert (see registerHooks()).
        if (! class_exists(WishlistEngine::class)) {
            return;
        }

        $this->engine = new WishlistEngine(
            repository: new WishlistTableRepository(),
            ajaxAction: 'shortlist_wishlist_toggle',
            nonceAction: 'shortlist_wishlist',
            scriptObjectName: 'shortlistWishlist',
            assetHandle: 'shortlist',
            styleUrl: \Shortlist\Plugin::instance()->url('assets/css/wishlist.css'),
            scriptUrl: \Shortlist\Plugin::instance()->url('assets/js/wishlist.js'),
            version: \Shortlist\VERSION,
            endpoint: 'shortlist',
            guestCookie: 'shortlist_session',
            loopButtonTemplate: 'loop-wishlist-button',
            singleButtonTemplate: 'single-wishlist-button',
            accountTemplate: 'account-wishlist',
            labels: [
                'add'            => __('Add to wishlist', 'shortlist'),
                'remove'         => __('Remove from wishlist', 'shortlist'),
                'account'        => __('Wishlist', 'shortlist'),
                'login_required' => __('Please log in to use your wishlist.', 'shortlist'),
                'not_found'      => __('Product not found.', 'shortlist'),
            ],
            isEnabled: fn (): bool => $this->isEnabled(),
            settings: fn (): array => $this->settings(),
            renderTemplate: function (string $template, array $context): void {
                $this->renderTemplate($template, $context);
            },
            renderAccount: fn (string $template, array $context): string => $this->renderAccount($template, $context),
        );
    }

    public function registerHooks(): void
    {
        if (! $this->engine instanceof WishlistEngine) {
            // TODO: storefront-kit < 1.4.0 has no WishlistEngine. Bump the
            // `wppoland/storefront-kit` constraint (composer update) to enable
            // the wishlist. No hooks are registered until the engine is present.
            return;
        }

        $this->engine->registerHooks();
        add_shortcode('shortlist', [$this, 'renderShortcode']);

        // Extend the kit's localized config object with translatable status
        // strings used by our script's aria-live announcements. Runs after the
        // engine's own enqueue (priority 10) so the handle exists.
        add_action('wp_enqueue_scripts', [$this, 'localizeStrings'], 20);

        // Append the saved-item count to the My Account "Wishlist" menu label.
        // The kit engine adds the menu item on the same filter at the default
        // priority (10); run later (20) so the count reflects the final label.
        add_filter('woocommerce_account_menu_items', [$this, 'appendAccountCount'], 20);
    }

    /**
     * Append the saved-item count to the My Account wishlist menu label, e.g.
     * "Wishlist (3)". No-op when the count toggle is off or the item is absent.
     *
     * @param array<string, string> $items
     * @return array<string, string>
     */
    public function appendAccountCount(array $items): array
    {
        if (! $this->engine instanceof WishlistEngine) {
            return $items;
        }

        $settings = $this->settings();

        if (empty($settings['show_account_count']) || empty($settings['show_in_account'])) {
            return $items;
        }

        if (! isset($items['shortlist'])) {
            return $items;
        }

        $count = $this->engine->getCount();

        if ($count < 1) {
            return $items;
        }

        $items['shortlist'] = sprintf(
            /* translators: 1: wishlist menu label, 2: saved item count */
            _x('%1$s (%2$d)', 'My Account menu label with item count', 'shortlist'),
            $items['shortlist'],
            $count,
        );

        return $items;
    }

    /**
     * Merge translatable status strings into the kit's localized config object
     * (`shortlistWishlist`) so our front-end script can announce add/remove and
     * failure outcomes to assistive tech. Uses wp_add_inline_script so it merges
     * onto the existing object rather than replacing the kit's payload.
     */
    public function localizeStrings(): void
    {
        if (! wp_script_is('shortlist', 'enqueued')) {
            return;
        }

        $strings = [
            'addedText'   => __('Added to your wishlist.', 'shortlist'),
            'removedText' => __('Removed from your wishlist.', 'shortlist'),
            'errorText'   => __('Sorry, something went wrong. Please try again.', 'shortlist'),
        ];

        $inline = sprintf(
            'window.shortlistWishlist=Object.assign(window.shortlistWishlist||{},%s);',
            wp_json_encode($strings),
        );

        wp_add_inline_script('shortlist', $inline, 'before');
    }

    /**
     * `[shortlist]` shortcode: renders the current visitor's wishlist body.
     */
    public function renderShortcode(): string
    {
        if (! $this->engine instanceof WishlistEngine) {
            return '';
        }

        return $this->engine->renderWishlist();
    }

    private function isEnabled(): bool
    {
        return (bool) ($this->settings()['enabled'] ?? false);
    }

    /**
     * Echo a packaged button template (loop / single).
     *
     * @param array<string, mixed> $context
     */
    private function renderTemplate(string $template, array $context): void
    {
        $file = SHORTLIST_DIR . 'templates/' . $template . '.php';

        if (! is_readable($file)) {
            return;
        }

        $shortlist_button   = isset($context['button']) && is_array($context['button']) ? $context['button'] : [];
        $shortlist_settings = isset($context['settings']) && is_array($context['settings']) ? $context['settings'] : [];

        require $file;
    }

    /**
     * Build the My Account / shortcode list HTML.
     *
     * @param array<string, mixed> $context
     */
    private function renderAccount(string $template, array $context): string
    {
        $file = SHORTLIST_DIR . 'templates/' . $template . '.php';

        if (! is_readable($file)) {
            return '';
        }

        /** @var list<\WC_Product> $shortlist_products */
        $shortlist_products = isset($context['products']) && is_array($context['products']) ? $context['products'] : [];
        $shortlist_settings = isset($context['settings']) && is_array($context['settings']) ? $context['settings'] : [];

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    /**
     * Stored settings merged over packaged defaults.
     *
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require SHORTLIST_DIR . 'config/defaults.php';

        return array_merge($defaults, $stored);
    }
}
