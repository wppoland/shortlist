<?php

declare(strict_types=1);

namespace Shortlist\Admin;

defined('ABSPATH') || exit;

use Shortlist\Contract\HasHooks;

/**
 * Admin settings page registered as a top-level "Shortlist" menu.
 *
 * Stores settings in the `shortlist_settings` option (array): the master toggle,
 * guest access, where the add-to-wishlist button renders, the toggle button
 * labels, and the My Account / shortcode list appearance (columns, which product
 * details to show, and the editable list strings). Every control carries inline
 * help: the WP Settings API `description` text plus an accessible "?" popover
 * (native Popover API, JS-fallback for older browsers) explaining the effect.
 * All output is escaped; all input is sanitised on save. The save capability is
 * aligned to `manage_woocommerce` so shop managers can save.
 */
final class Settings implements HasHooks
{
    private const OPTION = 'shortlist_settings';
    private const PAGE   = 'shortlist-settings';

    /** Incrementing id so each help popover gets a unique target. */
    private int $helpSeq = 0;

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenuPage(): void
    {
        add_menu_page(
            __('Shortlist Settings', 'plogins-shortlist'),
            __('Shortlist', 'plogins-shortlist'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
            'dashicons-heart',
            58,
        );
    }

    /**
     * Enqueue the settings-page stylesheet and help-popover fallback script,
     * only on our own admin page. Real files (no inline blobs) so Plugin Check
     * stays clean; the script is deferred and loaded in the footer.
     */
    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'toplevel_page_' . self::PAGE) {
            return;
        }

        wp_enqueue_style(
            'shortlist-admin',
            \Shortlist\Plugin::instance()->url('assets/css/admin-settings.css'),
            [],
            \Shortlist\VERSION,
        );

        wp_enqueue_script(
            'shortlist-admin',
            \Shortlist\Plugin::instance()->url('assets/js/admin-settings.js'),
            [],
            \Shortlist\VERSION,
            [
                'in_footer' => true,
                'strategy'  => 'defer',
            ],
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            self::PAGE,
            self::OPTION,
            [
                'type'              => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
            ],
        );

        // The menu uses manage_woocommerce; align the options.php save capability
        // so shop managers (not just admins with manage_options) can save.
        add_filter(
            'option_page_capability_' . self::PAGE,
            static fn (): string => 'manage_woocommerce',
        );
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $settings = $this->settings();

        $noticeKey = 'shortlist_page_created_' . get_current_user_id();
        if (get_transient($noticeKey)) {
            delete_transient($noticeKey);
            echo '<div class="notice notice-success is-dismissible"><p>';
            esc_html_e('Your wishlist page was created and selected below. You can edit the title or slug anytime under Pages.', 'plogins-shortlist');
            echo '</p></div>';
        }
        ?>
        <div class="wrap shortlist-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p class="shortlist-admin__intro">
                <?php esc_html_e('Give shoppers an accessible "save for later" wishlist. Choose where the button appears, tune the labels, and design the list shown on the My Account tab, the [shortlist] shortcode, and the Shortlist block.', 'plogins-shortlist'); ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE); ?>

                <div class="shortlist-admin__card">
                    <h2><?php esc_html_e('General', 'plogins-shortlist'); ?></h2>
                    <p class="shortlist-admin__card-desc">
                        <?php esc_html_e('Turn the wishlist on and decide who can use it.', 'plogins-shortlist'); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <?php
                            $this->checkboxRow(
                                'enabled',
                                __('Enable wishlist', 'plogins-shortlist'),
                                __('Let visitors add products to a wishlist.', 'plogins-shortlist'),
                                __('Master switch. When off, no buttons, menu item, shortcode or block output is rendered anywhere on your store - nothing is deleted, so you can switch it back on at any time.', 'plogins-shortlist'),
                                $settings,
                                true,
                            );
                            $this->checkboxRow(
                                'allow_guests',
                                __('Allow guests', 'plogins-shortlist'),
                                __('Allow logged-out visitors to build a wishlist.', 'plogins-shortlist'),
                                __('Guest lists are stored in a cookie and automatically merged into the customer account on login, so nothing is lost. Turn this off to require an account first - logged-out shoppers are then sent to the login page when they click the button.', 'plogins-shortlist'),
                                $settings,
                                true,
                            );
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="shortlist-admin__card">
                    <h2><?php esc_html_e('Button placement', 'plogins-shortlist'); ?></h2>
                    <p class="shortlist-admin__card-desc">
                        <?php esc_html_e('Pick where the add-to-wishlist button and the My Account tab appear.', 'plogins-shortlist'); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <?php
                            $this->checkboxRow(
                                'show_on_single',
                                __('Single product page', 'plogins-shortlist'),
                                __('Show the add-to-wishlist button on the product page.', 'plogins-shortlist'),
                                __('Adds the toggle button to the product summary, just under the add-to-cart area.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_on_loop',
                                __('Shop and archive loops', 'plogins-shortlist'),
                                __('Show the add-to-wishlist button on product cards in the shop loop.', 'plogins-shortlist'),
                                __('Adds a full-width toggle to each product card on the shop, category and tag pages so shoppers can save without opening the product.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_in_account',
                                __('My Account menu', 'plogins-shortlist'),
                                __('Add a "Wishlist" tab to the WooCommerce My Account area.', 'plogins-shortlist'),
                                __('Gives logged-in customers a dedicated Wishlist tab in My Account that lists everything they have saved.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_account_count',
                                __('Show item count', 'plogins-shortlist'),
                                __('Show the number of saved items next to the My Account "Wishlist" menu label.', 'plogins-shortlist'),
                                __('Appends a live count, e.g. "Wishlist (3)", to the menu label so customers see at a glance how many items they have saved. Requires the My Account menu option above.', 'plogins-shortlist'),
                                $settings,
                            );
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="shortlist-admin__card">
                    <h2><?php esc_html_e('Button labels', 'plogins-shortlist'); ?></h2>
                    <p class="shortlist-admin__card-desc">
                        <?php esc_html_e('The text shown on the toggle button. It switches between the two as items are saved and removed.', 'plogins-shortlist'); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <?php
                            $this->textRow(
                                'button_add_text',
                                __('Add label', 'plogins-shortlist'),
                                __('Add to wishlist', 'plogins-shortlist'),
                                __('Shown when the product is not yet saved. Leave blank to use the default "Add to wishlist".', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->textRow(
                                'button_remove_text',
                                __('Remove label', 'plogins-shortlist'),
                                __('Remove from wishlist', 'plogins-shortlist'),
                                __('Shown once the product is saved, so clicking again removes it. Leave blank to use the default "Remove from wishlist".', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->textRow(
                                'variation_required_text',
                                __('Variation hint', 'plogins-shortlist'),
                                __('Choose product options before adding to your wishlist.', 'plogins-shortlist'),
                                __('Shown under the button on variable products until the shopper picks size, colour, or other options.', 'plogins-shortlist'),
                                $settings,
                            );
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="shortlist-admin__card">
                    <h2><?php esc_html_e('Dedicated wishlist page', 'plogins-shortlist'); ?></h2>
                    <p class="shortlist-admin__card-desc">
                        <?php esc_html_e('Give shoppers a bookmarkable page with their saved products, great for navigation menus and email campaigns.', 'plogins-shortlist'); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label for="shortlist_wishlist_page_id"><?php esc_html_e('Wishlist page', 'plogins-shortlist'); ?></label>
                                    <?php echo $this->helpAffordance(__('Pick an existing page or create one with the button below. Shortlist loads its assets on that page automatically.', 'plogins-shortlist')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts in helpAffordance(). ?>
                                </th>
                                <td>
                                    <?php
                                    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_dropdown_pages() escapes its own markup.
                                    wp_dropdown_pages(
                                        [
                                            'name'              => self::OPTION . '[wishlist_page_id]',
                                            'id'                => 'shortlist_wishlist_page_id',
                                            'selected'          => (int) ($settings['wishlist_page_id'] ?? 0),
                                            'show_option_none'  => __(', None, use My Account or shortcode only, ', 'plogins-shortlist'),
                                            'option_none_value' => '0',
                                        ],
                                    );
                                    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
                                    ?>
                                    <p class="description">
                                        <?php esc_html_e('Optional. When set, the wishlist stylesheet and script load on that page so buttons and remove actions work there too.', 'plogins-shortlist'); ?>
                                    </p>
                                </td>
                            </tr>
                            <?php
                            $this->checkboxRow(
                                'inject_wishlist_on_page',
                                __('Show list on page', 'plogins-shortlist'),
                                __('Automatically output the wishlist at the top of the chosen page.', 'plogins-shortlist'),
                                __('Helpful when the page is still empty: shoppers see their saved products immediately. If the page already contains [shortlist], this stays off to avoid duplicates.', 'plogins-shortlist'),
                                $settings,
                                true,
                            );
                            ?>
                        </tbody>
                    </table>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="shortlist-admin__inline-form">
                        <?php wp_nonce_field('shortlist_create_wishlist_page'); ?>
                        <input type="hidden" name="action" value="shortlist_create_wishlist_page" />
                        <?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- submit_button() escapes its own markup.
                        submit_button(__('Create wishlist page', 'plogins-shortlist'), 'secondary', 'submit', false);
                        // phpcs:enable
                        ?>
                        <p class="description">
                            <?php esc_html_e('Creates a published page titled “My wishlist” with the [shortlist] shortcode and selects it above.', 'plogins-shortlist'); ?>
                        </p>
                    </form>
                </div>

                <div class="shortlist-admin__card">
                    <h2><?php esc_html_e('Wishlist list', 'plogins-shortlist'); ?></h2>
                    <p class="shortlist-admin__card-desc">
                        <?php esc_html_e('Design the list shown on the My Account tab and via the [shortlist] shortcode and the Shortlist block.', 'plogins-shortlist'); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <?php
                            $this->textRow(
                                'account_title',
                                __('List heading', 'plogins-shortlist'),
                                __('My wishlist', 'plogins-shortlist'),
                                __('The heading shown above the saved products. Only displayed when "Show heading" below is enabled.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->textRow(
                                'account_intro_text',
                                __('Intro text', 'plogins-shortlist'),
                                __('e.g. Items you saved for later', 'plogins-shortlist'),
                                __('Optional short paragraph shown under the heading - a good place to reassure shoppers their list is private to them. Leave blank to hide it.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->textRow(
                                'empty_text',
                                __('Empty-list message', 'plogins-shortlist'),
                                __('Your wishlist is empty.', 'plogins-shortlist'),
                                __('Friendly message shown when the shopper has not saved anything yet. A "Browse products" link is added automatically below it.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->numberRow(
                                'grid_columns',
                                __('Columns', 'plogins-shortlist'),
                                __('How many products sit side by side in the grid (1-6). The layout automatically drops to a comfortable number of columns on small screens.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_list_title',
                                __('Show heading', 'plogins-shortlist'),
                                __('Show the list heading above the products.', 'plogins-shortlist'),
                                __('Toggles the "List heading" above. Turn off if your page already has its own title.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_product_image',
                                __('Show image', 'plogins-shortlist'),
                                __('Show the product thumbnail.', 'plogins-shortlist'),
                                __('Displays each saved product\'s featured image. Space is reserved for it so the page never jumps as images load.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_product_name',
                                __('Show name', 'plogins-shortlist'),
                                __('Show the product name.', 'plogins-shortlist'),
                                __('Displays the product title as a link to the product page.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_price',
                                __('Show price', 'plogins-shortlist'),
                                __('Show the product price.', 'plogins-shortlist'),
                                __('Displays the current price, including any sale price, exactly as WooCommerce formats it.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_add_to_cart',
                                __('Show add-to-cart', 'plogins-shortlist'),
                                __('Show an add-to-cart button for in-stock products.', 'plogins-shortlist'),
                                __('Lets shoppers move an item straight from their wishlist to the cart. The button is only shown for purchasable, in-stock products.', 'plogins-shortlist'),
                                $settings,
                            );
                            $this->checkboxRow(
                                'show_remove_button',
                                __('Show remove button', 'plogins-shortlist'),
                                __('Show a remove-from-wishlist button on each item.', 'plogins-shortlist'),
                                __('Adds a one-click remove button to each saved product so shoppers can tidy their list.', 'plogins-shortlist'),
                                $settings,
                            );
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the "?" help affordance: a button that opens an accessible popover
     * holding the explanatory text. Uses the native Popover API (popovertarget);
     * the bundled fallback script wires older browsers. Returns markup so it can
     * be embedded inside a label row.
     */
    private function helpAffordance(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $panelId = 'shortlist-help-' . (++$this->helpSeq);
        $anchor  = '--' . $panelId;

        return sprintf(
            '<span class="shortlist-help">'
            . '<button type="button" class="shortlist-help__toggle" '
            . 'popovertarget="%1$s" aria-expanded="false" aria-details="%1$s" '
            . 'style="%4$s" aria-label="%2$s">?</button>'
            . '<span id="%1$s" class="shortlist-help__panel" popover role="tooltip">%3$s</span>'
            . '</span>',
            esc_attr($panelId),
            /* translators: accessible name for the help button. */
            esc_attr__('More information', 'plogins-shortlist'),
            esc_html($text),
            esc_attr('--shortlist-anchor:' . $anchor . ';anchor-name:' . $anchor . ';'),
        );
    }

    /**
     * Render a single checkbox row in the form-table, with inline help text and
     * a "?" popover explaining the effect.
     *
     * @param array<string, mixed> $settings
     */
    private function checkboxRow(
        string $key,
        string $label,
        string $help,
        string $tip,
        array $settings,
        bool $defaultOn = false
    ): void {
        $id = 'shortlist_' . $key;
        ?>
        <tr>
            <th scope="row">
                <span class="shortlist-admin__label-row">
                    <?php echo esc_html($label); ?>
                    <?php echo $this->helpAffordance($tip); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts in helpAffordance(). ?>
                </span>
            </th>
            <td>
                <label for="<?php echo esc_attr($id); ?>">
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($id); ?>"
                        name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]"
                        value="1"
                        <?php checked((bool) ($settings[$key] ?? $defaultOn), true); ?>
                    />
                    <?php echo esc_html($help); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    /**
     * Render a single text-input row in the form-table, with a description and a
     * "?" popover.
     *
     * @param array<string, mixed> $settings
     */
    private function textRow(
        string $key,
        string $label,
        string $placeholder,
        string $tip,
        array $settings
    ): void {
        $id = 'shortlist_' . $key;
        ?>
        <tr>
            <th scope="row">
                <span class="shortlist-admin__label-row">
                    <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                    <?php echo $this->helpAffordance($tip); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts in helpAffordance(). ?>
                </span>
            </th>
            <td>
                <input
                    type="text"
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]"
                    value="<?php echo esc_attr((string) ($settings[$key] ?? '')); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    class="regular-text"
                />
                <p class="description"><?php echo esc_html($tip); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Render the columns number-input row, with a description and a "?" popover.
     *
     * @param array<string, mixed> $settings
     */
    private function numberRow(string $key, string $label, string $tip, array $settings): void
    {
        $id = 'shortlist_' . $key;
        ?>
        <tr>
            <th scope="row">
                <span class="shortlist-admin__label-row">
                    <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
                    <?php echo $this->helpAffordance($tip); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts in helpAffordance(). ?>
                </span>
            </th>
            <td>
                <input
                    type="number"
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr(self::OPTION); ?>[<?php echo esc_attr($key); ?>]"
                    value="<?php echo esc_attr((string) ($settings[$key] ?? 3)); ?>"
                    min="1"
                    max="6"
                    step="1"
                    class="small-text"
                />
                <p class="description"><?php echo esc_html($tip); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Sanitises the submitted settings before save, preserving defaults for any
     * field not on the form.
     *
     * @param mixed $raw
     * @return array<string, mixed>
     */
    public function sanitize(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [];
        }

        $defaults = $this->settings();

        $addText    = isset($raw['button_add_text']) ? sanitize_text_field((string) $raw['button_add_text']) : '';
        $removeText = isset($raw['button_remove_text']) ? sanitize_text_field((string) $raw['button_remove_text']) : '';

        $columns = isset($raw['grid_columns']) ? (int) $raw['grid_columns'] : (int) ($defaults['grid_columns'] ?? 3);
        $columns = max(1, min(6, $columns));

        $pageId = isset($raw['wishlist_page_id']) ? (int) $raw['wishlist_page_id'] : (int) ($defaults['wishlist_page_id'] ?? 0);
        if ($pageId > 0 && get_post_status($pageId) === false) {
            $pageId = 0;
        }

        $variationText = isset($raw['variation_required_text']) ? sanitize_text_field((string) $raw['variation_required_text']) : '';

        return array_merge($defaults, [
            'enabled'            => ! empty($raw['enabled']),
            'allow_guests'       => ! empty($raw['allow_guests']),
            'show_on_single'     => ! empty($raw['show_on_single']),
            'show_on_loop'       => ! empty($raw['show_on_loop']),
            'show_in_account'    => ! empty($raw['show_in_account']),
            'show_account_count' => ! empty($raw['show_account_count']),
            'button_add_text'    => $addText !== '' ? $addText : (string) ($defaults['button_add_text'] ?? __('Add to wishlist', 'plogins-shortlist')),
            'button_remove_text' => $removeText !== '' ? $removeText : (string) ($defaults['button_remove_text'] ?? __('Remove from wishlist', 'plogins-shortlist')),
            'account_title'      => isset($raw['account_title']) ? sanitize_text_field((string) $raw['account_title']) : (string) ($defaults['account_title'] ?? ''),
            'account_intro_text' => isset($raw['account_intro_text']) ? sanitize_text_field((string) $raw['account_intro_text']) : (string) ($defaults['account_intro_text'] ?? ''),
            'empty_text'         => isset($raw['empty_text']) ? sanitize_text_field((string) $raw['empty_text']) : (string) ($defaults['empty_text'] ?? ''),
            'grid_columns'       => $columns,
            'show_list_title'    => ! empty($raw['show_list_title']),
            'show_product_image' => ! empty($raw['show_product_image']),
            'show_product_name'  => ! empty($raw['show_product_name']),
            'show_price'         => ! empty($raw['show_price']),
            'show_add_to_cart'   => ! empty($raw['show_add_to_cart']),
            'show_remove_button' => ! empty($raw['show_remove_button']),
            'wishlist_page_id'        => $pageId,
            'inject_wishlist_on_page' => ! empty($raw['inject_wishlist_on_page']),
            'variation_required_text' => $variationText !== '' ? $variationText : (string) ($defaults['variation_required_text'] ?? ''),
        ]);
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
