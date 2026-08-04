/**
 * Executes the plugin's real frontend.js against a stub SDK and a minimal DOM,
 * to assert what options a SeatPicker is actually constructed with.
 *
 * Repo-only: `export-ignore` in .gitattributes keeps it out of the plugin zip.
 *
 *     node tests/frontend-options.js
 *
 * No WordPress, no browser, no jsdom — frontend.js only touches a handful of DOM
 * methods, so stubbing exactly those keeps the thing that is verified the REAL
 * file rather than a copy of it that can drift.
 */
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );

const SRC = path.resolve( __dirname, '..', 'assets', 'js', 'frontend.js' );

let fail = 0;
function ok( label, actual, expected ) {
	const pass = actual === expected;
	if ( ! pass ) fail++;
	console.log(
		`${ pass ? 'PASS' : 'FAIL' }  ${ label.padEnd( 58 ) } got ${ JSON.stringify( actual ) }`
	);
}

function makeNode( config ) {
	const children = [];
	return {
		dataset: {},
		children,
		getAttribute: ( n ) =>
			n === 'data-seatlayer' ? JSON.stringify( config ) : null,
		querySelector: () => children.find( ( c ) => c.className === 'seatlayer-error' ) || null,
		appendChild: ( c ) => children.push( c ),
	};
}

/**
 * Run frontend.js once with one chart node on the page, and return the options
 * the SDK was constructed with (or null if it never was).
 */
function run( config, { withSdk = true } = {} ) {
	const node = makeNode( config );
	let captured = null;

	const win = {
		location: {
			href: 'https://shop.example.com/tickets/?utm_source=news#seats',
			assign: () => {},
		},
	};
	if ( withSdk ) {
		win.seatlayer = {
			SeatPicker: function ( options ) {
				captured = options;
				this.render = () => ( { catch: () => {} } );
			},
		};
	}

	const doc = {
		readyState: 'complete',
		querySelectorAll: ( sel ) => ( sel === '.seatlayer-chart' ? [ node ] : [] ),
		addEventListener: () => {},
		createElement: () => ( { className: '', textContent: '' } ),
	};

	// frontend.js reads `window` and `document` as free variables.
	const fn = new Function( 'window', 'document', fs.readFileSync( SRC, 'utf8' ) );
	fn( win, doc );

	return { options: captured, node, href: win.location.href };
}

const BASE = {
	event: 'summer-fest',
	maxSelection: 4,
	apiBase: 'https://api.seatlayer.io',
	appBase: 'https://app.seatlayer.io',
	i18n: {},
};

/* ---- hosted checkout ON: returnUrl is sent, verbatim ---- */
{
	const { options, href } = run( { ...BASE, hostedCheckout: true } );
	ok( 'hosted ON: checkout option is "hosted"', options.checkout, 'hosted' );
	ok( 'hosted ON: returnUrl === window.location.href', options.returnUrl, href );
	ok(
		'hosted ON: query string survives verbatim',
		options.returnUrl.includes( '?utm_source=news' ),
		true
	);
	ok(
		'hosted ON: path survives (not just the origin)',
		options.returnUrl.includes( '/tickets/' ),
		true
	);
}

/* ---- handoff (default): NO returnUrl, and no checkout option ---- */
{
	const { options } = run( { ...BASE, hostedCheckout: false } );
	ok( 'handoff: no checkout option', options.checkout, undefined );
	ok( 'handoff: NO returnUrl sent', options.returnUrl, undefined );
}
{
	// The field absent entirely — markup rendered by an older plugin version.
	const cfg = { ...BASE };
	const { options } = run( cfg );
	ok( 'hostedCheckout absent: no checkout option', options.checkout, undefined );
	ok( 'hostedCheckout absent: NO returnUrl sent', options.returnUrl, undefined );
}

/* ---- the handoff redirect stays wired in BOTH modes (it is the fallback) ---- */
{
	const on = run( { ...BASE, hostedCheckout: true } ).options;
	const off = run( { ...BASE, hostedCheckout: false } ).options;
	ok( 'hosted ON: onCheckout fallback still present', typeof on.onCheckout, 'function' );
	ok( 'handoff: onCheckout present', typeof off.onCheckout, 'function' );
	ok(
		'hosted ON: onCheckoutUnavailable present',
		typeof on.onCheckoutUnavailable,
		'function'
	);
}

/* ---- passthrough options still land ---- */
{
	const { options } = run( {
		...BASE,
		hostedCheckout: true,
		locale: 'de',
		currency: 'EUR',
	} );
	ok( 'locale forwarded', options.locale, 'de' );
	ok( 'currency forwarded', options.currency, 'EUR' );
	ok( 'apiBase forwarded', options.apiBase, 'https://api.seatlayer.io' );
	ok( 'maxSelection forwarded', options.maxSelection, 4 );
}

/* ---- no SDK on the page: error shown, and nothing constructed ---- */
{
	const { options, node } = run( { ...BASE, hostedCheckout: true }, { withSdk: false } );
	ok( 'SDK missing: no picker constructed', options, null );
	ok( 'SDK missing: one error box appended', node.children.length, 1 );
	ok( 'SDK missing: container NOT marked mounted', node.dataset.seatlayerMounted, undefined );
}

console.log( fail === 0 ? '\nALL PASS' : `\n${ fail } FAILURE(S)` );
process.exit( fail === 0 ? 0 : 1 );
