<?php
/**
 * Boot order: services listed here are resolved from the container and have
 * their registerHooks() called during Plugin::boot(). Each must implement
 * Shortlist\Contract\HasHooks.
 *
 * @package Shortlist
 *
 * @return array<class-string>
 */

declare(strict_types=1);

use Shortlist\Admin\Settings;
use Shortlist\Block\WishlistBlock;
use Shortlist\Service\ShortlistService;
use Shortlist\Service\WishlistPageService;

defined('ABSPATH') || exit;

return [
    ShortlistService::class,
    WishlistPageService::class,
    WishlistBlock::class,
    ...(is_admin() ? [Settings::class] : []),
];
