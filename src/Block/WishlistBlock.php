<?php

declare(strict_types=1);

namespace Shortlist\Block;

defined('ABSPATH') || exit;

use Shortlist\Contract\HasHooks;
use Shortlist\Service\ShortlistService;

/**
 * Registers the `shortlist/wishlist` Gutenberg block.
 *
 * A dynamic, server-rendered block that shows the current shopper's wishlist —
 * the same body as the `[shortlist]` shortcode. The block is defined by the
 * bundled `blocks/wishlist/block.json` and rendered through the injected
 * {@see ShortlistService}, so the editor preview and the front end match exactly.
 * The hand-written editor script depends only on @wordpress/* handles shipped by
 * core, so the plugin needs no build step.
 */
final class WishlistBlock implements HasHooks
{
    public function __construct(private readonly ShortlistService $service)
    {
    }

    public function registerHooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        if (! function_exists('register_block_type')) {
            return;
        }

        register_block_type(
            SHORTLIST_DIR . 'blocks/wishlist',
            ['render_callback' => [$this, 'render']],
        );
    }

    /**
     * Server render: delegate to the shortcode body so block and shortcode match.
     */
    public function render(): string
    {
        return $this->service->renderShortcode();
    }
}
