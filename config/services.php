<?php
/**
 * Service wiring. Returns a closure that registers every service in the
 * container. Keep services thin; product logic lives in storefront-kit engines
 * instantiated here with this plugin's text-domain / option prefix / asset URLs.
 *
 * @package Shortlist
 */

declare(strict_types=1);

use Shortlist\Admin\Settings;
use Shortlist\Block\WishlistBlock;
use Shortlist\Container;
use Shortlist\Migrator;
use Shortlist\Service\ShortlistService;
use Shortlist\Service\WishlistPageService;

defined('ABSPATH') || exit;

return static function (Container $c): void {
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());

    // Thin adapter over the storefront-kit WishlistEngine.
    $c->singleton(ShortlistService::class, static fn (): ShortlistService => new ShortlistService());

    $c->singleton(
        WishlistPageService::class,
        static fn (Container $c): WishlistPageService => new WishlistPageService($c->get(ShortlistService::class)),
    );

    // Gutenberg block: server-rendered, delegates to the shortcode body.
    $c->singleton(
        WishlistBlock::class,
        static fn (Container $c): WishlistBlock => new WishlistBlock($c->get(ShortlistService::class)),
    );

    // Admin (only needed in wp-admin context).
    if (is_admin()) {
        $c->singleton(Settings::class, static fn (): Settings => new Settings());
    }
};
