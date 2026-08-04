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
	 * Add the settings page under Settings.
	 */
	public static function add_menu(): void {
		add_options_page(
			__( 'SeatLayer', 'seatlayer' ),
			__( 'SeatLayer', 'seatlayer' ),
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
				__( 'That does not look like a SeatLayer secret key. It starts with sk_live_ or sk_test_. Your previous key was kept.', 'seatlayer' ),
				'error'
			);
			return self::secret_key();
		}

		return $value;
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
			<h1><?php esc_html_e( 'SeatLayer', 'seatlayer' ); ?></h1>

			<p style="max-width:60em;">
				<?php
				esc_html_e(
					'Add a seating chart to any page with the SeatLayer block or the [seatlayer_chart] shortcode. Buyers pick seats on your site and pay through your own Stripe or Razorpay account — connect that in your SeatLayer dashboard under Payments.',
					'seatlayer'
				);
				?>
			</p>

			<?php settings_errors( self::OPTION_KEY ); ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION_GROUP ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="seatlayer_secret_key"><?php esc_html_e( 'Secret key', 'seatlayer' ); ?></label>
						</th>
						<td>
							<input
								type="password"
								id="seatlayer_secret_key"
								name="<?php echo esc_attr( self::OPTION_KEY ); ?>"
								value=""
								class="regular-text"
								autocomplete="off"
								placeholder="<?php echo $has_key ? esc_attr__( 'Saved — leave blank to keep', 'seatlayer' ) : esc_attr( 'sk_live_…' ); ?>"
							/>
							<p class="description" style="max-width:44em;">
								<?php
								esc_html_e(
									'Optional. Used only to list your events in the editor so you can pick one from a dropdown instead of pasting an event key. The buyer chart works without it.',
									'seatlayer'
								);
								?>
								<br />
								<strong><?php esc_html_e( 'Note:', 'seatlayer' ); ?></strong>
								<?php
								esc_html_e(
									'This key is stored in your WordPress database and can create and modify events. Anyone with database or administrator access can read it. If that is not acceptable, leave it blank and enter event keys by hand.',
									'seatlayer'
								);
								?>
							</p>
							<?php if ( $has_key ) : ?>
								<p>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>" value="__remove__" />
										<?php esc_html_e( 'Remove the saved key', 'seatlayer' ); ?>
									</label>
								</p>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="seatlayer_api_base"><?php esc_html_e( 'API URL', 'seatlayer' ); ?></label>
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
								<?php esc_html_e( 'Leave as-is unless SeatLayer told you otherwise.', 'seatlayer' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="seatlayer_app_base"><?php esc_html_e( 'Checkout URL', 'seatlayer' ); ?></label>
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
								<?php esc_html_e( 'Where buyers are sent to pay. Leave as-is unless SeatLayer told you otherwise.', 'seatlayer' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<h2><?php esc_html_e( 'Shortcode', 'seatlayer' ); ?></h2>
			<p><code>[seatlayer_chart event="your-event-key"]</code></p>
			<p class="description" style="max-width:60em;">
				<?php
				esc_html_e(
					'Optional attributes: height (default 640), max_selection (default 10), locale, currency. Example: [seatlayer_chart event="summer-fest" height="720" max_selection="4"]',
					'seatlayer'
				);
				?>
			</p>
		</div>
		<?php
	}
}
