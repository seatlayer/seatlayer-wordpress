<?php
/**
 * Gutenberg block registration.
 *
 * @package SeatLayer
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The SeatLayer block.
 *
 * Server-side rendered via `render_callback`, so the block and the shortcode go
 * through the same `SeatLayer_Render::markup()` and cannot drift. It also means
 * the pinned CDN version and the configured API URL are resolved at render time
 * rather than frozen into post content when the author saved.
 *
 * The editor script is deliberately plain ES5-era JavaScript against the `wp.*`
 * globals, with NO build step. For a block whose whole job is "pick an event and
 * set a height", a webpack/npm toolchain would add a build artifact to review, a
 * lockfile to maintain, and a reason for the plugin to rot — without changing what
 * the author sees.
 */
class SeatLayer_Block {

	const HANDLE = 'seatlayer-block';

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the block type and its editor script.
	 */
	public static function register(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			self::HANDLE,
			SEATLAYER_PLUGIN_URL . 'assets/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-api-fetch' ),
			SEATLAYER_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( self::HANDLE, 'seatlayer-seating-charts' );
		}

		register_block_type(
			'seatlayer/chart',
			array(
				'api_version'     => 2,
				'title'           => __( 'SeatLayer seating chart', 'seatlayer-seating-charts' ),
				'description'     => __( 'Let visitors pick seats and buy tickets.', 'seatlayer-seating-charts' ),
				'category'        => 'embed',
				'icon'            => 'tickets-alt',
				'keywords'        => array(
					__( 'seating', 'seatlayer-seating-charts' ),
					__( 'tickets', 'seatlayer-seating-charts' ),
					__( 'seats', 'seatlayer-seating-charts' ),
					__( 'events', 'seatlayer-seating-charts' ),
				),
				'editor_script'   => self::HANDLE,
				'attributes'      => array(
					'event'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'height'       => array(
						'type'    => 'number',
						'default' => 640,
					),
					'maxSelection' => array(
						'type'    => 'number',
						'default' => 10,
					),
					'locale'       => array(
						'type'    => 'string',
						'default' => '',
					),
					'currency'     => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'supports'        => array(
					'html'   => false,
					'align'  => array( 'wide', 'full' ),
				),
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @param array<string,mixed> $attributes Block attributes.
	 */
	public static function render( array $attributes ): string {
		return SeatLayer_Render::markup(
			array(
				'event'         => isset( $attributes['event'] ) ? $attributes['event'] : '',
				'height'        => isset( $attributes['height'] ) ? $attributes['height'] : 640,
				'max_selection' => isset( $attributes['maxSelection'] ) ? $attributes['maxSelection'] : 10,
				'locale'        => isset( $attributes['locale'] ) ? $attributes['locale'] : '',
				'currency'      => isset( $attributes['currency'] ) ? $attributes['currency'] : '',
			)
		);
	}
}
