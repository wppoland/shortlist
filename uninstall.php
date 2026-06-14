<?php
/**
 * Uninstall cleanup for Shortlist.
 *
 * Runs only when the plugin is deleted from wp-admin. Removes the custom
 * wishlist items table and every option the plugin stores, so deleting the
 * plugin leaves no data behind.
 *
 * @package Shortlist
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

// Drop the custom wishlist items table created by the Migrator.
$shortlist_table = $wpdb->prefix . 'shortlist_items';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Custom plugin table removed on uninstall.
$wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS %i', $shortlist_table));

// Remove plugin options.
delete_option('shortlist_settings');
delete_option('shortlist_db_version');
