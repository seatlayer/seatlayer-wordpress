/**
 * Mounts a SeatLayer picker into every `.seatlayer-chart` container on the page.
 *
 * The whole buyer flow after seat selection is SeatLayer's, not WordPress's: the
 * widget creates the hold, and `onCheckout` hands off to the hosted checkout on
 * app.seatlayer.io, where the organizer's own Stripe or Razorpay account takes
 * the money. So this file is a mount shim, and deliberately nothing more — no
 * cart, no order state, no price arithmetic. Those all belong to the side that
 * can be trusted with them.
 *
 * Configuration arrives on a `data-seatlayer` attribute rather than an inline
 * script tag, so the plugin emits no inline JavaScript and works on sites with a
 * strict Content-Security-Policy.
 */
(function () {
	'use strict';

	/** Marks a container as handled so a re-run cannot double-mount. */
	var MOUNTED = 'seatlayerMounted';

	function readConfig( node ) {
		var raw = node.getAttribute( 'data-seatlayer' );
		if ( ! raw ) {
			return null;
		}
		try {
			return JSON.parse( raw );
		} catch ( err ) {
			return null;
		}
	}

	/**
	 * A translated string from the server-side config, or an English fallback if
	 * this page was rendered by an older version of the plugin.
	 */
	function text( config, key, fallback ) {
		return ( config && config.i18n && config.i18n[ key ] ) || fallback;
	}

	function showError( node, message ) {
		// One error box per container. Without this, a page builder re-dispatching
		// `seatlayer:refresh` after a failed mount stacks the same message again
		// and again down the page.
		var existing = node.querySelector( ':scope > .seatlayer-error' );
		if ( existing ) {
			existing.textContent = message;
			return;
		}
		var box = document.createElement( 'div' );
		box.className = 'seatlayer-error';
		// textContent, not innerHTML: `message` can carry an event key that came
		// from post content.
		box.textContent = message;
		node.appendChild( box );
	}

	function mount( node ) {
		if ( node.dataset[ MOUNTED ] === '1' ) {
			return;
		}

		var config = readConfig( node );
		if ( ! config || ! config.event ) {
			return;
		}

		var sdk = window.seatlayer || window.seatmap;
		if ( ! sdk || typeof sdk.SeatPicker !== 'function' ) {
			showError(
				node,
				text(
					config,
					'sdkUnreachable',
					'Seating chart could not load. Check that cdn.seatlayer.io is reachable from this page.'
				)
			);
			return;
		}

		node.dataset[ MOUNTED ] = '1';

		var options = {
			container: node,
			event: config.event,
			maxSelection: config.maxSelection || 10,
			confirmSelection: true,
			/**
			 * Seats are chosen; send the buyer to SeatLayer's hosted checkout.
			 *
			 * The hold id is the only thing passed along — never an amount. The
			 * server recomputes the total from its own hold records, which is what
			 * makes it impossible for a page on this site (or anyone editing it) to
			 * change what a buyer pays.
			 */
			onCheckout: function ( hold, seats, handoff ) {
				var holdId = ( handoff && handoff.holdId ) || ( hold && hold.holdId );
				if ( ! holdId ) {
					return;
				}
				var url =
					config.appBase +
					'/e/' +
					encodeURIComponent( config.event ) +
					'?hold=' +
					encodeURIComponent( holdId );
				window.location.assign( url );
			},
		};

		if ( config.apiBase ) {
			options.apiBase = config.apiBase;
		}
		if ( config.locale ) {
			options.locale = config.locale;
		}
		if ( config.currency ) {
			options.currency = config.currency;
		}

		var picker = new sdk.SeatPicker( options );
		var rendered = picker.render();
		if ( rendered && typeof rendered.catch === 'function' ) {
			rendered.catch( function () {
				showError(
					node,
					text( config, 'chartFailed', 'This seating chart is unavailable right now.' )
				);
			} );
		}
	}

	function mountAll() {
		var nodes = document.querySelectorAll( '.seatlayer-chart' );
		for ( var i = 0; i < nodes.length; i++ ) {
			mount( nodes[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', mountAll );
	} else {
		mountAll();
	}

	// Block themes and some page builders inject content after load, so a chart
	// can appear late. The mount guard makes re-running safe.
	document.addEventListener( 'seatlayer:refresh', mountAll );
} )();
