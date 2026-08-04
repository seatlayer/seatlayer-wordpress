<?php
/**
 * Settings screen and stored options.
 *
 * @package SeatLayer
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin settings.
 *
 * Only ONE setting is a secret, and it is optional: a SeatLayer secret key used
 * exclusively to list the site's events in the block editor. The buyer widget
 * itself is keyless (the event key is the credential), so a site that is happy
 * typing event keys by hand never needs to store a credential here at all.
 *
 * The key is stored in wp_options, which is the only durable store a plugin has.
 * That is worth being honest about rather than implying more protection than
 * exists: anyone who can read the database or has admin access can read it. It is
 * therefore never sent to the browser, never rendered back into the settings
 * field, and the screen says plainly what the key can do.
 */
class SeatLayer_Settings {

	const OPTION_GROUP  = 'seatlayer_settings';
	const OPTION_KEY    = 'seatlayer_secret_key';
	const OPTION_API    = 'seatlayer_api_base';
	const OPTION_APP    = 'seatlayer_app_base';
	const OPTION_HOSTED = 'seatlayer_hosted_checkout';
	const DEFAULT_API   = 'https://api.seatlayer.io';
	const DEFAULT_APP   = 'https://app.seatlayer.io';

	/**
	 * Register hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * The stored secret key, or an empty string.
	 */
	public static function secret_key(): string {
		$value = get_option( self::OPTION_KEY, '' );
		return is_string( $value ) ? trim( $value ) : '';
	}

	/**
	 * API base URL. Overridable for self-hosted or staging deployments.
	 */
	public static function api_base(): string {
		$value = get_option( self::OPTION_API, self::DEFAULT_API );
		$value = is_string( $value ) && '' !== trim( $value ) ? trim( $value ) : self::DEFAULT_API;
		return untrailingslashit( $value );
	}

	/**
	 * App base URL — where the hosted buyer page and checkout live.
	 */
	public static function app_base(): string {
		$value = get_option( self::OPTION_APP, self::DEFAULT_APP );
		$value = is_string( $value ) && '' !== trim( $value ) ? trim( $value ) : self::DEFAULT_APP;
		return untrailingslashit( $value );
	}

	/**
	 * Should the buyer pay on the WordPress page instead of being redirected?
	 *
	 * Default FALSE. Turning this on has prerequisites the plugin cannot check
	 * from here (see `render_page()`), and the redirect path works for everyone,
	 * so an existing install must keep behaving exactly as it did.
	 */
	public static function hosted_checkout(): bool {
		$value = get_option( self::OPTION_HOSTED, '' );
		// Scalar check before the cast: a corrupted or hand-edited option row can
		// hold an array, and casting one to string is a PHP warning in the log of
		// every page that renders a chart.
		return is_scalar( $value ) && '1' === (string) $value;
	}

	/**
	 * This site's origin, in the exact shape SeatLayer stores embed domains in:
	 * `scheme://host[:port]`, lowercased, no path.
	 *
	 * Built by hand from the parsed parts rather than by trimming `home_url()`,
	 * because a site installed in a subdirectory has a path that must not survive
	 * — `https://example.com/blog` and `https://example.com` are one origin, and
	 * an admin copying the wrong one gets a silent non-match at payment time.
	 */
	public static function site_origin(): string {
		$parts = wp_parse_url( home_url() );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return '';
		}

		$origin = strtolower( $parts['scheme'] ) . '://' . strtolower( $parts['host'] );

		// Default ports are elided on the server side, so including them here
		// would produce a string that never matches.
		if ( ! empty( $parts['port'] ) ) {
			$port = (int) $parts['port'];
			$is_default = ( 'https' === strtolower( $parts['scheme'] ) && 443 === $port )
				|| ( 'http' === strtolower( $parts['scheme'] ) && 80 === $port );
			if ( ! $is_default ) {
				$origin .= ':' . $port;
			}
		}

		return $origin;
	}

	/**
	 * Add the settings page under Settings.
	 */
	public static function add_menu(): void {
		add_options_page(
			__( 'SeatLayer', 'seatlayer-seating-charts' ),
			__( 'SeatLayer', 'seatlayer-seating-charts' ),
			'manage_options',
			'seatlayer',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the options with sanitizers.
	 */
	public static function register(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_KEY,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_secret_key' ),
				'default'           => '',
				// Never expose a credential through the REST options endpoint.
				'show_in_rest'      => false,
			)
		);

		register_setting(
			self::OPTION_GROUP,
			self::OPTION_HOSTED,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'default'           => '',
				'show_in_rest'      => false,
			)
		);

		foreach ( array( self::OPTION_API => self::DEFAULT_API, self::OPTION_APP => self::DEFAULT_APP ) as $option => $default ) {
			register_setting(
				self::OPTION_GROUP,
				$option,
				array(
					'type'              => 'string',
					'sanitize_callback' => array( __CLASS__, 'sanitize_base_url' ),
					'default'           => $default,
					'show_in_rest'      => false,
				)
			);
		}
	}

	/**
	 * Sanitize a submitted secret key.
	 *
	 * An empty submission KEEPS the existing key rather than clearing it, because
	 * the field is rendered empty on every load (we never echo a stored secret).
	 * Without this, simply saving the page would wipe a working key. Clearing is
	 * an explicit action via the Remove button.
	 *
	 * @param mixed $value Raw submitted value.
	 */
	public static function sanitize_secret_key( $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';

		if ( '' === $value ) {
			return self::secret_key();
		}

		if ( '__remove__' === $value ) {
			return '';
		}

		// Only shape is validated here; whether the key actually works is proven
		// by the connection test, which is a far more useful signal than a regex.
		if ( 1 !== preg_match( '/^sk_(live|test)_[A-Za-z0-9]{16,}$/', $value ) ) {
			add_settings_error(
				self::OPTION_KEY,
				'seatlayer_bad_key',
				__( 'That does not look like a SeatLayer secret key. It starts with sk_live_ or sk_test_. Your previous key was kept.', 'seatlayer-seating-charts' ),
				'error'
			);
			return self::secret_key();
		}

		return $value;
	}

	/**
	 * Sanitize a checkbox to '1' or ''.
	 *
	 * An unchecked box submits nothing, and WordPress hands this callback `null`
	 * in that case — which is precisely how "off" arrives. Unlike the secret key,
	 * an absent value here must NOT keep the stored one, or the box could never
	 * be turned back off.
	 *
	 * Anything that is not the scalar '1' is off, including an array: a POST can
	 * name any field as `option[]`, and casting that to string is a PHP warning
	 * rather than a decision.
	 *
	 * @param mixed $value Raw submitted value.
	 */
	public static function sanitize_checkbox( $value ): string {
		return ( is_scalar( $value ) && '1' === (string) $value ) ? '1' : '';
	}

	/**
	 * Sanitize a base URL, refusing anything that is not http(s).
	 *
	 * @param mixed $value Raw submitted value.
	 */
	public static function sanitize_base_url( $value ): string {
		$value = is_string( $value ) ? trim( $value ) : '';
		if ( '' === $value ) {
			return '';
		}
		$clean = esc_url_raw( $value, array( 'http', 'https' ) );
		return untrailingslashit( $clean );
	}

	/**
	 * Render the settings screen.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$has_key = '' !== self::secret_key();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'SeatLayer', 'seatlayer-seating-charts' ); ?></h1>

			<p style="max-width:60em;">
				<?php
				esc_html_e(
					'Add a seating chart to any page with the SeatLayer block or the [seatlayer_chart] shortcode. Buyers pick seats on your site and pay through your own Stripe or Razorpay account — connect that in your SeatLayer dashboard under Payments.',
					'seatlayer-seating-charts'
				);
				?>
			</p>

			<?php settings_errors( self::OPTION_KEY ); ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION_GROUP ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="seatlayer_secret_key"><?php esc_html_e( 'Secret key', 'seatlayer-seating-charts' ); ?></label>
						</th>
						<td>
							<input
								type="password"
								id="seatlayer_secret_key"
								name="<?php echo esc_attr( self::OPTION_KEY ); ?>"
								value=""
								class="regular-text"
								autocomplete="off"
								placeholder="<?php echo $has_key ? esc_attr__( 'Saved — leave blank to keep', 'seatlayer-seating-charts' ) : esc_attr( 'sk_live_…' ); ?>"
							/>
							<p class="description" style="max-width:44em;">
								<?php
								esc_html_e(
									'Optional. Used only to list your events in the editor so you can pick one from a dropdown instead of pasting an event key. The buyer chart works without it.',
									'seatlayer-seating-charts'
								);
								?>
								<br />
								<strong><?php esc_html_e( 'Note:', 'seatlayer-seating-charts' ); ?></strong>
								<?php
								esc_html_e(
									'This key is stored in your WordPress database and can create and modify events. Anyone with database or administrator access can read it. If that is not acceptable, leave it blank and enter event keys by hand.',
									'seatlayer-seating-charts'
								);
								?>
							</p>
							<?php if ( $has_key ) : ?>
								<p>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>" value="__remove__" />
										<?php esc_html_e( 'Remove the saved key', 'seatlayer-seating-charts' ); ?>
									</label>
								</p>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Checkout', 'seatlayer-seating-charts' ); ?></th>
						<td>
							<label>
								<input
									type="checkbox"
									name="<?php echo esc_attr( self::OPTION_HOSTED ); ?>"
									value="1"
									<?php checked( self::hosted_checkout() ); ?>
								/>
								<?php esc_html_e( 'Let buyers pay without leaving this site', 'seatlayer-seating-charts' ); ?>
							</label>
							<p class="description" style="max-width:44em;">
								<?php
								esc_html_e(
									'Off by default. Buyers are sent to SeatLayer to pay, then return with their tickets. Turn this on and the payment step appears on this page instead.',
									'seatlayer-seating-charts'
								);
								?>
							</p>

							<p class="description" style="max-width:44em;">
								<strong><?php esc_html_e( 'Requires:', 'seatlayer-seating-charts' ); ?></strong>
								<?php
								esc_html_e(
									'in-page checkout enabled on your SeatLayer account. It is granted per account — ask SeatLayer if you are not sure. Without it this setting quietly does nothing and buyers take the normal redirect, so switching it on early breaks nothing.',
									'seatlayer-seating-charts'
								);
								?>
							</p>

							<p class="description" style="max-width:44em;">
								<strong><?php esc_html_e( 'What this changes, honestly:', 'seatlayer-seating-charts' ); ?></strong>
								<?php
								esc_html_e(
									'Razorpay collects payment entirely on this page — the buyer never leaves. Stripe cards still open Stripe\'s own page, but the buyer now returns to this exact page afterwards instead of finishing on SeatLayer. That return needs the one step below.',
									'seatlayer-seating-charts'
								);
								?>
							</p>

							<p class="description" style="max-width:44em;">
								<strong><?php esc_html_e( 'Do this, or the return is ignored:', 'seatlayer-seating-charts' ); ?></strong>
								<?php
								esc_html_e(
									'In your SeatLayer dashboard, add this site to your account\'s embed domains, exactly as shown:',
									'seatlayer-seating-charts'
								);
								?>
								<br />
								<code><?php echo esc_html( self::site_origin() ); ?></code>
								<br />
								<?php
								esc_html_e(
									'Copy the whole line. The match is exact — no wildcards, and http and https count as different entries.',
									'seatlayer-seating-charts'
								);
								?>
							</p>

							<p class="description" style="max-width:44em;">
								<?php
								esc_html_e(
									'Skipping it costs you nothing but the return trip. SeatLayer ignores a return address it was not told about rather than refusing the payment, so the buyer still pays, the seats are still sold, and the tickets are still emailed — they just finish on SeatLayer\'s page instead of back here.',
									'seatlayer-seating-charts'
								);
								?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="seatlayer_api_base"><?php esc_html_e( 'API URL', 'seatlayer-seating-charts' ); ?></label>
						</th>
						<td>
							<input
								type="url"
								id="seatlayer_api_base"
								name="<?php echo esc_attr( self::OPTION_API ); ?>"
								value="<?php echo esc_attr( self::api_base() ); ?>"
								class="regular-text code"
							/>
							<p class="description">
								<?php esc_html_e( 'Leave as-is unless SeatLayer told you otherwise.', 'seatlayer-seating-charts' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="seatlayer_app_base"><?php esc_html_e( 'Checkout URL', 'seatlayer-seating-charts' ); ?></label>
						</th>
						<td>
							<input
								type="url"
								id="seatlayer_app_base"
								name="<?php echo esc_attr( self::OPTION_APP ); ?>"
								value="<?php echo esc_attr( self::app_base() ); ?>"
								class="regular-text code"
							/>
							<p class="description">
								<?php esc_html_e( 'Where buyers are sent to pay. Leave as-is unless SeatLayer told you otherwise.', 'seatlayer-seating-charts' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Shortcode', 'seatlayer-seating-charts' ); ?></h2>
			<p><code>[seatlayer_chart event="your-event-key"]</code></p>
			<p class="description" style="max-width:60em;">
				<?php
				esc_html_e(
					'Optional attributes: height (default 640), max_selection (default 10), locale, currency. Example: [seatlayer_chart event="summer-fest" height="720" max_selection="4"]',
					'seatlayer-seating-charts'
				);
				?>
			</p>
		</div>
		<?php
	}
}
