<?php
/**
 * Shortcode + shared frontend rendering.
 *
 * @package SeatLayer
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the buyer chart, for both the `[seatlayer_chart]` shortcode and the
 * block (the block's server-side render calls straight through to `markup()`, so
 * the two can never drift).
 *
 * The widget script is enqueued only on pages that actually contain a chart.
 * SeatLayer's bundle is not small, and loading a renderer on every page of a site
 * because one page embeds a chart is the kind of thing that gets a plugin
 * uninstalled.
 */
class SeatLayer_Render {

	const HANDLE_SDK      = 'seatlayer-sdk';
	const HANDLE_FRONTEND = 'seatlayer-frontend';

	/** Bounds for the container height attribute, in CSS pixels. */
	const MIN_HEIGHT = 320;
	const MAX_HEIGHT = 2000;

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_shortcode( 'seatlayer_chart', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	/**
	 * The CDN URL for the pinned SDK major.
	 */
	public static function sdk_url(): string {
		return 'https://cdn.seatlayer.io/seatlayer-js@' . SEATLAYER_SDK_VERSION . '/seatlayer.js';
	}

	/**
	 * Register (but do not enqueue) the frontend assets.
	 */
	public static function register_assets(): void {
		wp_register_script( self::HANDLE_SDK, self::sdk_url(), array(), null, true );

		wp_register_script(
			self::HANDLE_FRONTEND,
			SEATLAYER_PLUGIN_URL . 'assets/js/frontend.js',
			array( self::HANDLE_SDK ),
			SEATLAYER_VERSION,
			true
		);

		wp_register_style(
			self::HANDLE_FRONTEND,
			SEATLAYER_PLUGIN_URL . 'assets/css/frontend.css',
			array(),
			SEATLAYER_VERSION
		);
	}

	/**
	 * Enqueue on demand — called only when a chart is actually rendered.
	 */
	private static function enqueue(): void {
		wp_enqueue_script( self::HANDLE_SDK );
		wp_enqueue_script( self::HANDLE_FRONTEND );
		wp_enqueue_style( self::HANDLE_FRONTEND );
	}

	/**
	 * Normalize shortcode/block attributes into render config.
	 *
	 * @param array<string,mixed> $atts Raw attributes.
	 * @return array<string,mixed>
	 */
	public static function normalize( array $atts ): array {
		$event = isset( $atts['event'] ) ? sanitize_text_field( (string) $atts['event'] ) : '';

		$height = isset( $atts['height'] ) ? absint( $atts['height'] ) : 640;
		$height = max( self::MIN_HEIGHT, min( self::MAX_HEIGHT, $height ) );

		$max_selection = isset( $atts['max_selection'] ) ? absint( $atts['max_selection'] ) : 10;
		// 0 would mean "cannot select anything", which is never what an author
		// meant to type.
		$max_selection = max( 1, min( 50, $max_selection ) );

		return array(
			'event'        => $event,
			'height'       => $height,
			'maxSelection' => $max_selection,
			'locale'       => isset( $atts['locale'] ) ? sanitize_text_field( (string) $atts['locale'] ) : '',
			'currency'     => isset( $atts['currency'] ) ? strtoupper( sanitize_text_field( (string) $atts['currency'] ) ) : '',
			'apiBase'      => SeatLayer_Settings::api_base(),
			'appBase'      => SeatLayer_Settings::app_base(),
		);
	}

	/**
	 * Build the chart markup.
	 *
	 * Config rides on a `data-` attribute as JSON rather than an inline script, so
	 * the plugin adds no inline JavaScript and stays compatible with sites running
	 * a strict Content-Security-Policy.
	 *
	 * @param array<string,mixed> $atts Raw attributes.
	 */
	public static function markup( array $atts ): string {
		$config = self::normalize( $atts );

		if ( '' === $config['event'] ) {
			// Authors see the problem; visitors see nothing. A broken embed should
			// not put plugin diagnostics on a public page.
			if ( current_user_can( 'edit_posts' ) ) {
				return '<div class="seatlayer-notice">'
					. esc_html__( 'SeatLayer: no event selected for this chart.', 'seatlayer' )
					. '</div>';
			}
			return '';
		}

		self::enqueue();

		$json = wp_json_encode( $config );
		if ( false === $json ) {
			return '';
		}

		return sprintf(
			'<div class="seatlayer-chart" style="height:%1$dpx" data-seatlayer="%2$s"></div>',
			(int) $config['height'],
			esc_attr( $json )
		);
	}

	/**
	 * `[seatlayer_chart event="…"]`
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public static function shortcode( $atts ): string {
		$atts = shortcode_atts(
			array(
				'event'         => '',
				'height'        => 640,
				'max_selection' => 10,
				'locale'        => '',
				'currency'      => '',
			),
			is_array( $atts ) ? $atts : array(),
			'seatlayer_chart'
		);

		return self::markup( $atts );
	}
}
