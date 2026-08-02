<?php
/**
 * Server-side SeatLayer API calls.
 *
 * @package SeatLayer
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The plugin's entire authenticated API surface: listing events.
 *
 * Everything a BUYER does — loading the chart, holding seats, paying — happens
 * from the browser against SeatLayer's public endpoints, which take no key. So
 * this class exists only to make the block editor pleasant, and the secret key
 * never leaves the server.
 *
 * Uses `wp_remote_get()` rather than cURL so site proxy settings, timeouts, and
 * the `http_request_args` filters that hosts and admins depend on all apply.
 */
class SeatLayer_API {

	/** Editor convenience call — a short timeout is better than a hung screen. */
	const TIMEOUT = 10;

	/** Cache the event list briefly so opening the editor repeatedly is cheap. */
	const CACHE_TTL  = 60;
	const CACHE_KEY  = 'seatlayer_events_cache';

	/**
	 * List the account's events.
	 *
	 * @param bool $force Bypass the transient cache.
	 * @return array{ok:bool,events?:array<int,array<string,mixed>>,error?:string}
	 */
	public static function list_events( bool $force = false ): array {
		$key = SeatLayer_Settings::secret_key();
		if ( '' === $key ) {
			return array(
				'ok'    => false,
				'error' => __( 'No secret key saved. Add one in Settings → SeatLayer, or enter the event key by hand.', 'seatlayer' ),
			);
		}

		if ( ! $force ) {
			$cached = get_transient( self::CACHE_KEY );
			if ( is_array( $cached ) ) {
				return array(
					'ok'     => true,
					'events' => $cached,
				);
			}
		}

		$response = wp_remote_get(
			SeatLayer_Settings::api_base() . '/v1/events',
			array(
				'timeout' => self::TIMEOUT,
				'headers' => array(
					'Authorization' => 'Bearer ' . $key,
					'Accept'        => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'ok' => false,
				// The WP_Error message names the transport problem (DNS, timeout,
				// TLS), which is what an administrator needs to act on.
				'error' => sprintf(
					/* translators: %s: transport error message. */
					__( 'Could not reach SeatLayer: %s', 'seatlayer' ),
					$response->get_error_message()
				),
			);
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 401 === $status || 403 === $status ) {
			return array(
				'ok'    => false,
				'error' => __( 'SeatLayer rejected the secret key. Check it in Settings → SeatLayer.', 'seatlayer' ),
			);
		}

		if ( $status < 200 || $status > 299 || ! is_array( $body ) ) {
			return array(
				'ok'    => false,
				'error' => sprintf(
					/* translators: %d: HTTP status code. */
					__( 'SeatLayer returned an unexpected response (HTTP %d).', 'seatlayer' ),
					$status
				),
			);
		}

		$raw    = isset( $body['events'] ) && is_array( $body['events'] ) ? $body['events'] : array();
		$events = array();
		foreach ( $raw as $event ) {
			if ( ! is_array( $event ) ) {
				continue;
			}
			// Project to exactly what the editor dropdown shows. Anything else the
			// API returns is not the editor's business and should not sit in a
			// transient.
			$events[] = array(
				'key'    => isset( $event['key'] ) ? (string) $event['key'] : '',
				'name'   => isset( $event['name'] ) ? (string) $event['name'] : '',
				'venue'  => isset( $event['venue'] ) ? (string) $event['venue'] : '',
				'status' => isset( $event['status'] ) ? (string) $event['status'] : '',
			);
		}

		set_transient( self::CACHE_KEY, $events, self::CACHE_TTL );

		return array(
			'ok'     => true,
			'events' => $events,
		);
	}

	/**
	 * Drop the cached event list — called after the key changes.
	 */
	public static function flush_cache(): void {
		delete_transient( self::CACHE_KEY );
	}
}

// A new key must not keep serving the previous account's events.
add_action( 'update_option_' . SeatLayer_Settings::OPTION_KEY, array( 'SeatLayer_API', 'flush_cache' ) );
add_action( 'update_option_' . SeatLayer_Settings::OPTION_API, array( 'SeatLayer_API', 'flush_cache' ) );
