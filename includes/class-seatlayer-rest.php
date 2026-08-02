<?php
/**
 * Admin-only REST proxy for the block editor.
 *
 * @package SeatLayer
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One route, existing purely so the block editor can populate an event dropdown
 * without the secret key ever reaching a browser.
 *
 * It is a proxy, not a passthrough: the response is projected to four fields (see
 * SeatLayer_API), so widening what SeatLayer's own API returns cannot silently
 * widen what this route exposes.
 *
 * `permission_callback` requires `edit_posts`. That is the right bar because the
 * only thing the route reveals is which events the site could embed — the same
 * information anyone able to edit a page would obtain by embedding one. It is
 * deliberately NOT `manage_options`, which would stop editors using the block on
 * sites where they legitimately should.
 */
class SeatLayer_REST {

	const NAMESPACE = 'seatlayer/v1';

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register the events route.
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/events',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_events' ),
				'permission_callback' => array( __CLASS__, 'can_read_events' ),
				'args'                => array(
					'refresh' => array(
						'type'        => 'boolean',
						'default'     => false,
						'description' => __( 'Bypass the short server-side cache.', 'seatlayer' ),
					),
				),
			)
		);
	}

	/**
	 * Only users who can author content may list embeddable events.
	 */
	public static function can_read_events(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Return the event list, or a structured error the editor can display.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function get_events( WP_REST_Request $request ): WP_REST_Response {
		$result = SeatLayer_API::list_events( (bool) $request->get_param( 'refresh' ) );

		if ( ! $result['ok'] ) {
			// 200 with an `ok: false` body rather than an HTTP error: the editor
			// shows this inline next to a manual-entry field, and a 4xx/5xx would
			// make the block look broken when the honest state is "no key saved".
			return new WP_REST_Response(
				array(
					'ok'    => false,
					'error' => isset( $result['error'] ) ? $result['error'] : '',
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'ok'     => true,
				'events' => isset( $result['events'] ) ? $result['events'] : array(),
			),
			200
		);
	}
}
