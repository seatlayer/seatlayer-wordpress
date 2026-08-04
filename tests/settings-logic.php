<?php
/**
 * Minimal WordPress stubs so the settings class's PURE logic can be exercised
 * without a WordPress install. Only the functions the class actually reaches.
 *
 * Repo-only: `export-ignore` in .gitattributes keeps it out of the plugin zip.
 *
 *     php tests/settings-logic.php
 */
declare( strict_types = 1 );

define( 'ABSPATH', __DIR__ );

$GLOBALS['opts']   = array();
$GLOBALS['errors'] = array();
$GLOBALS['home']   = 'https://example.com';

function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['opts'] ) ? $GLOBALS['opts'][ $k ] : $d; }
function untrailingslashit( $s ) { return rtrim( (string) $s, '/\\' ); }
function add_settings_error( $s, $c, $m, $t = 'error' ) { $GLOBALS['errors'][] = $c; }
function __( $t, $d = null ) { return $t; }
function esc_url_raw( $u, $p = null ) {
	$parts = parse_url( $u );
	if ( ! $parts || empty( $parts['scheme'] ) ) { return ''; }
	if ( $p && ! in_array( strtolower( $parts['scheme'] ), $p, true ) ) { return ''; }
	return $u;
}
function home_url() { return $GLOBALS['home']; }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u ); }
function add_action() {}
function add_options_page() {}
function register_setting() {}
function current_user_can() { return true; }
function checked() {}
function settings_fields() {}
function settings_errors() {}
function submit_button() {}
function esc_html_e( $t, $d = null ) { echo $t; }
function esc_html( $t ) { return $t; }
function esc_attr( $t ) { return $t; }
function esc_attr__( $t, $d = null ) { return $t; }

require __DIR__ . '/../includes/class-seatlayer-settings.php';

$fail = 0;
function ok( string $label, $actual, $expected ): void {
	global $fail;
	$pass = $actual === $expected;
	if ( ! $pass ) { $fail++; }
	printf(
		"%s  %-52s got %s\n",
		$pass ? 'PASS' : 'FAIL',
		$label,
		var_export( $actual, true )
	);
}

/* ---- sanitize_checkbox: an unchecked box arrives as null and must mean OFF ---- */
ok( 'checkbox: checked  "1" -> on',        SeatLayer_Settings::sanitize_checkbox( '1' ), '1' );
ok( 'checkbox: unchecked null -> off',     SeatLayer_Settings::sanitize_checkbox( null ), '' );
ok( 'checkbox: absent ""   -> off',        SeatLayer_Settings::sanitize_checkbox( '' ), '' );
ok( 'checkbox: junk "yes"  -> off',        SeatLayer_Settings::sanitize_checkbox( 'yes' ), '' );
ok( 'checkbox: array       -> off',        SeatLayer_Settings::sanitize_checkbox( array( '1' ) ), '' );

/* ---- hosted_checkout() reads it back ---- */
$GLOBALS['opts']['seatlayer_hosted_checkout'] = '1';
ok( 'hosted_checkout: "1" -> true',        SeatLayer_Settings::hosted_checkout(), true );
$GLOBALS['opts']['seatlayer_hosted_checkout'] = '';
ok( 'hosted_checkout: ""  -> false',       SeatLayer_Settings::hosted_checkout(), false );
unset( $GLOBALS['opts']['seatlayer_hosted_checkout'] );
ok( 'hosted_checkout: unset -> false (default OFF)', SeatLayer_Settings::hosted_checkout(), false );

/* ---- site_origin(): must match the server's normalizeOrigin() shape ---- */
$cases = array(
	'https://example.com'            => 'https://example.com',
	'https://example.com/blog'       => 'https://example.com',
	'https://EXAMPLE.com/Blog/'      => 'https://example.com',
	'http://example.com'             => 'http://example.com',
	'https://example.com:443'        => 'https://example.com',
	'http://example.com:80'          => 'http://example.com',
	'http://localhost:8080'          => 'http://localhost:8080',
	'https://sub.example.co.uk/x?y=1'=> 'https://sub.example.co.uk',
);
foreach ( $cases as $home => $expected ) {
	$GLOBALS['home'] = $home;
	ok( 'site_origin: ' . $home, SeatLayer_Settings::site_origin(), $expected );
}
$GLOBALS['home'] = 'not a url';
ok( 'site_origin: unparseable -> ""', SeatLayer_Settings::site_origin(), '' );

/* ---- secret key sanitizer: empty KEEPS, __remove__ clears, bad shape KEEPS ---- */
$GLOBALS['home'] = 'https://example.com';
$GLOBALS['opts']['seatlayer_secret_key'] = 'sk_live_ABCDEFGHIJKLMNOP';
ok( 'key: empty submission keeps stored',  SeatLayer_Settings::sanitize_secret_key( '' ), 'sk_live_ABCDEFGHIJKLMNOP' );
ok( 'key: null submission keeps stored',   SeatLayer_Settings::sanitize_secret_key( null ), 'sk_live_ABCDEFGHIJKLMNOP' );
ok( 'key: __remove__ clears',              SeatLayer_Settings::sanitize_secret_key( '__remove__' ), '' );
ok( 'key: valid test key accepted',        SeatLayer_Settings::sanitize_secret_key( 'sk_test_0123456789ABCDEF' ), 'sk_test_0123456789ABCDEF' );
ok( 'key: garbage keeps stored',           SeatLayer_Settings::sanitize_secret_key( 'hunter2' ), 'sk_live_ABCDEFGHIJKLMNOP' );
ok( 'key: too short keeps stored',         SeatLayer_Settings::sanitize_secret_key( 'sk_live_short' ), 'sk_live_ABCDEFGHIJKLMNOP' );
ok( 'key: xss payload keeps stored',       SeatLayer_Settings::sanitize_secret_key( '<script>alert(1)</script>' ), 'sk_live_ABCDEFGHIJKLMNOP' );

/* ---- base URL sanitizer refuses non-http(s) ---- */
ok( 'url: https accepted',                 SeatLayer_Settings::sanitize_base_url( 'https://api.seatlayer.io/' ), 'https://api.seatlayer.io' );
ok( 'url: javascript: refused',            SeatLayer_Settings::sanitize_base_url( 'javascript:alert(1)' ), '' );
ok( 'url: data: refused',                  SeatLayer_Settings::sanitize_base_url( 'data:text/html,x' ), '' );
ok( 'url: ftp refused',                    SeatLayer_Settings::sanitize_base_url( 'ftp://x.example.com' ), '' );

echo $fail === 0 ? "\nALL PASS\n" : "\n$fail FAILURE(S)\n";
exit( $fail === 0 ? 0 : 1 );
