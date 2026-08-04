<?php
/**
 * Uninstall cleanup.
 *
 * WordPress runs this file (and only this file — the plugin is NOT loaded) when
 * an administrator deletes the plugin from the Plugins screen.
 *
 * The reason this exists is the secret key. Leaving a live credential in
 * `wp_options` after someone has deliberately removed the plugin is the kind of
 * thing that turns "I uninstalled it" into "I still have a key that can create
 * and modify events sitting in a database I stopped thinking about". Deactivation
 * deliberately leaves everything alone — only deletion clears it.
 *
 * @package SeatLayer
 */

declare( strict_types = 1 );

// Not a plugin file: this is only ever valid inside WordPress's uninstall path.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$seatlayer_options = array(
	'seatlayer_secret_key',
	'seatlayer_api_base',
	'seatlayer_app_base',
	'seatlayer_hosted_checkout',
);

foreach ( $seatlayer_options as $seatlayer_option ) {
	delete_option( $seatlayer_option );
}

// The editor's event-list cache. Harmless, but it is ours and it should go too.
delete_transient( 'seatlayer_events_cache' );

/*
 * Multisite: options are per-site, so a network with many sites needs each one
 * visited. Capped because `get_sites()` on a large network is not something an
 * uninstall request should try to page through — a network that big should use
 * WP-CLI, and the alternative (silently cleaning some sites) is worse than
 * cleaning none.
 */
if ( is_multisite() ) {
	$seatlayer_sites = get_sites(
		array(
			'fields' => 'ids',
			'number' => 500,
		)
	);

	foreach ( $seatlayer_sites as $seatlayer_site_id ) {
		switch_to_blog( (int) $seatlayer_site_id );
		foreach ( $seatlayer_options as $seatlayer_option ) {
			delete_option( $seatlayer_option );
		}
		delete_transient( 'seatlayer_events_cache' );
		restore_current_blog();
	}
}
