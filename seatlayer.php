<?php
/**
 * Plugin Name:       SeatLayer Seating Charts
 * Plugin URI:        https://seatlayer.io/integrations/wordpress
 * Description:       Interactive seating charts and seat maps for WordPress. Buyers choose seats and buy event tickets through your Stripe or Razorpay account.
 * Version:           0.2.2
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            SeatLayer
 * Author URI:        https://seatlayer.io
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       seatlayer-seating-charts
 *
 * @package SeatLayer
 */

/**
 * ## Why this plugin has no Composer dependency
 *
 * SeatLayer publishes a PHP SDK (`seatlayer/seatlayer-php`), and the obvious
 * design would be to depend on it. This plugin deliberately does not, for three
 * reasons that all point the same way:
 *
 * 1. **A Tier-1 embed needs almost no server-side API.** The buyer widget is
 *    keyless — the event key IS the credential — and hosted checkout is a public
 *    endpoint. The only authenticated call in the whole plugin is listing events
 *    to populate a dropdown in the block editor.
 * 2. **WordPress has its own HTTP layer.** `wp_remote_get()` honours site proxy
 *    configuration, timeouts, and the `http_request_args` filters administrators
 *    and hosts rely on. The SDK uses cURL directly, which bypasses all of that.
 * 3. **Bundled Composer trees are how WordPress plugins break each other.** Two
 *    plugins vendoring different versions of the same library in one PHP process
 *    is a classic conflict, and users install plugins as a zip from wp-admin
 *    rather than running `composer install`.
 *
 * (`seatlayer/seatlayer-php` is also not currently resolvable on Packagist, so
 * declaring it would have broken installation outright. That is a reason to
 * check, not the reason for this design.)
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SEATLAYER_VERSION', '0.2.2' );
define( 'SEATLAYER_PLUGIN_FILE', __FILE__ );
define( 'SEATLAYER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEATLAYER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Pinned CDN major. The filename is constant across versions, so tracking a
 * newer release is a one-token change here rather than a rewrite. Pinned to a
 * MAJOR alias (not `latest`) so a published breaking change cannot reach a live
 * buyer page without someone choosing it.
 */
define( 'SEATLAYER_SDK_VERSION', '0' );

require_once SEATLAYER_PLUGIN_DIR . 'includes/class-seatlayer-settings.php';
require_once SEATLAYER_PLUGIN_DIR . 'includes/class-seatlayer-api.php';
require_once SEATLAYER_PLUGIN_DIR . 'includes/class-seatlayer-rest.php';
require_once SEATLAYER_PLUGIN_DIR . 'includes/class-seatlayer-render.php';
require_once SEATLAYER_PLUGIN_DIR . 'includes/class-seatlayer-block.php';

/**
 * Boot the plugin.
 */
function seatlayer_bootstrap(): void {
	SeatLayer_Settings::init();
	SeatLayer_REST::init();
	SeatLayer_Render::init();
	SeatLayer_Block::init();
}
add_action( 'plugins_loaded', 'seatlayer_bootstrap' );

/*
 * No `load_plugin_textdomain()` call and no `Domain Path` header, deliberately.
 *
 * WordPress has loaded translations for wordpress.org-hosted plugins automatically
 * since 4.6, keyed on the plugin slug — calling it by hand is redundant, and Plugin
 * Check flags it. The `Domain Path: /languages` header was worse than redundant: it
 * pointed at a directory that has never existed in this repository, which Plugin
 * Check reports as a broken header.
 *
 * If this plugin ever ships translations OUTSIDE wordpress.org, both come back
 * together — the header, the directory, and the loader call. One without the others
 * is the state we just removed.
 */
